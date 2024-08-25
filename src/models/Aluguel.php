<?php

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../services/AluguelService.php';
require_once __DIR__ . '/../routes/api/alugar_carro.php';

class Aluguel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function alugarCarro($carroId, $usuarioId, $dataInicio, $dataFim)
    {
        $query = "INSERT INTO alugueis (carro_id, usuario_id, data_inicio, data_fim) VALUES (:carro_id, :usuario_id, :data_inicio, :data_fim)";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':carro_id', $carroId);
        $stmt->bindValue(':usuario_id', $usuarioId);
        $stmt->bindValue(':data_inicio', $dataInicio);
        $stmt->bindValue(':data_fim', $dataFim);
        return $stmt->execute();
    }

    public function listarAlugueis()
    {
        $query = "SELECT * FROM alugueis";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
