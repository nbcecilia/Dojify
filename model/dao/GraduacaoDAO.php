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
}