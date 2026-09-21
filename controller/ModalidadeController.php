<?php
//controller/ModalidadeController.php
session_start();

require_once __DIR__ . '/../model/dao/ModalidadeDAO.php';
require_once __DIR__ . '/../model/dto/ModalidadeDTO.php';

class ModalidadeController {
    private ModalidadeDAO $dao;

    public function __construct() {
        $this->dao = new ModalidadeDAO();
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $m = new ModalidadeDTO();

        // A academia vem da sessão, não do formulário
        $m->setIdAcademia((int)$_SESSION['id_academia']);

        $m->setNome(trim($_POST['nome']));
        $m->setDescricao(trim($_POST['descricao'] ?? ''));

        if ($this->dao->cadastrar($m)) {
            header('Location: ../view/gerente/listar_modalidade.php?sucesso=1');
        } else {
            header('Location: ../view/gerente/cadastrar_modalidade.php?erro=1');
        }

        exit;
    }

    private function atualizar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $m = new ModalidadeDTO();

        $m->setIdModalidade((int)$_POST['id_modalidade']);

        // A academia continua vindo da sessão
        $m->setIdAcademia((int)$_SESSION['id_academia']);

        $m->setNome(trim($_POST['nome']));
        $m->setDescricao(trim($_POST['descricao'] ?? ''));

        if ($this->dao->atualizar($m)) {
            header('Location: ../view/gerente/listar_modalidade.php?sucesso=1');
        } else {
            header('Location: ../view/gerente/editar_modalidade.php?id=' . $m->getIdModalidade() . '&erro=1');
        }

        exit;
    }

    private function excluir(): void {
        $id = (int)($_GET['id'] ?? 0);
        $idAcademia = (int)$_SESSION['id_academia'];

        if ($id > 0 && $this->dao->excluir($id, $idAcademia)) {
            header('Location: ../view/gerente/listar_modalidade.php?sucesso=1');
        } else {
            header('Location: ../view/gerente/listar_modalidade.php?erro=1');
        }

        exit;
    }
}

if (isset($_REQUEST['acao'])) {
    $controller = new ModalidadeController();
    $controller->processarRequisicao();
}