<?php

// require_once __DIR__ . '/../services/AluguelService.php';
// require_once __DIR__ . '/../../core/.php';

// $aluguelService = new AluguelService();

// session_start();

// if (!isset($_SESSION['usuario_id'])) {
//     header("Location: ../public/resultado.php?message=" . urlencode("Usuário não está logado.") . "&type=error");
//     exit();
// }

// $carro_id = $_POST['carro_id'];
// $usuario_id = $_POST['usuario_id'];
// $data_inicio = $_POST['data_inicio'];
// $data_fim = $_POST['data_fim'];

// $data_atual = date('Y-m-d'); // Data atual
// if ($data_inicio < $data_atual) {
//     header("Location: ../../public/resultado.php?message=" . urlencode("A data de início não pode ser anterior a " . $data_atual) . "&type=error");
//     exit();
// }

// if ($aluguelService->verificarDisponibilidade($carro_id, $data_inicio, $data_fim) > 0) {
//     header("Location: ../../public/resultado.php?message=" . urlencode("Carro não disponível para este período.") . "&type=error");
//     exit();
// }

// $valor_diaria = $aluguelService->obterValorDiaria($carro_id);

// if ($valor_diaria === false) {
//     header("Location: ../../public/resultado.php?message=" . urlencode("Carro não encontrado.") . "&type=error");
//     exit();
// }

// $dias = (strtotime($data_fim) - strtotime($data_inicio)) / (60 * 60 * 24) + 1;
// $valor_total = $dias * $valor_diaria;

// $aluguelService->inserirAluguel($carro_id, $usuario_id, $data_inicio, $data_fim, $valor_total);
// $aluguelService->atualizarDisponibilidade($carro_id);

// header("Location: ../../public/resultado.php?message=" . urlencode("Aluguel realizado com sucesso!") . "&type=success");
// exit();
