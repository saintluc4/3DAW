<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

$body  = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($body['email'] ?? '');
$senha = $body['senha']      ?? '';

if (!$email || !$senha) {
    json_response(['erro' => 'Informe e-mail e senha.'], 422);
}

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    $nome = ucfirst(explode('@', $email)[0]);
    $_SESSION['cliente'] = ['id' => 1, 'nome' => $nome, 'email' => $email, 'pontos' => 150, 'demo' => true];
    json_response(['sucesso' => true, 'cliente' => $_SESSION['cliente'], 'demo' => true]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    $stmt = db()->prepare('SELECT * FROM clientes WHERE email = ?');
    $stmt->execute([$email]);
    $cliente = $stmt->fetch();

    if (!$cliente || !password_verify($senha, $cliente['senha_hash'])) {
        json_response(['erro' => 'E-mail ou senha incorretos.'], 401);
    }

    $_SESSION['cliente'] = [
        'id'     => $cliente['id'],
        'nome'   => $cliente['nome'],
        'email'  => $cliente['email'],
        'pontos' => $cliente['pontos'],
    ];
    json_response(['sucesso' => true, 'cliente' => $_SESSION['cliente']]);

} catch (PDOException $e) {
    json_response(['erro' => 'Erro ao fazer login.'], 500);
}
