<?php

class Database
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/config.php';
            $dsn = "{$config['database']['driver']}:{$config['database']['database']}";
            
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
        $schemaPath = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaPath)) {
            $schema = file_get_contents($schemaPath);
            self::$instance->exec($schema);
        }
    }
}
