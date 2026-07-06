<?php
require_once '../includes/config.php';
$method   = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';
$action   = $_GET['action']   ?? '';

// ════════════════════════════════════════════════════════
// AVALIAÇÕES
// ════════════════════════════════════════════════════════
if ($endpoint === 'avaliacoes') {

    if ($method === 'GET' && $action === 'listar') {
        if (!db_ok()) {
            json_response(['sucesso' => true, 'avaliacoes' => demo_avaliacoes(), 'demo' => true]);
        }
        try {
            $stmt = db()->prepare(
                "SELECT av.nota, av.comentario, av.criado_em,
                        c.nome AS cliente, s.nome AS servico
                 FROM avaliacoes av
                 JOIN clientes c  ON c.id  = av.cliente_id
                 JOIN agendamentos ag ON ag.id = av.agendamento_id
                 JOIN servicos s  ON s.id  = ag.servico_id
                 WHERE av.comentario != ''
                 ORDER BY av.criado_em DESC LIMIT 10"
            );
            $stmt->execute();
            json_response(['sucesso' => true, 'avaliacoes' => $stmt->fetchAll()]);
        } catch (PDOException $e) {
            json_response(['sucesso' => true, 'avaliacoes' => demo_avaliacoes(), 'demo' => true]);
        }
    }

    if ($method === 'POST' && $action === 'enviar') {
        exige_login();
        $body           = json_decode(file_get_contents('php://input'), true) ?? [];
        $agendamento_id = intval($body['agendamento_id'] ?? 0);
        $nota           = $body['nota']        ?? '';
        $comentario     = trim($body['comentario'] ?? '');
        $notas_validas  = ['satisfeito','pouco_satisfeito','insatisfeito'];

        if (!$agendamento_id || !in_array($nota, $notas_validas))
            json_response(['erro' => 'Dados inválidos.'], 422);

        if (!db_ok()) {
            json_response(['sucesso' => true, 'mensagem' => 'Obrigado pelo seu feedback! Sua opinião é muito importante para nós.', 'demo' => true]);
        }

        try {
            $cliente = cliente_logado();
            $ag = db()->prepare("SELECT id FROM agendamentos WHERE id=? AND cliente_id=? AND status IN ('concluido','confirmado')");
            $ag->execute([$agendamento_id, $cliente['id']]);
            if (!$ag->fetch()) json_response(['erro' => 'Agendamento não encontrado ou não avaliável.'], 404);

            $dup = db()->prepare('SELECT id FROM avaliacoes WHERE agendamento_id=?');
            $dup->execute([$agendamento_id]);
            if ($dup->fetch()) json_response(['erro' => 'Este agendamento já foi avaliado.'], 409);

            db()->prepare('INSERT INTO avaliacoes (agendamento_id, cliente_id, nota, comentario) VALUES (?,?,?,?)')
               ->execute([$agendamento_id, $cliente['id'], $nota, $comentario]);

            json_response(['sucesso' => true, 'mensagem' => 'Obrigado pelo seu feedback! Sua opinião é muito importante para nós.']);
        } catch (PDOException $e) {
            json_response(['erro' => 'Erro ao salvar avaliação.'], 500);
        }
    }
}

// ════════════════════════════════════════════════════════
// CUPONS
// ════════════════════════════════════════════════════════
if ($endpoint === 'cupons') {

    if ($method === 'GET' && $action === 'listar') {
        if (!db_ok()) {
            json_response(['sucesso' => true, 'cupons' => demo_cupons(), 'demo' => true]);
        }
        try {
            $stmt = db()->prepare("SELECT codigo, desconto_percent, desconto_valor, pontos_necessarios, validade FROM cupons WHERE ativo=1 AND (validade IS NULL OR validade >= CURDATE()) ORDER BY pontos_necessarios ASC");
            $stmt->execute();
            $cupons = $stmt->fetchAll();
            if (empty($cupons)) json_response(['sucesso' => true, 'cupons' => demo_cupons(), 'demo' => true]);
            json_response(['sucesso' => true, 'cupons' => $cupons]);
        } catch (PDOException $e) {
            json_response(['sucesso' => true, 'cupons' => demo_cupons(), 'demo' => true]);
        }
    }

    if ($method === 'POST' && $action === 'validar') {
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $codigo = strtoupper(trim($body['codigo'] ?? ''));
        if (!$codigo) json_response(['erro' => 'Informe o código.'], 422);

        if (!db_ok()) {
            // valida contra os cupons demo
            $cupons = demo_cupons();
            $encontrado = null;
            foreach ($cupons as $c) { if ($c['codigo'] === $codigo) { $encontrado = $c; break; } }
            if (!$encontrado) json_response(['valido' => false, 'erro' => 'Cupom inválido ou expirado.']);
            $desc = $encontrado['desconto_percent'] ? $encontrado['desconto_percent'].'%' : 'R$ '.number_format($encontrado['desconto_valor'],2,',','.');
            json_response(['valido' => true, 'desconto' => $desc, 'pontos_necessarios' => $encontrado['pontos_necessarios'], 'demo' => true]);
        }

        try {
            $stmt = db()->prepare("SELECT * FROM cupons WHERE codigo=? AND ativo=1 AND (validade IS NULL OR validade >= CURDATE())");
            $stmt->execute([$codigo]);
            $cupom = $stmt->fetch();
            if (!$cupom) json_response(['valido' => false, 'erro' => 'Cupom inválido ou expirado.']);
            $desc = $cupom['desconto_percent'] ? $cupom['desconto_percent'].'%' : 'R$ '.number_format($cupom['desconto_valor'],2,',','.');
            json_response(['valido' => true, 'desconto' => $desc, 'pontos_necessarios' => $cupom['pontos_necessarios']]);
        } catch (PDOException $e) {
            json_response(['valido' => false, 'erro' => 'Erro ao validar cupom.']);
        }
    }
}

// ════════════════════════════════════════════════════════
// PLANOS
// ════════════════════════════════════════════════════════
if ($endpoint === 'planos') {
    if ($method === 'GET' && $action === 'listar') {
        if (!db_ok()) {
            json_response(['sucesso' => true, 'planos' => demo_planos(), 'demo' => true]);
        }
        try {
            $stmt = db()->prepare('SELECT * FROM planos WHERE ativo=1 ORDER BY preco ASC');
            $stmt->execute();
            $planos = $stmt->fetchAll();
            if (empty($planos)) json_response(['sucesso' => true, 'planos' => demo_planos(), 'demo' => true]);
            json_response(['sucesso' => true, 'planos' => $planos]);
        } catch (PDOException $e) {
            json_response(['sucesso' => true, 'planos' => demo_planos(), 'demo' => true]);
        }
    }
}

json_response(['erro' => 'Rota não encontrada.'], 404);
