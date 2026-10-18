<?php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/AvaliacaoDTO.php';

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
                a.data_prevista,
                a.habilidades_melhorar,
                a.observacoes,
                professor.nome AS nome_avaliador,
                modalidade.nome AS nome_modalidade
            FROM avaliacao a
            INNER JOIN usuario professor ON professor.id_usuario = a.id_usuario_professor
            LEFT JOIN modalidade ON modalidade.id_modalidade = a.id_modalidade
            WHERE a.id_usuario_aluno = :id_aluno
            ORDER BY a.data_avaliacao DESC, a.id_avaliacao DESC
        ");
        $stmt->execute(['id_aluno' => $idAluno]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarGraduacoesPrevistasAluno(int $idAluno): array
    {
        $stmt = $this->conexao->prepare("
            SELECT
                avaliacao.id_avaliacao,
                avaliacao.id_modalidade,
                avaliacao.data_prevista,
                modalidade.nome AS nome_modalidade
            FROM avaliacao
            INNER JOIN modalidade ON modalidade.id_modalidade = avaliacao.id_modalidade
            WHERE avaliacao.id_usuario_aluno = :id_aluno
              AND avaliacao.data_prevista >= CURDATE()
              AND NOT EXISTS (
                  SELECT 1
                  FROM avaliacao mais_recente
                  WHERE mais_recente.id_usuario_aluno = avaliacao.id_usuario_aluno
                    AND mais_recente.id_modalidade = avaliacao.id_modalidade
                    AND (
                        mais_recente.data_avaliacao > avaliacao.data_avaliacao
                        OR (
                            mais_recente.data_avaliacao = avaliacao.data_avaliacao
                            AND mais_recente.id_avaliacao > avaliacao.id_avaliacao
                        )
                    )
              )
            ORDER BY avaliacao.data_prevista ASC, modalidade.nome ASC
        ");
        $stmt->execute(['id_aluno' => $idAluno]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cadastrar(
        AvaliacaoDTO $avaliacao,
        int $idModalidade,
        ?string $dataPrevista
    ): bool {
        if (!$this->alunoTemMatriculaAtiva(
            $avaliacao->getIdUsuarioProfessor(),
            $avaliacao->getIdUsuarioAluno(),
            $idModalidade
        )) {
            throw new DomainException('Aluno, modalidade ou avaliador inválido.');
        }

        $stmt = $this->conexao->prepare("
            INSERT INTO avaliacao
                (id_usuario_professor, id_usuario_aluno, id_modalidade, data_avaliacao, data_prevista, habilidades_melhorar, observacoes)
            VALUES
                (:professor, :aluno, :modalidade, :data, :data_prevista, :habilidades, :observacoes)
        ");
        return $stmt->execute([
            'professor' => $avaliacao->getIdUsuarioProfessor(),
            'aluno' => $avaliacao->getIdUsuarioAluno(),
            'modalidade' => $idModalidade,
            'data' => $avaliacao->getDataAvaliacao(),
            'data_prevista' => $dataPrevista,
            'habilidades' => $avaliacao->getHabilidadesMelhorar(),
            'observacoes' => $avaliacao->getObservacoes(),
        ]);
    }

    public function atualizar(AvaliacaoDTO $avaliacao, int $perfilId = 3): bool
    {
        $stmt = $this->conexao->prepare("
            SELECT avaliacao.id_usuario_aluno, avaliacao.id_modalidade
            FROM avaliacao
            INNER JOIN usuario aluno ON aluno.id_usuario = avaliacao.id_usuario_aluno
            WHERE avaliacao.id_avaliacao = :id_avaliacao
              AND (
                  (avaliacao.id_usuario_professor = :professor AND :perfil_professor = 3)
                  OR (
                      :perfil_gerente = 2
                      AND EXISTS (
                          SELECT 1
                          FROM usuario gerente
                          WHERE gerente.id_usuario = :gerente
                            AND gerente.perfil_id = 2
                            AND gerente.id_academia = aluno.id_academia
                      )
                  )
              )
        ");
        $stmt->execute([
            'id_avaliacao' => $avaliacao->getIdAvaliacao(),
            'professor' => $avaliacao->getIdUsuarioProfessor(),
            'perfil_professor' => $perfilId,
            'perfil_gerente' => $perfilId,
            'gerente' => $avaliacao->getIdUsuarioProfessor(),
        ]);
        $vinculoAtual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vinculoAtual) {
            return false;
        }

        $mantemVinculoAtual =
            (int)$vinculoAtual['id_usuario_aluno'] === (int)$avaliacao->getIdUsuarioAluno() &&
            (int)$vinculoAtual['id_modalidade'] === (int)$avaliacao->getIdModalidade();
        if (!$mantemVinculoAtual && !$this->alunoTemMatriculaAtiva(
            $avaliacao->getIdUsuarioProfessor(),
            $avaliacao->getIdUsuarioAluno(),
            $avaliacao->getIdModalidade()
        )) {
            throw new DomainException('Aluno, modalidade ou avaliador inválido.');
        }

        $stmt = $this->conexao->prepare("
            UPDATE avaliacao
            INNER JOIN usuario aluno ON aluno.id_usuario = avaliacao.id_usuario_aluno
            SET id_usuario_aluno = :aluno,
                id_modalidade = :modalidade,
                data_avaliacao = :data,
                data_prevista = :data_prevista,
                habilidades_melhorar = :habilidades,
                observacoes = :observacoes
            WHERE id_avaliacao = :id_avaliacao
              AND (
                  (avaliacao.id_usuario_professor = :professor AND :perfil_professor = 3)
                  OR (
                      :perfil_gerente = 2
                      AND EXISTS (
                          SELECT 1
                          FROM usuario gerente
                          WHERE gerente.id_usuario = :gerente
                            AND gerente.perfil_id = 2
                            AND gerente.id_academia = aluno.id_academia
                      )
                  )
              )
        ");
        $stmt->execute([
            'aluno' => $avaliacao->getIdUsuarioAluno(),
            'modalidade' => $avaliacao->getIdModalidade(),
            'data' => $avaliacao->getDataAvaliacao(),
            'data_prevista' => $avaliacao->getDataPrevista(),
            'habilidades' => $avaliacao->getHabilidadesMelhorar(),
            'observacoes' => $avaliacao->getObservacoes(),
            'id_avaliacao' => $avaliacao->getIdAvaliacao(),
            'professor' => $avaliacao->getIdUsuarioProfessor(),
            'perfil_professor' => $perfilId,
            'perfil_gerente' => $perfilId,
            'gerente' => $avaliacao->getIdUsuarioProfessor(),
        ]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        $stmt = $this->conexao->prepare("
            SELECT 1
            FROM avaliacao
            INNER JOIN usuario aluno ON aluno.id_usuario = avaliacao.id_usuario_aluno
            WHERE avaliacao.id_avaliacao = :id_avaliacao
              AND (
                  (avaliacao.id_usuario_professor = :professor AND :perfil_professor = 3)
                  OR (
                      :perfil_gerente = 2
                      AND EXISTS (
                          SELECT 1
                          FROM usuario gerente
                          WHERE gerente.id_usuario = :gerente
                            AND gerente.perfil_id = 2
                            AND gerente.id_academia = aluno.id_academia
                      )
                  )
              )
        ");
        $stmt->execute([
            'id_avaliacao' => $avaliacao->getIdAvaliacao(),
            'professor' => $avaliacao->getIdUsuarioProfessor(),
            'perfil_professor' => $perfilId,
            'perfil_gerente' => $perfilId,
            'gerente' => $avaliacao->getIdUsuarioProfessor(),
        ]);

        return (bool)$stmt->fetchColumn();
    }

    private function alunoTemMatriculaAtiva(int $idProfessor, int $idAluno, int $idModalidade): bool
    {
        $stmt = $this->conexao->prepare("
            SELECT COUNT(DISTINCT aluno.id_usuario)
            FROM usuario staff
            INNER JOIN usuario aluno
                ON aluno.id_usuario = :aluno
                AND aluno.id_academia = staff.id_academia
                AND aluno.perfil_id = 4
                AND aluno.status = 'ATIVO'
            INNER JOIN plano
                ON plano.id_usuario_aluno = aluno.id_usuario
                AND plano.id_modalidade = :modalidade
                AND plano.status = 'ATIVO'
            WHERE staff.id_usuario = :professor
              AND staff.id_academia IS NOT NULL
              AND staff.perfil_id IN (2, 3)
              AND (
                  staff.perfil_id = 2
                  OR EXISTS (
                      SELECT 1
                      FROM turma
                      WHERE turma.id_usuario_professor = staff.id_usuario
                        AND turma.id_modalidade = :modalidade_turma
                  )
              )
        ");
        $stmt->execute([
            'aluno' => $idAluno,
            'modalidade' => $idModalidade,
            'modalidade_turma' => $idModalidade,
            'professor' => $idProfessor,
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function listarPorAcademia(int $idAcademia): array
    {
        $stmt = $this->conexao->prepare("
            SELECT
                avaliacao.*,
                aluno.nome AS nome_aluno,
                professor.nome AS nome_professor,
                modalidade.nome AS nome_modalidade
            FROM avaliacao
            INNER JOIN usuario aluno ON aluno.id_usuario = avaliacao.id_usuario_aluno
            INNER JOIN usuario professor ON professor.id_usuario = avaliacao.id_usuario_professor
            LEFT JOIN modalidade ON modalidade.id_modalidade = avaliacao.id_modalidade
            WHERE aluno.id_academia = :id_academia
            ORDER BY avaliacao.data_avaliacao DESC
        ");
        $stmt->execute(['id_academia' => $idAcademia]);

        $avaliacoes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $avaliacao = new AvaliacaoDTO();
            $avaliacao->setIdAvaliacao($linha['id_avaliacao']);
            $avaliacao->setIdUsuarioProfessor($linha['id_usuario_professor']);
            $avaliacao->setIdUsuarioAluno($linha['id_usuario_aluno']);
            $avaliacao->setIdModalidade($linha['id_modalidade']);
            $avaliacao->setDataAvaliacao($linha['data_avaliacao']);
            $avaliacao->setDataPrevista($linha['data_prevista']);
            $avaliacao->setHabilidadesMelhorar($linha['habilidades_melhorar']);
            $avaliacao->setObservacoes($linha['observacoes']);
            $avaliacao->setNomeAluno($linha['nome_aluno']);
            $avaliacao->setNomeProfessor($linha['nome_professor']);
            $avaliacao->setNomeModalidade($linha['nome_modalidade']);
            $avaliacoes[] = $avaliacao;
        }

        return $avaliacoes;
    }

    public function listarAlunosPorAcademia(int $idAcademia): array
    {
        $stmt = $this->conexao->prepare("
            SELECT DISTINCT
                aluno.id_usuario,
                aluno.nome,
                modalidade.id_modalidade,
                modalidade.nome AS nome_modalidade
            FROM usuario aluno
            INNER JOIN plano ON plano.id_usuario_aluno = aluno.id_usuario
                AND plano.status = 'ATIVO'
                AND plano.id_modalidade IS NOT NULL
            INNER JOIN modalidade ON modalidade.id_modalidade = plano.id_modalidade
            WHERE aluno.id_academia = :id_academia
              AND aluno.perfil_id = 4
              AND aluno.status = 'ATIVO'
            ORDER BY aluno.nome ASC, modalidade.nome ASC
        ");
        $stmt->execute(['id_academia' => $idAcademia]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // MÉTODOS ACRESCENTADOS PARA O PROFESSOR
    // ==========================================
    public function listarPorProfessor(int $idProfessor): array
    {
        $stmt = $this->conexao->prepare("
            SELECT DISTINCT
                avaliacao.*,
                aluno.nome AS nome_aluno,
                professor.nome AS nome_professor,
                modalidade.nome AS nome_modalidade
            FROM avaliacao
            INNER JOIN usuario aluno ON aluno.id_usuario = avaliacao.id_usuario_aluno
            INNER JOIN usuario professor ON professor.id_usuario = avaliacao.id_usuario_professor
            LEFT JOIN modalidade ON modalidade.id_modalidade = avaliacao.id_modalidade
            WHERE avaliacao.id_usuario_professor = :id_professor
            ORDER BY avaliacao.data_avaliacao DESC
        ");
        $stmt->execute(['id_professor' => $idProfessor]);

        $avaliacoes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $avaliacao = new AvaliacaoDTO();
            $avaliacao->setIdAvaliacao($linha['id_avaliacao']);
            $avaliacao->setIdUsuarioProfessor($linha['id_usuario_professor']);
            $avaliacao->setIdUsuarioAluno($linha['id_usuario_aluno']);
            $avaliacao->setIdModalidade($linha['id_modalidade']);
            $avaliacao->setDataAvaliacao($linha['data_avaliacao']);
            $avaliacao->setDataPrevista($linha['data_prevista']);
            $avaliacao->setHabilidadesMelhorar($linha['habilidades_melhorar']);
            $avaliacao->setObservacoes($linha['observacoes']);
            $avaliacao->setNomeAluno($linha['nome_aluno']);
            $avaliacao->setNomeProfessor($linha['nome_professor']);
            $avaliacao->setNomeModalidade($linha['nome_modalidade']);
            $avaliacoes[] = $avaliacao;
        }

        return $avaliacoes;
    }

    public function listarAlunosPorProfessor(int $idProfessor): array
    {
        $stmt = $this->conexao->prepare("
            SELECT DISTINCT
                aluno.id_usuario,
                aluno.nome,
                modalidade.id_modalidade,
                modalidade.nome AS nome_modalidade
            FROM usuario aluno
            INNER JOIN plano ON plano.id_usuario_aluno = aluno.id_usuario
                AND plano.status = 'ATIVO'
                AND plano.id_modalidade IS NOT NULL
            INNER JOIN modalidade ON modalidade.id_modalidade = plano.id_modalidade
            INNER JOIN turma t ON t.id_modalidade = modalidade.id_modalidade
            WHERE t.id_usuario_professor = :id_professor
              AND aluno.perfil_id = 4
              AND aluno.status = 'ATIVO'
            ORDER BY aluno.nome ASC, modalidade.nome ASC
        ");
        $stmt->execute(['id_professor' => $idProfessor]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}