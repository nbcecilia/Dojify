<?php
// model/dao/HorarioTurmaDAO.php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/HorarioTurmaDTO.php';

class HorarioTurmaDAO {
    private PDO $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConexao();
    }

    public function cadastrar(HorarioTurmaDTO $horario): bool {
        try {
            $sql = "INSERT INTO horario_turma (id_turma, dia_semana, hora_inicio, hora_fim) 
                    VALUES (:id_turma, :dia_semana, :hora_inicio, :hora_fim)";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_turma', $horario->getIdTurma(), PDO::PARAM_INT);
            $stmt->bindValue(':dia_semana', $horario->getDiaSemana());
            $stmt->bindValue(':hora_inicio', $horario->getHoraInicio());
            $stmt->bindValue(':hora_fim', $horario->getHoraFim());
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listarPorTurma(int $idTurma): array {
        try {
            $sql = "SELECT * FROM horario_turma WHERE id_turma = :id_turma ORDER BY FIELD(dia_semana, 'Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sábado', 'Domingo'), hora_inicio ASC";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function excluir(int $idHorario): bool {
        try {
            $sql = "DELETE FROM horario_turma WHERE id_horario = :id_horario";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_horario', $idHorario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}