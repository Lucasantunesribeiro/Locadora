<?php
class RoleMiddleware
{
    public static function checkRole($requiredRole)
    {
        if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== $requiredRole) {
            header('Location: ./locadora_de_carros/src/views/unauthorized.html');
            exit();
        }
    }
}
