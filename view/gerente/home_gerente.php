<?php
// view/gerente/home_gerente.php
session_start();

// Verifica se é Gerente (perfil_id = 2)
if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Gerente - Dojify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilo Personalizado Dojify -->
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body>

    <header class="navbar">
        <div class="navbar-brand">
            <a href="home_gerente.php" class="logo-link">
                <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div>
                    <h1>Dojify</h1>
                </div>
            </a>
        </div>

        <div class="navbar-user">
            <span class="user-greeting">
                Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong>
            </span>

            <a href="home_gerente.php" class="btn btn-sm btn-light text-dark">
                Início
            </a>

            <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">
                Sair
            </a>
        </div>
    </header>

    <div class="container my-4">
        <h2 class="text-center mb-3">Painel do Gerente</h2>
        <p class="text-muted text-center mb-4">Painel de controle e gestão da sua academia. Selecione uma opção abaixo:</p>

        <!-- Mensagens de Feedback (Opcional caso queiras usar no futuro) -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert-sucesso">Operação realizada com sucesso!</div>
        <?php endif; ?>
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-erro">Não foi possível realizar a operação.</div>
        <?php endif; ?>

        <!-- Primeira Linha de Atalhos (3 Cartões) -->
        <div class="row g-4 justify-content-center mb-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border p-4 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-3">Cadastrar Aluno</h3>
                            <p class="text-muted small mb-4">Registe um novo aluno no sistema da sua academia.</p>
                        </div>
                        <a href="cadastrar_aluno.php" class="btn btn-success w-100">+ Cadastrar Aluno</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm border p-4 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-3">Cadastrar Professor</h3>
                            <p class="text-muted small mb-4">Registe um novo professor para lecionar nas turmas.</p>
                        </div>
                        <a href="cadastrar_professor.php" class="btn btn-info w-100 text-white">+ Cadastrar Professor</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm border p-4 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-3">Alunos & Professores</h3>
                            <p class="text-muted small mb-4">Visualize, pesquise e gerencie todos os seus utilizadores registados.</p>
                        </div>
                        <a href="listar_usuarios.php" class="btn btn-warning w-100">Gerir Alunos & Professores</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segunda Linha de Atalhos (2 Cartões Centralizados) -->
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border p-4 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-3">Modalidades</h3>
                            <p class="text-muted small mb-4">Configure e gira as modalidades oferecidas pela academia.</p>
                        </div>
                        <a href="listar_modalidade.php" class="btn btn-secondary w-100">Gerir Modalidades</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm border p-4 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-3">Turmas</h3>
                            <p class="text-muted small mb-4">Crie e organize as turmas, horários e atribuições.</p>
                        </div>
                        <a href="listar_turma.php" class="btn btn-dark w-100">Gerir Turmas</a>
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