<?php

    if (extension_loaded('xdebug')) {
        ini_set('xdebug.mode', 'off');
    }

    $requestMethod = $_SERVER["REQUEST_METHOD"] ?? 'GET';

    if (php_sapi_name() === 'cli') {
        foreach (array_slice($argv, 1) as $arg) {
            if (str_contains($arg, '=')) {
                list($k, $v) = explode('=', $arg, 2);
                $_POST[$k] = $v;
            }
        }
        if (!empty($_POST)) {
            $requestMethod = 'POST';
        }
    }

    $resultado = ""; 

    if ($requestMethod == "POST") {

        $a = (float)$_POST["a"];
        $b = (float)$_POST["b"];
        $operacao = $_POST["operacao"];

        switch ($operacao) {
            case 'somar':

                $resultado = $a + $b;
                break;

            case 'subtrair':

                $resultado = $a - $b;
                break;

            case 'multiplicar':

                $resultado = $a * $b;
                break;

            case 'dividir':
                if ($b != 0) {
                    $resultado = $a / $b;
                } else {
                    $resultado = "Erro: Divisão por zero não permitida!";
                }
                break;
            default:
                $resultado = "Operação inválida.";
                break;
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Calculadora PHP</title>
</head>
<body>
    <h1><?php echo 'Minha Calculadora!';?></h1>

    <form method='POST' action='calculadora.php'>
        a: <input type='number' step='any' name='a' required><br><br>
        
        Operação: 
        <select name="operacao">
            <option value="somar">Somar (+)</option>
            <option value="subtrair">Subtrair (-)</option>
            <option value="multiplicar">Multiplicar (*)</option>
            <option value="dividir">Dividir (/)</option>
        </select><br><br>

        b: <input type='number' step='any' name='b' required><br><br>
        
        <input type='submit' value='Calcular'>
    </form>
    
    <br>
    
    <?php
    if ($requestMethod == "POST") {
        echo '<strong>Resultado: </strong> ' . $resultado; 
    }
    ?>
    
</body>
</html>