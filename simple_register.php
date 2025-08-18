<?php
require_once __DIR__ . '/src/autoload.php';

// Debug simples de cadastro
error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (empty($nome) || empty($email) || empty($senha)) {
        echo json_encode(['error' => 'Dados obrigatórios não preenchidos']);
        exit;
    }
    
    // Conectar database
    $db = Database::getInstance();
    
    // Verificar se email já existe
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetch()) {
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
        echo json_encode([
            'status' => 'success',
            'message' => 'Usuário criado com sucesso',
            'redirect' => '/login'
        ]);
    } else {
        echo json_encode(['error' => 'Erro ao criar usuário']);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro: ' . $e->getMessage()]);
}

exit;
?>