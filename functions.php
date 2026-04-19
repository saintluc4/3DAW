<?php
define('DATA_DIR', dirname($_SERVER['SCRIPT_FILENAME']) . '/data/');
define('PERGUNTAS_FILE', DATA_DIR . 'perguntas.txt');
define('USUARIOS_FILE', DATA_DIR . 'usuarios.txt');

// Garante que o diretório de dados existe
function init_data_dir() {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    if (!file_exists(PERGUNTAS_FILE)) file_put_contents(PERGUNTAS_FILE, '');
    if (!file_exists(USUARIOS_FILE)) file_put_contents(USUARIOS_FILE, '');
}

// Lê todas as perguntas
function ler_perguntas() {
    init_data_dir();
    $linhas = file(PERGUNTAS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $perguntas = [];
    foreach ($linhas as $linha) {
        $p = json_decode($linha, true);
        if ($p) $perguntas[] = $p;
    }
    return $perguntas;
}

// Salva todas as perguntas
function salvar_perguntas(array $perguntas) {
    init_data_dir();
    $conteudo = '';
    foreach ($perguntas as $p) {
        $conteudo .= json_encode($p, JSON_UNESCAPED_UNICODE) . "\n";
    }
    file_put_contents(PERGUNTAS_FILE, $conteudo);
}

// Gera um ID único
function gerar_id() {
    return uniqid('Q', true);
}

// Busca uma pergunta pelo ID
function buscar_pergunta($id) {
    $perguntas = ler_perguntas();
    foreach ($perguntas as $p) {
        if ($p['id'] === $id) return $p;
    }
    return null;
}

// Cria pergunta de múltipla escolha
function criar_multipla($enunciado, $alternativas, $correta) {
    $perguntas = ler_perguntas();
    $nova = [
        'id'           => gerar_id(),
        'tipo'         => 'multipla',
        'enunciado'    => trim($enunciado),
        'alternativas' => $alternativas,
        'correta'      => (int)$correta,
        'criado_em'    => date('Y-m-d H:i:s'),
    ];
    $perguntas[] = $nova;
    salvar_perguntas($perguntas);
    return $nova;
}

// Cria pergunta de texto
function criar_texto($enunciado, $resposta_modelo) {
    $perguntas = ler_perguntas();
    $nova = [
        'id'              => gerar_id(),
        'tipo'            => 'texto',
        'enunciado'       => trim($enunciado),
        'resposta_modelo' => trim($resposta_modelo),
        'criado_em'       => date('Y-m-d H:i:s'),
    ];
    $perguntas[] = $nova;
    salvar_perguntas($perguntas);
    return $nova;
}

// Altera pergunta de múltipla escolha
function alterar_multipla($id, $enunciado, $alternativas, $correta) {
    $perguntas = ler_perguntas();
    foreach ($perguntas as &$p) {
        if ($p['id'] === $id && $p['tipo'] === 'multipla') {
            $p['enunciado']    = trim($enunciado);
            $p['alternativas'] = $alternativas;
            $p['correta']      = (int)$correta;
            $p['atualizado_em']= date('Y-m-d H:i:s');
            salvar_perguntas($perguntas);
            return $p;
        }
    }
    return null;
}

// Altera pergunta de texto
function alterar_texto($id, $enunciado, $resposta_modelo) {
    $perguntas = ler_perguntas();
    foreach ($perguntas as &$p) {
        if ($p['id'] === $id && $p['tipo'] === 'texto') {
            $p['enunciado']       = trim($enunciado);
            $p['resposta_modelo'] = trim($resposta_modelo);
            $p['atualizado_em']   = date('Y-m-d H:i:s');
            salvar_perguntas($perguntas);
            return $p;
        }
    }
    return null;
}

// Exclui uma pergunta
function excluir_pergunta($id) {
    $perguntas = ler_perguntas();
    $novas = array_filter($perguntas, fn($p) => $p['id'] !== $id);
    if (count($novas) < count($perguntas)) {
        salvar_perguntas(array_values($novas));
        return true;
    }
    return false;
}

// --- USUÁRIOS ---
function ler_usuarios() {
    init_data_dir();
    $linhas = file(USUARIOS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $usuarios = [];
    foreach ($linhas as $linha) {
        $u = json_decode($linha, true);
        if ($u) $usuarios[] = $u;
    }
    return $usuarios;
}

function salvar_usuario($nome, $email, $senha) {
    init_data_dir();
    $usuarios = ler_usuarios();
    foreach ($usuarios as $u) {
        if ($u['email'] === $email) return ['erro' => 'E-mail já cadastrado.'];
    }
    $novo = [
        'id'         => gerar_id(),
        'nome'       => trim($nome),
        'email'      => trim($email),
        'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
        'criado_em'  => date('Y-m-d H:i:s'),
    ];
    file_put_contents(USUARIOS_FILE, json_encode($novo, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    return $novo;
}

function autenticar_usuario($email, $senha) {
    $usuarios = ler_usuarios();
    foreach ($usuarios as $u) {
        if ($u['email'] === $email && password_verify($senha, $u['senha_hash'])) {
            return $u;
        }
    }
    return null;
}

init_data_dir();
