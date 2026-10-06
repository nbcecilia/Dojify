<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['usuario'])) {
    http_response_code(403);
    exit('Acesso não autorizado.');
}
$idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
if ($idUsuario <= 0) {
    http_response_code(403);
    exit('Usuário inválido.');
}

$returnTo = (string)($_POST['return_to'] ?? '');
$returnPath = parse_url($returnTo, PHP_URL_PATH);
if (
    !is_string($returnPath) ||
    $returnPath === '' ||
    $returnPath[0] !== '/' ||
    str_starts_with($returnPath, '//') ||
    str_contains($returnTo, '\\') ||
    preg_match('/[\r\n]/', $returnTo) ||
    parse_url($returnTo, PHP_URL_SCHEME) !== null ||
    parse_url($returnTo, PHP_URL_HOST) !== null
) {
    $perfilId = (int)($_SESSION['usuario']['perfil_id'] ?? 0);
    $returnTo = match ($perfilId) {
        4 => '../view/aluno/home_aluno.php',
        3 => '../view/professor/home_professor.php',
        2 => '../view/gerente/home_gerente.php',
        default => '../view/login.php',
    };
}

$redirect = static function (string $feedback) use ($returnTo): void {
    $_SESSION['avatar_feedback'] = $feedback;
    header('Location: ' . $returnTo);
    exit;
};

$tokenSessao = $_SESSION['avatar_csrf_token'] ?? '';
$tokenFormulario = $_POST['csrf_token'] ?? '';
if (
    !is_string($tokenSessao) ||
    !is_string($tokenFormulario) ||
    $tokenSessao === '' ||
    !hash_equals($tokenSessao, $tokenFormulario)
) {
    $redirect('falha');
}

$diretorioAvatar = dirname(__DIR__) . '/assets/uploads/avatars';
if (($_POST['acao'] ?? '') === 'remover') {
    $remocaoOk = true;
    foreach (['webp', 'png', 'jpg', 'jpeg'] as $extensaoAvatar) {
        $arquivoAvatar = $diretorioAvatar . '/user-' . $idUsuario . '.' . $extensaoAvatar;
        if (is_file($arquivoAvatar) && !unlink($arquivoAvatar)) {
            $remocaoOk = false;
            error_log('Não foi possível remover a foto de perfil do usuário ' . $idUsuario . '.');
        }
    }
    $redirect($remocaoOk ? 'sucesso_remocao' : 'falha');
}

$arquivoEnviado = $_FILES['avatar'] ?? null;
if (
    !is_array($arquivoEnviado) ||
    ($arquivoEnviado['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
    !isset($arquivoEnviado['tmp_name'], $arquivoEnviado['size']) ||
    !is_string($arquivoEnviado['tmp_name']) ||
    !is_uploaded_file($arquivoEnviado['tmp_name']) ||
    (int)$arquivoEnviado['size'] <= 0 ||
    (int)$arquivoEnviado['size'] > 2 * 1024 * 1024 ||
    filesize($arquivoEnviado['tmp_name']) > 2 * 1024 * 1024
) {
    $redirect('falha');
}

$imagem = @getimagesize($arquivoEnviado['tmp_name']);
$tiposPermitidos = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];
if (!is_array($imagem) || !isset($tiposPermitidos[$imagem['mime'] ?? ''])) {
    $redirect('falha');
}
if (
    $imagem[0] <= 0 ||
    $imagem[1] <= 0 ||
    $imagem[0] > 6000 ||
    $imagem[1] > 6000 ||
    $imagem[0] * $imagem[1] > 20000000
) {
    $redirect('falha');
}

if (!class_exists('finfo')) {
    error_log('A extensão Fileinfo é necessária para validar fotos de perfil.');
    $redirect('falha');
}
$finfo = new finfo(FILEINFO_MIME_TYPE);
if ($finfo->file($arquivoEnviado['tmp_name']) !== $imagem['mime']) {
    $redirect('falha');
}

if (!is_dir($diretorioAvatar) && !mkdir($diretorioAvatar, 0750, true) && !is_dir($diretorioAvatar)) {
    error_log('Não foi possível criar o diretório de avatares.');
    $redirect('falha');
}

$extensao = $tiposPermitidos[$imagem['mime']];
$destino = $diretorioAvatar . '/user-' . $idUsuario . '.' . $extensao;
$temporario = $diretorioAvatar . '/.avatar-' . bin2hex(random_bytes(12)) . '.tmp';
if (!move_uploaded_file($arquivoEnviado['tmp_name'], $temporario) || !rename($temporario, $destino)) {
    if (is_file($temporario)) {
        unlink($temporario);
    }
    error_log('Não foi possível salvar a foto de perfil do usuário ' . $idUsuario . '.');
    $redirect('falha');
}

foreach (array_values($tiposPermitidos) as $extensaoAntiga) {
    $arquivoAntigo = $diretorioAvatar . '/user-' . $idUsuario . '.' . $extensaoAntiga;
    if ($arquivoAntigo !== $destino && is_file($arquivoAntigo) && !unlink($arquivoAntigo)) {
        error_log('Não foi possível remover uma foto de perfil antiga do usuário ' . $idUsuario . '.');
    }
}

$redirect('sucesso');
