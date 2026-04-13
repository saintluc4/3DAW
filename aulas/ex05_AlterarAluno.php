<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Alterar Aluno</title>
    <style>
        table {
            border-collapse: collapse;
            width: 60%;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #2196F3;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        form input[type="text"] {
            padding: 6px;
            width: 250px;
        }
        form input[type="submit"] {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .msg  { color: green; font-weight: bold; }
        .erro { color: red;   font-weight: bold; }
    </style>
</head>
<body>

<?php
    $msg    = "";
    $cssMsg = "msg";

    // -------------------------------------------------------
    // PASSO 1 — Salvar alterações (formulário de edição enviado)
    // -------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'salvar') {

        $matriculaOriginal = $_POST["matricula_original"];
        $novoNome          = trim($_POST["nome"]);
        $novoEmail         = trim($_POST["email"]);

        if (!file_exists("alunos.txt")) {
            $msg    = "Arquivo de alunos não encontrado.";
            $cssMsg = "erro";
        } else {
            $arqAluno = fopen("alunos.txt", "r") or die("Erro ao abrir arquivo");
            $linhas = [];

            while (!feof($arqAluno)) {
                $linha = fgets($arqAluno);
                $linhas[] = $linha;
            }
            fclose($arqAluno);

            $encontrou = false;

            for ($i = 0; $i < count($linhas); $i++) {
                $dados = explode(";", trim($linhas[$i]));
                if ($dados[0] == $matriculaOriginal) {
                    $linhas[$i] = $matriculaOriginal . ";" . $novoNome . ";" . $novoEmail . "\n";
                    $encontrou  = true;
                    break;
                }
            }

            if ($encontrou) {
                $arqAluno = fopen("alunos.txt", "w") or die("Erro ao gravar arquivo");
                foreach ($linhas as $linha) {
                    fwrite($arqAluno, $linha);
                }
                fclose($arqAluno);
                $msg = "Aluno alterado com sucesso!";
            } else {
                $msg    = "Aluno não encontrado.";
                $cssMsg = "erro";
            }
        }
    }

    // -------------------------------------------------------
    // PASSO 2 — Exibir formulário de edição
    // -------------------------------------------------------
    if (isset($_GET['matricula'])) {
        $matriculaBusca  = $_GET['matricula'];
        $alunoEncontrado = null;

        if (file_exists("alunos.txt")) {
            $arqAluno = fopen("alunos.txt", "r") or die("Erro ao abrir arquivo");
            fgets($arqAluno); // Pula cabeçalho

            while (!feof($arqAluno)) {
                $linha = trim(fgets($arqAluno));
                if ($linha == "") continue;
                $dados = explode(";", $linha);
                if ($dados[0] == $matriculaBusca) {
                    $alunoEncontrado = $dados;
                    break;
                }
            }
            fclose($arqAluno);
        }

        if ($alunoEncontrado) { ?>

            <h1>Alterar Aluno</h1>
            <form action="ex05_AlterarAluno.php" method="POST">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="matricula_original" value="<?php echo $alunoEncontrado[0]; ?>">

                Matrícula: <input type="text" value="<?php echo $alunoEncontrado[0]; ?>" disabled>
                <br><br>
                Nome: <input type="text" name="nome" value="<?php echo $alunoEncontrado[1]; ?>">
                <br><br>
                Email: <input type="text" name="email" value="<?php echo $alunoEncontrado[2]; ?>">
                <br><br>
                <input type="submit" value="Salvar Alterações">
            </form>
            <br>
            <a href="ex05_AlterarAluno.php">&#8592; Voltar para lista</a>

        <?php } else {
            echo "<p class='erro'>Aluno não encontrado.</p>";
        }

    // -------------------------------------------------------
    // PASSO 3 — Listar alunos com botão Alterar
    // -------------------------------------------------------
    } else { ?>

        <h1>Alterar Aluno — Selecione</h1>

        <?php if ($msg != "") echo "<p class='$cssMsg'>$msg</p>"; ?>

        <table>
            <tr>
                <th>Matrícula</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Ação</th>
            </tr>

            <?php
                if (!file_exists("alunos.txt")) {
                    echo "<tr><td colspan='4'>Nenhum aluno cadastrado ainda.</td></tr>";
                } else {
                    $arqAluno = fopen("alunos.txt", "r") or die("Erro ao abrir arquivo");
                    fgets($arqAluno); // Pula cabeçalho

                    while (!feof($arqAluno)) {
                        $linha = trim(fgets($arqAluno));
                        if ($linha == "") continue;

                        $dados = explode(";", $linha);

                        echo "<tr>" .
                                "<td>" . $dados[0] . "</td>" .
                                "<td>" . $dados[1] . "</td>" .
                                "<td>" . $dados[2] . "</td>" .
                                "<td><a href='ex05_AlterarAluno.php?matricula=" . $dados[0] . "'>✏️ Alterar</a></td>" .
                             "</tr>";
                    }

                    fclose($arqAluno);
                }
            ?>

        </table>

        <br>
        <a href="ex03_IncluirAluno.php">&#8592; Cadastrar novo aluno</a>

    <?php } ?>

</body>
</html>
