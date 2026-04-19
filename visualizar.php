<?php
require_once 'functions.php';
require_once 'layout.php';

$id       = isset($_GET['id']) ? $_GET['id'] : '';
$pergunta = buscar_pergunta($id);

if (!$pergunta) {
    render_header('Pergunta não encontrada', '');
    echo '<div class="alert alert-error">Pergunta não encontrada. <a href="listar.php" style="color:inherit;font-weight:600">Voltar à lista</a></div>';
    render_footer(); exit;
}

$letras = ['A','B','C','D','E'];
$tipo_label = $pergunta['tipo'] === 'multipla' ? 'Múltipla Escolha' : 'Dissertativa';

render_header('Detalhe da <span>Pergunta</span>', $tipo_label . ' — criada em ' . htmlspecialchars($pergunta['criado_em']));
?>

<!-- Barra de ações -->
<div class="action-row" style="margin-bottom:24px">
  <a href="listar.php" class="btn btn-secondary">← Voltar</a>
  <?php if ($pergunta['tipo'] === 'multipla'): ?>
    <a href="alterar_multipla.php?id=<?= urlencode($id) ?>" class="btn btn-primary">Editar</a>
  <?php else: ?>
    <a href="alterar_texto.php?id=<?= urlencode($id) ?>" class="btn btn-primary">Editar</a>
  <?php endif; ?>
  <a href="excluir.php?id=<?= urlencode($id) ?>" class="btn btn-danger"
     onclick="return confirm('Excluir permanentemente esta pergunta e suas respostas?')">Excluir</a>
</div>

<div class="card">
  <!-- Cabeçalho da pergunta -->
  <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:28px">
    <div style="flex:1">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
        <?php if ($pergunta['tipo'] === 'multipla'): ?>
          <span class="badge badge-multipla">Múltipla Escolha</span>
        <?php else: ?>
          <span class="badge badge-texto">Dissertativa</span>
        <?php endif; ?>
        <span style="color:var(--muted);font-size:.8rem">ID: <?= htmlspecialchars(substr($id,0,20)) ?>…</span>
        <?php if (!empty($pergunta['atualizado_em'])): ?>
          <span style="color:var(--muted);font-size:.8rem">· Editado em <?= htmlspecialchars($pergunta['atualizado_em']) ?></span>
        <?php endif; ?>
      </div>
      <div class="card-title" style="margin-bottom:0;font-size:1.15rem">Enunciado</div>
    </div>
  </div>

  <div style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:20px;margin-bottom:28px;line-height:1.7;font-size:1rem">
    <?= nl2br(htmlspecialchars($pergunta['enunciado'])) ?>
  </div>

  <!-- Múltipla Escolha -->
  <?php if ($pergunta['tipo'] === 'multipla'): ?>
    <div class="card-title">Alternativas</div>
    <?php foreach ($pergunta['alternativas'] as $i => $alt): ?>
      <div class="alt-item <?= ($i == $pergunta['correta']) ? 'correta' : '' ?>">
        <div class="alt-label"><?= $letras[$i] ?></div>
        <span style="flex:1"><?= htmlspecialchars($alt) ?></span>
        <?php if ($i == $pergunta['correta']): ?>
          <span class="correta-badge">✓ Correta</span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

  <!-- Dissertativa -->
  <?php else: ?>
    <div class="card-title">Resposta Modelo (Gabarito)</div>
    <div style="background:rgba(29,212,240,.05);border:1px solid rgba(29,212,240,.2);border-radius:8px;padding:20px;line-height:1.7;color:var(--text)">
      <?= nl2br(htmlspecialchars($pergunta['resposta_modelo'])) ?>
    </div>
  <?php endif; ?>
</div>

<?php render_footer(); ?>
