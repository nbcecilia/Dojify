<?php
// model/dao/PlanoDAO.php

require_once __DIR__ . '/Conexao.php';

class PlanoDAO {
    
    public function inserir(PlanoDTO $plano) {
        try {
            $pdo = Conexao::getConexao();
            $sql = "INSERT INTO plano (id_usuario_aluno, nome_plano, valor, data_inicio, data_fim, status) 
                    VALUES (:id_usuario_aluno, :nome_plano, :valor, :data_inicio, :data_fim, :status)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_usuario_aluno', $plano->getIdUsuarioAluno());
            $stmt->bindValue(':nome_plano', $plano->getNomePlano());
            $stmt->bindValue(':valor', $plano->getValor());
            $stmt->bindValue(':data_inicio', $plano->getDataInicio());
            $stmt->bindValue(':data_fim', $plano->getDataFim());
            $stmt->bindValue(':status', $plano->getStatus());
            $stmt->execute();
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function atualizar(PlanoDTO $plano) {
        try {
            $pdo = Conexao::getConexao();
            $sql = "UPDATE plano SET id_usuario_aluno = :id_usuario_aluno, nome_plano = :nome_plano, 
                    valor = :valor, data_inicio = :data_inicio, data_fim = :data_fim, status = :status 
                    WHERE id_plano = :id_plano";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_usuario_aluno', $plano->getIdUsuarioAluno());
            $stmt->bindValue(':nome_plano', $plano->getNomePlano());
            $stmt->bindValue(':valor', $plano->getValor());
            $stmt->bindValue(':data_inicio', $plano->getDataInicio());
            $stmt->bindValue(':data_fim', $plano->getDataFim());
            $stmt->bindValue(':status', $plano->getStatus());
            $stmt->bindValue(':id_plano', $plano->getIdPlano());
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function excluir($id) {
        try {
            $pdo = Conexao::getConexao();
            $sql = "DELETE FROM plano WHERE id_plano = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function buscarPorId($id, $idAcademia) {
        try {
            $pdo = Conexao::getConexao();
            $sql = "SELECT p.*, u.nome AS aluno_nome 
                    FROM plano p 
                    INNER JOIN usuario u ON p.id_usuario_aluno = u.id_usuario 
                    WHERE p.id_plano = :id AND u.id_academia = :id_academia";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listarPorAcademia($idAcademia) {
        try {
            $pdo = Conexao::getConexao();
            $sql = "SELECT p.*, u.nome AS aluno_nome 
                    FROM plano p 
                    INNER JOIN usuario u ON p.id_usuario_aluno = u.id_usuario 
                    WHERE u.id_academia = :id_academia 
                    ORDER BY p.data_inicio DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarAlunosPorAcademia($idAcademia) {
        try {
            $pdo = Conexao::getConexao();
            // Corrigido para perfil_id = 4 (Alunos)
            $sql = "SELECT id_usuario, nome FROM usuario WHERE id_academia = :id_academia AND perfil_id = 4 ORDER BY nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}