<?php
// view/gerente/editar_turma.php
session_start();

// Valida se o usuário é gerente (perfil 2) e se possui academia na sessão
if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2 || !isset($_SESSION['usuario']['id_academia'])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/TurmaDAO.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$idAcademia = (int) $_SESSION['usuario']['id_academia'];

if (!$id) {
    header('Location: listar_turma.php?erro=id_invalido');
    exit;
}

$dao = new TurmaDAO();
$turma = $dao->buscarPorId($id, $idAcademia);

if (!$turma) {
    header('Location: listar_turma.php?erro=nao_encontrado');
    exit;
}

$professores = $dao->listarProfessoresPorAcademia($idAcademia);
$modalidades = $dao->listarModalidadesPorAcademia($idAcademia);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Turma - Dojify</title>
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
                    <i class="bi bi-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="text-center mb-4">
                <h2 class="fs-4 fw-bold">Editar Turma</h2>
            </div>

            <form action="../../controller/TurmaController.php?acao=atualizar" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <!-- ID Oculto da Turma -->
                <input type="hidden" name="id_turma" value="<?= $turma['id_turma'] ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome da Turma: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" maxlength="100" value="<?= htmlspecialchars($turma['nome']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="id_usuario_professor" class="form-label">Professor: <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_usuario_professor" name="id_usuario_professor" required>
                            <option value="">Selecione o professor</option>
                            <?php foreach ($professores as $professor): ?>
                                <option value="<?= $professor['id_usuario'] ?>" <?= $professor['id_usuario'] == $turma['id_usuario_professor'] ? 'selected' : '' ?>>
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
                                <option value="<?= $modalidade['id_modalidade'] ?>" <?= $modalidade['id_modalidade'] == $turma['id_modalidade'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($modalidade['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="capacidade" class="form-label">Capacidade: <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="capacidade" name="capacidade" min="1" value="<?= htmlspecialchars($turma['capacidade']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="nivel" class="form-label">Nível: <span class="text-danger">*</span></label>
                        <select class="form-select" id="nivel" name="nivel" required>
                            <option value="">Selecione o nível</option>
                            <option value="INICIANTE" <?= $turma['nivel'] == 'INICIANTE' ? 'selected' : '' ?>>Iniciante</option>
                            <option value="INTERMEDIARIO" <?= $turma['nivel'] == 'INTERMEDIARIO' ? 'selected' : '' ?>>Intermediário</option>
                            <option value="AVANCADO" <?= $turma['nivel'] == 'AVANCADO' ? 'selected' : '' ?>>Avançado</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status: <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="ATIVA" <?= $turma['status'] == 'ATIVA' ? 'selected' : '' ?>>Ativa</option>
                            <option value="INATIVA" <?= $turma['status'] == 'INATIVA' ? 'selected' : '' ?>>Inativa</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                        <a href="listar_turma.php" class="btn btn-secondary px-4 m-0 w-auto">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 m-0 w-auto">
                            <i class="bi bi-check-circle me-1"></i> Salvar Alterações
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