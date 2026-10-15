<?php
session_start();

if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_usuario'] ?? 0) <= 0) {
    header('Location: ../view/login.php?erro=acesso_negado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/alterar_senha.php?erro=metodo_invalido');
    exit;
}

$tokenSessao = $_SESSION['senha_csrf_token'] ?? '';
$tokenFormulario = $_POST['csrf_token'] ?? '';
if (
    !is_string($tokenSessao) ||
    !is_string($tokenFormulario) ||
    $tokenSessao === '' ||
    !hash_equals($tokenSessao, $tokenFormulario)
) {
    header('Location: ../view/alterar_senha.php?erro=token');
    exit;
}

$senhaAtual = $_POST['senha_atual'] ?? '';
$senhaNova = $_POST['senha_nova'] ?? '';
$confirmacaoSenha = $_POST['confirmacao_senha'] ?? '';
if (!is_string($senhaAtual) || !is_string($senhaNova) || !is_string($confirmacaoSenha)) {
    header('Location: ../view/alterar_senha.php?erro=dados');
    exit;
}

if (strlen($senhaNova) < 6) {
    header('Location: ../view/alterar_senha.php?erro=senha_curta');
    exit;
}

if ($senhaNova !== $confirmacaoSenha) {
    header('Location: ../view/alterar_senha.php?erro=confirmacao');
    exit;
}

try {
    require_once __DIR__ . '/../model/dao/Conexao.php';
    $pdo = Conexao::getConexao();
    $idUsuario = (int)$_SESSION['usuario']['id_usuario'];

    $stmt = $pdo->prepare('SELECT senha_hash FROM login WHERE id_usuario = :id_usuario');
    $stmt->execute([':id_usuario' => $idUsuario]);
    $senhaHashAtual = $stmt->fetchColumn();

    if (!is_string($senhaHashAtual) || !password_verify($senhaAtual, $senhaHashAtual)) {
        header('Location: ../view/alterar_senha.php?erro=senha_atual');
        exit;
    }

    $stmt = $pdo->prepare('UPDATE login SET senha_hash = :senha_hash WHERE id_usuario = :id_usuario');
    $stmt->execute([
        ':senha_hash' => password_hash($senhaNova, PASSWORD_DEFAULT),
        ':id_usuario' => $idUsuario,
    ]);

    header('Location: ../view/alterar_senha.php?sucesso=1');
    exit;
} catch (PDOException $e) {
    error_log('Falha ao alterar a senha do usuário ' . (int)$_SESSION['usuario']['id_usuario'] . ': ' . $e->getMessage());
    header('Location: ../view/alterar_senha.php?erro=sistema');
    exit;
}
