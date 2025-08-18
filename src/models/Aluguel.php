<?php

class Aluguel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function criar($usuarioId, $carroId, $dataInicio, $dataFim, $valorTotal)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO alugueis (usuario_id, carro_id, data_inicio, data_fim, valor_total, status)
                VALUES (:usuario_id, :carro_id, :data_inicio, :data_fim, :valor_total, 'ativo')
            ");

            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindParam(':carro_id', $carroId, PDO::PARAM_INT);
            $stmt->bindParam(':data_inicio', $dataInicio);
            $stmt->bindParam(':data_fim', $dataFim);
            $stmt->bindParam(':valor_total', $valorTotal);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listarTodos()
    {
        try {
            $stmt = $this->db->query("
                SELECT a.*, u.nome as usuario_nome, c.marca, c.modelo 
                FROM alugueis a 
                JOIN usuarios u ON a.usuario_id = u.id 
                JOIN carros c ON a.carro_id = c.id 
                ORDER BY a.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarPorUsuario($usuarioId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, c.marca, c.modelo, c.ano 
                FROM alugueis a 
                JOIN carros c ON a.carro_id = c.id 
                WHERE a.usuario_id = :usuario_id 
                ORDER BY a.created_at DESC
            ");
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function buscarPorId($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.nome as usuario_nome, c.marca, c.modelo 
                FROM alugueis a 
                JOIN usuarios u ON a.usuario_id = u.id 
                JOIN carros c ON a.carro_id = c.id 
                WHERE a.id = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function finalizar($id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE alugueis SET status = 'finalizado' WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
