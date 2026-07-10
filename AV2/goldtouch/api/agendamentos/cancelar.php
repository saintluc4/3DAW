<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

exige_login();

$cliente = cliente_logado();
$body    = json_decode(file_get_contents('php://input'), true) ?? [];
$ag_id   = intval($body['agendamento_id'] ?? 0);

if (!$ag_id) {
    json_response(['erro' => 'ID de agendamento inválido.'], 422);
}

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    if (isset($_SESSION['agendamentos_demo'])) {
        foreach ($_SESSION['agendamentos_demo'] as &$ag) {
            if ($ag['id'] == $ag_id) {
                $ag['status'] = 'cancelado';
                break;
            }
        }
    }
    json_response(['sucesso' => true, 'mensagem' => 'Agendamento cancelado.', 'demo' => true]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    $stmt = db()->prepare(
        "SELECT * FROM agendamentos
         WHERE id = ? AND cliente_id = ? AND status = 'confirmado'"
    );
    $stmt->execute([$ag_id, $cliente['id']]);
    $ag = $stmt->fetch();

    if (!$ag) {
        json_response(['erro' => 'Agendamento não encontrado ou já cancelado.'], 404);
    }
    if (strtotime($ag['data_hora']) - time() < 7200) {
        json_response(['erro' => 'Cancelamento só permitido com 2 horas de antecedência.'], 422);
    }

    db()->prepare("UPDATE agendamentos SET status = 'cancelado' WHERE id = ?")
       ->execute([$ag_id]);

    json_response(['sucesso' => true, 'mensagem' => 'Agendamento cancelado com sucesso.']);

} catch (PDOException $e) {
    json_response(['erro' => 'Erro ao cancelar agendamento.'], 500);
}
