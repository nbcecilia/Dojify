<?php
// view/gerente/cadastrar_modalidade.php

session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar Modalidade - Dojify</title>

    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <main class="conteudo">
            <form action="../../controller/ModalidadeController.php" method="POST">
            <div class="text-center">
                <img src="../../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Cadastrar Modalidade</h2>
            </div>

            <input type="hidden" name="acao" value="cadastrar">

            <div class="campo">
                <label for="nome">Nome da modalidade</label>

                <input
                    type="text"
                    id="nome"
                    name="nome"
                    maxlength="100"
                    required
                >
            </div>

            <div class="campo">
                <label for="descricao">Descrição</label>

                <textarea
                    id="descricao"
                    name="descricao"
                    rows="5"
                ></textarea>
            </div>

            <button type="submit">
                Cadastrar
            </button>

            <a href="listar_modalidade.php">
                Voltar
            </a>

        </form>

    </main>

</body>

</html>