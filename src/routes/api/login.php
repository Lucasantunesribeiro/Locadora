<?php
session_start();
header('Content-Type: application/json');

// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controllers/UsuarioController.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../../core/Database.php';

$usuarioController = new UsuarioController();
$authController = new AuthController();

$db = Database::getInstance();

$response = [];

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber os dados do formulário e sanitizar
    $nome = trim(htmlspecialchars($_POST['nome']));
    $email = trim(htmlspecialchars($_POST['email']));
    $senha = $_POST['senha'];

    // Verificar se os campos obrigatórios foram preenchidos
    if (empty($nome) || empty($email) || empty($senha)) {
        $response['status'] = 'error';
        $response['message'] = 'Todos os campos são obrigatórios.';
        echo json_encode($response);
        exit;
    }

    // Validação do Formato do Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['status'] = 'error';
        $response['message'] = 'Email inválido.';
        echo json_encode($response);
        exit;
    }

    // Buscar o usuário no banco de dados
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Armazenar os dados do usuário na sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $nome; // Armazenar o nome digitado pelo usuário na sessão
        $_SESSION['usuario_email'] = $usuario['email'];

        // Resposta JSON de sucesso com URL de redirecionamento
        $response['status'] = 'success';
        $response['redirect'] = '/locadora_de_carros/src/views/home.php'; // Redirecionamento
        echo json_encode($response);
        exit;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Email ou senha inválidos.';
        $response['redirect'] = '/locadora_de_carros/public/index.html'; // Redirecionamento
        echo json_encode($response);
        exit;
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Método de requisição não permitido.';
    echo json_encode($response);
}
?>
