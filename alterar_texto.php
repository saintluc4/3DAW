<?php
require_once 'functions.php';
require_once 'layout.php';

$id = $_GET['id'] ?? $_POST['id'] ?? '';
$pergunta = buscar_pergunta($id);

if (!$pergunta) {
    render_header('Pergunta não encontrada', '');
    echo '<div class="alert alert-error">Pergunta não encontrada. <a href="listar.php" style="color:inherit">Voltar</a></div>';
    render_footer(); exit;
}

if ($pergunta['tipo'] !== 'texto') {
    header('Location: alterar_multipla.php?id=' . urlencode($id)); exit;
}

$erros   = [];
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enunciado       = trim($_POST['enunciado'] ?? '');
    $resposta_modelo = trim($_POST['resposta_modelo'] ?? '');

    if (empty($enunciado))       $erros[] = 'O enunciado é obrigatório.';
    if (empty($resposta_modelo)) $erros[] = 'A resposta modelo é obrigatória.';

    if (empty($erros)) {
        $pergunta = alterar_texto($id, $enunciado, $resposta_modelo);
        $sucesso  = 'Pergunta atualizada com sucesso!';
    }
}

render_header('Editar Pergunta — <span>Dissertativa</span>', htmlspecialchars(mb_strimwidth($pergunta['enunciado'], 0, 80, '…')));
?>

<?php if ($sucesso): ?>
  <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?> — <a href="listar.php" style="color:inherit;font-weight:600">Ver todas</a></div>
<?php endif; ?>
<?php foreach ($erros as $e): ?>
  <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="card">
  <div class="card-title">Editar Pergunta</div>
  <form method="POST" action="">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

    <div class="form-group">
      <label for="enunciado">Enunciado da Pergunta</label>
      <textarea id="enunciado" name="enunciado" rows="4"><?= htmlspecialchars($pergunta['enunciado']) ?></textarea>
    </div>

    <div class="form-group">
      <label for="resposta_modelo">Resposta Modelo (Gabarito)</label>
      <textarea id="resposta_modelo" name="resposta_modelo" rows="5"><?= htmlspecialchars($pergunta['resposta_modelo']) ?></textarea>
    </div>

    <div class="action-row">
      <button type="submit" class="btn btn-primary">Salvar Alterações</button>
      <a href="visualizar.php?id=<?= urlencode($id) ?>" class="btn btn-secondary">Cancelar</a>
      <a href="excluir.php?id=<?= urlencode($id) ?>" class="btn btn-danger" style="margin-left:auto"
         onclick="return confirm('Excluir esta pergunta permanentemente?')">Excluir</a>
    </div>
  </form>
</div>

<?php render_footer(); ?>
