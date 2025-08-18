<?php
// Teste específico com dados do usuário
error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');

// Simular POST com dados exatos do usuário
$_POST = [
    'nome' => 'a',
    'email' => 'Lucas.afvr@gmail.com',
    'senha' => 'teste123'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

try {
    // Testar simple_register primeiro
    echo json_encode([
        'teste' => 'simple_register',
        'dados' => $_POST
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

exit;
?>