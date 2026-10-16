<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login_redefinir.php?erro=metodo');
    exit;
}

$tokenSessao = $_SESSION['recuperacao_csrf_token'] ?? '';
$tokenFormulario = $_POST['csrf_token'] ?? '';
if (
    !is_string($tokenSessao) ||
    !is_string($tokenFormulario) ||
    $tokenSessao === '' ||
    !hash_equals($tokenSessao, $tokenFormulario)
) {
    header('Location: ../view/recuperar_senha.php?erro=token');
    exit;
}

$acao = $_POST['acao'] ?? '';

if ($acao === 'verificar') {
    $cpf = $_POST['cpf'] ?? '';
    $dataNascimento = $_POST['data_nascimento'] ?? '';

    if (
        !is_string($cpf) ||
        !preg_match('/^\d{11}$/', $cpf) ||
        !is_string($dataNascimento) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataNascimento)
    ) {
        header('Location: ../view/login_redefinir.php?erro=dados');
        exit;
    }

    [$ano, $mes, $dia] = array_map('intval', explode('-', $dataNascimento));
    if (!checkdate($mes, $dia, $ano)) {
        header('Location: ../view/login_redefinir.php?erro=dados');
        exit;
    }

    try {
        require_once __DIR__ . '/../model/dao/Conexao.php';
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare(
            "SELECT u.id_usuario
             FROM usuario u
             INNER JOIN login l ON l.id_usuario = u.id_usuario
             WHERE u.cpf = :cpf
               AND u.data_nascimento = :data_nascimento
               AND u.status = 'ATIVO'
             LIMIT 1"
        );
        $stmt->execute([
            ':cpf' => $cpf,
            ':data_nascimento' => $dataNascimento,
        ]);
        $idUsuario = $stmt->fetchColumn();

        if ($idUsuario === false) {
            header('Location: ../view/login_redefinir.php?erro=nao_encontrado');
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['recuperacao_usuario_id'] = (int) $idUsuario;
        $_SESSION['recuperacao_inicio'] = time();
        $_SESSION['recuperacao_csrf_token'] = bin2hex(random_bytes(32));

        header('Location: ../view/login_redefinir.php?etapa=senha');
        exit;
    } catch (PDOException $e) {
        error_log('Falha ao verificar dados para recuperação de senha: ' . $e->getMessage());
        header('Location: ../view/login_redefinir.php?erro=sistema');
        exit;
    }
}

if ($acao === 'redefinir') {
    $idUsuario = (int) ($_SESSION['recuperacao_usuario_id'] ?? 0);
    $inicioRecuperacao = (int) ($_SESSION['recuperacao_inicio'] ?? 0);
    if ($idUsuario <= 0 || $inicioRecuperacao <= 0 || time() - $inicioRecuperacao > 900) {
        unset($_SESSION['recuperacao_usuario_id'], $_SESSION['recuperacao_inicio']);
        header('Location: ../view/login_redefinir.php?erro=expirada');
        exit;
    }

    $senhaNova = $_POST['senha_nova'] ?? '';
    $confirmacaoSenha = $_POST['confirmacao_senha'] ?? '';
    if (!is_string($senhaNova) || !is_string($confirmacaoSenha)) {
        header('Location: ../view/login_redefinir.php?etapa=senha&erro=dados');
        exit;
    }
    if (strlen($senhaNova) < 6) {
        header('Location: ../view/login_redefinir.php?etapa=senha&erro=senha_curta');
        exit;
    }
    if ($senhaNova !== $confirmacaoSenha) {
        header('Location: ../view/login_redefinir.php?etapa=senha&erro=confirmacao');
        exit;
    }

    try {
        require_once __DIR__ . '/../model/dao/Conexao.php';
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare(
            'UPDATE login SET senha_hash = :senha_hash WHERE id_usuario = :id_usuario'
        );
        $stmt->execute([
            ':senha_hash' => password_hash($senhaNova, PASSWORD_DEFAULT),
            ':id_usuario' => $idUsuario,
        ]);

        unset($_SESSION['recuperacao_usuario_id'], $_SESSION['recuperacao_inicio']);
        $_SESSION['recuperacao_csrf_token'] = bin2hex(random_bytes(32));

        header('Location: ../view/login_redefinir.php?sucesso=1');
        exit;
    } catch (PDOException $e) {
        error_log('Falha ao redefinir a senha do usuário ' . $idUsuario . ': ' . $e->getMessage());
        header('Location: ../view/login_redefinir.php?etapa=senha&erro=sistema');
        exit;
    }
}

header('Location: ../view/login_redefinir.php?erro=metodo');
exit;
