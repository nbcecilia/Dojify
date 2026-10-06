<?php
/**
 * Acesso aos dados de avaliações já registradas, comentários dos alunos,
 * previsões de graduação e notificações da equipe.
 * Este DAO não cria nem edita avaliações do professor/gerente; essa etapa
 * poderá ser acrescentada ao fluxo de avaliações da equipe.
 */

require_once __DIR__ . '/Conexao.php';

class AvaliacaoDAO
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = Conexao::getConexao();
    }

    public function listarEvolucaoAluno(int $idAluno): array
    {
        $stmt = $this->conexao->prepare("
            SELECT
                a.id_avaliacao,
                a.data_avaliacao,
                a.habilidades_melhorar,
                a.observacoes,
                professor.nome AS nome_avaliador,
                comentario.id_comentario,
                comentario.comentario,
                comentario.data_comentario,
                aluno.nome AS nome_aluno
            FROM avaliacao a
            INNER JOIN usuario professor ON professor.id_usuario = a.id_usuario_professor
            LEFT JOIN avaliacao_comentario comentario ON comentario.id_avaliacao = a.id_avaliacao
            LEFT JOIN usuario aluno ON aluno.id_usuario = comentario.id_usuario_aluno
            WHERE a.id_usuario_aluno = :id_aluno
            ORDER BY a.data_avaliacao DESC, a.id_avaliacao DESC, comentario.data_comentario ASC
        ");
        $stmt->execute(['id_aluno' => $idAluno]);

        $avaliacoes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $idAvaliacao = (int)$linha['id_avaliacao'];
            if (!isset($avaliacoes[$idAvaliacao])) {
                $avaliacoes[$idAvaliacao] = [
                    'id_avaliacao' => $idAvaliacao,
                    'data_avaliacao' => (string)$linha['data_avaliacao'],
                    'habilidades_melhorar' => (string)$linha['habilidades_melhorar'],
                    'observacoes' => $linha['observacoes'] === null ? null : (string)$linha['observacoes'],
                    'nome_avaliador' => (string)$linha['nome_avaliador'],
                    'comentarios' => [],
                ];
            }

            if ($linha['id_comentario'] !== null) {
                $avaliacoes[$idAvaliacao]['comentarios'][] = [
                    'id_comentario' => (int)$linha['id_comentario'],
                    'comentario' => (string)$linha['comentario'],
                    'data_comentario' => (string)$linha['data_comentario'],
                    'nome_aluno' => (string)$linha['nome_aluno'],
                ];
            }
        }

        return array_values($avaliacoes);
    }

    public function listarGraduacoesPrevistasAluno(int $idAluno): array
    {
        $stmt = $this->conexao->prepare("
            SELECT gp.id_modalidade, gp.data_prevista, m.nome AS nome_modalidade
            FROM graduacao_prevista gp
            INNER JOIN modalidade m ON m.id_modalidade = gp.id_modalidade
            WHERE gp.id_usuario_aluno = :id_aluno
              AND gp.data_prevista >= CURDATE()
            ORDER BY gp.data_prevista ASC, m.nome ASC
        ");
        $stmt->execute(['id_aluno' => $idAluno]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAlunosModalidadesAcademia(int $idAcademia): array
    {
        $stmt = $this->conexao->prepare("
            SELECT
                aluno.id_usuario AS id_aluno,
                aluno.nome AS nome_aluno,
                modalidade.id_modalidade,
                modalidade.nome AS nome_modalidade,
                prevista.data_prevista
            FROM usuario aluno
            INNER JOIN (
                SELECT id_usuario_aluno, id_modalidade
                FROM aluno_modalidade
                UNION
                SELECT id_usuario_aluno, id_modalidade
                FROM plano
                WHERE status = 'ATIVO' AND id_modalidade IS NOT NULL
            ) modalidades_aluno ON modalidades_aluno.id_usuario_aluno = aluno.id_usuario
            INNER JOIN modalidade ON modalidade.id_modalidade = modalidades_aluno.id_modalidade
            LEFT JOIN graduacao_prevista prevista
                ON prevista.id_usuario_aluno = aluno.id_usuario
                AND prevista.id_modalidade = modalidade.id_modalidade
            WHERE aluno.id_academia = :id_academia
              AND aluno.perfil_id = 4
              AND aluno.status = 'ATIVO'
            ORDER BY aluno.nome ASC, modalidade.nome ASC
        ");
        $stmt->execute(['id_academia' => $idAcademia]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvarGraduacaoPrevista(
        int $idStaff,
        int $idAcademia,
        int $idAluno,
        int $idModalidade,
        string $dataPrevista
    ): void {
        $stmt = $this->conexao->prepare("
            SELECT COUNT(*)
            FROM usuario staff
            INNER JOIN usuario aluno
                ON aluno.id_usuario = :id_aluno
                AND aluno.id_academia = staff.id_academia
                AND aluno.perfil_id = 4
            INNER JOIN (
                SELECT id_usuario_aluno, id_modalidade
                FROM aluno_modalidade
                UNION
                SELECT id_usuario_aluno, id_modalidade
                FROM plano
                WHERE status = 'ATIVO' AND id_modalidade IS NOT NULL
            ) modalidades_aluno
                ON modalidades_aluno.id_usuario_aluno = aluno.id_usuario
                AND modalidades_aluno.id_modalidade = :id_modalidade
            WHERE staff.id_usuario = :id_staff
              AND staff.id_academia = :id_academia
              AND staff.perfil_id IN (2, 3)
        ");
        $stmt->execute([
            'id_aluno' => $idAluno,
            'id_modalidade' => $idModalidade,
            'id_staff' => $idStaff,
            'id_academia' => $idAcademia,
        ]);

        if ((int)$stmt->fetchColumn() !== 1) {
            throw new DomainException('Aluno ou modalidade não pertencem à academia do usuário.');
        }

        $stmt = $this->conexao->prepare("
            INSERT INTO graduacao_prevista
                (id_usuario_aluno, id_modalidade, id_usuario_atualizou, data_prevista)
            VALUES
                (:id_aluno, :id_modalidade, :id_staff, :data_prevista)
            ON DUPLICATE KEY UPDATE
                id_usuario_atualizou = VALUES(id_usuario_atualizou),
                data_prevista = VALUES(data_prevista),
                data_atualizada = CURRENT_TIMESTAMP
        ");
        $stmt->execute([
            'id_aluno' => $idAluno,
            'id_modalidade' => $idModalidade,
            'id_staff' => $idStaff,
            'data_prevista' => $dataPrevista,
        ]);
    }

    public function adicionarComentario(int $idAvaliacao, int $idAluno, string $comentario): void
    {
        $this->conexao->beginTransaction();

        try {
            $stmt = $this->conexao->prepare("
                SELECT a.id_usuario_professor, aluno.id_academia
                FROM avaliacao a
                INNER JOIN usuario aluno ON aluno.id_usuario = a.id_usuario_aluno
                WHERE a.id_avaliacao = :id_avaliacao
                  AND a.id_usuario_aluno = :id_aluno
                FOR UPDATE
            ");
            $stmt->execute([
                'id_avaliacao' => $idAvaliacao,
                'id_aluno' => $idAluno,
            ]);
            $avaliacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$avaliacao) {
                throw new DomainException('A avaliação não pertence ao aluno autenticado.');
            }

            $stmt = $this->conexao->prepare("
                INSERT INTO avaliacao_comentario
                    (id_avaliacao, id_usuario_aluno, comentario)
                VALUES
                    (:id_avaliacao, :id_aluno, :comentario)
            ");
            $stmt->execute([
                'id_avaliacao' => $idAvaliacao,
                'id_aluno' => $idAluno,
                'comentario' => $comentario,
            ]);
            $idComentario = (int)$this->conexao->lastInsertId();

            $stmt = $this->conexao->prepare("
                SELECT id_usuario
                FROM usuario
                WHERE id_academia = :id_academia
                  AND perfil_id = 2
                  AND status = 'ATIVO'
                UNION
                SELECT id_usuario
                FROM usuario
                WHERE id_usuario = :id_avaliador
                  AND id_academia = :id_academia_avaliador
                  AND perfil_id IN (2, 3)
            ");
            $stmt->execute([
                'id_academia' => (int)$avaliacao['id_academia'],
                'id_avaliador' => (int)$avaliacao['id_usuario_professor'],
                'id_academia_avaliador' => (int)$avaliacao['id_academia'],
            ]);
            $destinatarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmtNotificacao = $this->conexao->prepare("
                INSERT INTO notificacao_avaliacao
                    (id_avaliacao_comentario, id_usuario_destinatario)
                VALUES
                    (:id_comentario, :id_destinatario)
            ");
            foreach ($destinatarios as $idDestinatario) {
                $stmtNotificacao->execute([
                    'id_comentario' => $idComentario,
                    'id_destinatario' => (int)$idDestinatario,
                ]);
            }

            $this->conexao->commit();
        } catch (Throwable $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            throw $e;
        }
    }

    public function listarNotificacoesEquipe(int $idStaff): array
    {
        $stmt = $this->conexao->prepare("
            SELECT COUNT(*)
            FROM notificacao_avaliacao notificacao
            INNER JOIN avaliacao_comentario comentario
                ON comentario.id_comentario = notificacao.id_avaliacao_comentario
            INNER JOIN avaliacao avaliacao
                ON avaliacao.id_avaliacao = comentario.id_avaliacao
            INNER JOIN usuario aluno ON aluno.id_usuario = avaliacao.id_usuario_aluno
            WHERE notificacao.id_usuario_destinatario = :id_staff
              AND notificacao.data_leitura IS NULL
        ");
        $stmt->execute(['id_staff' => $idStaff]);
        $total = (int)$stmt->fetchColumn();

        $stmt = $this->conexao->prepare("
            SELECT
                notificacao.id_notificacao,
                notificacao.id_avaliacao_comentario,
                avaliacao.id_avaliacao,
                aluno.nome AS nome_aluno,
                comentario.data_comentario
            FROM notificacao_avaliacao notificacao
            INNER JOIN avaliacao_comentario comentario
                ON comentario.id_comentario = notificacao.id_avaliacao_comentario
            INNER JOIN avaliacao avaliacao
                ON avaliacao.id_avaliacao = comentario.id_avaliacao
            INNER JOIN usuario aluno ON aluno.id_usuario = avaliacao.id_usuario_aluno
            WHERE notificacao.id_usuario_destinatario = :id_staff
              AND notificacao.data_leitura IS NULL
            ORDER BY comentario.data_comentario DESC, notificacao.id_notificacao DESC
        ");
        $stmt->execute(['id_staff' => $idStaff]);

        return [
            'total' => $total,
            'lista' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function listarComentariosEquipe(int $idStaff, int $idAcademia, int $perfilId): array
    {
        $condicaoProfessor = $perfilId === 3 ? 'AND avaliacao.id_usuario_professor = :id_staff' : '';
        $sql = "
            SELECT
                comentario.id_comentario,
                comentario.comentario,
                comentario.data_comentario,
                avaliacao.id_avaliacao,
                avaliacao.data_avaliacao,
                avaliacao.habilidades_melhorar,
                aluno.nome AS nome_aluno,
                professor.nome AS nome_avaliador
            FROM avaliacao_comentario comentario
            INNER JOIN avaliacao avaliacao ON avaliacao.id_avaliacao = comentario.id_avaliacao
            INNER JOIN usuario aluno ON aluno.id_usuario = avaliacao.id_usuario_aluno
            INNER JOIN usuario professor ON professor.id_usuario = avaliacao.id_usuario_professor
            WHERE aluno.id_academia = :id_academia
              {$condicaoProfessor}
            ORDER BY comentario.data_comentario DESC, comentario.id_comentario DESC
        ";
        $stmt = $this->conexao->prepare($sql);
        $parametros = ['id_academia' => $idAcademia];
        if ($perfilId === 3) {
            $parametros['id_staff'] = $idStaff;
        }
        $stmt->execute($parametros);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarNotificacaoLida(int $idNotificacao, int $idStaff): void
    {
        $stmt = $this->conexao->prepare("
            UPDATE notificacao_avaliacao
            SET data_leitura = CURRENT_TIMESTAMP
            WHERE id_notificacao = :id_notificacao
              AND id_usuario_destinatario = :id_staff
              AND data_leitura IS NULL
        ");
        $stmt->execute([
            'id_notificacao' => $idNotificacao,
            'id_staff' => $idStaff,
        ]);
    }
}
