<?php
// view/turma/cadastrar_turma.php

session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/TurmaDAO.php';

$dao = new \TurmaDAO();

$idAcademia = (int) $_SESSION['id_academia'];

$professores = $dao->listarProfessoresPorAcademia($idAcademia);
$modalidades = $dao->listarModalidadesPorAcademia($idAcademia);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar Turma - Dojify</title>

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
                <a href="listar_turma.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Gestão de Turmas
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="text-center mb-4">
                <h2 class="fs-4 fw-bold">Cadastrar Nova Turma</h2>
                <p class="text-muted">Cadastrar nova turma na sua academia.</p>
            </div>

            <form action="../../controller/TurmaController.php" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                
                <input type="hidden" name="acao" value="cadastrar">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome da turma: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" maxlength="100" required>
                    </div>

                    <div class="col-md-6">
                        <label for="id_usuario_professor" class="form-label">Professor: <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_usuario_professor" name="id_usuario_professor" required>
                            <option value="">Selecione o professor</option>
                            <?php foreach ($professores as $professor): ?>
                                <option value="<?= $professor['id_usuario'] ?>">
                                    <?= htmlspecialchars($professor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="id_modalidade" class="form-label">Modalidade: <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_modalidade" name="id_modalidade" required>
                            <option value="">Selecione a modalidade</option>
                            <?php foreach ($modalidades as $modalidade): ?>
                                <option value="<?= $modalidade['id_modalidade'] ?>">
                                    <?= htmlspecialchars($modalidade['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="capacidade" class="form-label">Capacidade: <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="capacidade" name="capacidade" min="1" required>
                    </div>

                    <div class="col-md-6">
                        <label for="nivel" class="form-label">Nível(opcional):</label>
                        <select class="form-select" id="nivel" name="nivel">
                            <option value="">Selecione o nível</option>
                            <option value="INICIANTE">Iniciante</option>
                            <option value="INTERMEDIARIO">Intermediário</option>
                            <option value="AVANCADO">Avançado</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status: <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="ATIVA">Ativa</option>
                            <option value="INATIVA">Inativa</option>
                        </select>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                        <a href="listar_turma.php" class="btn btn-secondary px-4 m-0 w-auto">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 m-0 w-auto">
                            <i class="bi bi-check-circle me-1"></i> Cadastrar
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>