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
if (!isset($_SESSION['turma_status_csrf_token'])) {
    $_SESSION['turma_status_csrf_token'] = bin2hex(random_bytes(32));
}
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
    <style>
        .turma-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }

        .turma-status-form {
            display: inline-flex;
            align-items: center;
            width: auto;
            max-width: none;
            margin: 0;
            padding: 0;
            border: 0;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
        }

        .table .turma-status-form > button[type="submit"] {
            width: auto;
            margin: 0;
            padding: 5px 10px;
            font-size: 0.8rem;
            line-height: 1.5;
            border-radius: 4px;
            border: 1px solid transparent;
            font-weight: 500;
            white-space: nowrap;
        }

        .table .turma-status-form > button.btn-danger[type="submit"] {
            background-color: var(--action-danger);
            color: #ffffff;
        }

        .table .turma-status-form > button.btn-danger[type="submit"]:hover {
            background-color: var(--action-danger-hover);
        }

        .table .turma-status-form > button.btn-success[type="submit"] {
            background-color: var(--action-submit);
            color: #ffffff;
        }

        .table .turma-status-form > button.btn-success[type="submit"]:hover {
            background-color: var(--action-submit-hover);
        }
    </style>
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
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($turmas as $turma): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($turma['nome']) ?></strong></td>
                                    <td><?= htmlspecialchars($turma['professor_nome']) ?></td>
                                    <td><?= htmlspecialchars($turma['modalidade_nome']) ?></td>
                                    <td><?= htmlspecialchars($turma['capacidade']) ?></td>
                                    <td><?= htmlspecialchars($turma['status']) ?></td>
                                    <td class="text-center">
                                        <div class="turma-actions">
                                        <a href="gerenciar_horarios_turma.php?id=<?= $turma['id_turma'] ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-clock me-1"></i> Gerir Horário
                                        </a>
                                        <a href="editar_turma.php?id=<?= $turma['id_turma'] ?>" class="btn btn-info btn-sm">
                                            <i class="bi bi-pencil me-1"></i> Editar
                                        </a>
                                        <?php if (strtoupper((string)$turma['status']) === 'INATIVA'): ?>
                                            <form action="../../controller/TurmaController.php?acao=reativar" method="POST" class="turma-status-form" onsubmit="return confirm('Deseja reativar esta turma?');">
                                                <input type="hidden" name="id" value="<?= (int)$turma['id_turma'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['turma_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Ativar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="../../controller/TurmaController.php?acao=desativar" method="POST" class="turma-status-form" onsubmit="return confirm('Deseja desativar esta turma?');">
                                                <input type="hidden" name="id" value="<?= (int)$turma['id_turma'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['turma_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-power me-1"></i> Desativar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        </div>
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