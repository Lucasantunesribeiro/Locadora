<?php

class AuthController
{
    private $usuarioModel;
    private $validator;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->usuarioModel = new Usuario($db);
        $this->validator = new Validator();
    }

    public function login()
    {
        // Limpar qualquer output anterior
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método não permitido']);
            exit;
        }

        $data = [
            'email' => $_POST['email'] ?? '',
            'senha' => $_POST['senha'] ?? ''
        ];

        $data = sanitize($data);

        $rules = [
            'email' => 'required|email',
            'senha' => 'required|min:6'
        ];

        if (!$this->validator->validate($data, $rules)) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Dados inválidos',
                'errors' => $this->validator->getErrors()
            ]);
            exit;
        }

        $usuario = $this->usuarioModel->buscarPorEmail($data['email']);

        if ($usuario && password_verify($data['senha'], $usuario['senha'])) {
            startSession();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_role'] = $usuario['role'];

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Login realizado com sucesso',
                'redirect' => '/'
            ]);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Email ou senha inválidos'
            ]);
            exit;
        }
    }

    public function register()
    {
        // Máxima supressão de erros
        @ini_set('display_errors', 0);
        @ini_set('display_startup_errors', 0);
        @error_reporting(0);
        
        // Limpar todo buffer possível
        while (@ob_get_level()) {
            @ob_end_clean();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método não permitido']);
            exit;
        }

        try {
            $data = [
                'nome' => $_POST['nome'] ?? '',
                'email' => $_POST['email'] ?? '',
                'senha' => $_POST['senha'] ?? ''
            ];

            // Sanitização simples sem função externa
            $data['nome'] = trim($data['nome']);
            $data['email'] = trim($data['email']);
            $data['senha'] = trim($data['senha']);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao processar dados']);
            exit;
        }

        // Validação simples sem Validator
        if (empty($data['nome']) || strlen($data['nome']) < 2) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Nome deve ter pelo menos 2 caracteres']);
            exit;
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Email inválido']);
            exit;
        }
        
        if (empty($data['senha']) || strlen($data['senha']) < 6) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Senha deve ter pelo menos 6 caracteres']);
            exit;
        }

        try {
            // Verificar se email já existe
            if ($this->usuarioModel->buscarPorEmail($data['email'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Email já cadastrado']);
                exit;
            }

            if ($this->usuarioModel->criarUsuario($data['nome'], $data['email'], $data['senha'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Usuário criado com sucesso',
                    'redirect' => '/login'
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

    public function logout()
    {
        // Limpar qualquer output anterior
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        startSession();
        session_unset();
        session_destroy();

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Logout realizado com sucesso',
            'redirect' => '/login'
        ]);
        exit;
    }
}