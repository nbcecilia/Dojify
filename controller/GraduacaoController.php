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
}