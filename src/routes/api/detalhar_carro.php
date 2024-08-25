<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../models/carro.php';
require_once __DIR__ . '/../../services/CarroService.php';

// Obtém a instância do PDO usando o método getInstance
$db = Database::getInstance();

// Cria as instâncias dos modelos e serviços
$carroModel = new CarroModel();
$carroService = new CarroService();

$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Busca os detalhes do carro
    $carro = $carroService->detalharCarro($id);

    if ($carro) {
        // Busca os aluguéis do carro
        $stmt = $db->prepare("SELECT * FROM alugueis WHERE carro_id = :carro_id ORDER BY data_inicio DESC");
        $stmt->bindParam(':carro_id', $id);
        $stmt->execute();
        $aluguéis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retorna os dados como JSON
        echo json_encode(['carro' => $carro, 'aluguéis' => $aluguéis]);
    } else {
        echo json_encode(['error' => 'Carro não encontrado.']);
    }
} else {
    echo json_encode(['error' => 'ID do carro não fornecido.']);
}
?>
