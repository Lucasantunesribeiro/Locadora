<?php

class AuthMiddleware
{
    public function handle()
    {
        startSession();

        if (!isAuthenticated()) {
            if ($this->isApiRequest()) {
                jsonResponse(['error' => 'Não autorizado'], 401);
            } else {
                redirectTo('/login');
            }
            return false;
        }

        return true;
    }

    private function isApiRequest()
    {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false ||
               (isset($_SERVER['HTTP_ACCEPT']) && 
                strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    public static function verificarAutenticacao()
    {
        $middleware = new self();
        return $middleware->handle();
    }
}
