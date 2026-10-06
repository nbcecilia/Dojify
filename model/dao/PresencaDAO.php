<?php
// model/dao/PresencaDAO.php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/PresencaDTO.php';
require_once __DIR__ . '/../dto/Historico_FrequenciaDTO.php';

class PresencaDAO {
    private PDO $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConexao();
    }

    /**
     * 1. Lista os alunos agendados numa turma para uma data (para fazer a chamada)
     */
    public function listarAlunosParaChamada(int $idTurma, string $dataAula): array {
        $sql = "SELECT a.id_agendamento, u.id_usuario AS id_aluno, u.nome AS aluno_nome, 
                       p.status AS status_presenca, p.id_presenca
                FROM agendamento a
                JOIN usuario u ON a.id_usuario_aluno = u.id_usuario
                LEFT JOIN presenca p ON p.id_agendamento = a.id_agendamento AND p.data = :data_aula
                WHERE a.id_turma = :id_turma AND a.status = 'CONFIRMADO'
                ORDER BY u.nome ASC";
        
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
        $stmt->bindValue(':data_aula', $dataAula);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 2. Regista ou atualiza a chamada em lote para os agendamentos da aula
     */
    public function salvarChamada(string $dataAula, array $presencas): bool {
        try {
            $this->conexao->beginTransaction();

            $sqlVerifica = "SELECT id_presenca FROM presenca WHERE id_agendamento = :id_agendamento AND data = :data";
            $sqlInsere = "INSERT INTO presenca (id_agendamento, data, status) VALUES (:id_agendamento, :data, :status)";
            $sqlAtualiza = "UPDATE presenca SET status = :status WHERE id_presenca = :id_presenca";

            $stmtVerifica = $this->conexao->prepare($sqlVerifica);
            $stmtInsere = $this->conexao->prepare($sqlInsere);
            $stmtAtualiza = $this->conexao->prepare($sqlAtualiza);

            foreach ($presencas as $idAgendamento => $status) {
                $stmtVerifica->bindValue(':id_agendamento', (int)$idAgendamento, PDO::PARAM_INT);
                $stmtVerifica->bindValue(':data', $dataAula);
                $stmtVerifica->execute();
                $registo = $stmtVerifica->fetch(PDO::FETCH_ASSOC);

                if ($registo) {
                    $stmtAtualiza->bindValue(':status', (int)$status, PDO::PARAM_INT);
                    $stmtAtualiza->bindValue(':id_presenca', (int)$registo['id_presenca'], PDO::PARAM_INT);
                    $stmtAtualiza->execute();
                } else {
                    $stmtInsere->bindValue(':id_agendamento', (int)$idAgendamento, PDO::PARAM_INT);
                    $stmtInsere->bindValue(':data', $dataAula);
                    $stmtInsere->bindValue(':status', (int)$status, PDO::PARAM_INT);
                    $stmtInsere->execute();
                }
            }

            $this->conexao->commit();
            return true;
        } catch (Exception $e) {
            $this->conexao->rollBack();
            return false;
        }
    }

    /**
     * 3. Lista o histórico de frequência de um aluno específico
     */
    public function listarPorAluno(int $idAluno): array {
        if ($idAluno <= 0) {
            throw new InvalidArgumentException('O identificador do aluno deve ser positivo.');
        }

        $sql = "SELECT a.id_agendamento,
                       t.nome AS nome_turma,
                       a.data_agendamento,
                       p.status AS status_presenca
                FROM agendamento a
                INNER JOIN turma t ON t.id_turma = a.id_turma
                LEFT JOIN presenca p ON p.id_agendamento = a.id_agendamento
                WHERE a.id_usuario_aluno = :id_aluno
                  AND a.data_agendamento < NOW()
                ORDER BY a.data_agendamento DESC";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
        $stmt->execute();

        $historico = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $registro) {
            $statusPresenca = $registro['status_presenca'] === null
                ? null
                : (int)$registro['status_presenca'];

            $historico[] = new Historico_frequenciaDTO(
                (int)$registro['id_agendamento'],
                $registro['nome_turma'],
                $registro['data_agendamento'],
                $statusPresenca
            );
        }

        return $historico;
    }
}