<?php
// model/dao/GraduacaoDAO.php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/GraduacaoDTO.php';

class GraduacaoDAO
{
	private PDO $conexao;

	public function __construct()
	{
		$this->conexao = \Conexao::getConexao();
	}

	/** @return \GraduacaoDTO[] */
	public function listarHistoricoAluno(int $idAluno): array
	{
		$sql = "SELECT
					g.id_graduacao,
					g.data_graduacao,
					g.faixa,
					g.grau,
					m.nome AS nome_modalidade,
					p.nome AS nome_professor
				FROM graduacao g
				INNER JOIN modalidade m ON m.id_modalidade = g.id_modalidade
				INNER JOIN usuario p ON p.id_usuario = g.id_usuario_professor
				WHERE g.id_usuario_aluno = :id_aluno
				ORDER BY g.data_graduacao DESC, g.id_graduacao DESC";

		$stmt = $this->conexao->prepare($sql);
		$stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
		$stmt->execute();

		$historico = [];
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $registro) {
			$historico[] = GraduacaoDTO::fromArray($registro);
		}

		return $historico;
	}

	/**
	 * Cadastra uma nova graduação
	 */
	public function cadastrar(int $idAluno, int $idProfessor, int $idModalidade, string $dataGraduacao, string $faixa, ?string $grau): bool
	{
		$sql = "INSERT INTO graduacao (id_usuario_aluno, id_usuario_professor, id_modalidade, data_graduacao, faixa, grau) 
				VALUES (:id_aluno, :id_professor, :id_modalidade, :data_graduacao, :faixa, :grau)";
		$stmt = $this->conexao->prepare($sql);
		$stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
		$stmt->bindValue(':id_professor', $idProfessor, PDO::PARAM_INT);
		$stmt->bindValue(':id_modalidade', $idModalidade, PDO::PARAM_INT);
		$stmt->bindValue(':data_graduacao', $dataGraduacao);
		$stmt->bindValue(':faixa', $faixa);
		$stmt->bindValue(':grau', $grau !== '' ? $grau : null);
		return $stmt->execute();
	}

	public function atualizar(int $idGraduacao, int $idProfessor, string $dataGraduacao, string $faixa, ?string $grau): bool
	{
		$stmt = $this->conexao->prepare("
			UPDATE graduacao
			SET data_graduacao = :data_graduacao,
				faixa = :faixa,
				grau = :grau
			WHERE id_graduacao = :id_graduacao
			  AND id_usuario_professor = :id_professor
		");
		$stmt->execute([
			'data_graduacao' => $dataGraduacao,
			'faixa' => $faixa,
			'grau' => $grau !== '' ? $grau : null,
			'id_graduacao' => $idGraduacao,
			'id_professor' => $idProfessor,
		]);

		if ($stmt->rowCount() > 0) {
			return true;
		}

		$stmt = $this->conexao->prepare("
			SELECT 1
			FROM graduacao
			WHERE id_graduacao = :id_graduacao
			  AND id_usuario_professor = :id_professor
		");
		$stmt->execute([
			'id_graduacao' => $idGraduacao,
			'id_professor' => $idProfessor,
		]);

		return (bool)$stmt->fetchColumn();
	}

	/**
	 * Lista alunos da academia para o Gerente
	 */
	public function listarAlunosPorAcademia(int $idAcademia): array
	{
		$sql = "SELECT u.id_usuario, u.nome, m.id_modalidade, m.nome AS nome_modalidade
				FROM usuario u
				INNER JOIN plano pl ON pl.id_usuario_aluno = u.id_usuario AND pl.status = 'ATIVO'
				INNER JOIN modalidade m ON m.id_modalidade = pl.id_modalidade
				WHERE u.id_academia = :id_academia AND u.perfil_id = 4 AND u.status = 'ATIVO'
				GROUP BY u.id_usuario, m.id_modalidade
				ORDER BY u.nome ASC";
		$stmt = $this->conexao->prepare($sql);
		$stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Lista alunos apenas das modalidades lecionadas pelo professor
	 */
	public function listarAlunosPorProfessor(int $idProfessor): array
	{
		$sql = "SELECT DISTINCT u.id_usuario, u.nome, m.id_modalidade, m.nome AS nome_modalidade
				FROM usuario u
				INNER JOIN plano pl ON pl.id_usuario_aluno = u.id_usuario AND pl.status = 'ATIVO'
				INNER JOIN modalidade m ON m.id_modalidade = pl.id_modalidade
				INNER JOIN turma t ON t.id_modalidade = m.id_modalidade
				WHERE t.id_usuario_professor = :id_professor AND u.perfil_id = 4 AND u.status = 'ATIVO'
				ORDER BY u.nome ASC";
		$stmt = $this->conexao->prepare($sql);
		$stmt->bindValue(':id_professor', $idProfessor, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Lista todas as graduações da academia (para o Gerente)
	 */
	public function listarPorAcademia(int $idAcademia): array
	{
		$sql = "SELECT g.id_graduacao, g.id_usuario_professor, g.data_graduacao, g.faixa, g.grau,
					   u.nome AS nome_aluno, m.nome AS nome_modalidade, p.nome AS nome_professor
				FROM graduacao g
				INNER JOIN usuario u ON u.id_usuario = g.id_usuario_aluno
				INNER JOIN modalidade m ON m.id_modalidade = g.id_modalidade
				INNER JOIN usuario p ON p.id_usuario = g.id_usuario_professor
				WHERE u.id_academia = :id_academia
				ORDER BY g.data_graduacao DESC";
		$stmt = $this->conexao->prepare($sql);
		$stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Lista as graduações das modalidades lecionadas pelo professor
	 */
	public function listarPorProfessor(int $idProfessor): array
	{
		$sql = "SELECT DISTINCT g.id_graduacao, g.id_usuario_professor, g.data_graduacao, g.faixa, g.grau,
					   u.nome AS nome_aluno, m.nome AS nome_modalidade, p.nome AS nome_professor
				FROM graduacao g
				INNER JOIN usuario u ON u.id_usuario = g.id_usuario_aluno
				INNER JOIN modalidade m ON m.id_modalidade = g.id_modalidade
				INNER JOIN usuario p ON p.id_usuario = g.id_usuario_professor
				INNER JOIN turma t ON t.id_modalidade = g.id_modalidade
				WHERE t.id_usuario_professor = :id_professor
				ORDER BY g.data_graduacao DESC";
		$stmt = $this->conexao->prepare($sql);
		$stmt->bindValue(':id_professor', $idProfessor, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}