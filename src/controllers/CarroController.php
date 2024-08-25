<?php
require_once __DIR__ . '../services/CarroService.php'; // Corrigir o caminho se necessário

class CarroController
{
    private $carroService;

    public function __construct()
    {
        $this->carroService = new CarroService();
    }

    public function criar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->carroService->createCarro(
                $_POST['modelo'],
                $_POST['marca'],
                $_POST['ano'],
                $_POST['cor'],
                $_POST['placa'],
                $_POST['diaria'],
                'Disponível'
            );

            if ($resultado['status'] === 'success') {
                header('Location: /../../public/home.php');
                exit;
            } else {
                session_start();
                $_SESSION['error_message'] = $resultado['message'];
                header('Location: /../../public/create.html');
                exit;
            }
        } else {
            echo "Método de requisição não permitido.";
            exit;
        }
    }

    public function detalhar() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = isset($_GET['carro_id']) ? $_GET['carro_id'] : null;

            if ($id) {
                $carro = $this->carroService->detalharCarro($id);

                if ($carro) {
                    echo json_encode($carro);
                } else {
                    echo json_encode(['message' => 'Carro não encontrado.']);
                }
            } else {
                echo json_encode(['message' => 'ID do carro não fornecido.']);
            }
        } else {
            echo "Método de requisição não permitido.";
        }
    }

    public function listar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $carros = $this->carroService->listarCarros();
            echo json_encode($carros);
        } else {
            echo "Método de requisição não permitido.";
        }
    }
}
?>
