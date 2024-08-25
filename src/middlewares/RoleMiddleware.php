<?php
class RoleMiddleware
{
    public static function checkRole($requiredRole)
    {
        if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== $requiredRole) {
            header('Location: ../public/unauthorized.html');
            exit();
        }
    }
}
