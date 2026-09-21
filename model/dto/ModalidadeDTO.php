<?php

class ModalidadeDTO {
    private ?int $idModalidade = null;
    private int $idAcademia;
    private string $nome;
    private ?string $descricao = null;

    public function getIdModalidade(): ?int { return $this->idModalidade; }
    public function setIdModalidade(?int $id): void { $this->idModalidade = $id; }

    public function getIdAcademia(): int { return $this->idAcademia; }
    public function setIdAcademia(int $id): void { $this->idAcademia = $id; }

    public function getNome(): string { return $this->nome; }
    public function setNome(string $nome): void { $this->nome = $nome; }

    public function getDescricao(): ?string { return $this->descricao; }
    public function setDescricao(?string $descricao): void { $this->descricao = $descricao; }
}