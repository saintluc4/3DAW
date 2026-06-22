<?php
require_once 'functions.php';
require_once 'layout.php';

$erros   = [];
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao  = $_POST['acao'] ?? '';

    if ($acao === 'cadastrar') {
        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $conf  = $_POST['confirmar_senha'] ?? '';

        if (empty($nome))  $erros[] = 'Nome é obrigatório.';
        if (empty($email)) $erros[] = 'E-mail é obrigatório.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
        if (strlen($senha) < 6) $erros[] = 'Senha deve ter pelo menos 6 caracteres.';
        if ($senha !== $conf) $erros[] = 'As senhas não coincidem.';

        if (empty($erros)) {
            $resultado = salvar_usuario($nome, $email, $senha);
            if (isset($resultado['erro'])) {
                $erros[] = $resultado['erro'];
            } else {
                $sucesso = "Usuário \"{$resultado['nome']}\" cadastrado com sucesso!";
            }
        }
    }
}

$usuarios = ler_usuarios();

render_header('Gerenciar <span>Usuários</span>', 'Cadastro e listagem de usuários do sistema');
?>

<?php if ($sucesso): ?>
  <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>
<?php foreach ($erros as $e): ?>
  <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;align-items:start">

  <!-- Formulário -->
  <div class="card">
    <div class="card-title">Novo Usuário</div>
    <form method="POST" action="">
      <input type="hidden" name="acao" value="cadastrar">

      <div class="form-group">
        <label for="nome">Nome Completo</label>
        <input type="text" id="nome" name="nome" placeholder="Ex: João da Silva"
               value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" placeholder="joao@empresa.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres">
      </div>
      <div class="form-group">
        <label for="confirmar_senha">Confirmar Senha</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a senha">
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%">Cadastrar Usuário</button>
    </form>
  </div>

  <!-- Lista -->
  <div class="card">
    <div class="card-title">Usuários Cadastrados (<?= count($usuarios) ?>)</div>
    <?php if (empty($usuarios)): ?>
      <p style="color:var(--muted);font-size:.9rem">Nenhum usuário cadastrado ainda.</p>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Cadastro</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
          <tr>
            <td style="font-weight:500"><?= htmlspecialchars($u['nome']) ?></td>
            <td style="color:var(--muted);font-size:.88rem"><?= htmlspecialchars($u['email']) ?></td>
            <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars(substr($u['criado_em'],0,10)) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php render_footer(); ?>
