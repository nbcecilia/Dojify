<?php
// model/dto/AgendamentoDTO.php

class AgendamentoDTO
{
    private int $idAgendamento;
    private string $nomeTurma;
    private string $dataAgendamento;
    private string $status;

    public function __construct(int $idAgendamento, string $nomeTurma, string $dataAgendamento, string $status)
    {
        $this->idAgendamento = $idAgendamento;
        $this->nomeTurma = $nomeTurma;
        $this->dataAgendamento = $dataAgendamento;
        $this->status = $status;
    }

    public function getIdAgendamento(): int
    {
        return $this->idAgendamento;
    }

    public function getNomeTurma(): string
    {
        return $this->nomeTurma;
    }

    public function getDataAgendamento(): string
    {
        return $this->dataAgendamento;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
?>