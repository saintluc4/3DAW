<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

exige_login();

$cliente = cliente_logado();
$body    = json_decode(file_get_contents('php://input'), true) ?? [];

$servico_id      = intval($body['servico_id']      ?? 0);
$data            = $body['data']                   ?? '';
$hora            = $body['hora']                   ?? '';
$forma_pagamento = $body['forma_pagamento']        ?? '';
$cupom_codigo    = strtoupper(trim($body['cupom']  ?? ''));

if (!$servico_id || !$data || !$hora || !$forma_pagamento) {
    json_response(['erro' => 'Dados incompletos.'], 422);
}
if (!in_array($forma_pagamento, ['credito', 'debito', 'pix', 'dinheiro'])) {
    json_response(['erro' => 'Forma de pagamento inválida.'], 422);
}

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    $servicos = demo_servicos();
    $servico  = current(array_filter($servicos, fn($s) => $s['id'] == $servico_id));
    if (!$servico) json_response(['erro' => 'Serviço não encontrado.'], 404);

    $valor    = floatval($servico['preco']);
    $desconto = 0;

    if ($cupom_codigo) {
        foreach (demo_cupons() as $c) {
            if ($c['codigo'] === $cupom_codigo) {
                $desconto = $c['desconto_percent']
                    ? $valor * ($c['desconto_percent'] / 100)
                    : floatval($c['desconto_valor'] ?? 0);
                $valor = max(0, $valor - $desconto);
                break;
            }
        }
    }

    if (!isset($_SESSION['agendamentos_demo'])) $_SESSION['agendamentos_demo'] = [];
    $ag_id = count($_SESSION['agendamentos_demo']) + 1;

    $_SESSION['agendamentos_demo'][] = [
        'id'              => $ag_id,
        'data_hora'       => $data . ' ' . $hora . ':00',
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
        'data_hora'      => $data . ' ' . $hora,
        'valor'          => number_format($valor,    2, ',', '.'),
        'desconto'       => number_format($desconto, 2, ',', '.'),
        'pontos_ganhos'  => PONTOS_POR_SERVICO,
        'mensagem'       => 'Horário confirmado! Limite de espera: ' . LIMITE_ESPERA_MINUTOS . ' minutos.',
        'demo'           => true,
    ]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    $svc = db()->prepare('SELECT * FROM servicos WHERE id = ? AND ativo = 1');
    $svc->execute([$servico_id]);
    $servico = $svc->fetch();
    if (!$servico) json_response(['erro' => 'Serviço não encontrado.'], 404);

    $data_hora = $data . ' ' . $hora . ':00';

    $conf = db()->prepare(
        "SELECT id FROM agendamentos
         WHERE data_hora = ? AND status NOT IN ('cancelado')"
    );
    $conf->execute([$data_hora]);
    if ($conf->fetch()) json_response(['erro' => 'Horário já ocupado. Escolha outro.'], 409);

    $valor    = floatval($servico['preco']);
    $desconto = 0;

    if ($cupom_codigo) {
        $cup = db()->prepare(
            "SELECT * FROM cupons
             WHERE codigo = ? AND ativo = 1
             AND (validade IS NULL OR validade >= CURDATE())"
        );
        $cup->execute([$cupom_codigo]);
        $cupom = $cup->fetch();
        if (!$cupom) json_response(['erro' => 'Cupom inválido ou expirado.'], 422);

        if ($cupom['pontos_necessarios'] > 0) {
            $pts = db()->prepare('SELECT pontos FROM clientes WHERE id = ?');
            $pts->execute([$cliente['id']]);
            if (($pts->fetch()['pontos'] ?? 0) < $cupom['pontos_necessarios']) {
                json_response(['erro' => 'Pontos insuficientes para este cupom.'], 422);
            }
        }

        $desconto = $cupom['desconto_percent']
            ? $valor * ($cupom['desconto_percent'] / 100)
            : floatval($cupom['desconto_valor'] ?? 0);
        $valor = max(0, $valor - $desconto);

        db()->prepare('INSERT INTO cupons_resgatados (cliente_id, cupom_id) VALUES (?, ?)')
           ->execute([$cliente['id'], $cupom['id']]);
    }

    db()->prepare(
        "INSERT INTO agendamentos
            (cliente_id, servico_id, data_hora, status, valor_pago, forma_pagamento)
         VALUES (?, ?, ?, 'confirmado', ?, ?)"
    )->execute([$cliente['id'], $servico_id, $data_hora, $valor, $forma_pagamento]);

    $ag_id = db()->lastInsertId();

    db()->prepare('UPDATE clientes SET pontos = pontos + ? WHERE id = ?')
       ->execute([PONTOS_POR_SERVICO, $cliente['id']]);
    $_SESSION['cliente']['pontos'] = ($_SESSION['cliente']['pontos'] ?? 0) + PONTOS_POR_SERVICO;

    json_response([
        'sucesso'        => true,
        'agendamento_id' => $ag_id,
        'servico'        => $servico['nome'],
        'data_hora'      => $data_hora,
        'valor'          => number_format($valor,    2, ',', '.'),
        'desconto'       => number_format($desconto, 2, ',', '.'),
        'pontos_ganhos'  => PONTOS_POR_SERVICO,
        'mensagem'       => 'Horário confirmado! Limite de espera: ' . LIMITE_ESPERA_MINUTOS . ' minutos.',
    ]);

} catch (PDOException $e) {
    json_response(['erro' => 'Erro ao criar agendamento: ' . $e->getMessage()], 500);
}
