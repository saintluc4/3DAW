<?php
require_once '../includes/config.php';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── CRIAR AGENDAMENTO ────────────────────────────────────────────────────────
if ($method === 'POST' && $action === 'criar') {
    exige_login();
    $cliente = cliente_logado();
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];

    $servico_id      = intval($body['servico_id']      ?? 0);
    $data            = $body['data']                   ?? '';
    $hora            = $body['hora']                   ?? '';
    $forma_pagamento = $body['forma_pagamento']        ?? '';
    $cupom_codigo    = strtoupper(trim($body['cupom']  ?? ''));

    if (!$servico_id || !$data || !$hora || !$forma_pagamento)
        json_response(['erro' => 'Dados incompletos.'], 422);

    if (!in_array($forma_pagamento, ['credito','debito','pix','dinheiro']))
        json_response(['erro' => 'Forma de pagamento inválida.'], 422);

    if (!db_ok()) {
        // modo demo
        $servicos = demo_servicos();
        $servico  = current(array_filter($servicos, fn($s) => $s['id'] == $servico_id));
        if (!$servico) json_response(['erro' => 'Serviço não encontrado.'], 404);

        $valor    = floatval($servico['preco']);
        $desconto = 0;
        if ($cupom_codigo) {
            foreach (demo_cupons() as $c) {
                if ($c['codigo'] === $cupom_codigo) {
                    $desconto = $c['desconto_percent'] ? $valor * ($c['desconto_percent']/100) : floatval($c['desconto_valor']);
                    $valor = max(0, $valor - $desconto);
                    break;
                }
            }
        }

        // Salva na sessão para simular "meus agendamentos"
        if (!isset($_SESSION['agendamentos_demo'])) $_SESSION['agendamentos_demo'] = [];
        $ag_id = count($_SESSION['agendamentos_demo']) + 1;
        $_SESSION['agendamentos_demo'][] = [
            'id'              => $ag_id,
            'data_hora'       => $data.' '.$hora.':00',
            'status'          => 'confirmado',
            'valor_pago'      => $valor,
            'forma_pagamento' => $forma_pagamento,
            'servico'         => $servico['nome'],
            'categoria'       => $servico['categoria'],
        ];
        $_SESSION['cliente']['pontos'] = ($_SESSION['cliente']['pontos'] ?? 0) + PONTOS_POR_SERVICO;

        json_response([
            'sucesso'        => true,
            'agendamento_id' => $ag_id,
            'servico'        => $servico['nome'],
            'data_hora'      => $data.' '.$hora,
            'valor'          => number_format($valor, 2, ',', '.'),
            'desconto'       => number_format($desconto, 2, ',', '.'),
            'pontos_ganhos'  => PONTOS_POR_SERVICO,
            'mensagem'       => 'Horário confirmado! Limite de espera: '.LIMITE_ESPERA_MINUTOS.' minutos.',
            'demo'           => true,
        ]);
    }

    try {
        $svc = db()->prepare('SELECT * FROM servicos WHERE id=? AND ativo=1');
        $svc->execute([$servico_id]);
        $servico = $svc->fetch();
        if (!$servico) json_response(['erro' => 'Serviço não encontrado.'], 404);

        $data_hora = $data.' '.$hora.':00';
        $conf = db()->prepare("SELECT id FROM agendamentos WHERE data_hora=? AND status NOT IN ('cancelado')");
        $conf->execute([$data_hora]);
        if ($conf->fetch()) json_response(['erro' => 'Horário já ocupado. Escolha outro.'], 409);

        $valor = floatval($servico['preco']);
        $desconto = 0;

        if ($cupom_codigo) {
            $cup = db()->prepare("SELECT * FROM cupons WHERE codigo=? AND ativo=1 AND (validade IS NULL OR validade >= CURDATE())");
            $cup->execute([$cupom_codigo]);
            $cupom = $cup->fetch();
            if (!$cupom) json_response(['erro' => 'Cupom inválido ou expirado.'], 422);
            if ($cupom['desconto_percent']) $desconto = $valor * ($cupom['desconto_percent']/100);
            elseif ($cupom['desconto_valor']) $desconto = floatval($cupom['desconto_valor']);
            $valor = max(0, $valor - $desconto);
        }

        db()->prepare("INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status, valor_pago, forma_pagamento) VALUES (?,?,?,'confirmado',?,?)")
           ->execute([$cliente['id'], $servico_id, $data_hora, $valor, $forma_pagamento]);
        $ag_id = db()->lastInsertId();

        db()->prepare('UPDATE clientes SET pontos=pontos+? WHERE id=?')->execute([PONTOS_POR_SERVICO, $cliente['id']]);
        $_SESSION['cliente']['pontos'] = ($_SESSION['cliente']['pontos'] ?? 0) + PONTOS_POR_SERVICO;

        json_response([
            'sucesso'        => true,
            'agendamento_id' => $ag_id,
            'servico'        => $servico['nome'],
            'data_hora'      => $data_hora,
            'valor'          => number_format($valor, 2, ',', '.'),
            'desconto'       => number_format($desconto, 2, ',', '.'),
            'pontos_ganhos'  => PONTOS_POR_SERVICO,
            'mensagem'       => 'Horário confirmado! Limite de espera: '.LIMITE_ESPERA_MINUTOS.' minutos.',
        ]);
    } catch (PDOException $e) {
        json_response(['erro' => 'Erro ao criar agendamento: '.$e->getMessage()], 500);
    }
}

// ── MEUS AGENDAMENTOS ────────────────────────────────────────────────────────
if ($method === 'GET' && $action === 'meus') {
    exige_login();
    $cliente = cliente_logado();

    if (!db_ok()) {
        $ags = $_SESSION['agendamentos_demo'] ?? [];
        json_response(['sucesso' => true, 'agendamentos' => array_reverse($ags), 'demo' => true]);
    }

    try {
        $stmt = db()->prepare(
            "SELECT a.id, a.data_hora, a.status, a.valor_pago, a.forma_pagamento,
                    s.nome AS servico, s.categoria
             FROM agendamentos a JOIN servicos s ON s.id=a.servico_id
             WHERE a.cliente_id=? ORDER BY a.data_hora DESC LIMIT 20"
        );
        $stmt->execute([$cliente['id']]);
        json_response(['sucesso' => true, 'agendamentos' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        $ags = $_SESSION['agendamentos_demo'] ?? [];
        json_response(['sucesso' => true, 'agendamentos' => array_reverse($ags), 'demo' => true]);
    }
}

// ── CANCELAR ─────────────────────────────────────────────────────────────────
if ($method === 'POST' && $action === 'cancelar') {
    exige_login();
    $cliente = cliente_logado();
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $ag_id   = intval($body['agendamento_id'] ?? 0);
    if (!$ag_id) json_response(['erro' => 'ID inválido.'], 422);

    if (!db_ok()) {
        if (isset($_SESSION['agendamentos_demo'])) {
            foreach ($_SESSION['agendamentos_demo'] as &$ag) {
                if ($ag['id'] == $ag_id) { $ag['status'] = 'cancelado'; break; }
            }
        }
        json_response(['sucesso' => true, 'mensagem' => 'Agendamento cancelado.', 'demo' => true]);
    }

    try {
        $stmt = db()->prepare("SELECT * FROM agendamentos WHERE id=? AND cliente_id=? AND status='confirmado'");
        $stmt->execute([$ag_id, $cliente['id']]);
        $ag = $stmt->fetch();
        if (!$ag) json_response(['erro' => 'Agendamento não encontrado.'], 404);
        if (strtotime($ag['data_hora']) - time() < 7200)
            json_response(['erro' => 'Cancelamento só permitido com 2 horas de antecedência.'], 422);
        db()->prepare("UPDATE agendamentos SET status='cancelado' WHERE id=?")->execute([$ag_id]);
        json_response(['sucesso' => true, 'mensagem' => 'Agendamento cancelado.']);
    } catch (PDOException $e) {
        json_response(['erro' => 'Erro ao cancelar.'], 500);
    }
}

json_response(['erro' => 'Rota não encontrada.'], 404);
