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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="listar_modalidade.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="text-center mb-4">
                <h2 class="fs-4 fw-bold">Editar Modalidade</h2>
            </div>

            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger" role="alert">
                    Não foi possível salvar as alterações. Verifique o nome informado e tente novamente.
                </div>
            <?php endif; ?>

            <form action="../../controller/ModalidadeController.php" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">

                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="id_modalidade" value="<?= $modalidade['id_modalidade'] ?>">

                <div class="row g-3">
                    <div class="col-12">
                        <label for="nome" class="form-label">Nome da modalidade: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" maxlength="100" value="<?= htmlspecialchars($modalidade['nome']) ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="descricao" class="form-label">Descrição:</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="5"><?= htmlspecialchars($modalidade['descricao'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                        <a href="listar_modalidade.php" class="btn btn-secondary px-4 m-0 w-auto">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 m-0 w-auto">
                            <i class="bi bi-check-circle me-1"></i> Salvar alterações
                        </button>
                    </div>
                </div>

            </form>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>