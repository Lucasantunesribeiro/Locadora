<?php

require_once __DIR__ . '/../services/UsuarioService.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    private $usuarioService;

    public function __construct() {
        $this->usuarioService = new UsuarioService();
    }

    public function criar() {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if ($nome && $email && $senha) {
            $resultado = $this->usuarioService->criarUsuario($nome, $email, $senha);
            if ($resultado) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao criar usuário']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dados incompletos']);
        }
    }

    public function buscarPorEmail($email) {
        return $this->usuarioService->buscarPorEmail($email);
    }

    public function deleteUser($id) {
        return $this->usuarioService->deleteUser($id);
    }
}
?>
