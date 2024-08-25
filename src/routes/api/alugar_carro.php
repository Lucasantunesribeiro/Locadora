<?php

require_once __DIR__ . '/../../services/AluguelService.php';
require_once __DIR__ . '/../../models/Aluguel.php';
require_once __DIR__ . '/../../../core/Database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$aluguelService = new AluguelService();

session_start();

if (!isset($_SESSION['usuario_id'])) {
    $response['message'] = "Usuário não está logado.";
    echo json_encode($response);
    exit();
}

$carro_id = $_POST['carro_id'];
$usuario_id = $_POST['usuario_id'];
$data_inicio = $_POST['data_inicio'];
$data_fim = $_POST['data_fim'];

$data_atual = date('Y-m-d'); // Data atual
if ($data_inicio < $data_atual) {
    $response['message'] = "A data de início não pode ser anterior a " . $data_atual;
    echo json_encode($response);
    exit();
}

if ($aluguelService->verificarDisponibilidade($carro_id, $data_inicio, $data_fim) > 0) {
    $response['message'] = "Carro não disponível para este período.";
    echo json_encode($response);
    exit();
}

$valor_diaria = $aluguelService->obterValorDiaria($carro_id);

if ($valor_diaria === false) {
    $response['message'] = "Carro não encontrado.";
    echo json_encode($response);
    exit();
}

$dias = (strtotime($data_fim) - strtotime($data_inicio)) / (60 * 60 * 24) + 1;
$valor_total = $dias * $valor_diaria;

$aluguelService->inserirAluguel($carro_id, $usuario_id, $data_inicio, $data_fim, $valor_total);
$aluguelService->atualizarDisponibilidade($carro_id);

$response['success'] = true;
$response['message'] = "Aluguel realizado com sucesso!";
echo json_encode($response);
?>
