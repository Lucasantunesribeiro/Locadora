<?php

/**
 * Endpoint de verificação de saúde da aplicação
 * Verifica se todos os componentes estão funcionando
 */

// Suprimir erros para este endpoint específico
error_reporting(0);
ini_set('display_errors', 0);

// Limpar qualquer output
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');

try {
    // Definir constantes se não estiverem definidas
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(__DIR__));
    }
    
    // Verificar se o autoloader existe
    $autoloaderPath = ROOT_PATH . '/src/autoload.php';
    if (!file_exists($autoloaderPath)) {
        throw new Exception('Autoloader não encontrado');
    }
    
    require_once $autoloaderPath;
    
    // Verificar configuração
    $config = config();
    if (empty($config)) {
        throw new Exception('Configuração não carregada');
    }
    
    // Verificar banco de dados
    $db = Database::getInstance();
    $db->query("SELECT 1")->fetch();
    
    // Verificar se as tabelas principais existem
    $tables = ['usuarios', 'carros', 'alugueis'];
    foreach ($tables as $table) {
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'")->fetch();
        if (!$result) {
            throw new Exception("Tabela $table não encontrada");
        }
    }
    
    // Tudo OK
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'environment' => config('app.env'),
        'database' => 'connected',
        'tables' => 'ok'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'unhealthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage()
    ]);
}
