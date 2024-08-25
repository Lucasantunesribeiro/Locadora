<?php
session_start();
header('Content-Type: application/json');

// Incluindo os arquivos necessários
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../services/UsuarioService.php';

try {
    // Obtendo a conexão com o banco de dados a partir da classe Database
    $db = Database::getInstance();

    // Verificar se o usuário está logado
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['error' => 'Usuário não está logado.']);
        exit();
    }

    // Consultar todos os usuários
    $query = "SELECT id, nome, email FROM usuarios";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retornar os usuários como JSON
    echo json_encode($usuarios);
} catch (PDOException $e) {
    // Erro na conexão com o banco de dados ou na execução da query
    echo json_encode(['error' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Outros erros
    echo json_encode(['error' => 'Erro: ' . $e->getMessage()]);
}
?>
