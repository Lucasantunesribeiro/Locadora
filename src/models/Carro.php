<?php
require_once __DIR__ . '/../../core/Database.php'; // Corrija o caminho se necessário

class CarroModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function criar($modelo, $marca, $ano, $cor, $placa, $diaria, $disponibilidade)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO carros (modelo, marca, ano, cor, placa, diaria, disponibilidade)
                VALUES (:modelo, :marca, :ano, :cor, :placa, :diaria, :disponibilidade)
            ");

            $stmt->bindParam(':modelo', $modelo);
            $stmt->bindParam(':marca', $marca);
            $stmt->bindParam(':ano', $ano);
            $stmt->bindParam(':cor', $cor);
            $stmt->bindParam(':placa', $placa);
            $stmt->bindParam(':diaria', $diaria);
            $stmt->bindParam(':disponibilidade', $disponibilidade);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Erro ao criar carro: " . $e->getMessage();
            return false;
        }
    }

    public function detalhar($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM carros WHERE id = :id LIMIT 1");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erro ao detalhar carro: " . $e->getMessage();
            return false;
        }
    }

    public function listar()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM carros");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erro ao listar carros: " . $e->getMessage();
            return false;
        }
    }

    public function deletar($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM carros WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>