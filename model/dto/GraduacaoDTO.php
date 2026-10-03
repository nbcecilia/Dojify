<?php
// model/dto/GraduacaoDTO.php

class GraduacaoDTO
{
	private int $idGraduacao;
	private string $dataGraduacao;
	private string $faixa;
	private ?string $grau;
	private string $nomeModalidade;
	private string $nomeProfessor;

	public static function fromArray(array $dados): self
	{
		$graduacao = new self();
		$graduacao->idGraduacao = (int) $dados['id_graduacao'];
		$graduacao->dataGraduacao = (string) $dados['data_graduacao'];
		$graduacao->faixa = (string) $dados['faixa'];
		$graduacao->grau = $dados['grau'] === null ? null : (string) $dados['grau'];
		$graduacao->nomeModalidade = (string) $dados['nome_modalidade'];
		$graduacao->nomeProfessor = (string) $dados['nome_professor'];

		return $graduacao;
	}

	public function getIdGraduacao(): int
	{
		return $this->idGraduacao;
	}

	public function getDataGraduacao(): string
	{
		return $this->dataGraduacao;
	}

	public function getFaixa(): string
	{
		return $this->faixa;
	}

	public function getGrau(): ?string
	{
		return $this->grau;
	}

	public function getNomeModalidade(): string
	{
		return $this->nomeModalidade;
	}

	public function getNomeProfessor(): string
	{
		return $this->nomeProfessor;
	}
}