<?php

/**
 * Autoloader simples para o projeto
 */
spl_autoload_register(function ($class) {
    $prefixes = [
        'Controllers\\' => __DIR__ . '/controllers/',
        'Models\\' => __DIR__ . '/models/',
        'Services\\' => __DIR__ . '/services/',
        'Middlewares\\' => __DIR__ . '/middlewares/',
        'Core\\' => __DIR__ . '/../core/',
    ];

    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }

    // Fallback para classes sem namespace
    $locations = [
        __DIR__ . '/controllers/' . $class . '.php',
        __DIR__ . '/models/' . $class . '.php',
        __DIR__ . '/services/' . $class . '.php',
        __DIR__ . '/middlewares/' . $class . '.php',
        __DIR__ . '/../core/' . $class . '.php',
    ];
    
    foreach ($locations as $file) {
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Inicializar configuração e funções globais
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Helpers.php';