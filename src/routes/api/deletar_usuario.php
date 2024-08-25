<?php

require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../models/Usuario.php';

session_start();
header('Content-Type: application/json');

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit;
}

try {
    // Criação da instância de conexão com o banco de dados
    $db = new PDO('sqlite:C:/wamp64/www/locadora_de_carros/database/dados.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Instancia o objeto Usuario
    $usuario = new Usuario($db);

    $userId = $_SESSION['usuario_id'];

    // Chama o método deleteUser do modelo Usuario
    if ($usuario->deleteUser($userId)) {
        session_destroy();
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao excluir o usuário."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erro ao conectar com o banco de dados: " . $e->getMessage()]);
}
?>
