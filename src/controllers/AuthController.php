<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../../core/Database.php';

class AuthController
{
    private $usuarioModel;

    public function __construct()
    {
        $db = Database::getInstance(); // Obtenha a instância do banco de dados
        $this->usuarioModel = new Usuario($db); // Passe o banco de dados para o modelo
    }

    public function login($email, $senha)
    {
        session_start(); // Inicie a sessão

        $usuario = $this->usuarioModel->buscarPorEmail($email);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_email'] = $usuario['email']; // Armazene o email também
            $_SESSION['usuario_nome'] = $usuario['nome']; // Armazene o nome se necessário
            echo json_encode(['status' => 'success']); // Retorne uma resposta JSON
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Credenciais inválidas.']);
        }
    }

    public function logout()
    {
        session_start(); // Inicie a sessão
        session_unset();
        session_destroy();
        header('Location: /locadora_de_carros/src/views/login.html');
        exit();
    }
}
?>
