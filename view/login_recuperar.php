<?php
// view/login_recuperar.php

// TEMOS QUE PENSAR NESSA PARTE AQUI AINDA, GEMINI SUGERIU Token-based Reset, PARA ISSO TEMOS QUE MUDAR O MySQL!
//ALTER TABLE usuarios ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL;
//ALTER TABLE usuarios ADD COLUMN reset_expires_at DATETIME DEFAULT NULL;

session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Dojify</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

    <!-- Navbar Global -->
    <header class="navbar">
        <div class="navbar-brand">
            <a href="../index.php" class="logo-link">
                <img src="../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div>
                    <h1>Dojify</h1>
                </div>
            </a>
        </div>
        <div class="navbar-user">
            <a href="login.php" class="btn btn-sm">Voltar ao Login</a>
        </div>
    </header>

    <!-- Container Principal -->
    <div class="container">
        <form action="../controller/LoginController.php" method="POST">
            
            <div class="text-center" style="margin-bottom: 20px;">
                <img src="../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Recuperar Senha</h2>
                <p class="text-muted" style="font-size: 0.85rem; margin-top: 8px;">Insira o seu e-mail registado para receber uma nova palavra-passe temporária.</p>
            </div>

            <!-- Mensagens de Feedback -->
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert-erro">
                    <?php 
                        if ($_GET['erro'] == 'email_nao_encontrado') {
                            echo 'Este e-mail não está registado no sistema.';
                        } else {
                            echo 'Ocorreu um erro ao processar o pedido.';
                        }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert-sucesso">
                    <?= htmlspecialchars($_GET['sucesso']) ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="acao" value="processar_recuperacao">

            <label for="email">E-mail de Acesso</label>
            <input type="email" id="email" name="email" placeholder="exemplo@email.com" required>

            <!-- Botão de Submissão -->
            <button type="submit">Gerar Nova Senha</button>

            <!-- Botão Voltar -->
            <a href="login.php" class="btn" style="width: 100%; margin-top: 10px; background-color: transparent; border: 1px solid var(--border-color); color: var(--text-primary) !important;">
                Cancelar
            </a>

        </form>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

</body>
</html>