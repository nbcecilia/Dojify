<?php
// model/dao/PagamentoDAO.php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/PagamentoDTO.php';

class PagamentoDAO {
    private PDO $conexao;

    public function __construct() {
        $this->conexao = \Conexao::getConexao();
    }

    public function listarPagamentosDaAcademia(int $idAcademia): array {
        $sql = "SELECT 
                    MIN(p.id_pagamento) AS id_pagamento, 
                    SUM(p.valor) AS valor, 
                    MIN(p.data_vencimento) AS data_vencimento, 
                    MAX(p.data_pagamento) AS data_pagamento, 
                    p.status, 
                    MAX(p.forma_pagamento) AS forma_pagamento,
                    GROUP_CONCAT(pl.nome_plano SEPARATOR ', ') AS nome_plano,
                    u.nome AS aluno_nome,
                    u.id_usuario AS id_aluno
                FROM pagamento p
                INNER JOIN plano pl ON p.id_plano_matricula = pl.id_plano
                INNER JOIN usuario u ON pl.id_usuario_aluno = u.id_usuario
                WHERE u.id_academia = :id_academia
                  AND u.perfil_id = 4
                GROUP BY u.id_usuario, DATE_FORMAT(p.data_vencimento, '%Y-%m'), p.status
                ORDER BY p.data_vencimento ASC";
        
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registarRecebimento(int $idPagamento, string $formaPagamento, ?string $dataPagamento = null): bool {
        try {
            $dataReal = (!empty($dataPagamento)) ? $dataPagamento : date('Y-m-d');
            $sql = "UPDATE pagamento 
                    SET status = 'PAGO', data_pagamento = :data_pagamento, forma_pagamento = :forma 
                    WHERE id_pagamento = :id";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':data_pagamento', $dataReal);
            $stmt->bindValue(':forma', $formaPagamento);
            $stmt->bindValue(':id', $idPagamento, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obterNotificacoesFinanceiras(int $idAcademia): array {
        try {
            $sqlAtrasados = "SELECT COUNT(*) as total_qtd, COALESCE(SUM(p.valor), 0) as total_valor
                             FROM pagamento p
                             INNER JOIN plano pl ON p.id_plano_matricula = pl.id_plano
                             INNER JOIN usuario u ON pl.id_usuario_aluno = u.id_usuario
                             WHERE u.id_academia = :id_academia
                               AND (p.status = 'ATRASADO' OR (p.status = 'PENDENTE' AND p.data_vencimento < CURDATE()))";

            $stmt = $this->conexao->prepare($sqlAtrasados);
            $stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
            $stmt->execute();
            $resAtrasados = $stmt->fetch(PDO::FETCH_ASSOC);

            $sqlLista = "SELECT p.id_pagamento, p.valor, p.data_vencimento, u.nome AS aluno_nome
                         FROM pagamento p
                         INNER JOIN plano pl ON p.id_plano_matricula = pl.id_plano
                         INNER JOIN usuario u ON pl.id_usuario_aluno = u.id_usuario
                         WHERE u.id_academia = :id_academia
                           AND (p.status = 'ATRASADO' OR (p.status = 'PENDENTE' AND p.data_vencimento < CURDATE()))
                         ORDER BY p.data_vencimento ASC
                         LIMIT 5";

            $stmtLista = $this->conexao->prepare($sqlLista);
            $stmtLista->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
            $stmtLista->execute();
            $listaAtrasados = $stmtLista->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total_atrasados' => (int)($resAtrasados['total_qtd'] ?? 0),
                'valor_atrasados' => (float)($resAtrasados['total_valor'] ?? 0),
                'lista' => $listaAtrasados
            ];
        } catch (PDOException $e) {
            return [
                'total_atrasados' => 0,
                'valor_atrasados' => 0,
                'lista' => []
            ];
        }
    }

    public function relatorioPagamentos(int $idAcademia, string $dataInicio, string $dataFim, string $status): array {
        $sql = "SELECT p.id_pagamento, p.valor, p.data_vencimento, p.data_pagamento, p.status, p.forma_pagamento,
                       pl.nome_plano, u.nome AS aluno_nome
                FROM pagamento p
                INNER JOIN plano pl ON p.id_plano_matricula = pl.id_plano
                INNER JOIN usuario u ON pl.id_usuario_aluno = u.id_usuario
                WHERE u.id_academia = :id_academia
                  AND (
                      (DATE(p.data_vencimento) >= :data_inicio AND DATE(p.data_vencimento) <= :data_fim)
                      OR 
                      (DATE(p.data_pagamento) >= :data_inicio AND DATE(p.data_pagamento) <= :data_fim)
                  )";

        if ($status !== 'TODOS') {
            $sql .= " AND UPPER(p.status) = :status";
        }

        $sql .= " ORDER BY p.data_vencimento ASC";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
        $stmt->bindValue(':data_inicio', $dataInicio);
        $stmt->bindValue(':data_fim', $dataFim);

        if ($status !== 'TODOS') {
            $stmt->bindValue(':status', strtoupper($status));
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}