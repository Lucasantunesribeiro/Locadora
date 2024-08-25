<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../services/CarroService.php';

$carroService = new CarroService();

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do carro não informado.']);
    exit;
}

$id = (int)$_GET['id'];

try {
    $sucesso = $carroService->deletarCarro($id);
    if ($sucesso) {
        echo json_encode(['status' => 'sucesso']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao deletar carro.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao deletar carro: ' . $e->getMessage()]);
}
?>
