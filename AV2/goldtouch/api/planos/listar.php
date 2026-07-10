<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    json_response(['sucesso' => true, 'planos' => demo_planos(), 'demo' => true]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    $stmt = db()->prepare('SELECT * FROM planos WHERE ativo = 1 ORDER BY preco ASC');
    $stmt->execute();
    $planos = $stmt->fetchAll();

    if (empty($planos)) {
        json_response(['sucesso' => true, 'planos' => demo_planos(), 'demo' => true]);
    }
    json_response(['sucesso' => true, 'planos' => $planos]);

} catch (PDOException $e) {
    json_response(['sucesso' => true, 'planos' => demo_planos(), 'demo' => true]);
}
