<?php
//model/dao/AcademiaDAO.php
require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/AcademiaDTO.php';

class AcademiaDAO {
    private PDO $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConexao();
    }

    public function cadastrar(AcademiaDTO $academia): bool {
        try {
            $sql = "INSERT INTO academia (nome, documento, endereco, telefone, email) 
                    VALUES (:nome, :documento, :endereco, :telefone, :email)";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':nome', $academia->getNome());
            $stmt->bindValue(':documento', $academia->getDocumento());
            $stmt->bindValue(':endereco', $academia->getEndereco());
            $stmt->bindValue(':telefone', $academia->getTelefone());
            $stmt->bindValue(':email', $academia->getEmail());
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listarTodas(): array {
        $sql = "SELECT * FROM academia ORDER BY id_academia DESC";
        return $this->conexao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAcademiasEGerentes(): array {
        $sql = "SELECT a.id_academia, 
                       a.nome AS academia_nome, 
                       a.documento, 
                       a.email AS academia_email, 
                       a.telefone AS academia_telefone,
                       u.id_usuario AS id_gerente,
                       u.nome AS gerente_nome, 
                       u.email AS gerente_email, 
                       u.telefone AS gerente_telefone,
                       u.status AS gerente_status
                FROM academia a
                LEFT JOIN usuario u ON a.id_academia = u.id_academia AND u.perfil_id = 2
                ORDER BY a.id_academia DESC";
        
        return $this->conexao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array {
        $sql = "SELECT * FROM academia WHERE id_academia = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function atualizar(AcademiaDTO $academia): bool {
        try {
            $sql = "UPDATE academia 
                    SET nome = :nome, documento = :documento, endereco = :endereco, 
                        telefone = :telefone, email = :email 
                    WHERE id_academia = :id";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':nome', $academia->getNome());
            $stmt->bindValue(':documento', $academia->getDocumento());
            $stmt->bindValue(':endereco', $academia->getEndereco());
            $stmt->bindValue(':telefone', $academia->getTelefone());
            $stmt->bindValue(':email', $academia->getEmail());
            $stmt->bindValue(':id', $academia->getIdAcademia(), PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function excluir(int $id): bool {
        try {
            $sql = "DELETE FROM academia WHERE id_academia = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Buscar academias e gerentes por um termo (nome, documento, e-mail ou gerente)
    public function buscarAcademiasEGerentes(string $termo): array {
        $sql = "SELECT a.id_academia, 
                       a.nome AS academia_nome, 
                       a.documento, 
                       a.email AS academia_email, 
                       a.telefone AS academia_telefone,
                       u.id_usuario AS id_gerente,
                       u.nome AS gerente_nome, 
                       u.email AS gerente_email, 
                       u.telefone AS gerente_telefone,
                       u.status AS gerente_status
                FROM academia a
                LEFT JOIN usuario u ON a.id_academia = u.id_academia AND u.perfil_id = 2
                WHERE a.nome LIKE :termo 
                   OR a.documento LIKE :termo 
                   OR a.email LIKE :termo 
                   OR u.nome LIKE :termo
                ORDER BY a.id_academia DESC";
        
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':termo', '%' . $termo . '%');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}