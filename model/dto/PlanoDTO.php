<?php
//model/dto/PlanoDTO.php
class PlanoDTO {
    private $id_plano;
    private $id_usuario_aluno;
    private $nome_plano;
    private $valor;
    private $data_inicio;
    private $data_fim;
    private $status;

    // Getters e Setters
    public function getIdPlano() { return $this->id_plano; }
    public function setIdPlano($id_plano) { $this->id_plano = $id_plano; }

    public function getIdUsuarioAluno() { return $this->id_usuario_aluno; }
    public function setIdUsuarioAluno($id_usuario_aluno) { $this->id_usuario_aluno = $id_usuario_aluno; }

    public function getNomePlano() { return $this->nome_plano; }
    public function setNomePlano($nome_plano) { $this->nome_plano = $nome_plano; }

    public function getValor() { return $this->valor; }
    public function setValor($valor) { $this->valor = $valor; }

    public function getDataInicio() { return $this->data_inicio; }
    public function setDataInicio($data_inicio) { $this->data_inicio = $data_inicio; }

    public function getDataFim() { return $this->data_fim; }
    public function setDataFim($data_fim) { $this->data_fim = $data_fim; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }
}