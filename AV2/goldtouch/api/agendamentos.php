<?php
require_once '../includes/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ---------- CRIAR AGENDAMENTO ----------
if ($method === 'POST' && $action === 'criar') {
    exige_login();
    $cliente = cliente_logado();
    $body    = json_decode(file_get_contents('php://input'), true);

    $servico_id      = intval($body['servico_id']      ?? 0);
    $data            = $body['data']                   ?? '';  // YYYY-MM-DD
    $hora            = $body['hora']                   ?? '';  // HH:MM
    $forma_pagamento = $body['forma_pagamento']        ?? '';
    $cupom_codigo    = trim($body['cupom']             ?? '');

    if (!$servico_id || !$data || !$hora || !$forma_pagamento) {
        json_response(['erro' => 'Dados incompletos.'], 422);
    }

    $formas_validas = ['credito', 'debito', 'pix', 'dinheiro'];
    if (!in_array($forma_pagamento, $formas_validas)) {
        json_response(['erro' => 'Forma de pagamento inválida.'], 422);
    }

    $data_hora = $data . ' ' . $hora . ':00';
    if ($data_hora < date('Y-m-d H:i:s')) {
        json_response(['erro' => 'Data/hora não pode ser no passado.'], 422);
    }

    try {
        // Verifica se serviço existe
        $svc = db()->prepare('SELECT * FROM servicos WHERE id = ? AND ativo = 1');
        $svc->execute([$servico_id]);
        $servico = $svc->fetch();
        if (!$servico) json_response(['erro' => 'Serviço não encontrado.'], 404);

        // Verifica conflito de horário
        $conf = db()->prepare(
            "SELECT id FROM agendamentos
             WHERE data_hora = ? AND status NOT IN ('cancelado')"
        );
        $conf->execute([$data_hora]);
        if ($conf->fetch()) {
            json_response(['erro' => 'Horário já ocupado. Escolha outro.'], 409);
        }

        // Aplica cupom se fornecido
        $valor     = floatval($servico['preco']);
        $desconto  = 0;
        $cupom_id  = null;

        if ($cupom_codigo) {
            $cup = db()->prepare(
                "SELECT * FROM cupons
                 WHERE codigo = ? AND ativo = 1 AND (validade IS NULL OR validade >= CURDATE())"
            );
            $cup->execute([$cupom_codigo]);
            $cupom = $cup->fetch();

            if (!$cupom) {
                json_response(['erro' => 'Cupom inválido ou expirado.'], 422);
            }
            // Verifica pontos do cliente
            if ($cupom['pontos_necessarios'] > 0) {
                $pts = db()->prepare('SELECT pontos FROM clientes WHERE id = ?');
                $pts->execute([$cliente['id']]);
                $row = $pts->fetch();
                if ($row['pontos'] < $cupom['pontos_necessarios']) {
                    json_response(['erro' => 'Pontos insuficientes para este cupom.'], 422);
                }
            }

            $cupom_id = $cupom['id'];
            if ($cupom['desconto_percent']) {
                $desconto = $valor * ($cupom['desconto_percent'] / 100);
            } elseif ($cupom['desconto_valor']) {
                $desconto = floatval($cupom['desconto_valor']);
            }
            $valor = max(0, $valor - $desconto);
        }

        // Insere agendamento
        $ins = db()->prepare(
            "INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status, valor_pago, forma_pagamento)
             VALUES (?, ?, ?, 'confirmado', ?, ?)"
        );
        $ins->execute([$cliente['id'], $servico_id, $data_hora, $valor, $forma_pagamento]);
        $ag_id = db()->lastInsertId();

        // Deduz pontos do cupom e marca cupom como usado
        if ($cupom_id) {
            $cup_data = db()->prepare('SELECT pontos_necessarios FROM cupons WHERE id = ?');
            $cup_data->execute([$cupom_id]);
            $pontos_custo = $cup_data->fetch()['pontos_necessarios'] ?? 0;
            if ($pontos_custo > 0) {
                db()->prepare('UPDATE clientes SET pontos = pontos - ? WHERE id = ?')
                    ->execute([$pontos_custo, $cliente['id']]);
            }
            db()->prepare('INSERT INTO cupons_resgatados (cliente_id, cupom_id) VALUES (?, ?)')
                ->execute([$cliente['id'], $cupom_id]);
        }

        // Adiciona pontos pelo serviço
        db()->prepare('UPDATE clientes SET pontos = pontos + ? WHERE id = ?')
            ->execute([PONTOS_POR_SERVICO, $cliente['id']]);

        // Atualiza sessão com novos pontos
        $novos_pts = db()->prepare('SELECT pontos FROM clientes WHERE id = ?');
        $novos_pts->execute([$cliente['id']]);
        $_SESSION['cliente']['pontos'] = $novos_pts->fetch()['pontos'];

        json_response([
            'sucesso'       => true,
            'agendamento_id' => $ag_id,
            'servico'       => $servico['nome'],
            'data_hora'     => $data_hora,
            'valor'         => number_format($valor, 2, ',', '.'),
            'desconto'      => number_format($desconto, 2, ',', '.'),
            'pontos_ganhos' => PONTOS_POR_SERVICO,
            'mensagem'      => 'Horário confirmado! Limite de espera: ' . LIMITE_ESPERA_MINUTOS . ' minutos.',
        ]);

    } catch (PDOException $e) {
        json_response(['erro' => 'Erro ao criar agendamento: ' . $e->getMessage()], 500);
    }
}

// ---------- LISTAR AGENDAMENTOS DO CLIENTE ----------
if ($method === 'GET' && $action === 'meus') {
    exige_login();
    $cliente = cliente_logado();

    try {
        $stmt = db()->prepare(
            "SELECT a.id, a.data_hora, a.status, a.valor_pago, a.forma_pagamento,
                    s.nome AS servico, s.categoria
             FROM agendamentos a
             JOIN servicos s ON s.id = a.servico_id
             WHERE a.cliente_id = ?
             ORDER BY a.data_hora DESC
             LIMIT 20"
        );
        $stmt->execute([$cliente['id']]);
        json_response(['sucesso' => true, 'agendamentos' => $stmt->fetchAll()]);

    } catch (PDOException $e) {
        json_response(['erro' => 'Erro ao buscar agendamentos.'], 500);
    }
}

// ---------- CANCELAR AGENDAMENTO ----------
if ($method === 'POST' && $action === 'cancelar') {
    exige_login();
    $cliente = cliente_logado();
    $body    = json_decode(file_get_contents('php://input'), true);
    $ag_id   = intval($body['agendamento_id'] ?? 0);

    if (!$ag_id) json_response(['erro' => 'ID inválido.'], 422);

    try {
        $stmt = db()->prepare(
            "SELECT * FROM agendamentos WHERE id = ? AND cliente_id = ? AND status = 'confirmado'"
        );
        $stmt->execute([$ag_id, $cliente['id']]);
        $ag = $stmt->fetch();
        if (!$ag) json_response(['erro' => 'Agendamento não encontrado.'], 404);

        // Só permite cancelar com mais de 2h de antecedência
        if (strtotime($ag['data_hora']) - time() < 7200) {
            json_response(['erro' => 'Cancelamento só é permitido com 2 horas de antecedência.'], 422);
        }

        db()->prepare("UPDATE agendamentos SET status = 'cancelado' WHERE id = ?")
            ->execute([$ag_id]);

        json_response(['sucesso' => true, 'mensagem' => 'Agendamento cancelado.']);

    } catch (PDOException $e) {
        json_response(['erro' => 'Erro ao cancelar.'], 500);
    }
}

json_response(['erro' => 'Rota não encontrada.'], 404);
