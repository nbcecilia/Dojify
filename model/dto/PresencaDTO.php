<?php
// model/dto/PresencaDTO.php

class PresencaDTO {
    private ?int $idPresenca = null;
    private int $idAgendamento;
    private string $data;
    private int $status; // 1 = Presente, 0 = Ausente

    public function getIdPresenca(): ?int { return $this->idPresenca; }
    public function setIdPresenca(?int $idPresenca): void { $this->idPresenca = $idPresenca; }

    public function getIdAgendamento(): int { return $this->idAgendamento; }
    public function setIdAgendamento(int $idAgendamento): void { $this->idAgendamento = $idAgendamento; }

    public function getData(): string { return $this->data; }
    public function setData(string $data): void { $this->data = $data; }

    public function getStatus(): int { return $this->status; }
    public function setStatus(int $status): void { $this->status = $status; }
}