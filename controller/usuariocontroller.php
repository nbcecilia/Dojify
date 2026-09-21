<?php
//controller/UsuarioController.php
session_start();

require_once __DIR__ . '/../model/dao/UsuarioDAO.php';
require_once __DIR__ . '/../model/dto/UsuarioDTO.php';
require_once __DIR__ . '/../model/dao/AcademiaDAO.php';

class UsuarioController {
    private UsuarioDAO $dao;

    public function __construct() {
        $this->dao = new UsuarioDAO();
    }

    public function processarRequisicao(): void {
        $acao = $_REQUEST['acao'] ?? '';

        switch ($acao) {
            case 'novo_cadastro':
                $this->exibirTelaCadastro();
                break;
            case 'cadastrar':
                $this->cadastrar();
                break;
            case 'cadastrar_gerente':
                $this->cadastrarGerente();
                break;
            case 'atualizar':
                $this->atualizar();
                break;
            case 'excluir':
                $this->excluir();
                break;
            case 'alternar_status': 
                $this->alternarStatus();
                break;
            default:
                header('Location: ../view/login.php');
                exit;
        }
    }

    private function exibirTelaCadastro(): void {
        try {
            $academiaDAO = new AcademiaDAO();
            $academias = $academiaDAO->listarTodas();
        } catch (Exception $e) {
            $academias = [];
        }

        require_once __DIR__ . '/../view/cadastrar_usuario.php';
        exit;
    }

    private function cadastrar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../controller/UsuarioController.php?acao=novo_cadastro');
            exit;
        }

        try {
            $u = new UsuarioDTO();
            
            $idAcademia = !empty($_POST['id_academia']) ? (int)$_POST['id_academia'] : null;
            $u->setIdAcademia($idAcademia);
            
            $u->setPerfilId((int)$_POST['perfil_id']);
            $u->setNome(trim($_POST['nome']));
            $u->setCpf(preg_replace('/[^0-9]/', '', $_POST['cpf']));
            $u->setDataNascimento($_POST['data_nascimento']);
            $u->setTelefone(trim($_POST['telefone']));
            $u->setEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
            
            $u->setEspecialidade(!empty($_POST['especialidade']) ? $_POST['especialidade'] : null);
            $u->setDataAdmissao(!empty($_POST['data_admissao']) ? $_POST['data_admissao'] : null);
            $u->setResponsavel(!empty($_POST['responsavel']) ? $_POST['responsavel'] : null);
            $u->setObservacao(!empty($_POST['observacao']) ? $_POST['observacao'] : null);
            $u->setDataMatricula(!empty($_POST['data_matricula']) ? $_POST['data_matricula'] : null);
            $u->setStatus('ATIVO');

            $senha = $_POST['senha'] ?? '';

            if (empty($senha) || !$u->getEmail() || !$u->getIdAcademia() || !$u->getPerfilId()) {
                header('Location: ../controller/UsuarioController.php?acao=novo_cadastro&erro=dados_incompletos');
                exit;
            }

            if ($this->dao->cadastrar($u, $senha)) {
                header('Location: ../view/login.php?sucesso=cadastro');
            } else {
                header('Location: ../controller/UsuarioController.php?acao=novo_cadastro&erro=1');
            }
        } catch (Exception $e) {
            header('Location: ../controller/UsuarioController.php?acao=novo_cadastro&erro=excecao');
        }
        exit;
    }
    
    private function cadastrarGerente(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 1) {
            header('Location: ../view/login.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/admin/cadastrar_gerente.php');
            exit;
        }

        try {
            $u = new UsuarioDTO();
            
            $u->setIdAcademia((int)$_POST['id_academia']);
            $u->setPerfilId(2);
            $u->setNome(trim($_POST['nome']));
            $u->setCpf(preg_replace('/[^0-9]/', '', $_POST['cpf']));
            $u->setDataNascimento($_POST['data_nascimento']);
            $u->setTelefone(trim($_POST['telefone']));
            $u->setEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
            $u->setStatus('ATIVO');

            $senha = $_POST['senha'] ?? '';

            if (empty($senha) || !$u->getEmail() || !$u->getIdAcademia()) {
                header('Location: ../view/admin/cadastrar_gerente.php?erro=dados_incompletos');
                exit;
            }

            if ($this->dao->cadastrar($u, $senha)) {
                header('Location: ../view/admin/home_admin.php?sucesso=1');
            } else {
                header('Location: ../view/admin/cadastrar_gerente.php?erro=1');
            }
        } catch (Exception $e) {
            header('Location: ../view/admin/cadastrar_gerente.php?erro=excecao');
        }
        exit;
    }

    private function atualizar(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 1) {
            header('Location: ../view/login.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/admin/home_admin.php');
            exit;
        }

        try {
            $u = new UsuarioDTO();
            
            $u->setIdUsuario((int)$_POST['id_usuario']);
            $u->setPerfilId((int)$_POST['perfil_id']);
            $u->setNome(trim($_POST['nome']));
            $u->setCpf(preg_replace('/[^0-9]/', '', $_POST['cpf']));
            $u->setDataNascimento($_POST['data_nascimento']);
            $u->setTelefone(trim($_POST['telefone']));
            $u->setEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
            $u->setStatus($_POST['status'] ?? 'ATIVO');

            if (!$u->getIdUsuario() || !$u->getEmail()) {
                header('Location: ../view/admin/editar_gerente.php?id=' . $u->getIdUsuario() . '&erro=dados_incompletos');
                exit;
            }

            if ($this->dao->atualizar($u)) {
                header('Location: ../view/admin/home_admin.php?sucesso=atualizado');
            } else {
                header('Location: ../view/admin/editar_gerente.php?id=' . $u->getIdUsuario() . '&erro=1');
            }
        } catch (Exception $e) {
            header('Location: ../view/admin/home_admin.php?erro=excecao');
        }
        exit;
    }

    private function excluir(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 1) {
            header('Location: ../view/login.php');
            exit;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            header('Location: ../view/admin/home_admin.php?erro=id_invalido');
            exit;
        }

        try {
            if ($this->dao->excluir($id)) {
                header('Location: ../view/admin/home_admin.php?sucesso=excluido');
            } else {
                header('Location: ../view/admin/home_admin.php?erro=falha_exclusao');
            }
        } catch (Exception $e) {
            header('Location: ../view/admin/home_admin.php?erro=excecao');
        }
        exit;
    }
private function alternarStatus(): void {
        // Restrito exclusivamente ao Gerente (perfil_id = 2)
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
            header('Location: ../view/login.php');
            exit;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $novoStatus = $_GET['status'] ?? '';

        // Valida se o status enviado é um dos permitidos pela base de dados
        $statusPermitidos = ['ATIVO', 'INATIVO', 'SUSPENSO'];

        if ($id && in_array($novoStatus, $statusPermitidos)) {
            $usuario = $this->dao->buscarPorId($id);
            
            // Segurança: Garante que o gerente só altera utilizadores da sua própria academia
            if ($usuario && (int)$usuario['id_academia'] === (int)$_SESSION['usuario']['id_academia']) {
                
                $uDTO = new UsuarioDTO();
                $uDTO->setIdUsuario($id);
                $uDTO->setNome($usuario['nome']);
                $uDTO->setCpf($usuario['cpf']);
                $uDTO->setDataNascimento($usuario['data_nascimento']);
                $uDTO->setTelefone($usuario['telefone']);
                $uDTO->setEmail($usuario['email']);
                $uDTO->setStatus($novoStatus);

                $this->dao->atualizar($uDTO);
            }
        }
        
        header('Location: ../view/gerente/listar_usuarios.php?sucesso=1');
        exit;
    }
}

if (isset($_REQUEST['acao'])) {
    $controller = new UsuarioController();
    $controller->processarRequisicao();
}