<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$codigo = strtoupper(trim($body['codigo'] ?? ''));

if (!$codigo) {
    json_response(['erro' => 'Informe o código do cupom.'], 422);
}

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    $encontrado = null;
    foreach (demo_cupons() as $c) {
        if ($c['codigo'] === $codigo) { $encontrado = $c; break; }
    }
    if (!$encontrado) {
        json_response(['valido' => false, 'erro' => 'Cupom inválido ou expirado.']);
    }
    $desc = $encontrado['desconto_percent']
        ? $encontrado['desconto_percent'] . '%'
        : 'R$ ' . number_format($encontrado['desconto_valor'], 2, ',', '.');

    json_response([
        'valido'              => true,
        'desconto'            => $desc,
        'pontos_necessarios'  => $encontrado['pontos_necessarios'],
        'demo'                => true,
    ]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    $stmt = db()->prepare(
        "SELECT * FROM cupons
         WHERE codigo = ? AND ativo = 1
         AND (validade IS NULL OR validade >= CURDATE())"
    );
    $stmt->execute([$codigo]);
    $cupom = $stmt->fetch();

    if (!$cupom) {
        json_response(['valido' => false, 'erro' => 'Cupom inválido ou expirado.']);
    }

    $desc = $cupom['desconto_percent']
        ? $cupom['desconto_percent'] . '%'
        : 'R$ ' . number_format($cupom['desconto_valor'], 2, ',', '.');

    json_response([
        'valido'             => true,
        'desconto'           => $desc,
        'pontos_necessarios' => $cupom['pontos_necessarios'],
    ]);

} catch (PDOException $e) {
    json_response(['valido' => false, 'erro' => 'Erro ao validar cupom.']);
}
