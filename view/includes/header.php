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
