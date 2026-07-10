<?php
require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['erro' => 'Método não permitido.'], 405);
}

// ── MODO DEMO ────────────────────────────────────────────────────────────────
if (!db_ok()) {
    json_response(['sucesso' => true, 'avaliacoes' => demo_avaliacoes(), 'demo' => true]);
}

// ── COM BANCO ────────────────────────────────────────────────────────────────
try {
    $stmt = db()->prepare(
        "SELECT av.nota, av.comentario, av.criado_em,
                c.nome AS cliente, s.nome AS servico
         FROM avaliacoes av
         JOIN clientes c      ON c.id  = av.cliente_id
         JOIN agendamentos ag ON ag.id = av.agendamento_id
         JOIN servicos s      ON s.id  = ag.servico_id
         WHERE av.comentario != ''
         ORDER BY av.criado_em DESC
         LIMIT 10"
    );
    $stmt->execute();
    $lista = $stmt->fetchAll();

    if (empty($lista)) {
        json_response(['sucesso' => true, 'avaliacoes' => demo_avaliacoes(), 'demo' => true]);
    }
    json_response(['sucesso' => true, 'avaliacoes' => $lista]);

} catch (PDOException $e) {
    json_response(['sucesso' => true, 'avaliacoes' => demo_avaliacoes(), 'demo' => true]);
}
