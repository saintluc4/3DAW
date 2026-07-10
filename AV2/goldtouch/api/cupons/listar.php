<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    json_response(['sucesso' => true, 'cupons' => demo_cupons(), 'demo' => true]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    $stmt = db()->prepare(
        "SELECT codigo, desconto_percent, desconto_valor, pontos_necessarios, validade
         FROM cupons
         WHERE ativo = 1 AND (validade IS NULL OR validade >= CURDATE())
         ORDER BY pontos_necessarios ASC"
    );
    $stmt->execute();
    $cupons = $stmt->fetchAll();

    if (empty($cupons)) {
        json_response(['sucesso' => true, 'cupons' => demo_cupons(), 'demo' => true]);
    }
    json_response(['sucesso' => true, 'cupons' => $cupons]);

} catch (PDOException $e) {
    json_response(['sucesso' => true, 'cupons' => demo_cupons(), 'demo' => true]);
}
