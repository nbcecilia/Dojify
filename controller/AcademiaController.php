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
            case 'desativar':
                $this->desativar();
                break;
            case 'reativar':
                $this->reativar();
                break;
            default:
                header('Location: ../view/admin/home_admin.php');
                exit;
        }
    }

    private function cadastrar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $a = new AcademiaDTO();
        $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $documento = preg_replace('/\D/', '', $_POST['documento'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $estado = strtoupper(trim($_POST['estado'] ?? ''));
        $telefone = trim($_POST['telefone'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        if (
            strlen($cep) !== 8 || $nome === '' || $documento === '' || $endereco === ''
            || $cidade === '' || !preg_match('/^[A-Z]{2}$/', $estado)
            || $telefone === '' || !$email
        ) {
            header('Location: ../view/admin/cadastrar_academia.php?erro=1');
            exit;
        }

        $a->setNome($nome);
        $a->setDocumento($documento);
        $a->setCep(substr($cep, 0, 5) . '-' . substr($cep, 5));
        $a->setEndereco($endereco);
        $a->setCidade($cidade);
        $a->setEstado($estado);
        $a->setTelefone($telefone);
        $a->setEmail($email);

        if ($this->dao->cadastrar($a)) {
            header('Location: ../view/admin/listar_academias_gerentes.php?sucesso=1');
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
            header('Location: ../view/admin/listar_academias_gerentes.php?sucesso=1');
        } else {
            header('Location: ../view/admin/editar_academia.php?id=' . $a->getIdAcademia() . '&erro=1');
        }
        exit;
    }

    private function desativar(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->dao->desativar($id)) {
            header('Location: ../view/admin/listar_academias_gerentes.php?sucesso=1');
        } else {
            header('Location: ../view/admin/listar_academias_gerentes.php?erro=1');
        }
        exit;
    }

    private function reativar(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->dao->reativar($id)) {
            header('Location: ../view/admin/listar_academias_gerentes.php?sucesso=1');
        } else {
            header('Location: ../view/admin/listar_academias_gerentes.php?erro=1');
        }
        exit;
    }
}

if (isset($_REQUEST['acao'])) {
    $controller = new AcademiaController();
    $controller->processarRequisicao();
}