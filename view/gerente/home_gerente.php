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
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body>

    <header class="navbar">

        <div class="navbar-brand">

            <a href="home_gerente.php" class="logo-link">

                <img
                    src="../../assets/img/Dojify_original2.png"
                    alt="Dojify Logo"
                    class="navbar-logo"
                >

                <div>
                    <h1>Dojify</h1>
                </div>

            </a>

        </div>

        <div class="navbar-user">

            <span class="user-greeting">
                Olá,
                <strong>
                    <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>
                </strong>
            </span>

            <a href="home_gerente.php" class="btn btn-sm btn-voltar">
                Início
            </a>

            <a
                href="../../controller/UsuarioController.php?acao=logout"
                class="btn btn-sm btn-danger"
            >
                Sair
            </a>

        </div>

    </header>


    <div class="container">

        <div class="card shadow-sm p-4">

            <div class="text-center logo-header-painel">

                <h2>Painel do Gerente</h2>

            </div>

            <p class="lead text-center">
                Painel de controle e gestão da sua academia.
            </p>

            <hr>


            <div class="acoes-topbar">

                <a
                    href="listar_usuarios.php"
                    class="btn btn-dark"
                >
                    Gerir Alunos e Professores
                </a>

                <a
                    href="listar_modalidade.php"
                    class="btn btn-dark"
                >
                    Gerir Modalidades
                </a>

            </div>

        </div>

    </div>


    <footer class="footer">

        <p>
            &copy; <?php echo date('Y'); ?> Dojify.
            Todos os direitos reservados.
        </p>

    </footer>

</body>
</html>