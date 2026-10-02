<?php
// view/aluno/historico_frequencia.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['perfil_id'] ?? 0) !== 4) {
    header('Location: ../../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../controller/Historico_FrequenciaController.php';

$historicoFrequencia = [];
$resumoFrequencia = null;
$mensagemErro = null;
$idAluno = (int)$_SESSION['usuario']['id_usuario'];

try {
    $controller = new Historico_frequenciaController();
    $historicoFrequencia = $controller->listarHistoricoPorAluno($idAluno);
    $resumoFrequencia = $controller->obterResumoFrequencia($historicoFrequencia);
} catch (PDOException $e) {
    error_log('Erro ao carregar histórico de frequência do aluno: ' . $e->getMessage());
    $mensagemErro = 'Não foi possível carregar o histórico de frequência. Tente novamente mais tarde.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Frequência - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body class="aluno-theme">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container py-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="home_aluno.php" class="btn btn-outline-secondary">Voltar ao painel</a>
        </div>
        <div class="text-center mb-4">
            <h2 class="mb-1">Histórico de Frequência</h2>
            <p class="text-muted mb-0">Consulte suas aulas anteriores e os registros de presença.</p>
        </div>

        <?php if ($mensagemErro !== null): ?>
            <div class="alert alert-secondary" role="alert">
                <?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php else: ?>
            <section class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4" aria-label="Resumo da frequência">
                <div class="col">
                    <article class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="text-muted small text-uppercase fw-bold mb-2">Aulas com registro</h3>
                            <p class="display-6 fw-bold mb-0">
                                <?= $resumoFrequencia['aulas_com_registro']; ?>
                            </p>
                        </div>
                    </article>
                </div>
                <div class="col">
                    <article class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="text-muted small text-uppercase fw-bold mb-2">Presenças</h3>
                            <p class="display-6 fw-bold text-success mb-0">
                                <?= $resumoFrequencia['presencas']; ?>
                            </p>
                        </div>
                    </article>
                </div>
                <div class="col">
                    <article class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="text-muted small text-uppercase fw-bold mb-2">Ausências</h3>
                            <p class="display-6 fw-bold text-secondary mb-0">
                                <?= $resumoFrequencia['ausencias']; ?>
                            </p>
                        </div>
                    </article>
                </div>
                <div class="col">
                    <article class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="text-muted small text-uppercase fw-bold mb-2">Frequência</h3>
                            <p class="display-6 fw-bold text-primary mb-0">
                                <?= number_format($resumoFrequencia['frequencia'], 1, ',', '.'); ?>%
                            </p>
                        </div>
                    </article>
                </div>
            </section>

            <section class="card shadow-sm border-0">
                <div class="card-body">
                    <?php if (empty($historicoFrequencia)): ?>
                        <p class="text-muted text-center mb-0 py-4">
                            Ainda não há aulas anteriores no seu histórico de frequência.
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted">
                                        <th scope="col">Turma</th>
                                        <th scope="col">Data da aula</th>
                                        <th scope="col">Frequência</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historicoFrequencia as $registro): ?>
                                        <tr>
                                            <td class="fw-bold">
                                                <?= htmlspecialchars($registro->getNomeTurma(), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y H:i', strtotime($registro->getDataAgendamento())); ?>
                                            </td>
                                            <td>
                                                <?php if ($registro->getStatusPresenca() === null): ?>
                                                    <span class="badge bg-secondary">Não lançada</span>
                                                <?php elseif ($registro->getStatusPresenca() === 1): ?>
                                                    <span class="badge bg-success">Presente</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Ausente</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>