<?php
// controller/UsuarioController.php
session_start();

require_once __DIR__ . '/../model/dao/UsuarioDAO.php';
require_once __DIR__ . '/../model/dto/UsuarioDTO.php';
require_once __DIR__ . '/../model/dao/AcademiaDAO.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

class UsuarioController {
    private UsuarioDAO $dao;

    public function __construct() {
        $this->dao = new UsuarioDAO();
    }

    public function processarRequisicao(): void {
        $acao =$_REQUEST['acao'] ?? '';

        switch ($acao) {
            case 'cadastrar_aluno':
                $this->cadastrarAluno();
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

    /**
     * Registo de Aluno feito exclusivamente pelo Gerente,
     * criando em simultâneo o Login, o Plano e o Pagamento inicial (Transação PDO).
     */
    private function cadastrarAluno(): void {
        // Restrito exclusivamente ao Gerente (perfil_id = 2)
        if (!isset($_SESSION['usuario']) \vert{}\vert{} (int)$_SESSION['usuario']['perfil_id'] !== 2) {
            header('Location: ../view/login.php?erro=acesso_negado');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/gerente/cadastrar_aluno.php');
            exit;
        }

        try {
            $pdo = Conexao::getConexao();$pdo->beginTransaction();

            $idAcademia = $_SESSION['usuario']['id_academia'];$nome = trim($_POST['nome']);$cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);$dataNascimento = $_POST['data_nascimento'];$telefone = trim($_POST['telefone']);$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $senha =$_POST['senha'] ?? '';

            $nomePlano = trim($_POST['nome_plano']);
            $valorPlano = (float)$_POST['valor_plano'];

            if (empty($senha) || !$email \vert{}\vert{} empty($nome) || empty($cpf) \vert{}\vert{} empty($nomePlano)) {
                throw new Exception("Dados incompletos para o cadastro.");
            }

            // 1. Inserir Aluno na tabela `usuario` (perfil_id = 4 para Aluno)
            $sqlUsuario = "INSERT INTO usuario (id_academia, perfil_id, nome, cpf, data_nascimento, telefone, email, data_matricula, status) 
                           VALUES (:id_academia, 4, :nome, :cpf, :data_nascimento, :telefone, :email, CURDATE(), 'ATIVO')";
            $stmt =$pdo->prepare($sqlUsuario);$stmt->execute([
                ':id_academia' => $idAcademia,
                ':nome' => $nome,
                ':cpf' => $cpf,
                ':data_nascimento' => $dataNascimento,
                ':telefone' => $telefone,
                ':email' => $email
            ]);
            $idUsuarioNovo =$pdo->lastInsertId();

            // 2. Inserir credenciais na tabela `login`
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);$sqlLogin = "INSERT INTO login (id_usuario, senha_hash) VALUES (:id_usuario, :senha_hash)";
            $stmtLogin =$pdo->prepare($sqlLogin);$stmtLogin->execute([
                ':id_usuario' => $idUsuarioNovo,
                ':senha_hash' => $senhaHash
            ]);

            // 3. Inserir o contrato na tabela `plano` vinculado ao aluno criado (`id_usuario_aluno`)
            $sqlPlano = "INSERT INTO plano (id_usuario_aluno, nome_plano, valor, data_inicio, data_fim, status) 
                         VALUES (:id_usuario_aluno, :nome_plano, :valor, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'ATIVO')";
            $stmtPlano =$pdo->prepare($sqlPlano);$stmtPlano->execute([
                ':id_usuario_aluno' => $idUsuarioNovo,
                ':nome_plano' => $nomePlano,
                ':valor' => $valorPlano
            ]);
            $idPlanoNovo =$pdo->lastInsertId();

            // 4. Gerar a primeira fatura na tabela `pagamento`
            $sqlPagamento = "INSERT INTO pagamento (id_plano_matricula, valor, data_vencimento, status) 
                             VALUES (:id_plano_matricula, :valor, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'PENDENTE')";
            $stmtPagamento =$pdo->prepare($sqlPagamento);$stmtPagamento->execute([
                ':id_plano_matricula' => $idPlanoNovo,
                ':valor' => $valorPlano
            ]);

            // Efetiva todas as alterações em bloco
            $pdo->commit();
            header('Location: ../view/gerente/listar_usuarios.php?sucesso=cadastrado');
            exit;

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {$pdo->rollBack();
            }
            header('Location: ../view/gerente/cadastrar_aluno.php?erro=falha_cadastro');
            exit;
        }
    }
    
    private function cadastrarGerente(): void {
        if (!isset($_SESSION['usuario']) \vert{}\vert{} (int)$_SESSION['usuario']['perfil_id'] !== 1) {
            header('Location: ../view/login.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/admin/cadastrar_gerente.php');
            exit;
        }

        try {
            $u = new UsuarioDTO();$u->setIdAcademia((int)$_POST['id_academia']);$u->setPerfilId(2);
            $u->setNome(trim($_POST['nome']));
            $u->setCpf(preg_replace('/[^0-9]/', '',$_POST['cpf']));
            $u->setDataNascimento($_POST['data_nascimento']);
            $u->setTelefone(trim($_POST['telefone']));
            $u->setEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));$u->setStatus('ATIVO');

            $senha =$_POST['senha'] ?? '';

            if (empty($senha) || !$u->getEmail() \vert{}\vert{} !$u->getIdAcademia()) {
                header('Location: ../view/admin/cadastrar_gerente.php?erro=dados_incompletos');
                exit;
            }

            if ($this->dao->cadastrar($u,$senha)) {
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
        if (!isset($_SESSION['usuario']) \vert{}\vert{} (int)$_SESSION['usuario']['perfil_id'] !== 1) {
            header('Location: ../view/login.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/admin/home_admin.php');
            exit;
        }

        try {
            $u = new UsuarioDTO();$u->setIdUsuario((int)$_POST['id_usuario']);$u->setPerfilId((int)$_POST['perfil_id']);$u->setNome(trim($_POST['nome']));$u->setCpf(preg_replace('/[^0-9]/', '', $_POST['cpf']));$u->setDataNascimento($_POST['data_nascimento']);$u->setTelefone(trim($_POST['telefone']));$u->setEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
            $u->setStatus($_POST['status'] ?? 'ATIVO');

            if (!$u->getIdUsuario() \vert{}\vert{} !$u->getEmail()) {
                header('Location: ../view/admin/editar_gerente.php?id=' . $u->getIdUsuario() . '&erro=dados_incompletos');
                exit;
            }

            if ($this->dao->atualizar($u)) {
                header('Location: ../view/admin/home_admin.php?sucesso=atualizado');
            } else {
                header('Location: ../view/admin/editar_gerente.php?id=' . $u->getIdUsuario() . '&erro=1');             }         } catch (Exception$e) {
            header('Location: ../view/admin/home_admin.php?erro=excecao');
        }
        exit;
    }

    private function excluir(): void {
        if (!isset($_SESSION['usuario']) \vert{}\vert{} (int)$_SESSION['usuario']['perfil_id'] !== 1) {
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
        if (!isset($_SESSION['usuario']) \vert{}\vert{} (int)$_SESSION['usuario']['perfil_id'] !== 2) {
            header('Location: ../view/login.php');
            exit;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);$novoStatus = $_GET['status'] ?? '';$statusPermitidos = ['ATIVO', 'INATIVO', 'SUSPENSO'];

        if ($id && in_array($novoStatus, $statusPermitidos)) {$usuario = $this->dao->buscarPorId($id);
            
            if ($usuario && (int)$usuario['id_academia'] === (int)$_SESSION['usuario']['id_academia']) {$uDTO = new UsuarioDTO();
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
    $controller = new UsuarioController();$controller->processarRequisicao();
}