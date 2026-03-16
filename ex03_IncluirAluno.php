<?php
    $msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $matricula = $_POST["matricula"];
    $nome      = $_POST["nome"];
    $email     = $_POST["email"];

    if (!file_exists("alunos.txt")) {
        $arqAluno = fopen("alunos.txt", "w") or die("Erro ao criar arquivo");
        $cabecalho = "matricula;nome;email\n";
        fwrite($arqAluno, $cabecalho);
        fclose($arqAluno);
    }

    $arqAluno = fopen("alunos.txt", "a") or die("Erro ao abrir arquivo");
    $linha = $matricula . ";" . $nome . ";" . $email . "\n";
    fwrite($arqAluno, $linha);
    fclose($arqAluno);

    $msg = "Aluno cadastrado com sucesso!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Incluir Aluno</title>
</head>
<body>
    <h1>Cadastrar Novo Aluno</h1>

    <form action="ex03_IncluirAluno.php" method="POST">
        Matrícula: <input type="text" name="matricula">
        <br><br>
        Nome: <input type="text" name="nome">
        <br><br>
        Email: <input type="text" name="email">
        <br><br>
        <input type="submit" value="Cadastrar Aluno">
    </form>

    <p><?php echo $msg; ?></p>
</body>
</html>
