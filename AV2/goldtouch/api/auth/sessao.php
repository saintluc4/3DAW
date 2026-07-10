<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

$cliente = cliente_logado();
json_response($cliente
    ? ['logado' => true,  'cliente' => $cliente]
    : ['logado' => false]
);
