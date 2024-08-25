<?php

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../routes/api/alugar_carro.php';

class AluguelService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function verificarDisponibilidade($carro_id, $data_inicio, $data_fim)
    {
        $query = "SELECT COUNT(*) FROM alugueis 
                  WHERE carro_id = :carro_id 
                  AND (data_inicio <= :data_fim AND data_fim >= :data_inicio)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':carro_id', $carro_id);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function obterValorDiaria($carro_id)
    {
        $query = "SELECT diaria FROM carros WHERE id = :carro_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':carro_id', $carro_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function inserirAluguel($carro_id, $usuario_id, $data_inicio, $data_fim, $valor_total)
    {
        $query = "INSERT INTO alugueis (carro_id, usuario_id, data_inicio, data_fim, valor_total)
                  VALUES (:carro_id, :usuario_id, :data_inicio, :data_fim, :valor_total)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':carro_id', $carro_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->bindParam(':valor_total', $valor_total);
        $stmt->execute();
    }

    public function atualizarDisponibilidade($carro_id)
    {
        $query = "UPDATE carros SET disponibilidade = 'Indisponível' WHERE id = :carro_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':carro_id', $carro_id);
        $stmt->execute();
    }
}
?>
