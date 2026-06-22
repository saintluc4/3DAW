<?php
require_once '../includes/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ---------- REGISTRO ----------
if ($method === 'POST' && $action === 'registro') {
    $body = json_decode(file_get_contents('php://input'), true);

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

    try {
        $stmt = db()->prepare('SELECT id FROM clientes WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            json_response(['erro' => 'E-mail já cadastrado.'], 409);
        }

        $hash = password_hash($senha, PASSWORD_BCRYPT);
        $ins  = db()->prepare(
            'INSERT INTO clientes (nome, email, telefone, senha_hash) VALUES (?, ?, ?, ?)'
        );
        $ins->execute([$nome, $email, $telefone, $hash]);
        $id = db()->lastInsertId();

        $_SESSION['cliente'] = ['id' => $id, 'nome' => $nome, 'email' => $email, 'pontos' => 0];
        json_response(['sucesso' => true, 'cliente' => $_SESSION['cliente']]);

    } catch (PDOException $e) {
        json_response(['erro' => 'Erro no banco de dados.'], 500);
    }
}

// ---------- LOGIN ----------
if ($method === 'POST' && $action === 'login') {
    $body  = json_decode(file_get_contents('php://input'), true);
    $email = trim($body['email'] ?? '');
    $senha = $body['senha']      ?? '';

    if (!$email || !$senha) {
        json_response(['erro' => 'Informe e-mail e senha.'], 422);
    }

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
        json_response(['erro' => 'Erro no banco de dados.'], 500);
    }
}

// ---------- LOGOUT ----------
if ($method === 'POST' && $action === 'logout') {
    session_destroy();
    json_response(['sucesso' => true]);
}

// ---------- SESSÃO ATUAL ----------
if ($method === 'GET' && $action === 'sessao') {
    $c = cliente_logado();
    json_response($c ? ['logado' => true, 'cliente' => $c] : ['logado' => false]);
}

json_response(['erro' => 'Rota não encontrada.'], 404);
