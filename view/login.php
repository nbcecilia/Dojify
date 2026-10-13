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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            <a href="../index.php" class="btn btn-sm btn-outline-secondary">Início</a>
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
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        if ($_GET['erro'] == 'credenciais_incorretas') {
                            echo 'E-mail ou palavra-passe incorretos!';
                        } elseif ($_GET['erro'] == 'campos_vazios') {
                            echo 'Por favor, preencha todos os campos.';
                        } else {
                            echo 'E-mail ou palavra-passe inválidos!';
                        }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <input type="hidden" name="acao" value="logar">

            <div class="mb-3 text-start">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="exemplo@email.com" required>
            </div>

            <div class="mb-3 text-start">
                <label for="senha" class="form-label">Palavra-passe</label>
                <input type="password" class="form-control" id="senha" name="senha" placeholder="••••••••" required>
            </div>

            <!-- Link de Recuperação de Senha -->
            <div style="text-align: right; margin-top: 8px; margin-bottom: 16px;">
                <a href="login_redefinir.php" style="font-size: 0.8rem; text-decoration: none;" class="text-muted">Esqueceu a palavra-passe?</a>
            </div>

            <div class="d-flex gap-3 justify-content-end align-items-center">
                <a href="../index.php" class="btn btn-secondary px-4 m-0 w-auto">
                    Voltar
                </a>
                <button type="submit" class="btn btn-dark px-4 m-0 w-auto">Entrar</button>
            </div>

        </form>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>