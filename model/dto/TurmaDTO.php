<?php
// model/dto/TurmaDTO.php

class TurmaDTO {

    private $id_turma;
    private $id_usuario_professor;
    private $id_modalidade;
    private $nome;
    private $capacidade;
    private $nivel;
    private $status;


    // Getters e Setters

    public function getIdTurma() {
        return $this->id_turma;
    }

    public function setIdTurma($id_turma) {
        $this->id_turma = $id_turma;
    }


    public function getIdUsuarioProfessor() {
        return $this->id_usuario_professor;
    }

    public function setIdUsuarioProfessor($id_usuario_professor) {
        $this->id_usuario_professor = $id_usuario_professor;
    }


    public function getIdModalidade() {
        return $this->id_modalidade;
    }

    public function setIdModalidade($id_modalidade) {
        $this->id_modalidade = $id_modalidade;
    }


    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }


    public function getCapacidade() {
        return $this->capacidade;
    }

    public function setCapacidade($capacidade) {
        $this->capacidade = $capacidade;
    }


    public function getNivel() {
        return $this->nivel;
    }

    public function setNivel($nivel) {
        $this->nivel = $nivel;
    }


    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
}