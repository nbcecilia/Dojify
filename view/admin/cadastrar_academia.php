<?php
// view/admin/cadastrar_academia.php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 1) { 
    header('Location: ../login.php'); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Academia - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar-brand">
            <a href="home_admin.php" class="logo-link">
                <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div>
                    <h1>Dojify</h1>
                </div>
            </a>
        </div>
        
        <div class="navbar-user">
            <span class="user-greeting">Olá, <strong><strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></strong></span>
            <a href="home_admin.php" class="btn btn-sm btn-voltar">Voltar</a>
            <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <div class="container">

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-erro">Erro ao cadastrar. Verifique os dados fornecidos ou se o documento já está cadastrado.</div>
        <?php endif; ?>

        <form action="../../controller/AcademiaController.php" method="POST">
            <div class="text-center" style="margin-bottom: 20px;">
                <img src="../../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Cadastrar Academia</h2>
            </div>
            <input type="hidden" name="acao" value="cadastrar">
            
            <label>Nome da Academia:</label>
            <input type="text" name="nome" required>

            <label>Documento (CNPJ ou CPF do responsável):</label>
            <input type="text" name="documento" placeholder="Apenas números" required>

            <label>Endereço Completo:</label>
            <input type="text" name="endereco" required>

            <label>Telefone:</label>
            <input type="text" name="telefone" required>

            <label>E-mail de Contato:</label>
            <input type="email" name="email" required>

            <button type="submit">Cadastrar Academia</button>
             <div class="navbar-user"></div>
        </form>
    </div>
    <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>