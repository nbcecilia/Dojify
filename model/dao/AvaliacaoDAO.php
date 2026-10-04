<?php
// model/dao/AvaliacaoDAO.php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/AvaliacaoDTO.php';

class AvaliacaoDAO {

    public function cadastrar(AvaliacaoDTO $avaliacao) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "INSERT INTO avaliacao (id_usuario_professor, id_usuario_aluno, data_avaliacao, habilidades_melhorar, observacoes) 
                    VALUES (:professor, :aluno, :data, :habilidades, :observacoes)";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(':professor', $avaliacao->getIdUsuarioProfessor());
            $stmt->bindValue(':aluno', $avaliacao->getIdUsuarioAluno());
            $stmt->bindValue(':data', $avaliacao->getDataAvaliacao());
            $stmt->bindValue(':habilidades', $avaliacao->getHabilidadesMelhorar());
            $stmt->bindValue(':observacoes', $avaliacao->getObservacoes());
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listarPorAcademia(int $id_academia) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "SELECT a.*, 
                           u_aluno.nome AS nome_aluno, 
                           u_prof.nome AS nome_professor 
                    FROM avaliacao a
                    INNER JOIN usuario u_aluno ON a.id_usuario_aluno = u_aluno.id_usuario
                    INNER JOIN usuario u_prof ON a.id_usuario_professor = u_prof.id_usuario
                    WHERE u_aluno.id_academia = :academia
                    ORDER BY a.data_avaliacao DESC";
            
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(':academia', $id_academia, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $lista = [];
            foreach ($resultados as $row) {
                $av = new AvaliacaoDTO();
                $av->setIdAvaliacao($row['id_avaliacao']);
                $av->setIdUsuarioProfessor($row['id_usuario_professor']);
                $av->setIdUsuarioAluno($row['id_usuario_aluno']);
                $av->setDataAvaliacao($row['data_avaliacao']);
                $av->setHabilidadesMelhorar($row['habilidades_melhorar']);
                $av->setObservacoes($row['observacoes']);
                $av->setNomeAluno($row['nome_aluno']);
                $av->setNomeProfessor($row['nome_professor']);
                $lista[] = $av;
            }
            return $lista;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarAlunosPorAcademia(int $id_academia) {
        try {
            $conexao = Conexao::getConexao();
            $sql = "SELECT id_usuario, nome FROM usuario WHERE id_academia = :academia AND perfil_id = 4 AND status = 'ATIVO' ORDER BY nome ASC";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(':academia', $id_academia, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}