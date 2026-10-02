<?php
// controller/Historico_FrequenciaController.php

require_once __DIR__ . '/../model/dao/Historico_FrequenciaDAO.php';

class Historico_frequenciaController {
    private Historico_frequenciaDAO $dao;

    public function __construct() {
        $this->dao = new Historico_frequenciaDAO();
    }

    public function listarHistoricoPorAluno(int $idAluno): array {
        return $this->dao->listarPorAluno($idAluno);
    }

    public function obterResumoFrequencia(array $historico): array {
        $presencas = 0;
        $ausencias = 0;

        foreach ($historico as $registro) {
            if (!$registro instanceof Historico_frequenciaDTO) {
                throw new InvalidArgumentException('O histórico contém um registro inválido.');
            }

            if ($registro->getStatusPresenca() === 1) {
                $presencas++;
            } elseif ($registro->getStatusPresenca() === 0) {
                $ausencias++;
            }
        }

        $aulasComRegistro = $presencas + $ausencias;

        return [
            'aulas_com_registro' => $aulasComRegistro,
            'presencas' => $presencas,
            'ausencias' => $ausencias,
            'frequencia' => $aulasComRegistro > 0
                ? ($presencas / $aulasComRegistro) * 100
                : 0.0
        ];
    }
}
