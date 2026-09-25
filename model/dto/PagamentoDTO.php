<?php
// model/dto/PagamentoDTO.php

class PagamentoDTO {
    private ?int $idPagamento = null;
    private int $idPlanoMatricula;
    private float $valor;
    private string $dataVencimento;
    private ?string $dataPagamento = null;
    private ?string $formaPagamento = null;
    private string $status = 'PENDENTE';
    private ?string $observacao = null;

    public function getIdPagamento(): ?int { return $this->idPagamento; }
    public function setIdPagamento(?int $id): void { $this->idPagamento = $id; }

    public function getIdPlanoMatricula(): int { return $this->idPlanoMatricula; }
    public function setIdPlanoMatricula(int $id): void { $this->idPlanoMatricula = $id; }

    public function getValor(): float { return $this->valor; }
    public function setValor(float $valor): void { $this->valor = $valor; }

    public function getDataVencimento(): string { return $this->dataVencimento; }
    public function setDataVencimento(string $data): void { $this->dataVencimento = $data; }

    public function getDataPagamento(): ?string { return $this->dataPagamento; }
    public function setDataPagamento(?string $data): void { $this->dataPagamento = $data; }

    public function getFormaPagamento(): ?string { return $this->formaPagamento; }
    public function setFormaPagamento(?string $forma): void { $this->formaPagamento = $forma; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getObservacao(): ?string { return $this->observacao; }
    public function setObservacao(?string $obs): void { $this->observacao = $obs; }
}
?>