<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Incluir Aluno</title>
    <style>
        input[type="text"] {
            padding: 6px;
            width: 280px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="text"].campo-erro {
            border-color: red;
        }
        input[type="submit"] {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .erro  { color: red; font-size: 0.85em; margin: 2px 0 8px 0; display: none; }
        .msg   { color: green; font-weight: bold; }
        .campo { margin-bottom: 16px; }
    </style>
</head>
<body>

<h1>Cadastrar Novo Aluno</h1>

<?php
    $msg = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $matricula = trim($_POST["matricula"]);
        $nome      = trim($_POST["nome"]);
        $email     = trim($_POST["email"]);

        if (!file_exists("alunos.txt")) {
            $arqAluno = fopen("alunos.txt", "w") or die("Erro ao criar arquivo");
            fwrite($arqAluno, "matricula;nome;email\n");
            fclose($arqAluno);
        }

        $arqAluno = fopen("alunos.txt", "a") or die("Erro ao abrir arquivo");
        fwrite($arqAluno, $matricula . ";" . $nome . ";" . $email . "\n");
        fclose($arqAluno);

        $msg = "Aluno cadastrado com sucesso!";
    }
?>

<?php if ($msg != "") echo "<p class='msg'>✅ $msg</p>"; ?>

<form id="formAluno" action="ex03_IncluirAluno.php" method="POST" onsubmit="return validarFormulario()">

    <div class="campo">
        Matrícula:<br>
        <input type="text" id="matricula" name="matricula" maxlength="10">
        <p class="erro" id="erroMatricula"></p>
    </div>

    <div class="campo">
        Nome:<br>
        <input type="text" id="nome" name="nome" maxlength="100">
        <p class="erro" id="erroNome"></p>
    </div>

    <div class="campo">
        E-mail:<br>
        <input type="text" id="email" name="email" maxlength="150">
        <p class="erro" id="erroEmail"></p>
    </div>

    <input type="submit" value="Cadastrar Aluno">

</form>

<br>
<a href="ex04_listarTodosAlunos.php">&#8594; Listar alunos</a>

<script>
    // Exibe mensagem de erro abaixo do campo
    function mostrarErro(idCampo, idErro, mensagem) {
        document.getElementById(idCampo).classList.add("campo-erro");
        var spanErro = document.getElementById(idErro);
        spanErro.textContent = "⚠ " + mensagem;
        spanErro.style.display = "block";
    }

    // Limpa o erro de um campo
    function limparErro(idCampo, idErro) {
        document.getElementById(idCampo).classList.remove("campo-erro");
        var spanErro = document.getElementById(idErro);
        spanErro.textContent = "";
        spanErro.style.display = "none";
    }

    // Validação em tempo real ao sair do campo
    document.getElementById("matricula").addEventListener("blur", function () {
        validarMatricula();
    });

    document.getElementById("nome").addEventListener("blur", function () {
        validarNome();
    });

    document.getElementById("email").addEventListener("blur", function () {
        validarEmail();
    });

    // -------------------------------------------------------
    // Validação da Matrícula
    // -------------------------------------------------------
    function validarMatricula() {
        var valor = document.getElementById("matricula").value.trim();

        if (valor === "") {
            mostrarErro("matricula", "erroMatricula", "A matrícula é obrigatória.");
            return false;
        }
        if (!/^\d+$/.test(valor)) {
            mostrarErro("matricula", "erroMatricula", "A matrícula deve conter apenas números.");
            return false;
        }
        if (valor.length < 4 || valor.length > 10) {
            mostrarErro("matricula", "erroMatricula", "A matrícula deve ter entre 4 e 10 dígitos.");
            return false;
        }

        limparErro("matricula", "erroMatricula");
        return true;
    }

    // -------------------------------------------------------
    // Validação do Nome
    // -------------------------------------------------------
    function validarNome() {
        var valor = document.getElementById("nome").value.trim();

        if (valor === "") {
            mostrarErro("nome", "erroNome", "O nome é obrigatório.");
            return false;
        }
        if (valor.length < 3) {
            mostrarErro("nome", "erroNome", "O nome deve ter pelo menos 3 caracteres.");
            return false;
        }
        if (valor.length > 100) {
            mostrarErro("nome", "erroNome", "O nome deve ter no máximo 100 caracteres.");
            return false;
        }
        if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(valor)) {
            mostrarErro("nome", "erroNome", "O nome deve conter apenas letras e espaços.");
            return false;
        }

        limparErro("nome", "erroNome");
        return true;
    }

    // -------------------------------------------------------
    // Validação do E-mail
    // -------------------------------------------------------
    function validarEmail() {
        var valor = document.getElementById("email").value.trim();

        if (valor === "") {
            mostrarErro("email", "erroEmail", "O e-mail é obrigatório.");
            return false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
            mostrarErro("email", "erroEmail", "Informe um e-mail válido (ex: aluno@email.com).");
            return false;
        }

        limparErro("email", "erroEmail");
        return true;
    }

    // -------------------------------------------------------
    // Validação geral ao submeter o formulário
    // -------------------------------------------------------
    function validarFormulario() {
        var matriculaOk = validarMatricula();
        var nomeOk      = validarNome();
        var emailOk     = validarEmail();

        // Bloqueia o envio se houver qualquer erro
        return matriculaOk && nomeOk && emailOk;
    }
</script>

</body>
</html>
