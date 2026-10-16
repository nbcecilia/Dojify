<?php
session_start();

if (!isset($_SESSION['recuperacao_csrf_token'])) {
    $_SESSION['recuperacao_csrf_token'] = bin2hex(random_bytes(32));
}

$recuperacaoAtiva = isset($_SESSION['recuperacao_usuario_id'], $_SESSION['recuperacao_inicio'])
    && (time() - (int) $_SESSION['recuperacao_inicio']) <= 900;

if (!$recuperacaoAtiva) {
    unset($_SESSION['recuperacao_usuario_id'], $_SESSION['recuperacao_inicio']);
}

$mensagensErro = [
    'metodo' => 'Não foi possível processar a solicitação. Tente novamente.',
    'token' => 'Sua sessão expirou. Atualize a página e tente novamente.',
    'dados' => 'Informe um CPF e uma data de nascimento válidos.',
    'nao_encontrado' => 'Os dados informados não conferem ou o usuário não está ativo.',
    'expirada' => 'A verificação expirou. Informe novamente seus dados.',
    'senha_curta' => 'A nova senha deve ter pelo menos 6 caracteres.',
    'confirmacao' => 'A nova senha e a confirmação não coincidem.',
    'sistema' => 'Não foi possível redefinir sua senha. Tente novamente mais tarde.',
];
$erro = $mensagensErro[(string) ($_GET['erro'] ?? '')] ?? null;
$sucesso = ($_GET['sucesso'] ?? '') === '1';
$mostrarModal = $recuperacaoAtiva && !$sucesso;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar-brand">
            <a href="../index.php" class="logo-link">
                <img src="../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div><h1>Dojify</h1></div>
            </a>
        </div>
        <div class="navbar-user">
            <a href="login.php" class="btn btn-sm btn-outline-secondary">Voltar ao login</a>
        </div>
    </header>

    <main class="container my-4 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <section class="card shadow-sm border-0 p-4">
                    <div class="text-center mb-4">
                        <img src="../assets/img/dojify_logo1.png" alt="Logo Dojify" class="img-fluid mb-3" style="width: 100px; height: auto;">
                        <h1 class="fs-4 fw-bold mb-2">Recuperar senha</h1>
                        <p class="text-muted mb-0">Confirme seu CPF e sua data de nascimento para continuar.</p>
                    </div>

                    <?php if ($erro !== null): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php elseif ($sucesso): ?>
                        <div class="alert alert-success" role="status">
                            Senha redefinida com sucesso. Você já pode entrar com a nova senha.
                        </div>
                    <?php endif; ?>

                    <form action="../controller/RecuperarSenhaController.php" method="POST">
                        <input type="hidden" name="acao" value="verificar">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['recuperacao_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="mb-3">
                            <label for="cpf" class="form-label">CPF (somente números)</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" autocomplete="off" required>
                        </div>

                        <div class="mb-4">
                            <label for="data_nascimento" class="form-label">Data de nascimento</label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">Verificar dados</button>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <?php if ($mostrarModal): ?>
        <div class="modal fade" id="novaSenhaModal" tabindex="-1" aria-labelledby="novaSenhaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="../controller/RecuperarSenhaController.php" method="POST">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="novaSenhaModalLabel">Escolha uma nova senha</h2>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="acao" value="redefinir">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['recuperacao_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="mb-3">
                                <label for="senha_nova" class="form-label">Nova senha</label>
                                <input type="password" class="form-control" id="senha_nova" name="senha_nova" minlength="6" autocomplete="new-password" required>
                                <div class="form-text">Use pelo menos 6 caracteres.</div>
                            </div>

                            <div>
                                <label for="confirmacao_senha" class="form-label">Confirme a nova senha</label>
                                <input type="password" class="form-control" id="confirmacao_senha" name="confirmacao_senha" minlength="6" autocomplete="new-password" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="login_redefinir.php" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-dark">Salvar nova senha</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($mostrarModal): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('novaSenhaModal')).show();
            });
        </script>
    <?php endif; ?>
</body>
</html>
