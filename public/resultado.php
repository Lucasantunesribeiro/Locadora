<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Resultado</h1>
    </header>
    <div class="container">
        <?php
        $message = $_GET['message'] ?? 'Nenhuma mensagem';
        $type = $_GET['type'] ?? 'info';
        echo "<p class=\"$type\">$message</p>";
        ?>
        <a href="index.html">Voltar para a página inicial</a>
    </div>
</body>
</html>
