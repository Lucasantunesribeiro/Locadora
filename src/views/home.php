<?php
session_start();
require_once __DIR__ . '/../../core/Database.php'; // Corrigido o caminho para Database.php

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

try {
    $db = Database::getInstance(); // Obtenha a conexão com o banco de dados
    $stmt = $db->prepare("SELECT email FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $_SESSION['usuario_id']);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "Usuário não encontrado.";
        header('Location: /locadora_de_carros/public/index.html');
        exit;
    }

    // Usar o nome fornecido pelo usuário durante o login
    $usuario_nome = $_SESSION['usuario_nome']; // Nome digitado pelo usuário
    $usuario_email = $usuario['email'];

} catch (PDOException $e) {
    echo "Erro ao conectar com o banco de dados: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Locadora de Carros</title>
    <link rel="stylesheet" href="/locadora_de_carros/public/css/home.css">
</head>

<body>
    <header class="header">
        <nav class="container_home">
            <h1 class="navbar__title">Bem-vindo à Locadora de Carros</h1>
            <div class="navbar">
                <ul class="navbar__menu">
                    <li class="navbar__item"><a href="home.php" class="navbar__link">Início</a></li>
                    <li class="navbar__item"><a href="create_car.php" class="navbar__link">Criar o Carro</a></li>
                    <li class="navbar__item"><a href="detalhar_carro.html" class="navbar__link">Detalhar os Carros</a>
                    </li>
                    <li class="navbar__item"><a href="listagem_carro.php" class="navbar__link">Listar os Carros</a></li>
                    <li class="navbar__item"><a href="alugar.html" class="navbar__link">Alugar um Carro</a></li>
                    <li class="navbar__item"><a href="deletar_carro.html" class="navbar__link">Deletar um Carro</a></li>
                    <li class="navbar__item"><a href="listagem_user.html" class="navbar__link">Listar as Contas</a></li>
                    <li class="navbar__item"><a href="deletar_user.html" class="navbar__link">Deletar a Conta</a></li>
                    <li class="navbar__item"><a href="logout.php" class="navbar__link">Sair da Conta</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <section class="user-info">
            <h2 class="user-info__greeting">Olá, <?php echo htmlspecialchars($usuario_nome); ?>!</h2>
            <p class="user-info__email">Você está logado com o email: <?php echo htmlspecialchars($usuario_email); ?>
            </p>
            <p class="user-info__welcome">Bem-vindo ao painel principal da locadora de carros. Use os links acima para
                navegar pelo sistema.</p>
        </section>

        <section class="important-info">
            <h2 class="important-info__title">Informações Importantes</h2>
            <p class="important-info__details">Aqui você pode gerenciar suas locações, atualizar seu perfil e muito
                mais.</p>
        </section>
    </main>

    <footer class="footer">
        <p class="footer__text">&copy; 2024 Locadora de Carros. Todos os direitos reservados.</p>
    </footer>


</body>

</html>