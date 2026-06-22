<?php
// Configurações do banco de dados
// Altere conforme seu ambiente

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // seu usuário MySQL
define('DB_PASS', 'sua_senha');  // sua senha MySQL
define('DB_NAME', 'goldtouch');
define('DB_CHARSET', 'utf8mb4');

// Configurações gerais
define('SITE_NAME', 'Gold Touch');
define('SITE_URL', 'http://localhost/goldtouch');
define('LIMITE_ESPERA_MINUTOS', 15);
define('PONTOS_POR_SERVICO', 50);

// Sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    }
    return $pdo;
}

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function cliente_logado(): ?array {
    return $_SESSION['cliente'] ?? null;
}

function exige_login(): void {
    if (!cliente_logado()) {
        json_response(['erro' => 'Não autenticado'], 401);
    }
}

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}
