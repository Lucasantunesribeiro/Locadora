<?php

function dd(...$vars)
{
    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
    die();
}

function config($key = null, $default = null)
{
    static $config = null;
    
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    
    if ($key === null) {
        return $config;
    }
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function startSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        $config = config('session');
        
        session_name($config['name']);
        session_set_cookie_params([
            'lifetime' => $config['lifetime'],
            'httponly' => $config['httponly'],
            'secure' => $config['secure'],
            'samesite' => 'Strict'
        ]);
        
        session_start();
    }
}

function isAuthenticated()
{
    startSession();
    return isset($_SESSION['usuario_id']);
}

function redirectTo($path)
{
    $baseUrl = config('app.url');
    header("Location: {$baseUrl}{$path}");
    exit;
}

function jsonResponse($data, $statusCode = 200)
{
    // Suprimir erros
    error_reporting(0);
    ini_set('display_errors', 0);
    
    // Limpar buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
