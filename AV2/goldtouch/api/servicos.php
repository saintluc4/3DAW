<?php
require_once '../includes/config.php';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── LISTAR SERVIÇOS ──────────────────────────────────────────────────────────
if ($method === 'GET' && $action === 'listar') {
    $categoria = $_GET['categoria'] ?? '';

    if (!db_ok()) {
        // modo demo
        $lista = demo_servicos();
        if ($categoria) $lista = array_values(array_filter($lista, fn($s) => $s['categoria'] === $categoria));
        json_response(['sucesso' => true, 'servicos' => $lista, 'demo' => true]);
    }

    try {
        if ($categoria) {
            $stmt = db()->prepare('SELECT * FROM servicos WHERE ativo=1 AND categoria=? ORDER BY preco ASC');
            $stmt->execute([$categoria]);
        } else {
            $stmt = db()->prepare('SELECT * FROM servicos WHERE ativo=1 ORDER BY categoria, preco ASC');
            $stmt->execute();
        }
        json_response(['sucesso' => true, 'servicos' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        json_response(['erro' => 'Erro ao buscar serviços: '.$e->getMessage()], 500);
    }
}

// ── HORÁRIOS DISPONÍVEIS ─────────────────────────────────────────────────────
if ($method === 'GET' && $action === 'horarios') {
    $servico_id = intval($_GET['servico_id'] ?? 0);
    $data       = $_GET['data'] ?? '';
    if (!$servico_id || !$data) json_response(['erro' => 'Informe servico_id e data.'], 422);
    if ($data < date('Y-m-d'))  json_response(['erro' => 'Data inválida.'], 422);

    if (!db_ok()) {
        // modo demo — horários fixos com alguns "ocupados" aleatórios
        $ocupados = ['09:00','11:00','14:00']; // simula ocupação
        $slots = [];
        for ($h = 8; $h < 20; $h++) {
            $hora = sprintf('%02d:00', $h);
            $slots[] = ['hora' => $hora, 'disponivel' => !in_array($hora, $ocupados)];
        }
        json_response(['sucesso' => true, 'slots' => $slots, 'data' => $data, 'demo' => true]);
    }

    try {
        $ocup = db()->prepare("SELECT DATE_FORMAT(data_hora,'%H:%i') as hora FROM agendamentos WHERE DATE(data_hora)=? AND status NOT IN ('cancelado')");
        $ocup->execute([$data]);
        $ocupados = array_column($ocup->fetchAll(), 'hora');

        $slots = [];
        for ($h = 8; $h < 20; $h++) {
            $hora = sprintf('%02d:00', $h);
            $slots[] = ['hora' => $hora, 'disponivel' => !in_array($hora, $ocupados)];
        }
        json_response(['sucesso' => true, 'slots' => $slots, 'data' => $data]);
    } catch (PDOException $e) {
        json_response(['erro' => 'Erro ao buscar horários.'], 500);
    }
}

json_response(['erro' => 'Rota não encontrada.'], 404);
