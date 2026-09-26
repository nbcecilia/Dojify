<?php
// controller/HorarioTurmaController.php

require_once __DIR__ . '/../model/dao/HorarioTurmaDAO.php';
require_once __DIR__ . '/../model/dto/HorarioTurmaDTO.php';

class HorarioTurmaController {
    private HorarioTurmaDAO $dao;

    public function __construct() {
        $this->dao = new HorarioTurmaDAO();
    }

    public function salvar(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idTurma = filter_input(INPUT_POST, 'id_turma', FILTER_SANITIZE_NUMBER_INT);
            $diaSemana = filter_input(INPUT_POST, 'dia_semana', FILTER_DEFAULT);
            $horaInicio = filter_input(INPUT_POST, 'hora_inicio', FILTER_DEFAULT);
            $horaFim = filter_input(INPUT_POST, 'hora_fim', FILTER_DEFAULT);

            if ($idTurma && $diaSemana && $horaInicio && $horaFim) {
                $dto = new HorarioTurmaDTO();
                $dto->setIdTurma((int)$idTurma);
                $dto->setDiaSemana($diaSemana);
                $dto->setHoraInicio($horaInicio);
                $dto->setHoraFim($horaFim);

                if ($this->dao->cadastrar($dto)) {
                    header("Location: ../view/gerente/gerenciar_horarios_turma.php?id=" . $idTurma . "&sucesso=1");
                    exit;
                }
            }
            header("Location: ../view/gerente/gerenciar_horarios_turma.php?id=" . $idTurma . "&erro=1");
            exit;
        }
    }

    public function excluir(): void {
        $idHorario = filter_input(INPUT_GET, 'id_horario', FILTER_SANITIZE_NUMBER_INT);
        $idTurma = filter_input(INPUT_GET, 'id_turma', FILTER_SANITIZE_NUMBER_INT);

        if ($idHorario) {
            $this->dao->excluir((int)$idHorario);
        }
        header("Location: ../view/gerente/gerenciar_horarios_turma.php?id=" . $idTurma);
        exit;
    }
}

// Roteamento simples baseado em parâmetros GET/POST
$controller = new HorarioTurmaController();
$acao = $_GET['acao'] ?? '';

if ($acao === 'salvar') {
    $controller->salvar();
} elseif ($acao === 'excluir') {
    $controller->excluir();
}