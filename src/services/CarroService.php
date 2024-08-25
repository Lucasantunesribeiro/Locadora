<?php
require_once __DIR__ . '/../models/Carro.php'; // Corrigir o caminho se necessário
require_once __DIR__ . '/../../core/Database.php';  // Corrigir o caminho se necessário

class CarroService
{
    private $carroModel;

    public function __construct()
    {
        $this->carroModel = new CarroModel();
    }

    public function createCarro($modelo, $marca, $ano, $cor, $placa, $diaria, $disponibilidade)
    {
        $modelo = ucfirst(strtolower(trim($modelo)));
        $marca = ucfirst(strtolower(trim($marca)));
        $cor = ucfirst(strtolower(trim($cor)));
        $placa = strtoupper(str_replace('-', '', trim($placa)));

        if (!preg_match('/^[A-Z]{3}[0-9]{4}$/', $placa)) {
            return ['status' => 'error', 'message' => 'A placa deve estar no formato ABC1234.'];
        }

        if ($diaria <= 0) {
            return ['status' => 'error', 'message' => 'O preço da diária deve ser um valor positivo.'];
        }

        if ($ano < 1900 || $ano > 2024) {
            return ['status' => 'error', 'message' => 'O ano deve estar entre 1900 e 2024.'];
        }

        $coresValidas = ['Branco', 'Preto', 'Prata', 'Vermelho', 'Azul', 'Verde', 'Amarelo', 'Rosa', 'Roxo', 'Marrom', 'Cinza', 'Laranja', 'Dourado', 'Bege', 'Bordo', 'Champagne', 'Cobre', 'Grafite', 'Ouro', 'Pérola', 'Turquesa', 'Violeta'];
        if (!in_array($cor, $coresValidas)) {
            return ['status' => 'error', 'message' => 'Cor inválida.'];
        }

        $marcas = [
            'Chevrolet' => ['Chevrolet', 'Chevy'],
            'Fiat' => ['Fiat'],
            'Ford' => ['Ford'],
            'Volkswagen' => ['Volkswagen', 'VW'],
            'Renault' => ['Renault'],
            'Toyota' => ['Toyota'],
            'Hyundai' => ['Hyundai'],
            'Honda' => ['Honda'],
            'Jeep' => ['Jeep', 'Jipe'],
            'Nissan' => ['Nissan', 'Nissam'],
            'Peugeot' => ['Peugeot', 'Pegeout'],
            'Citroën' => ['Citroën', 'Citroen'],
            'Mitsubishi' => ['Mitsubishi', 'Mitsubish', 'Mitsubichi'],
            'Mercedes-Benz' => ['Mercedes-Benz', 'Mercedes', 'Benz'],
            'BMW' => ['BMW', 'BM'],
            'Audi' => ['Audi'],
            'Volvo' => ['Volvo'],
            'Kia' => ['Kia'],
            'Land Rover' => ['Land Rover', 'LandRover'],
            'Suzuki' => ['Suzuki'],
            'Subaru' => ['Subaru'],
            'Chery' => ['Chery'],
            'JAC' => ['JAC'],
            'Lifan' => ['Lifan'],
            'Troller' => ['Troller'],
            'Agrale' => ['Agrale'],
            'Aston Martin' => ['Aston Martin', 'Aston', 'Martin'],
            'Bentley' => ['Bentley'],
            'Bugatti' => ['Bugatti', 'Bugati'],
            'Chrysler' => ['Chrysler'],
            'Dodge' => ['Dodge', 'Ram'],
            'Ferrari' => ['Ferrari'],
            'Lamborghini' => ['Lamborghini', 'Lambo', 'Lambohini'],
            'Maserati' => ['Maserati'],
            'McLaren' => ['McLaren'],
            'Porsche' => ['Porsche', 'Porche', 'Porshe'],
            'Rolls-Royce' => ['Rolls-Royce', 'Rolls', 'Royce'],
            'Tesla' => ['Tesla'],
            'Alfa Romeo' => ['Alfa Romeo', 'Alfa', 'Romeo']
        ];

        $marcaValida = false;
        foreach ($marcas as $principal => $variacoes) {
            if (in_array($marca, $variacoes)) {
                $marcaValida = true;
                $marca = $principal;
                break;
            }
        }

        if (!$marcaValida) {
            return ['status' => 'error', 'message' => 'Marca inválida.'];
        }

        if (strlen($modelo) < 2 || strlen($modelo) > 50) {
            return ['status' => 'error', 'message' => 'O modelo deve ter entre 2 e 50 caracteres.'];
        }

        if ($this->isPlacaDuplicada($placa)) {
            return ['status' => 'error', 'message' => 'A placa informada já está cadastrada no sistema.'];
        }

        if ($this->carroModel->criar($modelo, $marca, $ano, $cor, $placa, $diaria, $disponibilidade)) {
            return ['status' => 'success', 'message' => 'Carro criado com sucesso.'];
        } else {
            return ['status' => 'error', 'message' => 'Erro ao criar carro no banco de dados.'];
        }
    }

    private function isPlacaDuplicada($placa)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM carros WHERE placa = :placa");
        $stmt->bindParam(':placa', $placa);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function detalharCarro($id) {
        return $this->carroModel->detalhar($id);
    }

    public function listarCarros()
    {
        return $this->carroModel->listar();
    }

    public function deletarCarro($id) {
        return $this->carroModel->deletar($id);
    }
}
?>
