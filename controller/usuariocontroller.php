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
        $acao = $_REQUEST['acao'] ?? '';

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
            case 'atualizar_aluno':
                $this->atualizarAluno();
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

    private function cadastrarAluno(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
            header('Location: ../view/login.php?erro=acesso_negado');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/gerente/cadastrar_aluno.php');
            exit;
        }

        $pdo = null;

        try {
            $pdo = Conexao::getConexao();
            $pdo->beginTransaction();

            $idAcademia     = $_SESSION['usuario']['id_academia'] ?? null;
            $nome           = trim($_POST['nome'] ?? '');
            $cpf            = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
            $dataNascimento = $_POST['data_nascimento'] ?? '';
            $telefone       = trim($_POST['telefone'] ?? '');
            $email          = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $senha          = $_POST['senha'] ?? '';

            $nomePlano      = trim($_POST['nome_plano'] ?? '');
            
            // Tratamento do valor do plano: converte vírgula para ponto e garante float
            $valorPlanoStr  = $_POST['valor_plano'] ?? '0';
            $valorPlanoStr  = str_replace(',', '.', $valorPlanoStr);
            $valorPlano     = (float)$valorPlanoStr;

            // Tratamento dos campos opcionais (responsavel, observacao, data_matricula)
            $responsavel    = !empty($_POST['responsavel']) ? trim($_POST['responsavel']) : null;
            $observacao     = !empty($_POST['observacao']) ? trim($_POST['observacao']) : null;
            $dataMatricula  = !empty($_POST['data_matricula']) ? $_POST['data_matricula'] : date('Y-m-d');

            if (empty($idAcademia) || empty($nome) || empty($cpf) || empty($dataNascimento) || !$email || empty($senha) || empty($nomePlano)) {
                throw new Exception("Preencha todos os campos obrigatórios corretamente.");
            }

            $sqlUsuario = "INSERT INTO usuario (id_academia, perfil_id, nome, cpf, data_nascimento, telefone, email, responsavel, observacao, data_matricula, status) 
                            VALUES (:id_academia, 4, :nome, :cpf, :data_nascimento, :telefone, :email, :responsavel, :observacao, :data_matricula, 'ATIVO')";
            
            $stmt = $pdo->prepare($sqlUsuario);
            $stmt->execute([
                ':id_academia'     => $idAcademia,
                ':nome'            => $nome,
                ':cpf'             => $cpf,
                ':data_nascimento' => $dataNascimento,
                ':telefone'        => $telefone,
                ':email'           => $email,
                ':responsavel'     => $responsavel,
                ':observacao'      => $observacao,
                ':data_matricula'  => $dataMatricula
            ]);
            $idUsuarioNovo = $pdo->lastInsertId();

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sqlLogin = "INSERT INTO login (id_usuario, senha_hash) VALUES (:id_usuario, :senha_hash)";
            
            $stmtLogin = $pdo->prepare($sqlLogin);
            $stmtLogin->execute([
                ':id_usuario' => $idUsuarioNovo,
                ':senha_hash' => $senhaHash
            ]);

            $sqlPlano = "INSERT INTO plano (id_usuario_aluno, nome_plano, valor, data_inicio, data_fim, status) 
                       VALUES (:id_usuario_aluno, :nome_plano, :valor, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'ATIVO')";
            
            $stmtPlano = $pdo->prepare($sqlPlano);
            $stmtPlano->execute([
                ':id_usuario_aluno' => $idUsuarioNovo,
                ':nome_plano'       => $nomePlano,
                ':valor'            => $valorPlano
            ]);
            $idPlanoNovo = $pdo->lastInsertId();

            $sqlPagamento = "INSERT INTO pagamento (id_plano_matricula, valor, data_vencimento, status) 
                             VALUES (:id_plano_matricula, :valor, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'PENDENTE')";
            
            $stmtPagamento = $pdo->prepare($sqlPagamento);
            $stmtPagamento->execute([
                ':id_plano_matricula' => $idPlanoNovo,
                ':valor'              => $valorPlano
            ]);

            $pdo->commit();
            header('Location: ../view/gerente/listar_usuarios.php?sucesso=cadastrado');
            exit;

        } catch (Exception $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header('Location: ../view/gerente/cadastrar_aluno.php?erro=falha_cadastro');
            exit;
        }
    }

    private function atualizarAluno(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
            header('Location: ../view/login.php?erro=acesso_negado');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/gerente/listar_usuarios.php');
            exit;
        }

        $pdo = null;

        try {
            $pdo = Conexao::getConexao();
            $pdo->beginTransaction();

            $idUsuario     = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
            $idAcademia    = $_SESSION['usuario']['id_academia'] ?? null;
            $nome          = trim($_POST['nome'] ?? '');
            $telefone      = trim($_POST['telefone'] ?? '');
            $email         = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $responsavel   = !empty($_POST['responsavel']) ? trim($_POST['responsavel']) : null;
            $observacao    = !empty($_POST['observacao']) ? trim($_POST['observacao']) : null;
            $status        = $_POST['status'] ?? 'ATIVO';

            $nomePlano     = trim($_POST['nome_plano'] ?? '');
            $valorPlanoStr = $_POST['valor_plano'] ?? '0';
            $valorPlanoStr = str_replace(',', '.', $valorPlanoStr);
            $valorPlano    = (float)$valorPlanoStr;

            if (!$idUsuario || empty($idAcademia) || empty($nome) || !$email || empty($nomePlano)) {
                throw new Exception("Preencha todos os campos obrigatórios corretamente.");
            }

            // Atualiza os dados cadastrais do aluno
            $sqlUsuario = "UPDATE usuario 
                           SET nome = :nome, telefone = :telefone, email = :email, 
                               responsavel = :responsavel, observacao = :observacao, status = :status 
                           WHERE id_usuario = :id_usuario AND id_academia = :id_academia";
            
            $stmt = $pdo->prepare($sqlUsuario);
            $stmt->execute([
                ':nome'        => $nome,
                ':telefone'    => $telefone,
                ':email'       => $email,
                ':responsavel' => $responsavel,
                ':observacao'  => $observacao,
                ':status'      => $status,
                ':id_usuario'  => $idUsuario,
                ':id_academia' => $idAcademia
            ]);

            // Atualiza o plano ativo do aluno
            $sqlPlano = "UPDATE plano 
                         SET nome_plano = :nome_plano, valor = :valor 
                         WHERE id_usuario_aluno = :id_usuario_aluno AND status = 'ATIVO'";
            
            $stmtPlano = $pdo->prepare($sqlPlano);
            $stmtPlano->execute([
                ':nome_plano'       => $nomePlano,
                ':valor'            => $valorPlano,
                ':id_usuario_aluno' => $idUsuario
            ]);

            $pdo->commit();
            header('Location: ../view/gerente/listar_usuarios.php?sucesso=atualizado');
            exit;

        } catch (Exception $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header('Location: ../view/gerente/listar_usuarios.php?erro=falha_atualizacao');
            exit;
        }
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
            $u->setIdAcademia((int)($_POST['id_academia'] ?? 0));
            $u->setPerfilId(2);
            $u->setNome(trim($_POST['nome'] ?? ''));
            $u->setCpf(preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? ''));
            $u->setDataNascimento($_POST['data_nascimento'] ?? '');
            $u->setTelefone(trim($_POST['telefone'] ?? ''));
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
            $u->setIdUsuario((int)($_POST['id_usuario'] ?? 0));
            $u->setPerfilId((int)($_POST['perfil_id'] ?? 0));
            $u->setNome(trim($_POST['nome'] ?? ''));
            $u->setCpf(preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? ''));
            $u->setDataNascimento($_POST['data_nascimento'] ?? '');
            $u->setTelefone(trim($_POST['telefone'] ?? ''));
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
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
            header('Location: ../view/login.php');
            exit;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $novoStatus = $_GET['status'] ?? '';
        $statusPermitidos = ['ATIVO', 'INATIVO', 'SUSPENSO'];

        if ($id && in_array($novoStatus, $statusPermitidos)) {
            $usuario = $this->dao->buscarPorId($id);
            
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