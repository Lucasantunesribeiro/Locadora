<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Carro</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Criar Carro</h1>
    <?php
    session_start();
    if (isset($_SESSION['error_message'])) {
        echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
        unset($_SESSION['error_message']);
    }
    ?>
    <form id="create-car-form">
        <label for="modelo">Modelo:</label>
        <input type="text" id="modelo" name="modelo" required>
        
        <label for="marca">Marca:</label>
        <input type="text" id="marca" name="marca" required>
        
        <label for="ano">Ano:</label>
        <input type="number" id="ano" name="ano" required>
        
        <label for="cor">Cor:</label>
        <input type="text" id="cor" name="cor" required>
        
        <label for="placa">Placa:</label>
        <input type="text" id="placa" name="placa" required>
        
        <label for="diaria">Diária:</label>
        <input type="number" id="diaria" name="diaria" required>
        
        <button type="submit">Criar Carro</button>
        <a href="index.php" class="btn-voltar">Voltar</a>
    </form>
    <script src="js/create_car.js"></script>
</body>
</html>
