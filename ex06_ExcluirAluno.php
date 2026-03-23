<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Excluir Aluno</title>
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
            background-color: #e53935;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .btn-excluir {
            padding: 5px 12px;
            background-color: #e53935;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-confirmar {
            padding: 8px 16px;
            background-color: #e53935;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn-cancelar {
            padding: 8px 16px;
            background-color: #9E9E9E;
            color: white;
            text-decoration: none;
            margin-left: 10px;
        }
        .msg  { color: green; font-weight: bold; }
        .erro { color: red;   font-weight: bold; }
        .box-confirmar {
            border: 1px solid #e53935;
            padding: 20px;
            width: 400px;
            border-radius: 6px;
            background-color: #fff3f3;
        }
    </style>
</head>
<body>

<?php
    $msg    = "";
    $cssMsg = "msg";

    // -------------------------------------------------------
    // PASSO 1 — Confirmar e executar a exclusão
    // -------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'excluir') {

        $matriculaExcluir = $_POST["matricula_excluir"];

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

            $encontrou   = false;
            $novasLinhas = [];

            // Copia todas as linhas EXCETO a do aluno a ser excluído
            foreach ($linhas as $linha) {
                $dados = explode(";", trim($linha));
                if ($dados[0] == $matriculaExcluir) {
                    $encontrou = true; // Encontrou — não copia
                } else {
                    $novasLinhas[] = $linha; // Mantém as demais
                }
            }

            if ($encontrou) {
                $arqAluno = fopen("alunos.txt", "w") or die("Erro ao gravar arquivo");
                foreach ($novasLinhas as $linha) {
                    fwrite($arqAluno, $linha);
                }
                fclose($arqAluno);
                $msg = "Aluno excluído com sucesso!";
            } else {
                $msg    = "Aluno não encontrado.";
                $cssMsg = "erro";
            }
        }
    }

    // -------------------------------------------------------
    // PASSO 2 — Exibir tela de confirmação de exclusão
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

            <h1>Excluir Aluno</h1>

            <div class="box-confirmar">
                <p>Tem certeza que deseja excluir o aluno abaixo?</p>
                <p>
                    <strong>Matrícula:</strong> <?php echo $alunoEncontrado[0]; ?><br>
                    <strong>Nome:</strong>      <?php echo $alunoEncontrado[1]; ?><br>
                    <strong>Email:</strong>     <?php echo $alunoEncontrado[2]; ?>
                </p>

                <form action="ex06_ExcluirAluno.php" method="POST">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="matricula_excluir" value="<?php echo $alunoEncontrado[0]; ?>">
                    <input type="submit" class="btn-confirmar" value="✅ Confirmar Exclusão">
                    <a href="ex06_ExcluirAluno.php" class="btn-cancelar">✖ Cancelar</a>
                </form>
            </div>

        <?php } else {
            echo "<p class='erro'>Aluno não encontrado.</p>";
            echo "<br><a href='ex06_ExcluirAluno.php'>&#8592; Voltar para lista</a>";
        }

    // -------------------------------------------------------
    // PASSO 3 — Listar alunos com botão Excluir
    // -------------------------------------------------------
    } else { ?>

        <h1>Excluir Aluno — Selecione</h1>

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
                                "<td><a href='ex06_ExcluirAluno.php?matricula=" . $dados[0] . "'>" .
                                "<button class='btn-excluir'>🗑️ Excluir</button></a></td>" .
                             "</tr>";
                    }

                    fclose($arqAluno);
                }
            ?>

        </table>

        <br>
        <a href="ex03_IncluirAluno.php">&#8592; Cadastrar novo aluno</a> &nbsp;|&nbsp;
        <a href="ex05_AlterarAluno.php">✏️ Alterar aluno</a>

    <?php } ?>

</body>
</html>
