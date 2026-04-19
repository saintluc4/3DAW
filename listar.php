<?php
require_once 'functions.php';
require_once 'layout.php';

$perguntas = ler_perguntas();
$filtro    = $_GET['tipo'] ?? 'todas';
if ($filtro === 'multipla') {
    $lista = array_filter($perguntas, fn($p) => $p['tipo'] === 'multipla');
} elseif ($filtro === 'texto') {
    $lista = array_filter($perguntas, fn($p) => $p['tipo'] === 'texto');
} else {
    $lista = $perguntas;
}

$sucesso = $_GET['excluido'] ?? '';

render_header('Banco de <span>Perguntas</span>', 'Gerencie todas as perguntas e respostas do sistema');
?>

<?php if ($sucesso): ?>
  <div class="alert alert-success">Pergunta excluída com sucesso.</div>
<?php endif; ?>

<!-- Filtros e ações -->
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap">
  <a href="listar.php" class="btn btn-sm <?= $filtro==='todas'?'btn-primary':'btn-secondary' ?>">Todas (<?= count($perguntas) ?>)</a>
  <a href="listar.php?tipo=multipla" class="btn btn-sm <?= $filtro==='multipla'?'btn-primary':'btn-secondary' ?>">Múltipla (<?= count(array_filter($perguntas,fn($p)=>$p['tipo']==='multipla')) ?>)</a>
  <a href="listar.php?tipo=texto" class="btn btn-sm <?= $filtro==='texto'?'btn-primary':'btn-secondary' ?>">Dissertativa (<?= count(array_filter($perguntas,fn($p)=>$p['tipo']==='texto')) ?>)</a>
  <div style="flex:1"></div>
  <a href="criar_multipla.php" class="btn btn-primary btn-sm">+ Múltipla Escolha</a>
  <a href="criar_texto.php" class="btn btn-info btn-sm">+ Dissertativa</a>
</div>

<div class="card">
  <?php if (empty($lista)): ?>
    <p style="color:var(--muted);text-align:center;padding:40px 0">
      Nenhuma pergunta cadastrada.
      <a href="criar_multipla.php" style="color:var(--accent)">Criar primeira pergunta</a>
    </p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Enunciado</th>
          <th>Tipo</th>
          <th>Alt.</th>
          <th>Criado em</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php $n = 1; foreach ($lista as $p): ?>
        <tr>
          <td style="color:var(--muted);font-size:.8rem"><?= $n++ ?></td>
          <td style="max-width:360px">
            <a href="visualizar.php?id=<?= urlencode($p['id']) ?>" style="color:var(--text);text-decoration:none;font-weight:500"
               onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text)'">
              <?= htmlspecialchars(mb_strimwidth($p['enunciado'], 0, 90, '…')) ?>
            </a>
          </td>
          <td>
            <?php if ($p['tipo'] === 'multipla'): ?>
              <span class="badge badge-multipla">Múltipla</span>
            <?php else: ?>
              <span class="badge badge-texto">Dissertativa</span>
            <?php endif; ?>
          </td>
          <td style="color:var(--muted)">
            <?= $p['tipo']==='multipla' ? count($p['alternativas']) : '—' ?>
          </td>
          <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars(substr($p['criado_em'],0,10)) ?></td>
          <td>
            <div class="action-row">
              <a href="visualizar.php?id=<?= urlencode($p['id']) ?>" class="btn btn-info btn-sm">Ver</a>
              <?php if ($p['tipo'] === 'multipla'): ?>
                <a href="alterar_multipla.php?id=<?= urlencode($p['id']) ?>" class="btn btn-secondary btn-sm">Editar</a>
              <?php else: ?>
                <a href="alterar_texto.php?id=<?= urlencode($p['id']) ?>" class="btn btn-secondary btn-sm">Editar</a>
              <?php endif; ?>
              <a href="excluir.php?id=<?= urlencode($p['id']) ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('Excluir permanentemente esta pergunta?')">Excluir</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php render_footer(); ?>
