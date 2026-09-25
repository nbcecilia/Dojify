<?php
// view/admin/home_admin.php
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
    <title>Dashboard Admin - Dojify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilo Personalizado Dojify -->
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
            <span class="user-greeting">Logado como: <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong> (Suporte Técnico)</span>
            <a href="../../controller/LoginController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>
    
    <div class="container my-4">
        <h2 class="text-center mb-3">Painel do Administrador</h2>
        <p class="text-muted text-center mb-4">Bem-vindo ao sistema de suporte técnico do Dojify. Selecione uma opção abaixo para gerir o ecossistema:</p>

        <!-- Mensagens de Feedback -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert-sucesso">Operação realizada com sucesso!</div>
        <?php endif; ?>
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-erro">Não foi possível realizar a operação. Verifique os dados fornecidos.</div>
        <?php endif; ?>

        <!-- Atalhos Principais com Grelha do Bootstrap -->
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border p-4 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-3">Academias & Gerentes</h3>
                            <p class="text-muted small mb-4">Visualize, pesquise e gerencie todas as academias e os respetivos gestores.</p>
                        </div>
                        <a href="listar_academias_gerentes.php" class="btn btn-warning w-100">Gerir Academias</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm border p-4 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-3">Nova Academia</h3>
                            <p class="text-muted small mb-4">Registe uma nova academia no sistema.</p>
                        </div>
                        <a href="cadastrar_academia.php" class="btn btn-success w-100">+ Cadastrar Academia</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm border p-4 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-3">Novo Gerente</h3>
                            <p class="text-muted small mb-4">Cadastre um novo gestor para uma academia.</p>
                        </div>
                        <a href="cadastrar_gerente.php" class="btn btn-info w-100">+ Cadastrar Gerente</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>