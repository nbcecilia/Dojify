<?php
// view/gerente/listar_modalidade.php

session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/ModalidadeDAO.php';

$dao = new ModalidadeDAO();

$idAcademia = (int) $_SESSION['id_academia'];

$modalidades = $dao->listarPorAcademia($idAcademia);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Modalidades - Dojify</title>

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

            <a href="home_gerente.php" class="btn btn-sm">
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


    <main class="container">

        <h2>Modalidades</h2>

        <?php if (isset($_GET['sucesso'])): ?>

            <div class="alert-sucesso">
                Operação realizada com sucesso!
            </div>

        <?php endif; ?>


        <?php if (isset($_GET['erro'])): ?>

            <div class="alert-erro">
                Não foi possível realizar a operação.
            </div>

        <?php endif; ?>


        <div class="acoes-topbar">

            <a
                href="cadastrar_modalidade.php"
                class="btn btn-success"
            >
                Cadastrar modalidade
            </a>

        </div>


        <?php if (empty($modalidades)): ?>

            <p class="text-center text-muted">
                Nenhuma modalidade cadastrada.
            </p>

        <?php else: ?>

            <table>

                <thead>

                    <tr>

                        <th>Nome</th>

                        <th>Descrição</th>

                        <th>Ações</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($modalidades as $modalidade): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($modalidade['nome']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($modalidade['descricao'] ?? '') ?>
                            </td>

                            <td>

                                <a
                                    href="editar_modalidade.php?id=<?= $modalidade['id_modalidade'] ?>"
                                    class="btn btn-sm btn-info"
                                >
                                    Editar
                                </a>

                                <a
                                    href="../../controller/ModalidadeController.php?acao=excluir&id=<?= $modalidade['id_modalidade'] ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Deseja realmente excluir esta modalidade?');"
                                >
                                    Excluir
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </main>


    <footer class="footer">

        <p>
            &copy; <?php echo date('Y'); ?> Dojify.
            Todos os direitos reservados.
        </p>

    </footer>

</body>

</html>