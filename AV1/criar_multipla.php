<?php
require_once 'functions.php';
require_once 'layout.php';

$erros   = array();
$sucesso = '';
$dados   = array(
    'enunciado'    => '',
    'alternativas' => array('','','','',''),
    'correta'      => 0
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enunciado    = isset($_POST['enunciado'])    ? trim($_POST['enunciado'])    : '';
    $alternativas = isset($_POST['alternativas']) ? $_POST['alternativas']       : array();
    $correta      = isset($_POST['correta'])      ? (int)$_POST['correta']       : -1;

    // Validações
    if (empty($enunciado)) {
        $erros[] = 'O enunciado é obrigatório.';
    }
    $alts_preenchidas = array();
    foreach ($alternativas as $a) {
        if (trim($a) !== '') $alts_preenchidas[] = $a;
    }
    if (count($alts_preenchidas) < 2) {
        $erros[] = 'Informe pelo menos 2 alternativas.';
    }
    if ($correta < 0 || !isset($alternativas[$correta]) || trim($alternativas[$correta]) === '') {
        $erros[] = 'Selecione a alternativa correta (marque o botão "Correta" ao lado dela).';
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
        criar_multipla($enunciado, $alts_limpas, $novo_correta);
        $sucesso = 'Pergunta de múltipla escolha criada com sucesso!';
        $dados   = array('enunciado' => '', 'alternativas' => array('','','','',''), 'correta' => 0);
    } else {
        $dados = array('enunciado' => $enunciado, 'alternativas' => $alternativas, 'correta' => $correta);
    }
}

render_header('Nova Pergunta — <span>Múltipla Escolha</span>', 'Crie uma pergunta com alternativas para os desafios corporativos');
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
      <textarea id="enunciado" name="enunciado" placeholder="Digite a situação ou pergunta para o gestor…" rows="4"><?= htmlspecialchars($dados['enunciado']) ?></textarea>
    </div>

    <div class="form-group">
      <label>Alternativas <span style="color:var(--muted);text-transform:none;letter-spacing:0;font-size:.78rem">(mín. 2, máx. 5 — marque a correta)</span></label>
      <?php $letras = array('A','B','C','D','E'); ?>
      <?php for ($i = 0; $i < 5; $i++): ?>
      <div class="alt-row">
        <div class="alt-label"><?= $letras[$i] ?></div>
        <input type="text" name="alternativas[<?= $i ?>]"
               placeholder="Alternativa <?= $letras[$i] ?> (deixe vazio para omitir)"
               value="<?= htmlspecialchars(isset($dados['alternativas'][$i]) ? $dados['alternativas'][$i] : '') ?>">
        <label>
          <input type="radio" name="correta" value="<?= $i ?>"
                 <?= ($dados['correta'] == $i) ? 'checked' : '' ?>>
          Correta
        </label>
      </div>
      <?php endfor; ?>
    </div>

    <div class="action-row">
      <button type="submit" class="btn btn-primary">Salvar Pergunta</button>
      <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<?php render_footer(); ?>
