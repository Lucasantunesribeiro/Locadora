<?php

/**
 * Script de inicialização do banco de dados
 * Garante que o banco seja criado e as tabelas inicializadas
 */

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Incluir autoloader
require_once __DIR__ . '/../src/autoload.php';

try {
    echo "Inicializando banco de dados...\n";
    
    // Obter instância do banco
    $db = Database::getInstance();
    
    echo "Banco de dados inicializado com sucesso!\n";
    
    // Verificar se há dados nas tabelas principais
    $usuarios = $db->query("SELECT COUNT(*) as count FROM usuarios")->fetch();
    $carros = $db->query("SELECT COUNT(*) as count FROM carros")->fetch();
    
    echo "Usuários cadastrados: " . $usuarios['count'] . "\n";
    echo "Carros cadastrados: " . $carros['count'] . "\n";
    
} catch (Exception $e) {
    echo "Erro ao inicializar banco de dados: " . $e->getMessage() . "\n";
    exit(1);
}
