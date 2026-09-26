<?php
// model/dto/HorarioTurmaDTO.php

class HorarioTurmaDTO {
    private ?int $id_horario = null;
    private int $id_turma;
    private string $dia_semana;
    private string $hora_inicio;
    private string $hora_fim;

    // Getters e Setters
    public function getIdHorario(): ?int {
        return $this->id_horario;
    }
    public function setIdHorario(?int $id_horario): void {
        $this->id_horario = $id_horario;
    }

    public function getIdTurma(): int {
        return $this->id_turma;
    }
    public function setIdTurma(int $id_turma): void {
        $this->id_turma = $id_turma;
    }

    public function getDiaSemana(): string {
        return $this->dia_semana;
    }
    public function setDiaSemana(string $dia_semana): void {
        $this->dia_semana = $dia_semana;
    }

    public function getHoraInicio(): string {
        return $this->hora_inicio;
    }
    public function setHoraInicio(string $hora_inicio): void {
        $this->hora_inicio = $hora_inicio;
    }

    public function getHoraFim(): string {
        return $this->hora_fim;
    }
    public function setHoraFim(string $hora_fim): void {
        $this->hora_fim = $hora_fim;
    }
}