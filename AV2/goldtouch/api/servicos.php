<?php
require_once '../includes/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ---------- LISTAR SERVIÇOS (com filtro por categoria) ----------
if ($method === 'GET' && $action === 'listar') {
    $categoria = $_GET['categoria'] ?? '';

    try {
        if ($categoria) {
            $stmt = db()->prepare('SELECT * FROM servicos WHERE ativo = 1 AND categoria = ? ORDER BY preco ASC');
            $stmt->execute([$categoria]);
        } else {
            $stmt = db()->prepare('SELECT * FROM servicos WHERE ativo = 1 ORDER BY categoria, preco ASC');
            $stmt->execute();
        }
        $servicos = $stmt->fetchAll();
        json_response(['sucesso' => true, 'servicos' => $servicos]);

    } catch (PDOException $e) {
        json_response(['erro' => 'Erro ao buscar serviços.'], 500);
    }
}

// ---------- BUSCAR HORÁRIOS DISPONÍVEIS ----------
if ($method === 'GET' && $action === 'horarios') {
    $servico_id = intval($_GET['servico_id'] ?? 0);
    $data       = $_GET['data'] ?? '';   // formato: YYYY-MM-DD

    if (!$servico_id || !$data) {
        json_response(['erro' => 'Informe servico_id e data.'], 422);
    }

    // Valida que a data não é no passado
    if ($data < date('Y-m-d')) {
        json_response(['erro' => 'Data inválida.'], 422);
    }

    try {
        // Busca duração do serviço
        $svc = db()->prepare('SELECT duracao_minutos FROM servicos WHERE id = ? AND ativo = 1');
        $svc->execute([$servico_id]);
        $servico = $svc->fetch();
        if (!$servico) {
            json_response(['erro' => 'Serviço não encontrado.'], 404);
        }

        // Horários já ocupados nesse dia
        $ocup = db()->prepare(
            "SELECT DATE_FORMAT(data_hora, '%H:%i') as hora
             FROM agendamentos
             WHERE DATE(data_hora) = ? AND status NOT IN ('cancelado')
             ORDER BY data_hora"
        );
        $ocup->execute([$data]);
        $ocupados = array_column($ocup->fetchAll(), 'hora');

        // Gera slots das 08:00 às 20:00 de hora em hora
        $slots = [];
        $inicio = strtotime($data . ' 08:00:00');
        $fim    = strtotime($data . ' 20:00:00');

        for ($ts = $inicio; $ts < $fim; $ts += 3600) {
            $hora = date('H:i', $ts);
            $slots[] = [
                'hora'       => $hora,
                'disponivel' => !in_array($hora, $ocupados),
            ];
        }

        json_response(['sucesso' => true, 'slots' => $slots, 'data' => $data]);

    } catch (PDOException $e) {
        json_response(['erro' => 'Erro ao buscar horários.'], 500);
    }
}

json_response(['erro' => 'Rota não encontrada.'], 404);
