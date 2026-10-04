<?php
// view/professor/listar_avaliacao.php
session_start();
require_once '../../controller/AvaliacaoController.php';

// Valida se o utilizador está logado e se é Professor (3) ou Gerente (2)
if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['perfil_id'], [2, 3])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$idAcademia = $_SESSION['usuario']['id_academia'] ?? 0;

$avaliacaoController = new AvaliacaoController();
$avaliacoes = $avaliacaoController->listarPorAcademia($idAcademia);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações Técnicas - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <link rel="stylesheet" href="../../assets/css/gerente.css">
</head>
<body class="gerente-page">

    <?php include '../includes/header.php'; ?>

    <main class="gerente-dashboard">
        <header class="gerente-welcome">
            <div>
                <h2>Avaliações Técnicas</h2>
                <p>Consulte o histórico de evolução, pontos fortes e aspetos a melhorar dos alunos.</p>
            </div>
            <div>
                <a href="../professor/cadastrar_avaliacao.php" class="btn gerente-btn">
                    <i class="bi bi-plus-lg me-1"></i> Nova Avaliação
                </a>
            </div>
        </header>

        <section class="gerente-section">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Data</th>
                                    <th>Aluno</th>
                                    <th>Professor</th>
                                    <th>Habilidades a Melhorar</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($avaliacoes)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            Nenhuma avaliação registada até o momento.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($avaliacoes as $av): ?>
                                        <tr>
                                            <td class="ps-4"><?= date('d/m/Y', strtotime($av->getDataAvaliacao())) ?></td>
                                            <td><strong><?= htmlspecialchars($av->getNomeAluno()) ?></strong></td>
                                            <td><?= htmlspecialchars($av->getNomeProfessor()) ?></td>
                                            <td><?= htmlspecialchars(mb_strimwidth($av->getHabilidadesMelhorar(), 0, 50, "...")) ?></td>
                                            <td class="text-end pe-4">
                                                <a href="ver_avaliacao.php?id=<?= $av->getIdAvaliacao() ?>" class="btn btn-sm btn-outline-secondary" title="Ver detalhes">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>