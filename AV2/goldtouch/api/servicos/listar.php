<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

$categoria = $_GET['categoria'] ?? '';

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    $lista = demo_servicos();
    if ($categoria) {
        $lista = array_values(array_filter($lista, fn($s) => $s['categoria'] === $categoria));
    }
    json_response(['sucesso' => true, 'servicos' => $lista, 'demo' => true]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    if ($categoria) {
        $stmt = db()->prepare('SELECT * FROM servicos WHERE ativo = 1 AND categoria = ? ORDER BY preco ASC');
        $stmt->execute([$categoria]);
    } else {
        $stmt = db()->prepare('SELECT * FROM servicos WHERE ativo = 1 ORDER BY categoria, preco ASC');
        $stmt->execute();
    }
    json_response(['sucesso' => true, 'servicos' => $stmt->fetchAll()]);

} catch (PDOException $e) {
    json_response(['erro' => 'Erro ao buscar serviços.'], 500);
}
