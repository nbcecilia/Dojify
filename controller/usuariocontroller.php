<?php
// controller/UsuarioController.php
session_start();

require_once __DIR__ . '/../model/dao/UsuarioDAO.php';
require_once __DIR__ . '/../model/dto/UsuarioDTO.php';
require_once __DIR__ . '/../model/dao/AcademiaDAO.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

class UsuarioController {
    private \UsuarioDAO $dao;

    public function __construct() {
        $this->dao = new \UsuarioDAO();
    }

    public function processarRequisicao(): void {
        $acao = $_REQUEST['acao'] ?? '';

        switch ($acao) {
            // === ROTAS DO GERENTE (Perfil 2) ===
            case 'cadastrar_aluno':
                $this->cadastrarAluno();
                break;
            case 'cadastrar_professor':
                $this->cadastrarProfessor();
                break;
            case 'atualizar_aluno':
                $this->atualizarAluno();
                break;
            case 'atualizar_professor':
                $this->atualizarProfessor();
                break;
            case 'alternar_status': 
                $this->alternarStatus();
                break;

            // === ROTAS DO ADMINISTRADOR (Perfil 1) ===
            case 'cadastrar_gerente':
                $this->cadastrarGerente();
                break;
            case 'atualizar':
                $this->atualizar();
                break;
            case 'atualizar_gerente':
                $this->atualizarGerente();
                break;
            case 'desativar':
                $this->desativar();
                break;
            case 'reativar':
                $this->reativar();
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
            header('Location: ../view/gerente/cadastrar_aluno_prof.php');
            exit;
        }

        $pdo = null;

        try {
            $pdo = \Conexao::getConexao();
            $pdo->beginTransaction();

            $idAcademia     = $_SESSION['usuario']['id_academia'] ?? null;
            $nome           = trim($_POST['nome'] ?? '');
            $cpf            = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
            $dataNascimento = $_POST['data_nascimento'] ?? '';
            $sexo           = trim($_POST['sexo'] ?? '');
            $telefone       = trim($_POST['telefone'] ?? '');
            $cep            = preg_replace('/\D/', '', $_POST['cep'] ?? '');
            $endereco       = trim($_POST['endereco'] ?? '');
            $cidade         = trim($_POST['cidade'] ?? '');
            $estado         = strtoupper(trim($_POST['estado'] ?? ''));
            $email          = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $senha          = 'aluno123';

            $modalidadesPost = $_POST['id_modalidades'] ?? [];
            $planosPost = $_POST['planos'] ?? [];
            $idsModalidades = [];
            $planosPorIndice = [];
            $modalidadesJaSelecionadas = [];

            if (!is_array($modalidadesPost) || !is_array($planosPost)) {
                throw new InvalidArgumentException('modalidade_invalida');
            }

            foreach ($modalidadesPost as $indice => $idModalidade) {
                $indiceValido = filter_var((string)$indice, FILTER_VALIDATE_INT);
                $idModalidade = is_scalar($idModalidade)
                    ? filter_var($idModalidade, FILTER_VALIDATE_INT)
                    : false;
                if ($idModalidade === false || $idModalidade < 1) {
                    continue;
                }
                if ($indiceValido === false || $indiceValido < 0 || $indiceValido > 2 || in_array($idModalidade, $modalidadesJaSelecionadas, true)) {
                    throw new InvalidArgumentException('modalidade_invalida');
                }

                $dadosPlano = $planosPost[$indice] ?? null;
                $nomePlano = is_array($dadosPlano) && is_scalar($dadosPlano['nome_plano'] ?? null)
                    ? trim((string)$dadosPlano['nome_plano'])
                    : '';
                $valorPlanoStr = is_array($dadosPlano) && is_scalar($dadosPlano['valor'] ?? null)
                    ? str_replace(',', '.', trim((string)$dadosPlano['valor']))
                    : '';

                if ($nomePlano === '' || !is_numeric($valorPlanoStr) || (float)$valorPlanoStr < 0) {
                    throw new InvalidArgumentException('plano_invalido');
                }

                $idsModalidades[$indiceValido] = (int)$idModalidade;
                $planosPorIndice[$indiceValido] = [
                    'nome_plano' => $nomePlano,
                    'valor' => (float)$valorPlanoStr,
                ];
                $modalidadesJaSelecionadas[] = (int)$idModalidade;
            }

            $responsavel    = !empty($_POST['responsavel']) ? trim($_POST['responsavel']) : null;
            $observacao     = !empty($_POST['observacao']) ? trim($_POST['observacao']) : null;
            $dataMatricula  = !empty($_POST['data_matricula']) ? $_POST['data_matricula'] : date('Y-m-d');

            if (
                empty($idAcademia) || empty($nome) || empty($cpf) || empty($dataNascimento)
                || !in_array($sexo, ['Feminino', 'Masculino', 'Outro', 'Prefiro não informar'], true)
                || !$email || empty($senha) || count($idsModalidades) < 1
                || count($idsModalidades) > 3 || !array_key_exists(0, $idsModalidades)
                || strlen($cep) !== 8 || $endereco === ''
                || $cidade === '' || !preg_match('/^[A-Z]{2}$/', $estado)
            ) {
                if (count($idsModalidades) < 1 || count($idsModalidades) > 3) {
                    throw new InvalidArgumentException('modalidade_invalida');
                }
                throw new Exception("Preencha todos os campos obrigatórios corretamente.");
            }

            $stmtModalidade = $pdo->prepare("SELECT 1 FROM modalidade WHERE id_modalidade = ? AND id_academia = ? AND status = 'ATIVA'");
            foreach ($idsModalidades as $idModalidadeSelecionada) {
                $stmtModalidade->execute([$idModalidadeSelecionada, $idAcademia]);
                if (!$stmtModalidade->fetchColumn()) {
                    throw new InvalidArgumentException('modalidade_invalida');
                }
            }

            $sqlUsuario = "INSERT INTO usuario (id_academia, perfil_id, nome, cpf, data_nascimento, sexo, telefone, email, cep, endereco, cidade, estado, responsavel, observacao, data_matricula, status)
                            VALUES (:id_academia, 4, :nome, :cpf, :data_nascimento, :sexo, :telefone, :email, :cep, :endereco, :cidade, :estado, :responsavel, :observacao, :data_matricula, 'ATIVO')";
            
            $stmt = $pdo->prepare($sqlUsuario);
            $stmt->execute([
                ':id_academia'     => $idAcademia,
                ':nome'            => $nome,
                ':cpf'             => $cpf,
                ':data_nascimento' => $dataNascimento,
                ':sexo'            => $sexo,
                ':telefone'        => $telefone,
                ':email'           => $email,
                ':cep'             => substr($cep, 0, 5) . '-' . substr($cep, 5),
                ':endereco'        => $endereco,
                ':cidade'          => $cidade,
                ':estado'          => $estado,
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

            $sqlPlano = "INSERT INTO plano (id_usuario_aluno, id_modalidade, nome_plano, valor, data_inicio, data_fim, status)
                       VALUES (:id_usuario_aluno, :id_modalidade, :nome_plano, :valor, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'ATIVO')";
            $stmtPlano = $pdo->prepare($sqlPlano);

            $sqlPagamento = "INSERT INTO pagamento (id_plano_matricula, valor, data_vencimento, status)
                             VALUES (:id_plano_matricula, :valor, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'PENDENTE')";
            $stmtPagamento = $pdo->prepare($sqlPagamento);

            foreach ($idsModalidades as $indice => $idModalidadeSelecionada) {
                $plano = $planosPorIndice[$indice];
                $stmtPlano->execute([
                    ':id_usuario_aluno' => $idUsuarioNovo,
                    ':id_modalidade'    => $idModalidadeSelecionada,
                    ':nome_plano'       => $plano['nome_plano'],
                    ':valor'            => $plano['valor']
                ]);
                $idPlanoCriado = (int)$pdo->lastInsertId();

                // Cria um registo de pagamento individual para cada plano/modalidade
                $stmtPagamento->execute([
                    ':id_plano_matricula' => $idPlanoCriado,
                    ':valor' => $plano['valor']
                ]);
            }

            $pdo->commit();
            header('Location: ../view/gerente/listar_usuarios.php?sucesso=cadastrado');
            exit;

        } catch (Exception $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Falha ao cadastrar aluno: ' . $e->getMessage());
            if ($e instanceof InvalidArgumentException) {
                $erro = $e->getMessage() === 'plano_invalido' ? 'plano_invalido' : 'modalidade_invalida';
            } elseif (
                $e instanceof PDOException
                && (int)($e->errorInfo[1] ?? 0) === 1062
                && preg_match("/for key ['`](?:[^'`]*[.])?(cpf|email)(?:_UNIQUE)?['`]/i", $e->getMessage(), $duplicidade)
            ) {
                $erro = strtolower($duplicidade[1]) . '_duplicado';
            } else {
                $erro = 'falha_cadastro';
            }
            if (in_array($erro, ['cpf_duplicado', 'email_duplicado'], true)) {
                $this->guardarDadosCadastroAluno();
            }
            header('Location: ../view/gerente/cadastrar_aluno_prof.php?erro=' . $erro);
            exit;
        }
    }

    private function guardarDadosCadastroAluno(): void {
        $campos = [
            'nome', 'cpf', 'telefone', 'data_nascimento', 'sexo', 'email',
            'cep', 'endereco', 'cidade', 'estado', 'data_matricula',
            'responsavel', 'observacao',
        ];
        $dados = [];

        foreach ($campos as $campo) {
            $valor = $_POST[$campo] ?? '';
            $dados[$campo] = is_string($valor) ? substr($valor, 0, 1000) : '';
        }

        $modalidades = $_POST['id_modalidades'] ?? [];
        $dados['id_modalidades'] = [];
        if (is_array($modalidades)) {
            foreach (array_slice($modalidades, 0, 3, true) as $indice => $valor) {
                if ((is_string($valor) || is_numeric($valor)) && (string)$indice !== '') {
                    $dados['id_modalidades'][(string)$indice] = substr((string)$valor, 0, 20);
                }
            }
        }

        $planos = $_POST['planos'] ?? [];
        $dados['planos'] = [];
        if (is_array($planos)) {
            foreach (array_slice($planos, 0, 3, true) as $indice => $plano) {
                if (!is_array($plano)) {
                    continue;
                }
                $dados['planos'][(string)$indice] = [
                    'nome_plano' => is_string($plano['nome_plano'] ?? null)
                        ? substr($plano['nome_plano'], 0, 100)
                        : '',
                    'valor' => is_string($plano['valor'] ?? null)
                        ? substr($plano['valor'], 0, 20)
                        : '',
                ];
            }
        }

        $_SESSION['cadastro_aluno_old_input'] = $dados;
    }

    private function cadastrarProfessor(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
            header('Location: ../view/login.php?erro=acesso_negado');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/gerente/cadastrar_aluno_prof.php');
            exit;
        }

        $pdo = null;

        try {
            $pdo = \Conexao::getConexao();
            $pdo->beginTransaction();

            $idAcademia     = $_SESSION['usuario']['id_academia'] ?? null;
            $nome           = trim($_POST['nome'] ?? '');
            $cpf            = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
            $dataNascimento = $_POST['data_nascimento'] ?? '';
            $sexo           = trim($_POST['sexo'] ?? '');
            $telefone       = trim($_POST['telefone'] ?? '');
            $cep            = preg_replace('/\D/', '', $_POST['cep'] ?? '');
            $endereco       = trim($_POST['endereco'] ?? '');
            $cidade         = trim($_POST['cidade'] ?? '');
            $estado         = strtoupper(trim($_POST['estado'] ?? ''));
            $email          = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $senha          = 'professor123';
            $especialidade  = trim($_POST['especialidade'] ?? '');
            $dataAdmissao   = !empty($_POST['data_admissao']) ? $_POST['data_admissao'] : date('Y-m-d');

            if (
                empty($idAcademia) || empty($nome) || empty($cpf) || empty($dataNascimento) || !$email
                || !in_array($sexo, ['Feminino', 'Masculino', 'Outro', 'Prefiro não informar'], true)
                || empty($senha) || strlen($cep) !== 8 || $endereco === '' || $cidade === ''
                || !preg_match('/^[A-Z]{2}$/', $estado)
            ) {
                throw new Exception("Preencha todos os campos obrigatórios corretamente.");
            }

            $sqlUsuario = "INSERT INTO usuario (id_academia, perfil_id, nome, cpf, data_nascimento, sexo, telefone, email, cep, endereco, cidade, estado, especialidade, data_admissao, status)
                            VALUES (:id_academia, 3, :nome, :cpf, :data_nascimento, :sexo, :telefone, :email, :cep, :endereco, :cidade, :estado, :especialidade, :data_admissao, 'ATIVO')";
            
            $stmt = $pdo->prepare($sqlUsuario);
            $stmt->execute([
                ':id_academia'     => $idAcademia,
                ':nome'            => $nome,
                ':cpf'             => $cpf,
                ':data_nascimento' => $dataNascimento,
                ':sexo'            => $sexo,
                ':telefone'        => $telefone,
                ':email'           => $email,
                ':cep'             => substr($cep, 0, 5) . '-' . substr($cep, 5),
                ':endereco'        => $endereco,
                ':cidade'          => $cidade,
                ':estado'          => $estado,
                ':especialidade'   => $especialidade,
                ':data_admissao'   => $dataAdmissao
            ]);
            $idUsuarioNovo = $pdo->lastInsertId();

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sqlLogin = "INSERT INTO login (id_usuario, senha_hash) VALUES (:id_usuario, :senha_hash)";
            
            $stmtLogin = $pdo->prepare($sqlLogin);
            $stmtLogin->execute([
                ':id_usuario' => $idUsuarioNovo,
                ':senha_hash' => $senhaHash
            ]);

            $pdo->commit();
            header('Location: ../view/gerente/listar_usuarios.php?sucesso=professor_cadastrado');
            exit;

        } catch (Exception $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
    
            if ($e->getCode() == '23000' || strpos($e->getMessage(), '1062') !== false) {
                header('Location: ../view/gerente/cadastrar_aluno_prof.php?erro=cpf_duplicado&tipo=professor');
                exit;
            }

            header('Location: ../view/gerente/cadastrar_aluno_prof.php?erro=falha_cadastro&tipo=professor');
            exit;
        }
    }

    private function atualizarProfessor(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
            header('Location: ../view/login.php?erro=acesso_negado');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/gerente/listar_usuarios.php');
            exit;
        }

        $idProfessor = filter_var($_POST['id_usuario'] ?? null, FILTER_VALIDATE_INT);
        $nome = trim((string)($_POST['nome'] ?? ''));
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $telefone = trim((string)($_POST['telefone'] ?? ''));
        $especialidade = trim((string)($_POST['especialidade'] ?? ''));
        $status = (string)($_POST['status'] ?? '');

        if (
            !$idProfessor || $idProfessor < 1 || $nome === '' || !$email
            || $telefone === '' || $especialidade === ''
            || !in_array($status, ['ATIVO', 'INATIVO', 'SUSPENSO'], true)
        ) {
            header('Location: ../view/gerente/listar_usuarios.php?erro=atualizar_professor');
            exit;
        }

        $idAcademia = (int)($_SESSION['usuario']['id_academia'] ?? 0);
        if ($idAcademia < 1) {
            header('Location: ../view/gerente/listar_usuarios.php?erro=atualizar_professor');
            exit;
        }

        $sucesso = $this->dao->atualizarProfessor(
            $idProfessor,
            $idAcademia,
            $nome,
            $email,
            $telefone,
            $especialidade,
            $status
        );

        header(
            'Location: ../view/gerente/listar_usuarios.php?'
            . ($sucesso ? 'sucesso=professor_atualizado' : 'erro=atualizar_professor')
        );
        exit;
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
            $pdo = \Conexao::getConexao();
            $pdo->beginTransaction();

            $idUsuario     = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
            $idAcademia    = $_SESSION['usuario']['id_academia'] ?? null;
            $nome          = trim($_POST['nome'] ?? '');
            $telefone      = trim($_POST['telefone'] ?? '');
            $email         = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $responsavel   = !empty($_POST['responsavel']) ? trim($_POST['responsavel']) : null;
            $observacao    = !empty($_POST['observacao']) ? trim($_POST['observacao']) : null;
            $status        = $_POST['status'] ?? 'ATIVO';

            $modalidadesPost = $_POST['id_modalidades'] ?? [];
            $planosPost = $_POST['planos'] ?? [];
            $idsModalidades = [];
            $planosPorIndice = [];
            $modalidadesJaSelecionadas = [];

            if (!is_array($modalidadesPost) || !is_array($planosPost)) {
                throw new InvalidArgumentException('modalidade_invalida');
            }

            foreach ($modalidadesPost as $indice => $idModalidade) {
                $indiceValido = filter_var((string)$indice, FILTER_VALIDATE_INT);
                $idModalidade = is_scalar($idModalidade)
                    ? filter_var($idModalidade, FILTER_VALIDATE_INT)
                    : false;
                if ($idModalidade === false || $idModalidade < 1) {
                    continue;
                }
                if ($indiceValido === false || $indiceValido < 0 || $indiceValido > 2 || in_array($idModalidade, $modalidadesJaSelecionadas, true)) {
                    throw new InvalidArgumentException('modalidade_invalida');
                }

                $dadosPlano = $planosPost[$indice] ?? null;
                $nomePlano = is_array($dadosPlano) && is_scalar($dadosPlano['nome_plano'] ?? null)
                    ? trim((string)$dadosPlano['nome_plano'])
                    : '';
                $valorPlanoStr = is_array($dadosPlano) && is_scalar($dadosPlano['valor'] ?? null)
                    ? str_replace(',', '.', trim((string)$dadosPlano['valor']))
                    : '';

                if ($nomePlano === '' || !is_numeric($valorPlanoStr) || (float)$valorPlanoStr < 0) {
                    throw new InvalidArgumentException('plano_invalido');
                }

                $idsModalidades[$indiceValido] = (int)$idModalidade;
                $planosPorIndice[$indiceValido] = [
                    'nome_plano' => $nomePlano,
                    'valor' => (float)$valorPlanoStr,
                ];
                $modalidadesJaSelecionadas[] = (int)$idModalidade;
            }

            if (
                !$idUsuario || empty($idAcademia) || empty($nome) || !$email 
                || count($idsModalidades) < 1 || count($idsModalidades) > 3 
                || !array_key_exists(0, $idsModalidades)
            ) {
                throw new Exception("Preencha todos os campos obrigatórios corretamente.");
            }

            $stmtModalidade = $pdo->prepare("SELECT 1 FROM modalidade WHERE id_modalidade = ? AND id_academia = ? AND status = 'ATIVA'");
            foreach ($idsModalidades as $idModalidadeSelecionada) {
                $stmtModalidade->execute([$idModalidadeSelecionada, $idAcademia]);
                if (!$stmtModalidade->fetchColumn()) {
                    throw new Exception("Selecione modalidades válidas da sua academia.");
                }
            }

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

            $stmtPlanosAtivos = $pdo->prepare("
                SELECT pl.id_plano, pl.id_modalidade,
                       (
                           SELECT pag.id_pagamento
                           FROM pagamento pag
                           WHERE pag.id_plano_matricula = pl.id_plano
                           ORDER BY pag.data_vencimento DESC, pag.id_pagamento DESC
                           LIMIT 1
                       ) AS id_pagamento_atual,
                       (
                           SELECT pag.status
                           FROM pagamento pag
                           WHERE pag.id_plano_matricula = pl.id_plano
                           ORDER BY pag.data_vencimento DESC, pag.id_pagamento DESC
                           LIMIT 1
                       ) AS status_pagamento_atual
                FROM plano pl
                WHERE pl.id_usuario_aluno = :id_usuario_aluno AND pl.status = 'ATIVO'
                ORDER BY pl.id_plano ASC
            ");
            $stmtPlanosAtivos->execute([':id_usuario_aluno' => $idUsuario]);
            $planosAtivosBanco = $stmtPlanosAtivos->fetchAll(PDO::FETCH_ASSOC);

            $stmtAtualizarPlano = $pdo->prepare("
                UPDATE plano SET id_modalidade = :id_modalidade, nome_plano = :nome_plano, valor = :valor
                WHERE id_plano = :id_plano
            ");

            $stmtAtualizarPagamento = $pdo->prepare("
                UPDATE pagamento
                SET valor = :valor
                WHERE id_pagamento = :id_pagamento
                  AND status IN ('PENDENTE', 'ATRASADO')
            ");
            
            $stmtNovoPlano = $pdo->prepare("
                INSERT INTO plano (id_usuario_aluno, id_modalidade, nome_plano, valor, data_inicio, data_fim, status)
                VALUES (:id_usuario_aluno, :id_modalidade, :nome_plano, :valor, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'ATIVO')
            ");

            $stmtNovoPagamento = $pdo->prepare("
                INSERT INTO pagamento (id_plano_matricula, valor, data_vencimento, status)
                VALUES (:id_plano_matricula, :valor, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'PENDENTE')
            ");

            $indicesModalidades = array_keys($idsModalidades);
            
            foreach ($indicesModalidades as $posicao) {
                $idModSel = $idsModalidades[$posicao];
                $dadosPlanoModalidade = $planosPorIndice[$posicao];

                if (isset($planosAtivosBanco[$posicao])) {
                    $idPlanoExistente = $planosAtivosBanco[$posicao]['id_plano'];
                    $stmtAtualizarPlano->execute([
                        ':id_modalidade' => $idModSel,
                        ':nome_plano'    => $dadosPlanoModalidade['nome_plano'],
                        ':valor'         => $dadosPlanoModalidade['valor'],
                        ':id_plano'      => $idPlanoExistente
                    ]);
                    $idPagamentoAtual = $planosAtivosBanco[$posicao]['id_pagamento_atual'];
                    if ($idPagamentoAtual === null) {
                        $stmtNovoPagamento->execute([
                            ':id_plano_matricula' => $idPlanoExistente,
                            ':valor' => $dadosPlanoModalidade['valor']
                        ]);
                    } elseif (in_array(
                        strtoupper((string)$planosAtivosBanco[$posicao]['status_pagamento_atual']),
                        ['PENDENTE', 'ATRASADO'],
                        true
                    )) {
                        $stmtAtualizarPagamento->execute([
                            ':valor' => $dadosPlanoModalidade['valor'],
                            ':id_pagamento' => $idPagamentoAtual
                        ]);
                    }
                    unset($planosAtivosBanco[$posicao]);
                } else {
                    $stmtNovoPlano->execute([
                        ':id_usuario_aluno' => $idUsuario,
                        ':id_modalidade'    => $idModSel,
                        ':nome_plano'       => $dadosPlanoModalidade['nome_plano'],
                        ':valor'            => $dadosPlanoModalidade['valor']
                    ]);
                    $idNovoPlano = (int)$pdo->lastInsertId();
                    $stmtNovoPagamento->execute([
                        ':id_plano_matricula' => $idNovoPlano,
                        ':valor' => $dadosPlanoModalidade['valor']
                    ]);
                }
            }

            if (!empty($planosAtivosBanco)) {
                $idsParaCancelar = array_column($planosAtivosBanco, 'id_plano');
                $placeholders = implode(',', array_fill(0, count($idsParaCancelar), '?'));
                $stmtCancelarPlanos = $pdo->prepare("
                    UPDATE plano SET status = 'CANCELADO', data_fim = CURDATE()
                    WHERE id_plano IN ($placeholders)
                ");
                $stmtCancelarPlanos->execute($idsParaCancelar);
            }

            $pdo->commit();
            header('Location: ../view/gerente/listar_usuarios.php?sucesso=atualizado');
            exit;

        } catch (Exception $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Falha ao atualizar aluno/plano: ' . $e->getMessage());
            header('Location: ../view/gerente/listar_usuarios.php?erro=falha_atualizacao');
            exit;
        }
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
                $uDTO = new \UsuarioDTO();
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
            $u = new \UsuarioDTO();
            $u->setIdAcademia((int)($_POST['id_academia'] ?? 0));
            $u->setPerfilId(2);
            $u->setNome(trim($_POST['nome'] ?? ''));
            $u->setCpf(preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? ''));
            $u->setDataNascimento($_POST['data_nascimento'] ?? '');
            $sexo = trim($_POST['sexo'] ?? '');
            $u->setTelefone(trim($_POST['telefone'] ?? ''));
            $u->setEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
            
            $u->setCep(preg_replace('/\D/', '', $_POST['cep'] ?? ''));
            $u->setEndereco(trim($_POST['endereco'] ?? ''));
            $u->setCidade(trim($_POST['cidade'] ?? ''));
            $u->setEstado(strtoupper(trim($_POST['estado'] ?? '')));
            
            $u->setStatus('ATIVO');

            $senha = 'gerente123';

            if (
                !$u->getEmail() || !$u->getIdAcademia()
                || !in_array($sexo, ['Feminino', 'Masculino', 'Outro', 'Prefiro não informar'], true)
            ) {
                header('Location: ../view/admin/cadastrar_gerente.php?erro=dados_incompletos');
                exit;
            }
            $u->setSexo($sexo);

            if ($this->dao->cadastrar($u, $senha)) {
                header('Location: ../view/admin/listar_academias_gerentes.php?sucesso=1');
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
            header('Location: ../view/admin/listar_academias_gerentes.php');
            exit;
        }

        try {
            $u = new \UsuarioDTO();
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
                header('Location: ../view/admin/listar_academias_gerentes.php?sucesso=atualizado');
            } else {
                header('Location: ../view/admin/editar_gerente.php?id=' . $u->getIdUsuario() . '&erro=1'); 
            } 
        } catch (Exception $e) {
            header('Location: ../view/admin/listar_academias_gerentes.php?erro=excecao');
        }
        exit;
    }

    private function atualizarGerente(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 1) {
            header('Location: ../view/login.php?erro=acesso_negado');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../view/admin/listar_academias_gerentes.php');
            exit;
        }

        try {
            $idUsuario  = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
            $nome       = trim($_POST['nome'] ?? '');
            $email      = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $telefone   = trim($_POST['telefone'] ?? '');
            $idAcademia = filter_input(INPUT_POST, 'id_academia', FILTER_VALIDATE_INT);
            $status     = $_POST['status'] ?? 'ATIVO';

            if (!$idUsuario || empty($nome) || !$email || !$idAcademia) {
                header('Location: ../view/admin/editar_gerente.php?id=' . $idUsuario . '&erro=dados_incompletos');
                exit;
            }

            $usuarioAtual = $this->dao->buscarPorId($idUsuario);
            if (!$usuarioAtual || (int)$usuarioAtual['perfil_id'] !== 2) {
                header('Location: ../view/admin/listar_academias_gerentes.php?erro=nao_encontrado');
                exit;
            }

            $uDTO = new \UsuarioDTO();
            $uDTO->setIdUsuario($idUsuario);
            $uDTO->setIdAcademia($idAcademia);
            $uDTO->setPerfilId(2);
            $uDTO->setNome($nome);
            $uDTO->setCpf($usuarioAtual['cpf']);
            $uDTO->setDataNascimento($usuarioAtual['data_nascimento']);
            $uDTO->setTelefone($telefone);
            $uDTO->setEmail($email);
            $uDTO->setStatus($status);

            if ($this->dao->atualizar($uDTO)) {
                header('Location: ../view/admin/listar_academias_gerentes.php?sucesso=gerente_atualizado');
            } else {
                header('Location: ../view/admin/editar_gerente.php?id=' . $idUsuario . '&erro=falha_atualizacao');
            }
        } catch (Exception $e) {
            header('Location: ../view/admin/listar_academias_gerentes.php?erro=excecao');
        }
        exit;
    }

    private function desativar(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 1) {
            header('Location: ../view/login.php');
            exit;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            header('Location: ../view/admin/listar_academias_gerentes.php?erro=id_invalido');
            exit;
        }

        try {
            if ($this->dao->desativar($id)) {
                header('Location: ../view/admin/listar_academias_gerentes.php?sucesso=desativado');
            } else {
                header('Location: ../view/admin/listar_academias_gerentes.php?erro=falha_desativacao');
            }
        } catch (Exception $e) {
            header('Location: ../view/admin/listar_academias_gerentes.php?erro=excecao');
        }
        exit;
    }

    private function reativar(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 1) {
            header('Location: ../view/login.php');
            exit;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            header('Location: ../view/admin/listar_academias_gerentes.php?erro=id_invalido');
            exit;
        }

        try {
            $usuario = $this->dao->buscarPorId($id);
            if (!$usuario) {
                header('Location: ../view/admin/listar_academias_gerentes.php?erro=nao_encontrado');
                exit;
            }

            if ((int)$usuario['perfil_id'] === 2 && !empty($usuario['id_academia'])) {
                $idAcademia = (int)$usuario['id_academia'];
                if ($this->dao->existeGerenteAtivoNaAcademia($idAcademia)) {
                    header('Location: ../view/admin/listar_academias_gerentes.php?erro=academia_ja_tem_gerente');
                    exit;
                }
            }

            if ($this->dao->reativar($id)) {
                header('Location: ../view/admin/listar_academias_gerentes.php?sucesso=reativado');
            } else {
                header('Location: ../view/admin/listar_academias_gerentes.php?erro=falha_reativacao');
            }
        } catch (Exception $e) {
            header('Location: ../view/admin/listar_academias_gerentes.php?erro=excecao');
        }
        exit;
    }
}

if (isset($_REQUEST['acao'])) {
    $controller = new UsuarioController();
    $controller->processarRequisicao();
}