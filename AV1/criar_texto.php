<?php
require_once 'functions.php';
require_once 'layout.php';

$erros   = array();
$sucesso = '';
$dados   = array('enunciado' => '', 'resposta_modelo' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enunciado       = isset($_POST['enunciado'])       ? trim($_POST['enunciado'])       : '';
    $resposta_modelo = isset($_POST['resposta_modelo']) ? trim($_POST['resposta_modelo']) : '';

    if (empty($enunciado))       $erros[] = 'O enunciado é obrigatório.';
    if (empty($resposta_modelo)) $erros[] = 'A resposta modelo é obrigatória.';

    if (empty($erros)) {
        criar_texto($enunciado, $resposta_modelo);
        $sucesso = 'Pergunta dissertativa criada com sucesso!';
        $dados   = array('enunciado' => '', 'resposta_modelo' => '');
    } else {
        $dados = array('enunciado' => $enunciado, 'resposta_modelo' => $resposta_modelo);
    }
}

render_header('Nova Pergunta — <span>Dissertativa</span>', 'Crie uma pergunta aberta com resposta modelo de referência');
?>

<?php if ($sucesso): ?>
  <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?> — <a href="listar.php" style="color:inherit;font-weight:600">Ver todas as perguntas</a></div>
<?php endif; ?>
<?php foreach ($erros as $e): ?>
  <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="card">
  <div class="card-title">Dados da Pergunta</div>
  <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">

    <div class="form-group">
      <label for="enunciado">Enunciado da Pergunta</label>
      <textarea id="enunciado" name="enunciado" placeholder="Descreva a situação ou pergunta que o gestor deverá responder…" rows="4"><?= htmlspecialchars($dados['enunciado']) ?></textarea>
    </div>

    <div class="form-group">
      <label for="resposta_modelo">Resposta Modelo (Gabarito)</label>
      <textarea id="resposta_modelo" name="resposta_modelo" rows="5"
        placeholder="Descreva a resposta ideal esperada do gestor. Este campo é usado como referência pelo facilitador."><?= htmlspecialchars($dados['resposta_modelo']) ?></textarea>
    </div>

    <div class="action-row">
      <button type="submit" class="btn btn-primary">Salvar Pergunta</button>
      <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php render_footer(); ?>
