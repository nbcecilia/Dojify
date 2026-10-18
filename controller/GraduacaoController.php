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

		if (!$this->dataValida($dataGraduacao)) {
			return ['status' => false, 'mensagem' => 'Informe uma data de graduação válida.'];
		}

		$sucesso = $this->dao->cadastrar($idAluno, $idProfessor, $idModalidade, $dataGraduacao, $faixa, $grau);
		if ($sucesso) {
			return ['status' => true, 'mensagem' => 'Graduação registada com sucesso!'];
		}
		return ['status' => false, 'mensagem' => 'Erro ao registar a graduação. Tente novamente.'];
	}

	public function atualizar(int $idGraduacao, int $idProfessor, string $dataGraduacao, string $faixa, ?string $grau, int $perfilId = 3): array
	{
		if ($idGraduacao <= 0 || $idProfessor <= 0 || $dataGraduacao === '' || trim($faixa) === '') {
			return ['status' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.'];
		}

		if (!$this->dataValida($dataGraduacao)) {
			return ['status' => false, 'mensagem' => 'Informe uma data de graduação válida.'];
		}

		if (mb_strlen(trim($faixa), 'UTF-8') > 50 || ($grau !== null && mb_strlen(trim($grau), 'UTF-8') > 20)) {
			return ['status' => false, 'mensagem' => 'A faixa ou o grau excede o tamanho permitido.'];
		}

		if (!$this->dao->atualizar($idGraduacao, $idProfessor, $dataGraduacao, trim($faixa), $grau !== null ? trim($grau) : null, $perfilId)) {
			return ['status' => false, 'mensagem' => 'Não foi possível atualizar a graduação.'];
		}

		return ['status' => true, 'mensagem' => 'Graduação atualizada com sucesso!'];
	}

	private function dataValida(string $data): bool
	{
		$dataObj = DateTime::createFromFormat('!Y-m-d', $data);
		$errosData = DateTime::getLastErrors();

		return $dataObj !== false
			&& $dataObj->format('Y-m-d') === $data
			&& ($errosData === false
				|| ($errosData['warning_count'] === 0 && $errosData['error_count'] === 0));
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