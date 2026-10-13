<?php
// view/professor/listar_avaliacao.php
session_start();
require_once '../../controller/AvaliacaoController.php';

if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['perfil_id'], [2, 3])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$perfilId = (int)$_SESSION['usuario']['perfil_id'];
$idAcademia = $_SESSION['usuario']['id_academia'] ?? 0;
$idUsuario = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']['id_usuario'] ?? 0;

$avaliacaoController = new AvaliacaoController();
$avaliacoes = $avaliacaoController->listarPorPerfilUsuario($perfilId, $idAcademia, $idUsuario);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações Técnicas - Dojify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilo Global Dojify -->
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="../professor/cadastrar_avaliacao.php" class="btn btn-dark">
                    <i class="bi bi-plus-circle me-1"></i> Registar Nova Avaliação
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="mb-4">
                <h2>Avaliações Técnicas</h2>
                <p class="text-muted">Consulte o histórico de evolução, pontos fortes e aspetos a melhorar dos alunos.</p>
            </div>
             
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Aluno</th>
                            <th>Modalidade</th>
                            <th>Professor</th>
                            <th>Habilidades a Melhorar</th>
                            <th>Próxima graduação prevista</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($avaliacoes)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Nenhuma avaliação registada até o momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($avaliacoes as $av): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($av->getDataAvaliacao())) ?></td>
                                    <td><strong><?= htmlspecialchars($av->getNomeAluno()) ?></strong></td>
                                    <td><?= htmlspecialchars((string)($av->getNomeModalidade() ?? 'Não informada'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($av->getNomeProfessor()) ?></td>
                                    <td><?= htmlspecialchars(mb_strimwidth($av->getHabilidadesMelhorar(), 0, 50, "...")) ?></td>
                                    <td><?= $av->getDataPrevista() ? date('d/m/Y', strtotime($av->getDataPrevista())) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>