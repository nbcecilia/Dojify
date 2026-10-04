<?php
// controller/AvaliacaoController.php

require_once __DIR__ . '/../model/dao/AvaliacaoDAO.php';
require_once __DIR__ . '/../model/dto/AvaliacaoDTO.php';

class AvaliacaoController {
    private $avaliacaoDAO;

    public function __construct() {
        $this->avaliacaoDAO = new AvaliacaoDAO();
    }

    public function cadastrar($id_professor, $id_aluno, $data, $habilidades, $observacoes) {
        if (empty($id_aluno) || empty($data) || empty($habilidades)) {
            return ['status' => false, 'mensagem' => 'Por favor, preencha todos os campos obrigatórios.'];
        }

        $avaliacao = new AvaliacaoDTO();
        $avaliacao->setIdUsuarioProfessor($id_professor);
        $avaliacao->setIdUsuarioAluno($id_aluno);
        $avaliacao->setDataAvaliacao($data);
        $avaliacao->setHabilidadesMelhorar(trim($habilidades));
        $avaliacao->setObservacoes(trim($observacoes));

        $resultado = $this->avaliacaoDAO->cadastrar($avaliacao);

        if ($resultado) {
            return ['status' => true, 'mensagem' => 'Avaliação registada com sucesso!'];
        } else {
            return ['status' => false, 'mensagem' => 'Erro ao registar a avaliação no banco de dados.'];
        }
    }

    public function listarPorAcademia($id_academia) {
        return $this->avaliacaoDAO->listarPorAcademia($id_academia);
    }

    public function listarAlunosPorAcademia($id_academia) {
        return $this->avaliacaoDAO->listarAlunosPorAcademia($id_academia);
    }
}