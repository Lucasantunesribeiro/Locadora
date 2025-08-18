<?php

class UsuarioController {
    private $usuarioModel;

    public function __construct() {
        $db = Database::getInstance();
        $this->usuarioModel = new Usuario($db);
    }

    public function criar() {
        // Máxima supressão de erros
        @ini_set('display_errors', 0);
        @error_reporting(0);
        
        while (@ob_get_level()) {
            @ob_end_clean();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método não permitido']);
            exit;
        }

        try {
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';
            $role = $_POST['role'] ?? 'user';

            if (empty($nome) || empty($email) || empty($senha)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Todos os campos são obrigatórios']);
                exit;
            }

            // Verificar se email já existe
            if ($this->usuarioModel->buscarPorEmail($email)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Email já cadastrado']);
                exit;
            }

            // Criar usuário
            $db = Database::getInstance();
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO usuarios (nome, email, senha, role) VALUES (:nome, :email, :senha, :role)');
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $hashSenha);
            $stmt->bindParam(':role', $role);

            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Usuário criado com sucesso'
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Erro ao criar usuário']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno']);
            exit;
        }
    }

    public function atualizar() {
        @ini_set('display_errors', 0);
        @error_reporting(0);
        
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        // Suportar PUT /api/usuarios/123 ou POST /api/atualizar_usuario
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['usuario_id'] ?? '';
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? '';
        } else {
            $path = $_SERVER['REQUEST_URI'];
            $id = basename($path);
            $input = json_decode(file_get_contents('php://input'), true);
            $nome = $input['nome'] ?? '';
            $email = $input['email'] ?? '';
            $role = $input['role'] ?? '';
        }

        if (!is_numeric($id) || empty($nome) || empty($email)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Dados inválidos']);
            exit;
        }

        try {
            $db = Database::getInstance();
            
            // Verificar se email já existe em outro usuário
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Email já está em uso']);
                exit;
            }

            $stmt = $db->prepare("UPDATE usuarios SET nome = :nome, email = :email, role = :role WHERE id = :id");
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Usuário atualizado com sucesso'
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Usuário não encontrado']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao atualizar usuário']);
            exit;
        }
    }

    public function listar() {
        // Limpar qualquer output anterior
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT id, nome, email, role FROM usuarios ORDER BY created_at DESC");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode($usuarios);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao listar usuários']);
            exit;
        }
    }

    public function deletar() {
        // Limpar qualquer output anterior
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Suportar tanto DELETE /api/usuarios/123 quanto POST /api/deletar_usuario
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['usuario_id'] ?? '';
        } else {
            $path = $_SERVER['REQUEST_URI'];
            $id = basename($path);
        }

        if (!is_numeric($id)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Usuário deletado com sucesso'
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Usuário não encontrado']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao deletar usuário']);
            exit;
        }
    }

    public function obter() {
        @ini_set('display_errors', 0);
        @error_reporting(0);
        
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        // GET /api/usuarios/123
        $path = $_SERVER['REQUEST_URI'];
        $id = basename($path);

        if (!is_numeric($id)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT id, nome, email, role, created_at FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                header('Content-Type: application/json');
                echo json_encode($usuario);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Usuário não encontrado']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao buscar usuário']);
            exit;
        }
    }

    public function obterPerfil() {
        @ini_set('display_errors', 0);
        @error_reporting(0);
        
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        // Verificar se está autenticado
        startSession();
        if (!isAuthenticated()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Não autenticado']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT id, nome, email, role, created_at FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $_SESSION['usuario_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                header('Content-Type: application/json');
                echo json_encode($usuario);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Usuário não encontrado']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao buscar perfil']);
            exit;
        }
    }

    public function atualizarPerfil() {
        @ini_set('display_errors', 0);
        @error_reporting(0);
        
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        // Verificar se está autenticado
        startSession();
        if (!isAuthenticated()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Não autenticado']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método não permitido']);
            exit;
        }

        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';

        if (empty($nome) || empty($email)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Nome e email são obrigatórios']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $usuario_id = $_SESSION['usuario_id'];

            // Verificar se email já existe em outro usuário
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Email já está em uso']);
                exit;
            }

            // Se nova senha foi fornecida, verificar senha atual
            if (!empty($nova_senha)) {
                if (empty($senha_atual)) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Senha atual é obrigatória para alteração']);
                    exit;
                }

                // Verificar senha atual
                $stmt = $db->prepare("SELECT senha FROM usuarios WHERE id = :id");
                $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
                $stmt->execute();
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$usuario || !password_verify($senha_atual, $usuario['senha'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Senha atual incorreta']);
                    exit;
                }

                // Atualizar com nova senha
                $hash_nova_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET nome = :nome, email = :email, senha = :senha WHERE id = :id");
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':senha', $hash_nova_senha);
                $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
            } else {
                // Atualizar apenas nome e email
                $stmt = $db->prepare("UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id");
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
            }

            if ($stmt->execute()) {
                // Atualizar session com novo nome
                $_SESSION['usuario_nome'] = $nome;
                
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Perfil atualizado com sucesso'
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Erro ao atualizar perfil']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno']);
            exit;
        }
    }
}
?>
