<?php
// view/gerente/listar_plano.php
session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/PlanoDAO.php';

$dao = new PlanoDAO();
$idAcademia = (int) $_SESSION['id_academia'];
$planos = $dao->listarPorAcademia($idAcademia);
if (!isset($_SESSION['plano_status_csrf_token'])) {
    $_SESSION['plano_status_csrf_token'] = bin2hex(random_bytes(32));
}

// Agrupar planos por aluno
$alunosAgrupados = [];
foreach ($planos as $plano) {
    $idAluno = $plano['id_usuario_aluno'];
    if (!isset($alunosAgrupados[$idAluno])) {
        $alunosAgrupados[$idAluno] = [
            'nome' => $plano['aluno_nome'],
            'planos' => []
        ];
    }
    $alunosAgrupados[$idAluno]['planos'][] = $plano;
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <style>
        .plano-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.5rem;
        }

        .plano-status-form {
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

        .table .plano-status-form > button[type="submit"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: auto;
            margin: 0;
            padding: 5px 10px;
            font-size: 0.8rem;
            line-height: 1.5;
            border: 1px solid transparent;
            border-radius: 4px;
            font-weight: 500;
            white-space: nowrap;
        }

        .table .plano-status-form > button.btn-danger[type="submit"] {
            background-color: var(--action-danger);
            color: #ffffff;
        }

        .table .plano-status-form > button.btn-danger[type="submit"]:hover {
            background-color: var(--action-danger-hover);
        }

        .table .plano-status-form > button.btn-success[type="submit"] {
            background-color: var(--action-submit);
            color: #ffffff;
        }

        .table .plano-status-form > button.btn-success[type="submit"]:hover {
            background-color: var(--action-submit-hover);
        }
    </style>

</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="cadastrar_plano.php" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i> Cadastrar Plano
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
                <h2>Planos dos Alunos</h2>
                <p class="text-muted">Consulte e gira os planos ativos na academia.</p>
            </div>

            <?php if (empty($alunosAgrupados)): ?>
                <p class="text-center text-muted py-4">Nenhum plano cadastrado.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Aluno</th>
                                <th>Plano</th>
                                <th>Modalidade</th>
                                <th>Valor (R$)</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alunosAgrupados as $dadosAluno): ?>
                                <?php 
                                $totalPlanos = count($dadosAluno['planos']);
                                foreach ($dadosAluno['planos'] as $index => $plano): 
                                ?>
                                    <tr <?= $index > 0 ? 'class="aluno-sub-linha"' : '' ?>>
                                        <?php if ($index === 0): ?>
                                            <td rowspan="<?= $totalPlanos ?>" class="align-middle bg-white">
                                                <strong><?= htmlspecialchars($dadosAluno['nome']) ?></strong>
                                            </td>
                                        <?php endif; ?>
                                        <td><?= htmlspecialchars($plano['nome_plano']) ?></td>
                                        <td>
                                            <span class="badge bg-dark">
                                                <?= htmlspecialchars($plano['modalidade_nome'] ?? $plano['nome_modalidade'] ?? $plano['modalidade'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td>R$ <?= number_format($plano['valor'], 2, ',', '.') ?></td>
                                        <td><?= date('d/m/Y', strtotime($plano['data_inicio'])) ?></td>
                                        <td><?= $plano['data_fim'] ? date('d/m/Y', strtotime($plano['data_fim'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($plano['status']) ?></td>
                                        <td class="text-end">
                                            <div class="plano-actions">
                                            <a href="editar_plano.php?id=<?= $plano['id_plano'] ?>" class="btn btn-info btn-sm">
                                                <i class="bi bi-pencil me-1"></i> Editar
                                            </a>
                                            <?php if (strtoupper((string)$plano['status']) === 'INATIVO'): ?>
                                                <form action="../../controller/PlanoController.php?acao=reativar" method="POST" class="plano-status-form" onsubmit="return confirm('Deseja reativar este plano?');">
                                                    <input type="hidden" name="id" value="<?= (int)$plano['id_plano'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['plano_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Ativar
                                                    </button>
                                                </form>
                                            <?php elseif (strtoupper((string)$plano['status']) === 'ATIVO'): ?>
                                                <form action="../../controller/PlanoController.php?acao=desativar" method="POST" class="plano-status-form" onsubmit="return confirm('Deseja desativar este plano?');">
                                                    <input type="hidden" name="id" value="<?= (int)$plano['id_plano'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['plano_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="bi bi-power me-1"></i> Desativar
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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