<?php
// view/professor/listar_frequencia.php
session_start();

// Validação de acesso (Gerente ou Professor)
if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['perfil_id'], [2, 3])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/PresencaDAO.php';

$dao = new PresencaDAO();

$perfilId = (int)$_SESSION['usuario']['perfil_id'];
$idAcademia = $_SESSION['usuario']['id_academia'] ?? 0;
$idUsuario = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']['id_usuario'] ?? 0;

// Parâmetros de filtro obtidos via GET
$idTurma = filter_input(INPUT_GET, 'id_turma', FILTER_VALIDATE_INT) ?: null;
$dataFiltro = $_GET['data'] ?? '';

// Distinção de regras: Gerente vê todas as turmas/histórico da academia; Professor vê apenas os seus
if ($perfilId === 2) {
    $listaTurmas = $dao->listarTurmasPorAcademia($idAcademia);
    $historicoPresencas = $dao->listarHistoricoGeralAcademia($idAcademia, $idTurma, $dataFiltro !== '' ? $dataFiltro : null);
} else {
    $listaTurmas = $dao->listarTurmasPorProfessor($idUsuario);
    $historicoPresencas = $dao->listarHistoricoGeralProfessor($idUsuario, $idTurma, $dataFiltro !== '' ? $dataFiltro : null);
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Chamada - Dojify</title>
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
                <a href="registrar_frequencia.php" class="btn btn-dark">
                    <i class="bi bi-check2-square me-1"></i> Registar Nova Chamada
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="mb-4">
                <h2>Histórico de Chamada</h2>
                <p class="text-muted">Consulte o registo de assiduidade dos alunos nas turmas.</p>
            </div>

            <!-- Filtro por Turma e Data opcional -->
            <form method="GET" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="id_turma" class="form-label">Turma (Opcional)</label>
                        <select name="id_turma" id="id_turma" class="form-select">
                            <option value="">Todas as turmas disponíveis...</option>
                            <?php foreach ($listaTurmas as $turma): ?>
                                <option value="<?= (int)$turma['id_turma'] ?>" <?= $idTurma == $turma['id_turma'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($turma['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="data" class="form-label">Data da Aula (Opcional)</label>
                        <input type="date" name="data" id="data" class="form-control" value="<?= htmlspecialchars($dataFiltro) ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="bi bi-search me-1"></i> Filtrar Histórico
                        </button>
                    </div>
                </div>
            </form>

            <!-- Tabela de Histórico -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Aluno</th>
                            <th>Turma</th>
                            <th>Data da Aula</th>
                            <th class="text-center" style="width: 150px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historicoPresencas)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nenhum registo de presença encontrado para os filtros selecionados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historicoPresencas as $registo): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($registo['aluno_nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td><?= htmlspecialchars($registo['nome_turma'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= date('d/m/Y', strtotime($registo['data'])) ?></td>
                                    <td class="text-center">
                                        <?php if ((int)$registo['status'] === 1): ?>
                                            <span class="badge bg-success">Presente</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Ausente</span>
                                        <?php endif; ?>
                                    </td>
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