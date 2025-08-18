<?php
// Debug específico para cadastro
error_reporting(0);
ini_set('display_errors', 0);

// Limpar buffer
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método deve ser POST']);
    exit;
}

try {
    // Simular dados do POST
    $data = [
        'nome' => $_POST['nome'] ?? 'não informado',
        'email' => $_POST['email'] ?? 'não informado',
        'senha' => $_POST['senha'] ?? 'não informado'
    ];
    
    echo json_encode([
        'status' => 'debug_success',
        'message' => 'Debug cadastro funcionando',
        'dados_recebidos' => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Erro no debug: ' . $e->getMessage()
    ]);
}

exit;
?>