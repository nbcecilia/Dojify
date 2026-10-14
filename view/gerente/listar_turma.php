<?php
// view/gerente/listar_turma.php
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
$turmas = $dao->listarPorAcademia($idAcademia);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turmas - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="cadastrar_turma.php" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i> Cadastrar Turma
                </a>
            </div>

        </header>

        <section class="card shadow-sm border-0 p-4">
            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Operação realizada com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Não foi possível realizar a operação.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h2>Gestão de Turmas e Horários</h2>
                <p class="text-muted">Gestão das turmas e horários da academia.</p>
            </div>

            <?php if (empty($turmas)): ?>
                <p class="text-center text-muted py-4">Nenhuma turma cadastrada.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Turma</th>
                                <th>Professor</th>
                                <th>Modalidade</th>
                                <th>Capacidade</th>
                                <th>Nível</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($turmas as $turma): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($turma['nome']) ?></strong></td>
                                    <td><?= htmlspecialchars($turma['professor_nome']) ?></td>
                                    <td><?= htmlspecialchars($turma['modalidade_nome']) ?></td>
                                    <td><?= htmlspecialchars($turma['capacidade']) ?></td>
                                    <td><?= htmlspecialchars($turma['nivel']) ?></td>
                                    <td><?= htmlspecialchars($turma['status']) ?></td>
                                    <td class="text-end">
                                        <a href="gerenciar_horarios_turma.php?id=<?= $turma['id_turma'] ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-clock me-1"></i> Gerir Horário
                                        </a>
                                        <a href="editar_turma.php?id=<?= $turma['id_turma'] ?>" class="btn btn-info btn-sm">
                                            <i class="bi bi-pencil me-1"></i> Editar
                                        </a>
                                        <a href="../../controller/TurmaController.php?acao=excluir&id=<?= $turma['id_turma'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Deseja realmente excluir esta turma?');">
                                            <i class="bi bi-trash me-1"></i> Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>