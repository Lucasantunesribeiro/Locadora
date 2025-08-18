<?php
// Teste final de cadastro com limpeza máxima
@ini_set('display_errors', 0);
@ini_set('display_startup_errors', 0);
@error_reporting(0);

// Limpar todo buffer possível
while (@ob_get_level()) {
    @ob_end_clean();
}

// Suprimir todos os outputs
ob_start();

// Definir timezone sem output
@date_default_timezone_set('America/Sao_Paulo');

// Definir constantes sem output
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

try {
    // Incluir autoloader sem output
    require_once ROOT_PATH . '/src/autoload.php';
    
    // Limpar buffer novamente
    @ob_end_clean();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Método deve ser POST']);
        exit;
    }
    
    // Dados do teste
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (empty($nome) || empty($email) || empty($senha)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Todos os campos são obrigatórios']);
        exit;
    }
    
    // Conectar database silenciosamente
    $db = Database::getInstance();
    
    // Verificar se email já existe
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Email já cadastrado']);
        exit;
    }
    
    // Criar usuário
    $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO usuarios (nome, email, senha, role) VALUES (:nome, :email, :senha, :role)');
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $hashSenha);
    $stmt->bindValue(':role', 'user');
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Usuário criado com sucesso',
            'redirect' => '/login'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao criar usuário']);
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro: ' . $e->getMessage()]);
}

exit;
?>