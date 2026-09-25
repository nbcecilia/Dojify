<?php
// view/login.php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dojify</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

    <!-- Navbar Global do Sistema -->
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
            <a href="../index.php" class="btn btn-sm">Início</a>
        </div>
    </header>

    <!-- Container padrão do teu estilo.css -->
    <div class="container">
        
        <!-- O formulário herda diretamente as regras gerais de 'form' do teu estilo.css -->
        <form action="../controller/LoginController.php" method="POST">
            
            <div class="text-center" style="margin-bottom: 20px;">
                <img src="../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Acesso ao Sistema</h2>
            </div>

            <!-- Mensagem de Erro -->
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert-erro">
                    <?php 
                        if ($_GET['erro'] == 'credenciais_incorretas') {
                            echo 'E-mail ou palavra-passe incorretos!';
                        } elseif ($_GET['erro'] == 'campos_vazios') {
                            echo 'Por favor, preencha todos os campos.';
                        } else {
                            echo 'E-mail ou palavra-passe inválidos!';
                        }
                    ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="acao" value="logar">

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" placeholder="exemplo@email.com" required>

            <label for="senha">Palavra-passe</label>
            <input type="password" id="senha" name="senha" placeholder="••••••••" required>

            <!-- Link de Recuperação de Senha -->
            <div style="text-align: right; margin-top: 8px; margin-bottom: 4px;">
                <a href="login_recuperar.php" style="font-size: 0.8rem; text-decoration: none;" class="text-muted">Esqueceu a palavra-passe?</a>
            </div>

            <!-- Botão de Submissão -->
            <button type="submit">Entrar</button>

            <!-- Botão Voltar -->
            <a href="../index.php" class="btn" style="width: 100%; margin-top: 10px; background-color: transparent; border: 1px solid var(--border-color); color: var(--text-primary) !important;">
                Voltar
            </a>

        </form>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

</body>
</html>