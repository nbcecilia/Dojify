<?php
// model/dao/Historico_FrequenciaDAO.php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/Historico_FrequenciaDTO.php';

class Historico_frequenciaDAO {
    private PDO $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConexao();
    }

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
