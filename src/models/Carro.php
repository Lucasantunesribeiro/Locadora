<?php

class Carro
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function criar($marca, $modelo, $ano, $cor, $preco_diario)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO carros (marca, modelo, ano, cor, preco_diario, disponivel)
                VALUES (:marca, :modelo, :ano, :cor, :preco_diario, 1)
            ");

            $stmt->bindParam(':marca', $marca);
            $stmt->bindParam(':modelo', $modelo);
            $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
            $stmt->bindParam(':cor', $cor);
            $stmt->bindParam(':preco_diario', $preco_diario);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function buscarPorId($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM carros WHERE id = :id LIMIT 1");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listarTodos()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM carros ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarDisponiveis()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM carros WHERE disponivel = 1 ORDER BY marca, modelo");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function deletar($id) 
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM carros WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function atualizar($id, $disponivel)
    {
        try {
            $stmt = $this->db->prepare("UPDATE carros SET disponivel = :disponivel WHERE id = :id");
            $stmt->bindParam(':disponivel', $disponivel, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}