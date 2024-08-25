<?php

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../services/UsuarioService.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';
class Usuario
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function buscarPorEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criarUsuario($nome, $email, $senha)
    {
        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)');
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $hashSenha);
        return $stmt->execute();
    }

    public function deleteUser($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
