<?php
session_start();

if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_usuario'] ?? 0) <= 0) {
    header('Location: login.php?erro=acesso_negado');
    exit;
}

if (!isset($_SESSION['senha_csrf_token'])) {
    $_SESSION['senha_csrf_token'] = bin2hex(random_bytes(32));
}

$mensagensErro = [
    'metodo_invalido' => 'Não foi possível processar a solicitação. Tente novamente.',
    'token' => 'Sua sessão expirou. Atualize a página e tente novamente.',
    'dados' => 'Preencha os campos com valores válidos.',
    'senha_curta' => 'A nova senha deve ter pelo menos 6 caracteres.',
    'confirmacao' => 'A nova senha e a confirmação não coincidem.',
    'senha_atual' => 'A senha atual está incorreta.',
    'sistema' => 'Não foi possível alterar sua senha. Tente novamente mais tarde.',
];
$erro = $mensagensErro[(string)($_GET['erro'] ?? '')] ?? null;
$sucesso = ($_GET['sucesso'] ?? '') === '1';
$perfilId = (int)($_SESSION['usuario']['perfil_id'] ?? 0);
$homeUrl = match ($perfilId) {
    1 => 'admin/home_admin.php',
    2 => 'gerente/home_gerente.php',
    3 => 'professor/home_professor.php',
    4 => 'aluno/home_aluno.php',
    default => 'login.php',
};
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar senha - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
    <main class="container my-4 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark btn-sm mb-4">
                    Voltar
                </a>

                <section class="card shadow-sm border-0 p-4">
                    <h1 class="fs-4 fw-bold mb-2">Alterar senha</h1>
                    <p class="text-muted mb-4">Confirme sua senha atual e escolha uma nova senha.</p>

                    <?php if ($erro !== null): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php elseif ($sucesso): ?>
                        <div class="alert alert-success" role="status">Sua senha foi alterada com sucesso.</div>
                    <?php endif; ?>

                    <form action="../controller/AlterarSenhaController.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['senha_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="mb-3">
                            <label for="senha_atual" class="form-label">Senha atual</label>
                            <input type="password" class="form-control" id="senha_atual" name="senha_atual" autocomplete="current-password" required>
                        </div>

                        <div class="mb-3">
                            <label for="senha_nova" class="form-label">Nova senha</label>
                            <input type="password" class="form-control" id="senha_nova" name="senha_nova" minlength="6" autocomplete="new-password" required>
                            <div class="form-text">Use pelo menos 6 caracteres.</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirmacao_senha" class="form-label">Confirme a nova senha</label>
                            <input type="password" class="form-control" id="confirmacao_senha" name="confirmacao_senha" minlength="6" autocomplete="new-password" required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">Salvar nova senha</button>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
