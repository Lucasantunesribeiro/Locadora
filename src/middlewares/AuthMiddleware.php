<?php

class AuthMiddleware
{
    public static function verificarAutenticacao()
    {
        session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /locadora_de_carros/src/views/login.html');
            exit();
        }
    }
}
