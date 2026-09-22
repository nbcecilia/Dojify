```php
<?php
// controller/TurmaController.php

session_start();

require_once __DIR__ . '/../model/dao/TurmaDAO.php';
require_once __DIR__ . '/../model/dto/TurmaDTO.php';

class TurmaController {

    private TurmaDAO $dao;

    public function __construct() {
        $this->dao = new TurmaDAO();
    }

    public function processarRequisicao(): void {

        if (
            !isset($_SESSION['usuario']) ||
            $_SESSION['usuario']['perfil_id'] != 2 ||
            !isset($_SESSION['id_academia'])
        ) {
            header('Location: ../view/login.php');
            exit;
        }

        $acao = $_REQUEST['acao'] ?? '';

        switch ($acao) {

            case 'cadastrar':
                $this->cadastrar();
                break;

            case 'atualizar':
                $this->atualizar();
                break;

            case 'excluir':
                $this->excluir();
                break;

            default:
                header('Location: ../view/gerente/home_gerente.php');
                exit;
        }
    }

    private function cadastrar(): void {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $turma = new TurmaDTO();

        $idAcademia = (int) $_SESSION['id_academia'];

        $turma->setIdUsuarioProfessor(
            (int) $_POST['id_usuario_professor']
        );

        $turma->setIdModalidade(
            (int) $_POST['id_modalidade']
        );

        $turma->setNome(
            trim($_POST['nome'])
        );

        $turma->setCapacidade(
            (int) $_POST['capacidade']
        );

        $turma->setNivel(
            trim($_POST['nivel'])
        );

        $turma->setStatus(
            $_POST['status']
        );

        if ($this->dao->inserir($turma, $idAcademia)) {

            header(
                'Location: ../view/turmas/listar_turma.php?sucesso=1'
            );

        } else {

            header(
                'Location: ../view/turmas/cadastrar_turma.php?erro=1'
            );
        }

        exit;
    }

    private function atualizar(): void {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $turma = new TurmaDTO();

        $idAcademia = (int) $_SESSION['id_academia'];

        $turma->setIdTurma(
            (int) $_POST['id_turma']
        );

        $turma->setIdUsuarioProfessor(
            (int) $_POST['id_usuario_professor']
        );

        $turma->setIdModalidade(
            (int) $_POST['id_modalidade']
        );

        $turma->setNome(
            trim($_POST['nome'])
        );

        $turma->setCapacidade(
            (int) $_POST['capacidade']
        );

        $turma->setNivel(
            trim($_POST['nivel'])
        );

        $turma->setStatus(
            $_POST['status']
        );

        if ($this->dao->atualizar($turma, $idAcademia)) {

            header(
                'Location: ../view/turma/listar_turma.php?sucesso=1'
            );

        } else {

            header(
                'Location: ../view/turmas/editar_turma.php?id='
                . $turma->getIdTurma()
                . '&erro=1'
            );
        }

        exit;
    }

    private function excluir(): void {

        $id = (int) ($_GET['id'] ?? 0);

        $idAcademia = (int) $_SESSION['id_academia'];

        if (
            $id > 0 &&
            $this->dao->excluir($id, $idAcademia)
        ) {

            header(
                'Location: ../view/turmas/listar_turma.php?sucesso=1'
            );

        } else {

            header(
                'Location: ../view/turmas/listar_turma.php?erro=1'
            );
        }

        exit;
    }
}

if (isset($_REQUEST['acao'])) {

    $controller = new TurmaController();

    $controller->processarRequisicao();
}