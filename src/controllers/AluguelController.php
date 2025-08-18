<?php

class AluguelController
{
    private $aluguelModel;
    private $carroModel;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->aluguelModel = new Aluguel($db);
        $this->carroModel = new Carro($db);
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

        startSession();
        if (!isAuthenticated()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }

        try {
            $carro_id = $_POST['carro_id'] ?? '';
            $data_inicio = $_POST['data_inicio'] ?? '';
            $data_fim = $_POST['data_fim'] ?? '';

            // Validação simples
            if (empty($carro_id) || empty($data_inicio) || empty($data_fim)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Todos os campos são obrigatórios']);
                exit;
            }

            if (!is_numeric($carro_id)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'ID do carro inválido']);
                exit;
            }

            // Verificar se o carro existe e está disponível
            $carro = $this->carroModel->buscarPorId($carro_id);
            if (!$carro || !$carro['disponivel']) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Carro não disponível']);
                exit;
            }

            // Verificar se as datas são válidas
            $dataInicio = new DateTime($data_inicio . ' 00:00:00');
            $dataFim = new DateTime($data_fim . ' 23:59:59');
            $hoje = new DateTime('today');

            if ($dataInicio < $hoje) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Data de início não pode ser no passado']);
                exit;
            }

            if ($dataFim <= $dataInicio) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Data fim deve ser posterior à data início']);
                exit;
            }

            // Calcular valor total - incluir o dia de início
            $dias = $dataInicio->diff($dataFim)->days + 1;
            $valorTotal = $dias * $carro['preco_diario'];

            if ($this->aluguelModel->criar($_SESSION['usuario_id'], $carro_id, $data_inicio, $data_fim, $valorTotal)) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Aluguel realizado com sucesso',
                    'valor_total' => $valorTotal,
                    'dias' => $dias
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Erro ao realizar aluguel']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno']);
            exit;
        }
    }

    public function listarTodos()
    {
        @ini_set('display_errors', 0);
        @error_reporting(0);
        
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT a.*, u.nome as usuario_nome, u.email as usuario_email, 
                       c.marca, c.modelo, c.ano 
                FROM alugueis a 
                JOIN usuarios u ON a.usuario_id = u.id 
                JOIN carros c ON a.carro_id = c.id 
                ORDER BY a.created_at DESC
            ");
            $alugueis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode($alugueis);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao listar aluguéis']);
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

        startSession();
        if (!isAuthenticated()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }

        // Suportar PUT /api/alugueis/123 ou POST /api/atualizar_aluguel
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['aluguel_id'] ?? '';
            $status = $_POST['status'] ?? '';
            $data_inicio = $_POST['data_inicio'] ?? '';
            $data_fim = $_POST['data_fim'] ?? '';
        } else {
            $path = $_SERVER['REQUEST_URI'];
            $id = basename($path);
            $input = json_decode(file_get_contents('php://input'), true);
            $status = $input['status'] ?? '';
            $data_inicio = $input['data_inicio'] ?? '';
            $data_fim = $input['data_fim'] ?? '';
        }

        if (!is_numeric($id)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        try {
            $db = Database::getInstance();
            
            // Verificar se o aluguel existe e se o usuário tem permissão
            $stmt = $db->prepare("SELECT usuario_id, carro_id, status FROM alugueis WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $aluguel = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$aluguel) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Aluguel não encontrado']);
                exit;
            }
            
            // Verificar permissão: admin pode editar todos, usuário apenas seus próprios
            if ($_SESSION['usuario_role'] !== 'admin' && $aluguel['usuario_id'] != $_SESSION['usuario_id']) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Sem permissão para editar este aluguel']);
                exit;
            }

            // Validar datas se fornecidas
            if (!empty($data_inicio) && !empty($data_fim)) {
                $dataInicio = new DateTime($data_inicio . ' 00:00:00');
                $dataFim = new DateTime($data_fim . ' 23:59:59');
                
                if ($dataFim <= $dataInicio) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Data fim deve ser posterior à data início']);
                    exit;
                }
                
                // Recalcular valor se datas mudaram
                $stmt = $db->prepare("SELECT preco_diario FROM carros WHERE id = :carro_id");
                $stmt->bindParam(':carro_id', $aluguel['carro_id'], PDO::PARAM_INT);
                $stmt->execute();
                $carro = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($carro) {
                    $dias = $dataInicio->diff($dataFim)->days + 1;
                    $valorTotal = $dias * $carro['preco_diario'];
                    
                    $stmt = $db->prepare("UPDATE alugueis SET status = :status, data_inicio = :data_inicio, data_fim = :data_fim, valor_total = :valor_total WHERE id = :id");
                    $stmt->bindParam(':valor_total', $valorTotal);
                } else {
                    $stmt = $db->prepare("UPDATE alugueis SET status = :status, data_inicio = :data_inicio, data_fim = :data_fim WHERE id = :id");
                }
            } else {
                // Atualizar apenas status
                $stmt = $db->prepare("UPDATE alugueis SET status = :status WHERE id = :id");
            }
            
            $stmt->bindParam(':status', $status);
            if (!empty($data_inicio)) $stmt->bindParam(':data_inicio', $data_inicio);
            if (!empty($data_fim)) $stmt->bindParam(':data_fim', $data_fim);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Aluguel atualizado com sucesso'
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Erro ao atualizar aluguel']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao atualizar aluguel']);
            exit;
        }
    }

    public function deletar()
    {
        @ini_set('display_errors', 0);
        @error_reporting(0);
        
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        startSession();
        if (!isAuthenticated()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }

        // Suportar DELETE /api/alugueis/123 ou POST /api/deletar_aluguel
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['aluguel_id'] ?? '';
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
            
            // Buscar o aluguel para verificar permissão e liberar o carro
            $stmt = $db->prepare("SELECT carro_id, usuario_id FROM alugueis WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $aluguel = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$aluguel) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Aluguel não encontrado']);
                exit;
            }

            // Verificar permissão: admin pode deletar todos, usuário apenas seus próprios
            if ($_SESSION['usuario_role'] !== 'admin' && $aluguel['usuario_id'] != $_SESSION['usuario_id']) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Sem permissão para cancelar este aluguel']);
                exit;
            }

            // Deletar aluguel
            $stmt = $db->prepare("DELETE FROM alugueis WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Liberar carro (marcar como disponível)
                $stmt = $db->prepare("UPDATE carros SET disponivel = 1 WHERE id = :carro_id");
                $stmt->bindParam(':carro_id', $aluguel['carro_id'], PDO::PARAM_INT);
                $stmt->execute();
                
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Aluguel cancelado com sucesso'
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Erro ao cancelar aluguel']);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao cancelar aluguel']);
            exit;
        }
    }

    public function obter()
    {
        @ini_set('display_errors', 0);
        @error_reporting(0);
        
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        startSession();
        if (!isAuthenticated()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }

        // GET /api/alugueis/123
        $path = $_SERVER['REQUEST_URI'];
        $id = basename($path);

        if (!is_numeric($id)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT a.*, u.nome as usuario_nome, u.email as usuario_email, 
                       c.marca, c.modelo, c.ano, c.cor, c.preco_diario
                FROM alugueis a 
                JOIN usuarios u ON a.usuario_id = u.id 
                JOIN carros c ON a.carro_id = c.id 
                WHERE a.id = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $aluguel = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$aluguel) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Aluguel não encontrado']);
                exit;
            }

            // Verificar permissão: admin pode ver todos, usuário apenas seus próprios
            if ($_SESSION['usuario_role'] !== 'admin' && $aluguel['usuario_id'] != $_SESSION['usuario_id']) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Sem permissão para ver este aluguel']);
                exit;
            }
            
            header('Content-Type: application/json');
            echo json_encode($aluguel);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao buscar aluguel']);
            exit;
        }
    }

    public function listar()
    {
        // Limpar qualquer output anterior
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        startSession();
        if (!isAuthenticated()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }

        try {
            $alugueis = $this->aluguelModel->listarPorUsuario($_SESSION['usuario_id']);
            header('Content-Type: application/json');
            echo json_encode($alugueis);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao listar aluguéis']);
            exit;
        }
    }
}