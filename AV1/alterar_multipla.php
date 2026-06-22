<?php
require_once 'functions.php';
require_once 'layout.php';

$id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : '');
$pergunta = buscar_pergunta($id);

if (!$pergunta) {
    render_header('Pergunta não encontrada', '');
    echo '<div class="alert alert-error">Pergunta não encontrada. <a href="listar.php" style="color:inherit">Voltar</a></div>';
    render_footer(); exit;
}

if ($pergunta['tipo'] !== 'multipla') {
    header('Location: alterar_texto.php?id=' . urlencode($id)); exit;
}

$erros   = array();
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enunciado    = isset($_POST['enunciado'])    ? trim($_POST['enunciado'])  : '';
    $alternativas = isset($_POST['alternativas']) ? $_POST['alternativas']     : array();
    $correta      = isset($_POST['correta'])      ? (int)$_POST['correta']     : -1;

    if (empty($enunciado)) $erros[] = 'O enunciado é obrigatório.';
    $alts_preenchidas = array();
    foreach ($alternativas as $a) {
        if (trim($a) !== '') $alts_preenchidas[] = $a;
    }
    if (count($alts_preenchidas) < 2) $erros[] = 'Informe pelo menos 2 alternativas.';
    if ($correta < 0 || !isset($alternativas[$correta]) || trim($alternativas[$correta]) === '') {
        $erros[] = 'Selecione a alternativa correta válida.';
    }

    if (empty($erros)) {
        $alts_limpas  = array();
        $novo_correta = 0;
        $idx_real     = 0;
        foreach ($alternativas as $i => $alt) {
            if (trim($alt) !== '') {
                $alts_limpas[] = trim($alt);
                if ($i == $correta) $novo_correta = $idx_real;
                $idx_real++;
            }
        }
        $pergunta = alterar_multipla($id, $enunciado, $alts_limpas, $novo_correta);
        $sucesso  = 'Pergunta atualizada com sucesso!';
    }
}

// Preenche sempre 5 slots
$alts = array_pad($pergunta['alternativas'], 5, '');
$correta_atual = $pergunta['correta'];

render_header('Editar Pergunta — <span>Múltipla Escolha</span>', htmlspecialchars(mb_substr($pergunta['enunciado'], 0, 80) . '…'));
?>

<?php if ($sucesso): ?>
  <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?> — <a href="listar.php" style="color:inherit;font-weight:600">Ver todas</a></div>
<?php endif; ?>
<?php foreach ($erros as $e): ?>
  <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="card">
  <div class="card-title">Editar Pergunta</div>
  <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

    <div class="form-group">
      <label for="enunciado">Enunciado da Pergunta</label>
      <textarea id="enunciado" name="enunciado" rows="4"><?= htmlspecialchars($pergunta['enunciado']) ?></textarea>
    </div>

    <div class="form-group">
      <label>Alternativas <span style="color:var(--muted);text-transform:none;letter-spacing:0;font-size:.78rem">(marque a correta)</span></label>
      <?php $letras = array('A','B','C','D','E'); ?>
      <?php for ($i = 0; $i < 5; $i++): ?>
      <div class="alt-row">
        <div class="alt-label"><?= $letras[$i] ?></div>
        <input type="text" name="alternativas[<?= $i ?>]"
               placeholder="Alternativa <?= $letras[$i] ?> (deixe vazio para omitir)"
               value="<?= htmlspecialchars($alts[$i]) ?>">
        <label>
          <input type="radio" name="correta" value="<?= $i ?>"
                 <?= ($correta_atual == $i) ? 'checked' : '' ?>>
          Correta
        </label>
      </div>
      <?php endfor; ?>
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
