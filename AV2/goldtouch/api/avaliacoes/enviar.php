<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

exige_login();

$cliente        = cliente_logado();
$body           = json_decode(file_get_contents('php://input'), true) ?? [];
$agendamento_id = intval($body['agendamento_id'] ?? 0);
$nota           = $body['nota']                  ?? '';
$comentario     = trim($body['comentario']       ?? '');
$notas_validas  = ['satisfeito', 'pouco_satisfeito', 'insatisfeito'];

if (!$agendamento_id || !in_array($nota, $notas_validas)) {
    json_response(['erro' => 'Dados inválidos.'], 422);
}

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    json_response([
        'sucesso'  => true,
        'mensagem' => 'Obrigado pelo seu feedback! Sua opinião é muito importante para nós.',
        'demo'     => true,
    ]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    $ag = db()->prepare(
        "SELECT id FROM agendamentos
         WHERE id = ? AND cliente_id = ? AND status IN ('concluido', 'confirmado')"
    );
    $ag->execute([$agendamento_id, $cliente['id']]);
    if (!$ag->fetch()) {
        json_response(['erro' => 'Agendamento não encontrado ou não avaliável.'], 404);
    }

    $dup = db()->prepare('SELECT id FROM avaliacoes WHERE agendamento_id = ?');
    $dup->execute([$agendamento_id]);
    if ($dup->fetch()) {
        json_response(['erro' => 'Este agendamento já foi avaliado.'], 409);
    }

    db()->prepare(
        'INSERT INTO avaliacoes (agendamento_id, cliente_id, nota, comentario) VALUES (?, ?, ?, ?)'
    )->execute([$agendamento_id, $cliente['id'], $nota, $comentario]);

    json_response([
        'sucesso'  => true,
        'mensagem' => 'Obrigado pelo seu feedback! Sua opinião é muito importante para nós.',
    ]);

} catch (PDOException $e) {
    json_response(['erro' => 'Erro ao salvar avaliação.'], 500);
}
