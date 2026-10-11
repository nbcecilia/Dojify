<?php
// model/dto/AcademiaDTO.php
class AcademiaDTO {
    private ?int $idAcademia = null;
    private string $nome;
    private string $documento;
    private string $cep;
    private string $endereco;
    private string $cidade;
    private string $estado;
    private string $telefone;
    private string $email;
    private ?string $dataCadastro = null;

    public function getIdAcademia(): ?int { return $this->idAcademia; }
    public function setIdAcademia(?int $id): void { $this->idAcademia = $id; }

    public function getNome(): string { return $this->nome; }
    public function setNome(string $nome): void { $this->nome = $nome; }

    public function getDocumento(): string { return $this->documento; }
    public function setDocumento(string $documento): void { $this->documento = $documento; }

    public function getCep(): string { return $this->cep; }
    public function setCep(string $cep): void { $this->cep = $cep; }

    public function getEndereco(): string { return $this->endereco; }
    public function setEndereco(string $endereco): void { $this->endereco = $endereco; }

    public function getCidade(): string { return $this->cidade; }
    public function setCidade(string $cidade): void { $this->cidade = $cidade; }

    public function getEstado(): string { return $this->estado; }
    public function setEstado(string $estado): void { $this->estado = $estado; }

    public function getTelefone(): string { return $this->telefone; }
    public function setTelefone(string $telefone): void { $this->telefone = $telefone; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getDataCadastro(): ?string { return $this->dataCadastro; }
    public function setDataCadastro(?string $data): void { $this->dataCadastro = $data; }
}