<?php
// controller/PresencaController.php
session_start();

require_once __DIR__ . '/../model/dao/PresencaDAO.php';

class PresencaController {
    private PresencaDAO $dao;

    public function __construct() {
        $this->dao = new PresencaDAO();
    }

    public function processarRequisicao(): void {
        // Validação de Acesso: Apenas Gerente (Perfil 2) ou Professor (Perfil 3) podem gerir presenças
        if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['perfil_id'], [2, 3])) {
            header('Location: ../view/login.php?erro=acesso_negado');
            exit;
        }

        $acao = $_REQUEST['acao'] ?? '';

        switch ($acao) {
            case 'form_chamada':
                $this->formChamada();
                break;
            case 'salvar_chamada':
                $this->salvarChamada();
                break;
            default:
                header('Location: ../view/login.php');
                exit;
        }
    }

    /*** Carrega a lista de alunos agendados para a turma e data selecionada
     */
    private function formChamada(): void {
        $idTurma = filter_input(INPUT_GET, 'id_turma', FILTER_VALIDATE_INT) ?? 1;
        $dataAula = $_GET['data'] ?? date('Y-m-d');

        // Busca os alunos através do DAO adaptado ao seu SQL
        $alunosChamada = $this->dao->listarAlunosParaChamada($idTurma, $dataAula);

        // Exemplo de inclusão da view (ajuste o caminho conforme sua estrutura)
        // include __DIR__ . '/../view/professor/registar_frequencia.php';
        
    }

    /**
     * Processa e grava o envio da chamada em lote (presente/ausente)
     */
    private function salvarChamada(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/professor/chamada.php');
            exit;
        }

        try {
            $dataAula = $_POST['data_aula'] ?? date('Y-m-d');
            $presencas = $_POST['status_presenca'] ?? []; // Array enviado pelo formulário [id_agendamento => status]

            $sucesso = $this->dao->salvarChamada($dataAula, $presencas);

            if ($sucesso) {
                header('Location: ../view/professor/chamada.php?sucesso=salvo');
                exit;
            } else {
                header('Location: ../view/professor/chamada.php?erro=falha');
                exit;
            }
        } catch (Exception $e) {
            header('Location: ../view/professor/chamada.php?erro=excecao');
            exit;
        }
    }
}

// Executa o controlador se houver uma ação requisitada
if (isset($_REQUEST['acao'])) {
    $controller = new PresencaController();
    $controller->processarRequisicao();
}