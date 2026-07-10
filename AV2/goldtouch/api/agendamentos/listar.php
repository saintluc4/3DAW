<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

exige_login();

$cliente = cliente_logado();

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    $ags = $_SESSION['agendamentos_demo'] ?? [];
    json_response(['sucesso' => true, 'agendamentos' => array_reverse($ags), 'demo' => true]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
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
