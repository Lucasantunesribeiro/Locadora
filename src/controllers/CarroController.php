<?php

class CarroController
{
    private $carroModel;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->carroModel = new Carro($db);
    }

    public function listar()
    {
        // Limpar qualquer output anterior
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            $carros = $this->carroModel->listarTodos();
            header('Content-Type: application/json');
            echo json_encode($carros);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao listar carros']);
            exit;
        }
    }

    public function detalhar()
    {
        @ini_set('display_errors', 0);
        @error_reporting(0);
        
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        $path = $_SERVER['REQUEST_URI'];
        $id = basename($path);

        if (!is_numeric($id)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        try {
            $carro = $this->carroModel->buscarPorId($id);
            if ($carro) {
                header('Content-Type: application/json');
                echo json_encode($carro);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Carro não encontrado']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao buscar carro']);
            exit;
        }
    }

    public function criar()
    {
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
            $marca = $_POST['marca'] ?? '';
            $modelo = $_POST['nome'] ?? $_POST['modelo'] ?? '';
            $ano = $_POST['ano'] ?? '';
            $preco_diario = $_POST['preco_diario'] ?? '';
            $cor = $_POST['cor'] ?? 'Não informado';

            // Validação simples
            if (empty($marca) || empty($modelo) || empty($ano) || empty($preco_diario)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Todos os campos são obrigatórios']);
                exit;
            }

            if (!is_numeric($ano) || $ano < 1900 || $ano > date('Y')) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Ano inválido']);
                exit;
            }

            if (!is_numeric($preco_diario) || $preco_diario <= 0) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Preço diário deve ser um número positivo']);
                exit;
            }

            if ($this->carroModel->criar($marca, $modelo, $ano, $cor, $preco_diario)) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Carro criado com sucesso'
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Erro ao criar carro']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno']);
            exit;
        }
    }

    public function atualizar()
    {
        @ini_set('display_errors', 0);
        @error_reporting(0);
        
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        // Suportar PUT /api/carros/123 ou POST /api/atualizar_carro
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['carro_id'] ?? '';
            $marca = $_POST['marca'] ?? '';
            $modelo = $_POST['modelo'] ?? '';
            $ano = $_POST['ano'] ?? '';
            $cor = $_POST['cor'] ?? '';
            $preco_diario = $_POST['preco_diario'] ?? '';
        } else {
            $path = $_SERVER['REQUEST_URI'];
            $id = basename($path);
            $input = json_decode(file_get_contents('php://input'), true);
            $marca = $input['marca'] ?? '';
            $modelo = $input['modelo'] ?? '';
            $ano = $input['ano'] ?? '';
            $cor = $input['cor'] ?? '';
            $preco_diario = $input['preco_diario'] ?? '';
        }

        if (!is_numeric($id)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        if (empty($marca) || empty($modelo) || empty($ano) || empty($preco_diario)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Todos os campos são obrigatórios']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("UPDATE carros SET marca = :marca, modelo = :modelo, ano = :ano, cor = :cor, preco_diario = :preco_diario WHERE id = :id");
            $stmt->bindParam(':marca', $marca);
            $stmt->bindParam(':modelo', $modelo);
            $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
            $stmt->bindParam(':cor', $cor);
            $stmt->bindParam(':preco_diario', $preco_diario);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Carro atualizado com sucesso'
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Carro não encontrado']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao atualizar carro']);
            exit;
        }
    }

    public function deletar()
    {
        // Limpar qualquer output anterior
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Suportar tanto DELETE /api/carros/123 quanto POST /api/deletar_carro
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['carro_id'] ?? '';
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
            if ($this->carroModel->deletar($id)) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Carro deletado com sucesso'
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Carro não encontrado']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao deletar carro']);
            exit;
        }
    }
}
