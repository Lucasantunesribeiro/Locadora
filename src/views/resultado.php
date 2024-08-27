<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado</title>
    <link rel="stylesheet" href="/locadora_de_carros/public/css/style.css">
</head>
<body>
    <header>
        <h1>Resultado</h1>
    </header>
    <main class="container">
        <?php
        $message = htmlspecialchars($_GET['message'] ?? 'Nenhuma mensagem', ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars($_GET['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
        echo "<p class=\"$type\" role=\"alert\">$message</p>";
        ?>
        <a href="home.php" class="btn-voltar" role="button">Voltar para a página inicial</a>
    </main>
</body>
</html>
