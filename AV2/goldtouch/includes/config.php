<?php
// ============================================================
// Gold Touch — Configuração global
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // vazio = XAMPP sem senha
define('DB_NAME', 'goldtouch');
define('DB_CHARSET', 'utf8mb4');

define('PONTOS_POR_SERVICO',    50);
define('LIMITE_ESPERA_MINUTOS', 15);

if (session_status() === PHP_SESSION_NONE) session_start();

// ------------------------------------------------------------
// Conexão PDO — Singleton
// Retorna null se banco não estiver disponível (modo demo)
// ------------------------------------------------------------
function db(): ?PDO {
    static $pdo    = null;
    static $tentou = false;
    if ($tentou) return $pdo;
    $tentou = true;
    try {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (Exception $e) {
        $pdo = null;
    }
    return $pdo;
}

function db_ok(): bool {
    return db() !== null;
}

// ------------------------------------------------------------
// Resposta JSON padronizada
// Toda rota termina chamando esta função
// ------------------------------------------------------------
function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ------------------------------------------------------------
// Autenticação
// ------------------------------------------------------------
function cliente_logado(): ?array {
    return $_SESSION['cliente'] ?? null;
}

function exige_login(): void {
    if (!cliente_logado()) {
        json_response(['erro' => 'Não autenticado. Faça login.'], 401);
    }
}

// ------------------------------------------------------------
// Dados de demonstração (quando banco não conectado)
// ------------------------------------------------------------
function demo_servicos(): array {
    return [
        ['id'=>1,'nome'=>'Corte feminino',       'descricao'=>'Corte personalizado com lavagem e finalização profissional.',       'preco'=>'80.00', 'duracao_minutos'=>60,  'categoria'=>'cabelo'],
        ['id'=>2,'nome'=>'Coloração completa',   'descricao'=>'Coloração com tintura profissional e hidratação inclusa.',          'preco'=>'250.00','duracao_minutos'=>180, 'categoria'=>'cabelo'],
        ['id'=>3,'nome'=>'Progressiva',          'descricao'=>'Alisamento progressivo com produtos premium, sem formol.',          'preco'=>'300.00','duracao_minutos'=>240, 'categoria'=>'cabelo'],
        ['id'=>4,'nome'=>'Manicure simples',     'descricao'=>'Esmaltação completa com cutilagem e tratamento de cutículas.',      'preco'=>'50.00', 'duracao_minutos'=>45,  'categoria'=>'manicure'],
        ['id'=>5,'nome'=>'Pedicure completa',    'descricao'=>'Pedicure com esfoliação, hidratação e esmaltação.',                 'preco'=>'70.00', 'duracao_minutos'=>60,  'categoria'=>'manicure'],
        ['id'=>6,'nome'=>'Maquiagem social',     'descricao'=>'Maquiagem profissional para eventos, formaturas e festas.',         'preco'=>'150.00','duracao_minutos'=>90,  'categoria'=>'maquiagem'],
        ['id'=>7,'nome'=>'Design de sobrancelha','descricao'=>'Modelagem e design personalizado para o seu tipo de rosto.',        'preco'=>'80.00', 'duracao_minutos'=>30,  'categoria'=>'sobrancelha'],
        ['id'=>8,'nome'=>'Massagem relaxante',   'descricao'=>'Massagem corporal completa com óleos essenciais aromáticos.',       'preco'=>'120.00','duracao_minutos'=>60,  'categoria'=>'massagem'],
    ];
}

function demo_planos(): array {
    return [
        ['id'=>1,'nome'=>'Plano Prata',    'descricao'=>'Ideal para quem quer cuidar da beleza todo mês com economia.',         'preco'=>'180.00','servicos_inclusos'=>'Manicure + Pedicure + Sobrancelha + Corte'],
        ['id'=>2,'nome'=>'Plano Ouro',     'descricao'=>'O plano mais completo para quem não abre mão do melhor.',              'preco'=>'350.00','servicos_inclusos'=>'Todos do Prata + Coloração + Maquiagem'],
        ['id'=>3,'nome'=>'Plano Diamante', 'descricao'=>'Serviços ilimitados com prioridade no agendamento e atendimento VIP.', 'preco'=>'600.00','servicos_inclusos'=>'Todos os serviços sem limite mensal'],
    ];
}

function demo_cupons(): array {
    return [
        ['codigo'=>'TARDE20',        'desconto_percent'=>20, 'desconto_valor'=>null, 'pontos_necessarios'=>0,   'validade'=>'2026-12-31'],
        ['codigo'=>'PRIMEIRAVISITA', 'desconto_percent'=>15, 'desconto_valor'=>null, 'pontos_necessarios'=>0,   'validade'=>'2026-12-31'],
        ['codigo'=>'VIP50',          'desconto_percent'=>50, 'desconto_valor'=>null, 'pontos_necessarios'=>500, 'validade'=>'2026-12-31'],
    ];
}

function demo_avaliacoes(): array {
    return [
        ['nota'=>'satisfeito',       'comentario'=>'Adorei o atendimento! Saí renovada e super satisfeita.',              'cliente'=>'Ana Paula',  'servico'=>'Corte feminino',    'criado_em'=>'2024-06-10 14:00:00'],
        ['nota'=>'satisfeito',       'comentario'=>'Melhor massagem que já fiz. Ambiente aconchegante e muito profissional.','cliente'=>'Marina Lima','servico'=>'Massagem relaxante','criado_em'=>'2024-06-08 10:30:00'],
        ['nota'=>'satisfeito',       'comentario'=>'Coloração ficou perfeita! Exatamente a cor que eu queria.',            'cliente'=>'Fernanda S.','servico'=>'Coloração completa','criado_em'=>'2024-06-05 16:00:00'],
        ['nota'=>'pouco_satisfeito', 'comentario'=>'Gostei bastante, só achei um pouquinho demorado.',                    'cliente'=>'Juliana R.', 'servico'=>'Progressiva',       'criado_em'=>'2024-06-01 11:00:00'],
    ];
}
