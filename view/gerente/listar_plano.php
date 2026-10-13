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
            <div>
                <a href="home_gerente.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar ao Painel
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

            <?php if (empty($planos)): ?>
                <p class="text-center text-muted py-4">Nenhum plano cadastrado.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Aluno</th>
                                <th>Plano</th>
                                <th>Valor (R$)</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($planos as $plano): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($plano['aluno_nome']) ?></strong></td>
                                    <td><?= htmlspecialchars($plano['nome_plano']) ?></td>
                                    <td>R$ <?= number_format($plano['valor'], 2, ',', '.') ?></td>
                                    <td><?= date('d/m/Y', strtotime($plano['data_inicio'])) ?></td>
                                    <td><?= $plano['data_fim'] ? date('d/m/Y', strtotime($plano['data_fim'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($plano['status']) ?></td>
                                    <td class="text-end">
                                        <a href="editar_plano.php?id=<?= $plano['id_plano'] ?>" class="btn btn-info btn-sm">
                                            <i class="bi bi-pencil me-1"></i> Editar
                                        </a>
                                        <a href="../../controller/PlanoController.php?acao=excluir&id=<?= $plano['id_plano'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir este plano?');">
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