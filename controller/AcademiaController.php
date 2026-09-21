<?php
//controller/AcademiaController.php
session_start();
require_once __DIR__ . '/../model/dao/AcademiaDAO.php';
require_once __DIR__ . '/../model/dto/AcademiaDTO.php';

class AcademiaController {
    private AcademiaDAO $dao;

    public function __construct() {
        $this->dao = new AcademiaDAO();
    }

    public function processarRequisicao(): void {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 1) {
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
                header('Location: ../view/admin/home_admin.php');
                exit;
        }
    }

    private function cadastrar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $a = new AcademiaDTO();
        $a->setNome(trim($_POST['nome']));
        $a->setDocumento(preg_replace('/[^0-9]/', '', $_POST['documento']));
        $a->setEndereco(trim($_POST['endereco']));
        $a->setTelefone(trim($_POST['telefone']));
        $a->setEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));

        if ($this->dao->cadastrar($a)) {
            header('Location: ../view/admin/home_admin.php?sucesso=1');
        } else {
            header('Location: ../view/admin/cadastrar_academia.php?erro=1');
        }
        exit;
    }

    private function atualizar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $a = new AcademiaDTO();
        $a->setIdAcademia((int)$_POST['id_academia']);
        $a->setNome(trim($_POST['nome']));
        $a->setDocumento(preg_replace('/[^0-9]/', '', $_POST['documento']));
        $a->setEndereco(trim($_POST['endereco']));
        $a->setTelefone(trim($_POST['telefone']));
        $a->setEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));

        if ($this->dao->atualizar($a)) {
            header('Location: ../view/admin/home_admin.php?sucesso=1');
        } else {
            header('Location: ../view/admin/editar_academia.php?id=' . $a->getIdAcademia() . '&erro=1');
        }
        exit;
    }

    private function excluir(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->dao->excluir($id)) {
            header('Location: ../view/admin/home_admin.php?sucesso=1');
        } else {
            header('Location: ../view/admin/home_admin.php?erro=1');
        }
        exit;
    }
}

if (isset($_REQUEST['acao'])) {
    $controller = new AcademiaController();
    $controller->processarRequisicao();
}