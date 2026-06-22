<?php
if (!defined('DATA_DIR')) {
    define('DATA_DIR',       __DIR__ . '/data/');
    define('PERGUNTAS_FILE', DATA_DIR . 'perguntas.txt');
    define('USUARIOS_FILE',  DATA_DIR . 'usuarios.txt');
}

/* -------------------------------------------------------
   INICIALIZAÇÃO
------------------------------------------------------- */
function init_data_dir() {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    if (!file_exists(PERGUNTAS_FILE)) file_put_contents(PERGUNTAS_FILE, '');
    if (!file_exists(USUARIOS_FILE))  file_put_contents(USUARIOS_FILE, '');
}

/* -------------------------------------------------------
   PERGUNTAS — LEITURA / ESCRITA
------------------------------------------------------- */
function ler_perguntas() {
    init_data_dir();
    $linhas    = file(PERGUNTAS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $perguntas = array();
    foreach ($linhas as $linha) {
        $p = json_decode($linha, true);
        if ($p) {
            $perguntas[] = $p;
        }
    }
    return $perguntas;
}

function salvar_perguntas(array $perguntas) {
    init_data_dir();
    $conteudo = '';
    foreach ($perguntas as $p) {
        $conteudo .= json_encode($p, JSON_UNESCAPED_UNICODE) . "\n";
    }
    $ok = file_put_contents(PERGUNTAS_FILE, $conteudo);
    return ($ok !== false);
}

function gerar_id() {
    return uniqid('Q', true);
}

function buscar_pergunta($id) {
    $perguntas = ler_perguntas();
    foreach ($perguntas as $p) {
        if ($p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

/* -------------------------------------------------------
   CRIAR
------------------------------------------------------- */
function criar_multipla($enunciado, $alternativas, $correta) {
    $perguntas = ler_perguntas();
    $nova = array(
        'id'           => gerar_id(),
        'tipo'         => 'multipla',
        'enunciado'    => trim($enunciado),
        'alternativas' => array_values($alternativas),
        'correta'      => (int)$correta,
        'criado_em'    => date('Y-m-d H:i:s'),
    );
    $perguntas[] = $nova;
    salvar_perguntas($perguntas);
    return $nova;
}

function criar_texto($enunciado, $resposta_modelo) {
    $perguntas = ler_perguntas();
    $nova = array(
        'id'              => gerar_id(),
        'tipo'            => 'texto',
        'enunciado'       => trim($enunciado),
        'resposta_modelo' => trim($resposta_modelo),
        'criado_em'       => date('Y-m-d H:i:s'),
    );
    $perguntas[] = $nova;
    salvar_perguntas($perguntas);
    return $nova;
}

/* -------------------------------------------------------
   ALTERAR
------------------------------------------------------- */
function alterar_multipla($id, $enunciado, $alternativas, $correta) {
    $perguntas  = ler_perguntas();
    $encontrada = false;
    for ($i = 0; $i < count($perguntas); $i++) {
        if ($perguntas[$i]['id'] === $id && $perguntas[$i]['tipo'] === 'multipla') {
            $perguntas[$i]['enunciado']    = trim($enunciado);
            $perguntas[$i]['alternativas'] = array_values($alternativas);
            $perguntas[$i]['correta']      = (int)$correta;
            $perguntas[$i]['atualizado_em']= date('Y-m-d H:i:s');
            $encontrada = $perguntas[$i];
            break;
        }
    }
    if ($encontrada) {
        salvar_perguntas($perguntas);
        return $encontrada;
    }
    return null;
}

function alterar_texto($id, $enunciado, $resposta_modelo) {
    $perguntas  = ler_perguntas();
    $encontrada = false;
    for ($i = 0; $i < count($perguntas); $i++) {
        if ($perguntas[$i]['id'] === $id && $perguntas[$i]['tipo'] === 'texto') {
            $perguntas[$i]['enunciado']       = trim($enunciado);
            $perguntas[$i]['resposta_modelo'] = trim($resposta_modelo);
            $perguntas[$i]['atualizado_em']   = date('Y-m-d H:i:s');
            $encontrada = $perguntas[$i];
            break;
        }
    }
    if ($encontrada) {
        salvar_perguntas($perguntas);
        return $encontrada;
    }
    return null;
}

/* -------------------------------------------------------
   EXCLUIR
------------------------------------------------------- */
function excluir_pergunta($id) {
    $perguntas = ler_perguntas();
    $novas     = array();
    foreach ($perguntas as $p) {
        if ($p['id'] !== $id) {
            $novas[] = $p;
        }
    }
    if (count($novas) < count($perguntas)) {
        salvar_perguntas($novas);
        return true;
    }
    return false;
}

/* -------------------------------------------------------
   USUÁRIOS
------------------------------------------------------- */
function ler_usuarios() {
    init_data_dir();
    $linhas   = file(USUARIOS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $usuarios = array();
    foreach ($linhas as $linha) {
        $u = json_decode($linha, true);
        if ($u) {
            $usuarios[] = $u;
        }
    }
    return $usuarios;
}

function salvar_usuario($nome, $email, $senha) {
    init_data_dir();
    $usuarios = ler_usuarios();
    foreach ($usuarios as $u) {
        if ($u['email'] === $email) {
            return array('erro' => 'E-mail já cadastrado.');
        }
    }
    $novo = array(
        'id'         => gerar_id(),
        'nome'       => trim($nome),
        'email'      => trim($email),
        'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
        'criado_em'  => date('Y-m-d H:i:s'),
    );
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
