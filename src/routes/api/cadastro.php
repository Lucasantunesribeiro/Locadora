<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../controllers/UsuarioController.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../../core/Database.php';

$usuarioController = new UsuarioController();
$authController = new AuthController();

$db = Database::getInstance();

$response = array();

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

    // Criptografar a senha
    $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

    // Verifica se o email já está cadastrado
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->fetchColumn() > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Este email já está cadastrado.';
        // Enviar resposta JSON e não redirecionar imediatamente
        echo json_encode($response);
        exit;
    } else {
        // Inserir os dados no banco de dados
        $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha_hash);

        // Executar a inserção e verificar se foi bem-sucedida
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Usuário criado com sucesso';
            echo json_encode($response);
            exit;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Erro ao criar a conta. Por favor, tente novamente.';
            echo json_encode($response);
            exit;
        }
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Método de requisição não permitido.';
    echo json_encode($response);
}
?>
