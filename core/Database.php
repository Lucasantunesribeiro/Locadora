<?php

class Database
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/config.php';
            $dbPath = $config['database']['database'];
            
            // Garantir que o diretório do banco de dados existe
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            $dsn = "{$config['database']['driver']}:{$dbPath}";
            
            self::$instance = new PDO($dsn);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Habilitar foreign keys no SQLite
            self::$instance->exec('PRAGMA foreign_keys = ON');
            
            // Criar tabelas se não existirem
            self::initSchema();
        }

        return self::$instance;
    }

    private static function initSchema()
    {
        try {
            $schemaPath = __DIR__ . '/../database/schema.sql';
            if (file_exists($schemaPath)) {
                $schema = file_get_contents($schemaPath);
                // Executar cada comando SQL separadamente
                $statements = array_filter(array_map('trim', explode(';', $schema)));
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        self::$instance->exec($statement);
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Database Schema Error: " . $e->getMessage());
            throw new Exception("Falha ao inicializar banco de dados: " . $e->getMessage());
        }
    }
}
