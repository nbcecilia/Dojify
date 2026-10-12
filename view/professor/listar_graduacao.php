<?php
// view/professor/listar_graduacao.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../controller/GraduacaoController.php';

if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['perfil_id'], [2, 3])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$perfilId = (int)$_SESSION['usuario']['perfil_id'];
$idAcademia = $_SESSION['usuario']['id_academia'] ?? 0;
$idUsuario = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']['id_usuario'] ?? 0;

$controller = new GraduacaoController();
$graduacoes = $controller->listarPorPerfilUsuario($perfilId, $idAcademia, $idUsuario);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Graduações - Dojify</title>
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
                <a href="cadastrar_graduacao.php" class="btn btn-dark">
                    <i class="bi bi-plus-circle me-1"></i> Registar Nova Graduação
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="mb-4">
                <h2>Histórico de Graduações</h2>
                <p class="text-muted">Consulte as graduações atribuídas aos alunos nas suas modalidades.</p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Aluno</th>
                            <th>Modalidade</th>
                            <th>Faixa / Grau</th>
                            <th>Professor</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($graduacoes)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nenhuma graduação registada encontrada.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($graduacoes as $grad): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($grad['nome_aluno'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td><?= htmlspecialchars($grad['nome_modalidade'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars($grad['faixa'], ENT_QUOTES, 'UTF-8') ?>
                                            <?= !empty($grad['grau']) ? ' - ' . htmlspecialchars($grad['grau'], ENT_QUOTES, 'UTF-8') : '' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($grad['nome_professor'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= date('d/m/Y', strtotime($grad['data_graduacao'])) ?></td>
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