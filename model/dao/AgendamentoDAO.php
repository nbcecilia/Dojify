<?php
// model/dao/AgendamentoDAO.php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/AgendamentoDTO.php';

class AgendamentoDAO
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = \Conexao::getConexao();
    }

    /**
     * Retorna informações de uma turma (capacidade, etc)
     */
    public function obterInfoTurma(int $id_turma): ?array
    {
        $stmt = $this->conexao->prepare("
            SELECT capacidade, id_modalidade
            FROM turma
            WHERE id_turma = ? AND status = 'ATIVA'
        ");
        $stmt->execute([$id_turma]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function listarTurmasAtivasComHorario(?int $id_modalidade): array
    {
        if ($id_modalidade === null || $id_modalidade <= 0) {
            return [];
        }

        $stmt = $this->conexao->prepare("
            SELECT t.id_turma, t.nome AS nome_turma, t.capacidade, h.dia_semana, h.hora_inicio
            FROM turma t
            LEFT JOIN horario_turma h ON t.id_turma = h.id_turma
            WHERE t.status = 'ATIVA' AND t.id_modalidade = ?
        ");
        $stmt->execute([$id_modalidade]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obterDadosPlanoAtivo(int $id_aluno): ?array
    {
        $stmt = $this->conexao->prepare("
            SELECT p.id_modalidade, m.nome AS modalidade_nome, p.nome_plano
            FROM plano p
            LEFT JOIN modalidade m ON m.id_modalidade = p.id_modalidade
            WHERE p.id_usuario_aluno = ? AND p.status = 'ATIVO'
            ORDER BY p.data_inicio DESC, p.id_plano DESC
            LIMIT 1
        ");
        $stmt->execute([$id_aluno]);
        $dadosPlano = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dadosPlano ?: null;
    }

    /**
     * @return AgendamentoDTO[]
     */
    public function listarAgendamentosFuturosPorAluno(int $id_aluno): array
    {
        $stmt = $this->conexao->prepare("
            SELECT a.id_agendamento, a.data_agendamento, a.status, t.nome AS nome_turma
            FROM agendamento a
            INNER JOIN turma t ON t.id_turma = a.id_turma
            WHERE a.id_usuario_aluno = ?
              AND a.status = 'CONFIRMADO'
              AND a.data_agendamento >= ?
            ORDER BY a.data_agendamento ASC
        ");
        $stmt->execute([$id_aluno, date('Y-m-d H:i:s')]);

        $agendamentos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $registro) {
            $agendamentos[] = new AgendamentoDTO(
                (int)$registro['id_agendamento'],
                (string)$registro['nome_turma'],
                (string)$registro['data_agendamento'],
                (string)$registro['status']
            );
        }

        return $agendamentos;
    }

    /**
     * Conta quantos agendamentos confirmados existem para uma turma numa data específica
     */
    public function contarVagasOcupadas(int $id_turma, string $data_agendamento): int
    {
        $stmt = $this->conexao->prepare("
            SELECT COUNT(*) as total 
            FROM agendamento 
            WHERE id_turma = ? AND DATE(data_agendamento) = DATE(?) AND status = 'CONFIRMADO'
        ");
        $stmt->execute([$id_turma, $data_agendamento]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    /**
     * Verifica se o aluno já está agendado para a turma no dia
     */
    public function verificarAgendamentoDuplicado(int $id_aluno, int $id_turma, string $data_agendamento): bool
    {
        $stmt = $this->conexao->prepare("
            SELECT COUNT(*) as total 
            FROM agendamento 
            WHERE id_usuario_aluno = ? AND id_turma = ? AND DATE(data_agendamento) = DATE(?) AND status = 'CONFIRMADO'
        ");
        $stmt->execute([$id_aluno, $id_turma, $data_agendamento]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ((int)($row['total'] ?? 0)) > 0;
    }

    /**
     * Conta agendamentos na semana (segunda a domingo)
     */
    public function contarAgendamentosSemana(int $id_aluno, string $data_inicio_semana, string $data_fim_semana): int
    {
        $stmt = $this->conexao->prepare("
            SELECT COUNT(*) as total_semana 
            FROM agendamento 
            WHERE id_usuario_aluno = ? 
              AND status = 'CONFIRMADO' 
              AND data_agendamento BETWEEN ? AND ?
        ");
        $stmt->execute([$id_aluno, $data_inicio_semana, $data_fim_semana]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total_semana'] ?? 0);
    }

    /**
     * Obtém o limite semanal do plano ativo do aluno
     */
    public function obterLimiteSemanalPlano(int $id_aluno): int
    {
        $stmt = $this->conexao->prepare("
            SELECT nome_plano 
            FROM plano 
            WHERE id_usuario_aluno = ? AND status = 'ATIVO' 
            ORDER BY data_inicio DESC, id_plano DESC
            LIMIT 1
        ");
        $stmt->execute([$id_aluno]);
        $dadosPlano = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $limiteSemanal = 99; // Default (Livre)
        if ($dadosPlano && !empty($dadosPlano['nome_plano'])) {
            $nomeDoPlano = $dadosPlano['nome_plano'];
            if (preg_match('/(\d+)/', $nomeDoPlano, $matches)) {
                $limiteSemanal = (int)$matches[1];
            }
        }
        return $limiteSemanal;
    }

    /**
     * Efetua o agendamento
     */
    public function registrarAgendamento(int $id_turma, int $id_aluno, string $data_agendamento): bool
    {
        $stmt = $this->conexao->prepare("
            INSERT INTO agendamento (id_turma, id_usuario_aluno, data_agendamento, status) 
            VALUES (?, ?, ?, 'CONFIRMADO')
        ");
        return $stmt->execute([$id_turma, $id_aluno, $data_agendamento]);
    }

    /**
     * Cancela um agendamento
     */
    public function cancelarAgendamento(int $id_agendamento, int $id_aluno): bool
    {
        $stmt = $this->conexao->prepare("
            UPDATE agendamento 
            SET status = 'CANCELADO' 
            WHERE id_agendamento = ? AND id_usuario_aluno = ?
        ");
        return $stmt->execute([$id_agendamento, $id_aluno]) && $stmt->rowCount() > 0;
    }
}