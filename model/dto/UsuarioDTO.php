<?php
//model/dto/UsuarioDTO.php
class UsuarioDTO {
    private ?int $idUsuario = null;
    private ?int $idAcademia = null;
    private int $perfilId;
    private string $nome;
    private string $cpf;
    private string $dataNascimento;
    private string $telefone;
    private string $email;
    private ?string $especialidade = null;
    private ?string $dataAdmissao = null;
    private ?string $responsavel = null;
    private ?string $observacao = null;
    private ?string $dataMatricula = null;
    private string $status = 'ATIVO';

    // Getters e Setters
    public function getIdUsuario(): ?int { return $this->idUsuario; }
    public function setIdUsuario(?int $id): void { $this->idUsuario = $id; }

    public function getIdAcademia(): ?int { return $this->idAcademia; }
    public function setIdAcademia(?int $id): void { $this->idAcademia = $id; }

    public function getPerfilId(): int { return $this->perfilId; }
    public function setPerfilId(int $perfilId): void { $this->perfilId = $perfilId; }

    public function getNome(): string { return $this->nome; }
    public function setNome(string $nome): void { $this->nome = $nome; }

    public function getCpf(): string { return $this->cpf; }
    public function setCpf(string $cpf): void { $this->cpf = $cpf; }

    public function getDataNascimento(): string { return $this->dataNascimento; }
    public function setDataNascimento(string $dataNascimento): void { $this->dataNascimento = $dataNascimento; }

    public function getTelefone(): string { return $this->telefone; }
    public function setTelefone(string $telefone): void { $this->telefone = $telefone; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getEspecialidade(): ?string { return $this->especialidade; }
    public function setEspecialidade(?string $especialidade): void { $this->especialidade = $especialidade; }

    public function getDataAdmissao(): ?string { return $this->dataAdmissao; }
    public function setDataAdmissao(?string $dataAdmissao): void { $this->dataAdmissao = $dataAdmissao; }

    public function getResponsavel(): ?string { return $this->responsavel; }
    public function setResponsavel(?string $responsavel): void { $this->responsavel = $responsavel; }

    public function getObservacao(): ?string { return $this->observacao; }
    public function setObservacao(?string $observacao): void { $this->observacao = $observacao; }

    public function getDataMatricula(): ?string { return $this->dataMatricula; }
    public function setDataMatricula(?string $dataMatricula): void { $this->dataMatricula = $dataMatricula; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
}