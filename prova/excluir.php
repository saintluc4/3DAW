<?php
require_once 'functions.php';
require_once 'layout.php';

$id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : '');
$pergunta = buscar_pergunta($id);

if (!$pergunta) {
    header('Location: listar.php'); exit;
}

// Se confirmação via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    excluir_pergunta($id);
    header('Location: listar.php?excluido=1'); exit;
}

// Se veio via GET com ?confirm=1 (link direto de botão inline)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Mostra página de confirmação (fallback sem JS)
}

render_header('Excluir <span>Pergunta</span>', 'Esta ação não pode ser desfeita');
?>

<div class="alert alert-warning">
  ⚠️ Você está prestes a excluir permanentemente esta pergunta e todas as suas respostas.
</div>

<div class="card">
  <div class="card-title">Confirmar Exclusão</div>

  <div style="margin-bottom:20px">
    <div class="detail-label">Tipo</div>
    <div class="detail-value">
      <?php if ($pergunta['tipo'] === 'multipla'): ?>
        <span class="badge badge-multipla">Múltipla Escolha</span>
      <?php else: ?>
        <span class="badge badge-texto">Dissertativa</span>
      <?php endif; ?>
    </div>

    <div class="detail-label">Enunciado</div>
    <div style="background:var(--surface2);border:1px solid var(--danger);border-radius:8px;padding:16px;color:var(--text);line-height:1.6;margin-bottom:20px">
      <?= nl2br(htmlspecialchars($pergunta['enunciado'])) ?>
    </div>

    <?php if ($pergunta['tipo'] === 'multipla'): ?>
      <div class="detail-label"><?= count($pergunta['alternativas']) ?> alternativas serão removidas</div>
    <?php else: ?>
      <div class="detail-label">A resposta modelo também será removida</div>
    <?php endif; ?>
  </div>

  <form method="POST" action="">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
    <div class="action-row">
      <button type="submit" name="confirmar" value="1" class="btn btn-danger">Confirmar Exclusão</button>
      <a href="visualizar.php?id=<?= urlencode($id) ?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php render_footer(); ?>
