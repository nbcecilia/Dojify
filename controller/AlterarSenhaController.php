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

$redirecionarComFeedback = static function (string $erro = '', bool $sucesso = false): void {
    $destino = $_POST['return_to'] ?? '';
    $partesDestino = is_string($destino) ? parse_url($destino) : false;
    $caminhoApp = rtrim(str_replace('\\', '/', dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '')))), '/');
    $caminhoDestino = is_array($partesDestino) ? ($partesDestino['path'] ?? '') : '';

    $retornoValido = is_array($partesDestino)
        && !isset($partesDestino['scheme'])
        && !isset($partesDestino['host'])
        && $caminhoDestino !== ''
        && ($caminhoApp === '' || $caminhoDestino === $caminhoApp || str_starts_with($caminhoDestino, $caminhoApp . '/'));

    if (!$retornoValido) {
        unset($_SESSION['senha_modal_feedback']);
        $destino = '../view/alterar_senha.php?' . ($sucesso ? 'sucesso=1' : 'erro=' . rawurlencode($erro));
    } else {
        $destino = $caminhoDestino;
        if (isset($partesDestino['query'])) {
            $destino .= '?' . $partesDestino['query'];
        }
        $_SESSION['senha_modal_feedback'] = $sucesso
            ? ['sucesso' => true]
            : ['erro' => $erro];
    }

    header('Location: ' . $destino);
    exit;
};

$tokenSessao = $_SESSION['senha_csrf_token'] ?? '';
$tokenFormulario = $_POST['csrf_token'] ?? '';
if (
    !is_string($tokenSessao) ||
    !is_string($tokenFormulario) ||
    $tokenSessao === '' ||
    !hash_equals($tokenSessao, $tokenFormulario)
) {
    $redirecionarComFeedback('token');
}

$senhaAtual = $_POST['senha_atual'] ?? '';
$senhaNova = $_POST['senha_nova'] ?? '';
$confirmacaoSenha = $_POST['confirmacao_senha'] ?? '';
if (!is_string($senhaAtual) || !is_string($senhaNova) || !is_string($confirmacaoSenha)) {
    $redirecionarComFeedback('dados');
}

if (strlen($senhaNova) < 6) {
    $redirecionarComFeedback('senha_curta');
}

if ($senhaNova !== $confirmacaoSenha) {
    $redirecionarComFeedback('confirmacao');
}

try {
    require_once __DIR__ . '/../model/dao/Conexao.php';
    $pdo = Conexao::getConexao();
    $idUsuario = (int)$_SESSION['usuario']['id_usuario'];

    $stmt = $pdo->prepare('SELECT senha_hash FROM login WHERE id_usuario = :id_usuario');
    $stmt->execute([':id_usuario' => $idUsuario]);
    $senhaHashAtual = $stmt->fetchColumn();

    if (!is_string($senhaHashAtual) || !password_verify($senhaAtual, $senhaHashAtual)) {
        $redirecionarComFeedback('senha_atual');
    }

    $stmt = $pdo->prepare('UPDATE login SET senha_hash = :senha_hash WHERE id_usuario = :id_usuario');
    $stmt->execute([
        ':senha_hash' => password_hash($senhaNova, PASSWORD_DEFAULT),
        ':id_usuario' => $idUsuario,
    ]);

    $_SESSION['senha_csrf_token'] = bin2hex(random_bytes(32));
    $redirecionarComFeedback('', true);
} catch (PDOException $e) {
    error_log('Falha ao alterar a senha do usuário ' . (int)$_SESSION['usuario']['id_usuario'] . ': ' . $e->getMessage());
    $redirecionarComFeedback('sistema');
}
