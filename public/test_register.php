<?php
// Suprimir todos os erros
error_reporting(0);
ini_set('display_errors', 0);

// Limpar buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Testar apenas resposta JSON
header('Content-Type: application/json');
echo json_encode([
    'status' => 'test_success',
    'message' => 'Endpoint de teste funcionando',
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST
]);
exit;
?>