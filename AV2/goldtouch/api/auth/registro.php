<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$nome     = trim($body['nome']     ?? '');
$email    = trim($body['email']    ?? '');
$telefone = trim($body['telefone'] ?? '');
$senha    = $body['senha']         ?? '';

if (!$nome || !$email || !$senha) {
    json_response(['erro' => 'Preencha nome, e-mail e senha.'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['erro' => 'E-mail inválido.'], 422);
}
if (strlen($senha) < 6) {
    json_response(['erro' => 'Senha deve ter ao menos 6 caracteres.'], 422);
}

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    $_SESSION['cliente'] = [
        'id'     => rand(100, 999),
        'nome'   => $nome,
        'email'  => $email,
        'pontos' => 0,
        'demo'   => true,
    ];
    json_response(['sucesso' => true, 'cliente' => $_SESSION['cliente'], 'demo' => true]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    $chk = db()->prepare('SELECT id FROM clientes WHERE email = ?');
    $chk->execute([$email]);
    if ($chk->fetch()) {
        json_response(['erro' => 'E-mail já cadastrado.'], 409);
    }

    $hash = password_hash($senha, PASSWORD_BCRYPT);
    db()->prepare('INSERT INTO clientes (nome, email, telefone, senha_hash) VALUES (?, ?, ?, ?)')
       ->execute([$nome, $email, $telefone, $hash]);

    $id = db()->lastInsertId();
    $_SESSION['cliente'] = ['id' => $id, 'nome' => $nome, 'email' => $email, 'pontos' => 0];
    json_response(['sucesso' => true, 'cliente' => $_SESSION['cliente']]);

} catch (PDOException $e) {
    json_response(['erro' => 'Erro ao criar conta: ' . $e->getMessage()], 500);
}
