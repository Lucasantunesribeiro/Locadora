<?php

class AuthMiddleware
{
    public static function verificarAutenticacao()
    {
        session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ../public/login.html');
            exit();
        }
    }
}
