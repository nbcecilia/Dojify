<?php
// view/gerente/editar_modalidade.php

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

$id = (int)($_GET['id'] ?? 0);
$idAcademia = (int)$_SESSION['id_academia'];

if ($id <= 0) {
    header('Location: listar_modalidade.php');
    exit;
}

$dao = new ModalidadeDAO();

$modalidade = $dao->buscarPorId($id, $idAcademia);

if (!$modalidade) {
    header('Location: listar_modalidade.php?erro=1');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar Modalidade - Dojify</title>

    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <main class="conteudo">

        <h1>Editar Modalidade</h1>

        <form action="../../controller/ModalidadeController.php" method="POST">

            <input type="hidden" name="acao" value="atualizar">

            <input
                type="hidden"
                name="id_modalidade"
                value="<?= $modalidade['id_modalidade'] ?>"
            >

            <div class="campo">

                <label for="nome">Nome da modalidade</label>

                <input
                    type="text"
                    id="nome"
                    name="nome"
                    maxlength="100"
                    value="<?= htmlspecialchars($modalidade['nome']) ?>"
                    required
                >

            </div>

            <div class="campo">

                <label for="descricao">Descrição</label>

                <textarea
                    id="descricao"
                    name="descricao"
                    rows="5"
                ><?= htmlspecialchars($modalidade['descricao'] ?? '') ?></textarea>

            </div>

            <button type="submit">
                Salvar alterações
            </button>

            <a href="listar_modalidade.php">
                Cancelar
            </a>

        </form>

    </main>

</body>

</html>