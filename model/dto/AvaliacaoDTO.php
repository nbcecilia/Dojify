<?php
// model/dto/AvaliacaoDTO.php

class AvaliacaoDTO {
    private $id_avaliacao;
    private $id_usuario_professor;
    private $id_usuario_aluno;
    private $data_avaliacao;
    private $habilidades_melhorar;
    private $observacoes;
    
    // Campos auxiliares para joins
    private $nome_aluno;
    private $nome_professor;

    // Getters e Setters
    public function getIdAvaliacao() { return $this->id_avaliacao; }
    public function setIdAvaliacao($id_avaliacao) { $this->id_avaliacao = $id_avaliacao; }

    public function getIdUsuarioProfessor() { return $this->id_usuario_professor; }
    public function setIdUsuarioProfessor($id_usuario_professor) { $this->id_usuario_professor = $id_usuario_professor; }

    public function getIdUsuarioAluno() { return $this->id_usuario_aluno; }
    public function setIdUsuarioAluno($id_usuario_aluno) { $this->id_usuario_aluno = $id_usuario_aluno; }

    public function getDataAvaliacao() { return $this->data_avaliacao; }
    public function setDataAvaliacao($data_avaliacao) { $this->data_avaliacao = $data_avaliacao; }

    public function getHabilidadesMelhorar() { return $this->habilidades_melhorar; }
    public function setHabilidadesMelhorar($habilidades_melhorar) { $this->habilidades_melhorar = $habilidades_melhorar; }

    public function getObservacoes() { return $this->observacoes; }
    public function setObservacoes($observacoes) { $this->observacoes = $observacoes; }

    public function getNomeAluno() { return $this->nome_aluno; }
    public function setNomeAluno($nome_aluno) { $this->nome_aluno = $nome_aluno; }

    public function getNomeProfessor() { return $this->nome_professor; }
    public function setNomeProfessor($nome_professor) { $this->nome_professor = $nome_professor; }
}