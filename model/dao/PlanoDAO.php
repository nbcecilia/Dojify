<?php
//model/dao/PlanoDAO.php
require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/PlanoDTO.php';

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
}