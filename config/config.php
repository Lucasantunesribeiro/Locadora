<?php

// Configuração do ambiente
$env = $_ENV['APP_ENV'] ?? 'development';

return [
    'app' => [
        'name' => 'Sistema Locadora de Carros',
        'env' => $env,
        'debug' => $env === 'development',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    ],
    'database' => [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../database/dados.db',
    ],
    'session' => [
        'name' => 'locadora_session',
        'lifetime' => 3600, // 1 hora
        'secure' => $env === 'production',
        'httponly' => true,
    ],
];
