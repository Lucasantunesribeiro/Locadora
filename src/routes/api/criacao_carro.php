<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../core/Database.php'; 
require_once __DIR__ . '/../../models/Carro.php'; 
require_once __DIR__ . '/../../services/CarroService.php'; // Corrigir o caminho se necessário

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber os dados do formulário e sanitizar
    $modelo = trim(htmlspecialchars($_POST['modelo']));
    $marca = trim(htmlspecialchars($_POST['marca']));
    $ano = $_POST['ano'];
    $cor = $_POST['cor'];
    $placa = strtoupper(str_replace('-', '', $_POST['placa']));
    $diaria = $_POST['diaria'];
    $disponibilidade = 'Disponível';

    // Instanciar o serviço de carro
    $carroService = new CarroService();

    // Criar carro usando o serviço
    $resultado = $carroService->createCarro($modelo, $marca, $ano, $cor, $placa, $diaria, $disponibilidade);

    if ($resultado['status'] === 'success') {
        $response['status'] = 'success';
        $response['message'] = 'Carro criado com sucesso.';
    } else {
        $response['status'] = 'error';
        $response['message'] = $resultado['message'];
    }

    echo json_encode($response);
    exit;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Método de requisição não permitido.';
    echo json_encode($response);
    exit;
}
?>
