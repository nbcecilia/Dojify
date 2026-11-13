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

    public static function fromArray(array $dados): self {
        $valor = $dados['valor'] ?? null;
        if (!is_scalar($valor) || !is_numeric($valor) || !is_finite((float)$valor)
            || (float)$valor <= 0 || (float)$valor > 99999999.99) {
            throw new InvalidArgumentException('Informe um valor válido para o pagamento.');
        }

        $dataVencimento = $dados['data_vencimento'] ?? '';
        if (!is_string($dataVencimento)) {
            throw new InvalidArgumentException('Informe uma data de vencimento válida.');
        }
        $dataVencimento = trim($dataVencimento);
        if (!self::dataValida($dataVencimento)) {
            throw new InvalidArgumentException('Informe uma data de vencimento válida.');
        }

        $dataPagamento = $dados['data_pagamento'] ?? '';
        if (!is_string($dataPagamento)) {
            throw new InvalidArgumentException('Informe uma data de pagamento válida.');
        }
        $dataPagamento = trim($dataPagamento);
        if ($dataPagamento !== '' && !self::dataValida($dataPagamento)) {
            throw new InvalidArgumentException('Informe uma data de pagamento válida.');
        }

        $formaPagamento = $dados['forma_pagamento'] ?? '';
        if (!is_string($formaPagamento)) {
            throw new InvalidArgumentException('Selecione uma forma de pagamento válida.');
        }
        $formaPagamento = strtoupper(trim($formaPagamento));
        $formasPermitidas = ['DINHEIRO', 'PIX', 'CARTAO', 'OUTRO'];
        if ($formaPagamento !== '' && !in_array($formaPagamento, $formasPermitidas, true)) {
            throw new InvalidArgumentException('Selecione uma forma de pagamento válida.');
        }

        $pagamento = new self();
        $pagamento->setValor((float)$valor);
        $pagamento->setDataVencimento($dataVencimento);
        $pagamento->setDataPagamento($dataPagamento !== '' ? $dataPagamento : null);
        $pagamento->setFormaPagamento($formaPagamento !== '' ? $formaPagamento : null);

        return $pagamento;
    }

    private static function dataValida(string $data): bool {
        $dataObj = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
        $erros = DateTimeImmutable::getLastErrors();

        return $dataObj !== false
            && $dataObj->format('Y-m-d') === $data
            && ($erros === false || ($erros['warning_count'] === 0 && $erros['error_count'] === 0));
    }

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