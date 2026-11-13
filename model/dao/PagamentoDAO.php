<?php
// model/dao/PagamentoDAO.php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/PagamentoDTO.php';

class PagamentoDAO {
    private PDO $conexao;

    public function __construct() {
        $this->conexao = \Conexao::getConexao();
    }

    public function gerarMensalidadesRecorrentes(int $idAcademia): int {
        if ($idAcademia <= 0) {
            return 0;
        }

        $dataLimite = new DateTimeImmutable('last day of this month');
        $geradas = 0;

        $this->conexao->beginTransaction();

        try {
            $stmtPlanos = $this->conexao->prepare(
                "SELECT pl.id_plano, pl.valor
                 FROM plano pl
                 INNER JOIN usuario u ON u.id_usuario = pl.id_usuario_aluno
                 WHERE u.id_academia = :id_academia
                   AND u.perfil_id = 4
                   AND pl.status = 'ATIVO'
                 ORDER BY pl.id_plano
                 FOR UPDATE"
            );
            $stmtPlanos->execute([':id_academia' => $idAcademia]);
            $planos = $stmtPlanos->fetchAll(PDO::FETCH_ASSOC);

            $stmtDatas = $this->conexao->prepare(
                "SELECT MIN(data_vencimento) AS primeiro_vencimento,
                        MAX(data_vencimento) AS ultimo_vencimento
                 FROM pagamento
                 WHERE id_plano_matricula = :id_plano"
            );
            $stmtExiste = $this->conexao->prepare(
                "SELECT 1
                 FROM pagamento
                 WHERE id_plano_matricula = :id_plano
                   AND data_vencimento = :data_vencimento
                 LIMIT 1"
            );
            $stmtInserir = $this->conexao->prepare(
                "INSERT INTO pagamento (id_plano_matricula, valor, data_vencimento, status)
                 VALUES (:id_plano, :valor, :data_vencimento, 'PENDENTE')"
            );

            foreach ($planos as $plano) {
                $idPlano = (int)$plano['id_plano'];
                $stmtDatas->execute([':id_plano' => $idPlano]);
                $datas = $stmtDatas->fetch(PDO::FETCH_ASSOC);

                if (empty($datas['primeiro_vencimento']) || empty($datas['ultimo_vencimento'])) {
                    continue;
                }

                $primeiroVencimento = new DateTimeImmutable($datas['primeiro_vencimento']);
                $ultimoVencimento = new DateTimeImmutable($datas['ultimo_vencimento']);
                $diaOriginal = (int)$primeiroVencimento->format('d');

                while ($ultimoVencimento < $dataLimite) {
                    $proximoMes = $ultimoVencimento->modify('first day of next month');
                    $diaVencimento = min($diaOriginal, (int)$proximoMes->format('t'));
                    $proximoVencimento = $proximoMes->setDate(
                        (int)$proximoMes->format('Y'),
                        (int)$proximoMes->format('m'),
                        $diaVencimento
                    );

                    if ($proximoVencimento > $dataLimite) {
                        break;
                    }

                    $dataVencimento = $proximoVencimento->format('Y-m-d');
                    $stmtExiste->execute([
                        ':id_plano' => $idPlano,
                        ':data_vencimento' => $dataVencimento
                    ]);

                    if (!$stmtExiste->fetchColumn()) {
                        $stmtInserir->execute([
                            ':id_plano' => $idPlano,
                            ':valor' => $plano['valor'],
                            ':data_vencimento' => $dataVencimento
                        ]);
                        $geradas++;
                    }

                    $ultimoVencimento = $proximoVencimento;
                }
            }

            $this->conexao->commit();
            return $geradas;
        } catch (Throwable $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            throw $e;
        }
    }

    public function listarPagamentosDaAcademia(int $idAcademia): array {
        $sql = "SELECT 
                    MIN(p.id_pagamento) AS id_pagamento, 
                    CASE
                        WHEN SUM(CASE WHEN UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO' THEN 1 ELSE 0 END) = 0
                        THEN SUM(p.valor)
                        ELSE SUM(CASE WHEN UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO' THEN p.valor ELSE 0 END)
                    END AS valor,
                    CASE
                        WHEN SUM(CASE WHEN UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO' THEN 1 ELSE 0 END) = 0
                        THEN MIN(p.data_vencimento)
                        ELSE MIN(CASE WHEN UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO' THEN p.data_vencimento END)
                    END AS data_vencimento,
                    CASE
                        WHEN SUM(CASE WHEN UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO' THEN 1 ELSE 0 END) = 0
                        THEN MAX(p.data_pagamento)
                        ELSE MAX(CASE WHEN UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO' THEN p.data_pagamento END)
                    END AS data_pagamento,
                    CASE
                        WHEN SUM(CASE
                            WHEN UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO'
                            THEN 1 ELSE 0
                        END) = 0 THEN 'CANCELADO'
                        WHEN SUM(CASE
                            WHEN UPPER(COALESCE(p.status, 'PENDENTE')) = 'ATRASADO'
                              OR (UPPER(COALESCE(p.status, 'PENDENTE')) = 'PENDENTE'
                                  AND p.data_vencimento < CURDATE())
                            THEN 1 ELSE 0
                        END) > 0 THEN 'ATRASADO'
                        WHEN SUM(CASE
                            WHEN UPPER(COALESCE(p.status, 'PENDENTE')) = 'PENDENTE'
                            THEN 1 ELSE 0
                        END) > 0 THEN 'PENDENTE'
                        WHEN SUM(CASE
                            WHEN UPPER(COALESCE(p.status, 'PENDENTE')) = 'EM_ANALISE'
                            THEN 1 ELSE 0
                        END) > 0 THEN 'EM_ANALISE'
                        ELSE 'PAGO'
                    END AS status,
                    CASE
                        WHEN SUM(CASE WHEN UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO' THEN 1 ELSE 0 END) = 0
                        THEN MAX(p.forma_pagamento)
                        ELSE MAX(CASE WHEN UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO' THEN p.forma_pagamento END)
                    END AS forma_pagamento,
                    GROUP_CONCAT(DISTINCT pl.nome_plano SEPARATOR ', ') AS nome_plano,
                    GROUP_CONCAT(
                        CASE WHEN UPPER(COALESCE(p.status, 'PENDENTE')) NOT IN ('PAGO', 'CANCELADO')
                            THEN p.id_pagamento
                        END
                        ORDER BY p.id_pagamento SEPARATOR ','
                    ) AS ids_pagamentos,
                    u.nome AS aluno_nome,
                    u.id_usuario AS id_aluno,
                    DATE_FORMAT(p.data_vencimento, '%Y-%m') AS mes_ano
                FROM pagamento p
                INNER JOIN plano pl ON p.id_plano_matricula = pl.id_plano
                INNER JOIN usuario u ON pl.id_usuario_aluno = u.id_usuario
                WHERE u.id_academia = :id_academia
                  AND u.perfil_id = 4
                  AND UPPER(COALESCE(p.status, 'PENDENTE')) NOT IN ('PAGO', 'CANCELADO')
                GROUP BY u.id_usuario, DATE_FORMAT(p.data_vencimento, '%Y-%m')
                ORDER BY MIN(p.data_vencimento) DESC";
        
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registarRecebimento(int $idPagamento, int $idAcademia, string $formaPagamento, string $dataPagamento): bool {
        try {
            $pagamentos = $this->obterPagamentosDoGrupo($idPagamento, $idAcademia, true);
            if (empty($pagamentos)) {
                return false;
            }

            $ids = array_column($pagamentos, 'id_pagamento');
            $placeholders = [];
            $parametros = [
                ':data_pagamento' => $dataPagamento,
                ':forma' => $formaPagamento
            ];
            foreach ($ids as $indice => $id) {
                $placeholder = ':id' . $indice;
                $placeholders[] = $placeholder;
                $parametros[$placeholder] = (int)$id;
            }
            $sql = "UPDATE pagamento 
                    SET status = 'PAGO', data_pagamento = :data_pagamento, forma_pagamento = :forma 
                    WHERE id_pagamento IN (" . implode(',', $placeholders) . ")";
            
            $stmt = $this->conexao->prepare($sql);
            return $stmt->execute($parametros);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function editarPagamentosDaAcademia(int $idPagamento, int $idAcademia, PagamentoDTO $pagamento): bool {
        try {
            $pagamentos = $this->obterPagamentosDoGrupo($idPagamento, $idAcademia);
            if (empty($pagamentos)) {
                return false;
            }
            if (!$this->grupoEstaPago($pagamentos)) {
                return false;
            }

            $this->conexao->beginTransaction();
            $valores = $this->distribuirValor((float)$pagamento->getValor(), $pagamentos);
            $stmt = $this->conexao->prepare(
                "UPDATE pagamento
                 SET valor = :valor,
                     data_vencimento = :data_vencimento,
                     data_pagamento = :data_pagamento,
                     forma_pagamento = :forma_pagamento,
                     status = CASE
                         WHEN UPPER(COALESCE(status, 'PENDENTE')) = 'ATRASADO' THEN 'PENDENTE'
                         ELSE status
                     END
                 WHERE id_pagamento = :id_pagamento"
            );

            foreach ($pagamentos as $indice => $registro) {
                $stmt->execute([
                    ':valor' => number_format($valores[$indice], 2, '.', ''),
                    ':data_vencimento' => $pagamento->getDataVencimento(),
                    ':data_pagamento' => $pagamento->getDataPagamento(),
                    ':forma_pagamento' => $pagamento->getFormaPagamento(),
                    ':id_pagamento' => (int)$registro['id_pagamento']
                ]);
            }

            $this->conexao->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            return false;
        }
    }

    public function cancelarPagamentosDaAcademia(int $idPagamento, int $idAcademia): bool {
        try {
            $pagamentos = $this->obterPagamentosDoGrupo($idPagamento, $idAcademia);
            if (empty($pagamentos)) {
                return false;
            }
            if (!$this->grupoEstaPago($pagamentos)) {
                return false;
            }

            $ids = array_column($pagamentos, 'id_pagamento');
            $placeholders = [];
            $parametros = [];
            foreach ($ids as $indice => $id) {
                $placeholder = ':id' . $indice;
                $placeholders[] = $placeholder;
                $parametros[$placeholder] = (int)$id;
            }
            $stmt = $this->conexao->prepare(
                "UPDATE pagamento SET status = 'CANCELADO' WHERE id_pagamento IN (" . implode(',', $placeholders) . ")"
            );

            return $stmt->execute($parametros);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function editarDataVencimento(int $idPagamento, int $idAcademia, string $dataVencimento): bool {
        try {
            $pagamentos = $this->obterPagamentosDoGrupo($idPagamento, $idAcademia, true);
            if (empty($pagamentos) || !$this->grupoPendenteOuAtrasado($pagamentos)) {
                return false;
            }

            $placeholders = [];
            $parametros = [':data_vencimento' => $dataVencimento];
            foreach ($pagamentos as $indice => $pagamento) {
                $placeholder = ':id' . $indice;
                $placeholders[] = $placeholder;
                $parametros[$placeholder] = (int)$pagamento['id_pagamento'];
            }

            $stmt = $this->conexao->prepare(
                "UPDATE pagamento
                 SET data_vencimento = :data_vencimento,
                     status = CASE
                         WHEN UPPER(COALESCE(status, 'PENDENTE')) = 'ATRASADO' THEN 'PENDENTE'
                         ELSE status
                     END
                 WHERE id_pagamento IN (" . implode(',', $placeholders) . ")"
            );

            return $stmt->execute($parametros);
        } catch (PDOException $e) {
            return false;
        }
    }

    private function obterPagamentosDoGrupo(int $idPagamento, int $idAcademia, bool $somenteAbertos = false): array {
        $sqlGrupo = "SELECT u.id_usuario, DATE_FORMAT(p.data_vencimento, '%Y-%m') AS mes_ano
                     FROM pagamento p
                     INNER JOIN plano pl ON p.id_plano_matricula = pl.id_plano
                     INNER JOIN usuario u ON pl.id_usuario_aluno = u.id_usuario
                     WHERE p.id_pagamento = :id_pagamento
                       AND u.id_academia = :id_academia
                       AND u.perfil_id = 4";
        $stmtGrupo = $this->conexao->prepare($sqlGrupo);
        $stmtGrupo->execute([
            ':id_pagamento' => $idPagamento,
            ':id_academia' => $idAcademia
        ]);
        $grupo = $stmtGrupo->fetch(PDO::FETCH_ASSOC);
        if (!$grupo) {
            return [];
        }

        $filtroStatus = $somenteAbertos
            ? "AND UPPER(COALESCE(p.status, 'PENDENTE')) NOT IN ('PAGO', 'CANCELADO')"
            : "AND UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO'";
        $sqlPagamentos = "SELECT p.id_pagamento, p.valor, p.status
                          FROM pagamento p
                          INNER JOIN plano pl ON p.id_plano_matricula = pl.id_plano
                          INNER JOIN usuario u ON pl.id_usuario_aluno = u.id_usuario
                          WHERE u.id_academia = :id_academia
                            AND u.perfil_id = 4
                            AND u.id_usuario = :id_usuario
                            AND DATE_FORMAT(p.data_vencimento, '%Y-%m') = :mes_ano
                            $filtroStatus
                          ORDER BY p.id_pagamento";
        $stmtPagamentos = $this->conexao->prepare($sqlPagamentos);
        $stmtPagamentos->execute([
            ':id_academia' => $idAcademia,
            ':id_usuario' => (int)$grupo['id_usuario'],
            ':mes_ano' => $grupo['mes_ano']
        ]);

        return $stmtPagamentos->fetchAll(PDO::FETCH_ASSOC);
    }

    private function grupoEstaPago(array $pagamentos): bool {
        foreach ($pagamentos as $pagamento) {
            if (strtoupper((string)($pagamento['status'] ?? 'PENDENTE')) !== 'PAGO') {
                return false;
            }
        }

        return true;
    }

    private function grupoPendenteOuAtrasado(array $pagamentos): bool {
        foreach ($pagamentos as $pagamento) {
            if (!in_array(strtoupper((string)($pagamento['status'] ?? 'PENDENTE')), ['PENDENTE', 'ATRASADO'], true)) {
                return false;
            }
        }

        return true;
    }

    private function distribuirValor(float $valorTotal, array $pagamentos): array {
        $totalCentavos = (int)round($valorTotal * 100);
        $valoresOriginais = array_map(
            static function (array $pagamento): int {
                return (int)round((float)$pagamento['valor'] * 100);
            },
            $pagamentos
        );
        $somaOriginal = array_sum($valoresOriginais);
        $distribuicao = [];
        $centavosAlocados = 0;

        foreach ($valoresOriginais as $indice => $valorOriginal) {
            if ($indice === count($valoresOriginais) - 1) {
                $centavos = $totalCentavos - $centavosAlocados;
            } elseif ($somaOriginal > 0) {
                $centavos = (int)floor($totalCentavos * $valorOriginal / $somaOriginal);
            } else {
                $centavos = (int)floor($totalCentavos / count($valoresOriginais));
            }

            $distribuicao[] = $centavos / 100;
            $centavosAlocados += $centavos;
        }

        return $distribuicao;
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
        $sql = "SELECT p.id_pagamento, p.valor, p.data_vencimento, p.data_pagamento,
                       COALESCE(p.status, 'PENDENTE') AS status, p.forma_pagamento,
                       u.id_usuario AS id_aluno,
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
        if ($status === 'TODOS') {
            $sql .= " AND UPPER(COALESCE(p.status, 'PENDENTE')) <> 'CANCELADO'";
        } else {
            $sql .= " AND UPPER(COALESCE(p.status, 'PENDENTE')) = :status";
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