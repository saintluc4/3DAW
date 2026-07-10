<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

$servico_id = intval($_GET['servico_id'] ?? 0);
$data       = $_GET['data'] ?? '';

if (!$servico_id || !$data) {
    json_response(['erro' => 'Informe servico_id e data.'], 422);
}
if ($data < date('Y-m-d')) {
    json_response(['erro' => 'Data não pode ser no passado.'], 422);
}

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    $ocupados = ['09:00', '11:00', '14:00'];
    $slots    = [];
    for ($h = 8; $h < 20; $h++) {
        $hora    = sprintf('%02d:00', $h);
        $slots[] = ['hora' => $hora, 'disponivel' => !in_array($hora, $ocupados)];
    }
    json_response(['sucesso' => true, 'slots' => $slots, 'data' => $data, 'demo' => true]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    $ocup = db()->prepare(
        "SELECT DATE_FORMAT(data_hora, '%H:%i') AS hora
         FROM agendamentos
         WHERE DATE(data_hora) = ? AND status NOT IN ('cancelado')"
    );
    $ocup->execute([$data]);
    $ocupados = array_column($ocup->fetchAll(), 'hora');

    $slots = [];
    for ($h = 8; $h < 20; $h++) {
        $hora    = sprintf('%02d:00', $h);
        $slots[] = ['hora' => $hora, 'disponivel' => !in_array($hora, $ocupados)];
    }
    json_response(['sucesso' => true, 'slots' => $slots, 'data' => $data]);

} catch (PDOException $e) {
    json_response(['erro' => 'Erro ao buscar horários.'], 500);
}
