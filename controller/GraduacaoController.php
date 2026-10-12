<?php
// controller/GraduacaoController.php

require_once __DIR__ . '/../model/dao/GraduacaoDAO.php';

class GraduacaoController
{
	private GraduacaoDAO $dao;

	public function __construct()
	{
		$this->dao = new GraduacaoDAO();
	}

	/** @return GraduacaoDTO[] */
	public function listarHistoricoAluno(int $idAluno): array
	{
		if ($idAluno <= 0) {
			return [];
		}

		return $this->dao->listarHistoricoAluno($idAluno);
	}

	public function cadastrar(int $idAluno, int $idProfessor, int $idModalidade, string $dataGraduacao, string $faixa, ?string $grau): array
	{
		if ($idAluno <= 0 || $idProfessor <= 0 || $idModalidade <= 0 || empty($dataGraduacao) || empty($faixa)) {
			return ['status' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.'];
		}

		$sucesso = $this->dao->cadastrar($idAluno, $idProfessor, $idModalidade, $dataGraduacao, $faixa, $grau);
		if ($sucesso) {
			return ['status' => true, 'mensagem' => 'Graduação registada com sucesso!'];
		}
		return ['status' => false, 'mensagem' => 'Erro ao registar a graduação. Tente novamente.'];
	}

	public function listarAlunosPorPerfilUsuario(int $perfilId, int $idAcademia, int $idUsuario): array
	{
		if ($perfilId == 2) {
			return $this->dao->listarAlunosPorAcademia($idAcademia);
		} elseif ($perfilId == 3) {
			return $this->dao->listarAlunosPorProfessor($idUsuario);
		}
		return [];
	}

	public function listarPorPerfilUsuario(int $perfilId, int $idAcademia, int $idUsuario): array
	{
		if ($perfilId == 2) {
			return $this->dao->listarPorAcademia($idAcademia);
		} elseif ($perfilId == 3) {
			return $this->dao->listarPorProfessor($idUsuario);
		}
		return [];
	}
}