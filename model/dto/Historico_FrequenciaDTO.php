<?php
// model/dto/Historico_FrequenciaDTO.php

class Historico_frequenciaDTO {
    private int $idAgendamento;
    private string $nomeTurma;
    private string $dataAgendamento;
    private ?int $statusPresenca;

    public function __construct(
        int $idAgendamento,
        string $nomeTurma,
        string $dataAgendamento,
        ?int $statusPresenca
    ) {
        $this->idAgendamento = $idAgendamento;
        $this->nomeTurma = $nomeTurma;
        $this->dataAgendamento = $dataAgendamento;
        $this->statusPresenca = $statusPresenca;
    }

    public function getIdAgendamento(): int {
        return $this->idAgendamento;
    }

    public function getNomeTurma(): string {
        return $this->nomeTurma;
    }

    public function getDataAgendamento(): string {
        return $this->dataAgendamento;
    }

    public function getStatusPresenca(): ?int {
        return $this->statusPresenca;
    }
}
