<?php
// model/dao/TurmaDAO.php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/TurmaDTO.php';

class TurmaDAO {

    private PDO $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConexao();
    }

    public function inserir(TurmaDTO $turma, int $idAcademia): bool {
        try {

            $sql = "INSERT INTO turma (
                        id_usuario_professor,
                        id_modalidade,
                        nome,
                        capacidade,
                        nivel,
                        status
                    )
                    SELECT
                        :id_usuario_professor,
                        :id_modalidade,
                        :nome,
                        :capacidade,
                        :nivel,
                        :status
                    WHERE EXISTS (
                        SELECT 1
                        FROM usuario
                        WHERE id_usuario = :id_usuario_professor
                        AND id_academia = :id_academia
                        AND perfil_id = 3
                    )
                    AND EXISTS (
                        SELECT 1
                        FROM modalidade
                        WHERE id_modalidade = :id_modalidade
                        AND id_academia = :id_academia
                    )";

            $stmt = $this->conexao->prepare($sql);

            $stmt->bindValue(
                ':id_usuario_professor',
                $turma->getIdUsuarioProfessor(),
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':id_modalidade',
                $turma->getIdModalidade(),
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':nome',
                $turma->getNome()
            );

            $stmt->bindValue(
                ':capacidade',
                $turma->getCapacidade(),
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':nivel',
                $turma->getNivel()
            );

            $stmt->bindValue(
                ':status',
                $turma->getStatus()
            );

            $stmt->bindValue(
                ':id_academia',
                $idAcademia,
                PDO::PARAM_INT
            );

            return $stmt->execute();

        } catch (PDOException $e) {
            return false;
        }
    }


    public function listarPorAcademia(int $idAcademia): array {

        $sql = "SELECT 
                    t.*,
                    u.nome AS professor_nome,
                    m.nome AS modalidade_nome
                FROM turma t
                INNER JOIN usuario u 
                    ON t.id_usuario_professor = u.id_usuario
                INNER JOIN modalidade m 
                    ON t.id_modalidade = m.id_modalidade
                WHERE u.id_academia = :id_academia
                AND m.id_academia = :id_academia
                ORDER BY t.id_turma DESC";

        $stmt = $this->conexao->prepare($sql);

        $stmt->bindValue(
            ':id_academia',
            $idAcademia,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function buscarPorId(int $id, int $idAcademia): ?array {

        $sql = "SELECT 
                    t.*,
                    u.nome AS professor_nome,
                    m.nome AS modalidade_nome
                FROM turma t
                INNER JOIN usuario u 
                    ON t.id_usuario_professor = u.id_usuario
                INNER JOIN modalidade m 
                    ON t.id_modalidade = m.id_modalidade
                WHERE t.id_turma = :id
                AND u.id_academia = :id_academia
                AND m.id_academia = :id_academia";

        $stmt = $this->conexao->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_academia',
            $idAcademia,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }


    public function atualizar(TurmaDTO $turma, int $idAcademia): bool {

        try {

            $sql = "UPDATE turma t
                    INNER JOIN usuario u 
                        ON t.id_usuario_professor = u.id_usuario
                    INNER JOIN modalidade m 
                        ON t.id_modalidade = m.id_modalidade
                    SET
                        t.id_usuario_professor = :id_usuario_professor,
                        t.id_modalidade = :id_modalidade,
                        t.nome = :nome,
                        t.capacidade = :capacidade,
                        t.nivel = :nivel,
                        t.status = :status
                    WHERE t.id_turma = :id
                    AND u.id_academia = :id_academia
                    AND m.id_academia = :id_academia";

            $stmt = $this->conexao->prepare($sql);

            $stmt->bindValue(
                ':id_usuario_professor',
                $turma->getIdUsuarioProfessor(),
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':id_modalidade',
                $turma->getIdModalidade(),
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':nome',
                $turma->getNome()
            );

            $stmt->bindValue(
                ':capacidade',
                $turma->getCapacidade(),
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':nivel',
                $turma->getNivel()
            );

            $stmt->bindValue(
                ':status',
                $turma->getStatus()
            );

            $stmt->bindValue(
                ':id',
                $turma->getIdTurma(),
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':id_academia',
                $idAcademia,
                PDO::PARAM_INT
            );

            return $stmt->execute();

        } catch (PDOException $e) {
            return false;
        }
    }


    public function excluir(int $id, int $idAcademia): bool {

        try {

            $sql = "DELETE t
                    FROM turma t
                    INNER JOIN usuario u 
                        ON t.id_usuario_professor = u.id_usuario
                    INNER JOIN modalidade m 
                        ON t.id_modalidade = m.id_modalidade
                    WHERE t.id_turma = :id
                    AND u.id_academia = :id_academia
                    AND m.id_academia = :id_academia";

            $stmt = $this->conexao->prepare($sql);

            $stmt->bindValue(
                ':id',
                $id,
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':id_academia',
                $idAcademia,
                PDO::PARAM_INT
            );

            return $stmt->execute();

        } catch (PDOException $e) {
            return false;
        }
    }


    // Lista os professores da academia
    public function listarProfessoresPorAcademia(int $idAcademia): array {

        $sql = "SELECT 
                    id_usuario,
                    nome
                FROM usuario
                WHERE id_academia = :id_academia
                AND perfil_id = 3
                ORDER BY nome";

        $stmt = $this->conexao->prepare($sql);

        $stmt->bindValue(
            ':id_academia',
            $idAcademia,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Lista as modalidades da academia
    public function listarModalidadesPorAcademia(int $idAcademia): array {

        $sql = "SELECT 
                    id_modalidade,
                    nome
                FROM modalidade
                WHERE id_academia = :id_academia
                ORDER BY nome";

        $stmt = $this->conexao->prepare($sql);

        $stmt->bindValue(
            ':id_academia',
            $idAcademia,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}