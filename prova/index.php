<?php
require_once 'functions.php';
require_once 'layout.php';

$perguntas  = ler_perguntas();
$total      = count($perguntas);
$multiplas  = 0; $textos = 0;
foreach ($perguntas as $p) {
    if ($p['tipo'] === 'multipla') $multiplas++;
    elseif ($p['tipo'] === 'texto') $textos++;
}
$recentes   = array_slice(array_reverse($perguntas), 0, 5);

render_header('Dashboard', 'Bem-vindo ao Sistema de Treinamento Corporativo WaterFalls');
?>

<div class="stats-grid">
  <div class="stat-card">
    <span class="stat-number" style="color:var(--accent)"><?= $total ?></span>
    <span class="stat-desc">Total de Perguntas</span>
  </div>
  <div class="stat-card">
    <span class="stat-number" style="color:var(--accent)"><?= $multiplas ?></span>
    <span class="stat-desc">Múltipla Escolha</span>
  </div>
  <div class="stat-card">
    <span class="stat-number" style="color:var(--accent2)"><?= $textos ?></span>
    <span class="stat-desc">Dissertativas</span>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">

  <a href="criar_multipla.php" style="text-decoration:none">
    <div class="card" style="cursor:pointer;transition:.2s;border-color:transparent" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='transparent'">
      <div class="card-title">Nova Pergunta — Múltipla Escolha</div>
      <p style="color:var(--muted);font-size:.9rem">Crie perguntas com até 5 alternativas e defina a resposta correta.</p>
    </div>
  </a>

  <a href="criar_texto.php" style="text-decoration:none">
    <div class="card" style="cursor:pointer;transition:.2s;border-color:transparent" onmouseover="this.style.borderColor='var(--accent2)'" onmouseout="this.style.borderColor='transparent'">
      <div class="card-title" style="color:var(--accent2)">Nova Pergunta — Dissertativa</div>
      <p style="color:var(--muted);font-size:.9rem">Crie perguntas abertas com resposta modelo para referência do gestor.</p>
    </div>
  </a>

</div>

<div class="card">
  <div class="card-title">Perguntas Recentes</div>
  <?php if (empty($recentes)): ?>
    <p style="color:var(--muted);font-size:.9rem">Nenhuma pergunta cadastrada ainda. <a href="criar_multipla.php" style="color:var(--accent)">Crie a primeira!</a></p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Enunciado</th>
          <th>Tipo</th>
          <th>Criado em</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentes as $p): ?>
        <tr>
          <td><?= htmlspecialchars(mb_strimwidth($p['enunciado'], 0, 80, '…')) ?></td>
          <td>
            <?php if ($p['tipo'] === 'multipla'): ?>
              <span class="badge badge-multipla">Múltipla</span>
            <?php else: ?>
              <span class="badge badge-texto">Texto</span>
            <?php endif; ?>
          </td>
          <td style="color:var(--muted);font-size:.83rem"><?= htmlspecialchars($p['criado_em']) ?></td>
          <td>
            <div class="action-row">
              <a href="visualizar.php?id=<?= urlencode($p['id']) ?>" class="btn btn-info btn-sm">Ver</a>
              <?php if ($p['tipo'] === 'multipla'): ?>
                <a href="alterar_multipla.php?id=<?= urlencode($p['id']) ?>" class="btn btn-secondary btn-sm">Editar</a>
              <?php else: ?>
                <a href="alterar_texto.php?id=<?= urlencode($p['id']) ?>" class="btn btn-secondary btn-sm">Editar</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="margin-top:16px">
    <a href="listar.php" class="btn btn-secondary">Ver todas as perguntas →</a>
  </div>
  <?php endif; ?>
</div>

<?php render_footer(); ?>
