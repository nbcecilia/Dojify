<?php
// view/aluno/historico_graduacao.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || (int) ($_SESSION['usuario']['perfil_id'] ?? 0) !== 4) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../controller/GraduacaoController.php';
require_once __DIR__ . '/../../model/dao/AvaliacaoDAO.php';

$historico = [];
$graduacoesPrevistas = [];
$avaliacoes = [];
$mensagemErro = null;
$avaliacaoDAO = new AvaliacaoDAO();

try {
    $controller = new GraduacaoController();
    $historico = $controller->listarHistoricoAluno((int) $_SESSION['usuario']['id_usuario']);
    $graduacoesPrevistas = $avaliacaoDAO->listarGraduacoesPrevistasAluno((int) $_SESSION['usuario']['id_usuario']);
    $avaliacoes = $avaliacaoDAO->listarEvolucaoAluno((int) $_SESSION['usuario']['id_usuario']);
} catch (Throwable $e) {
    error_log('Erro ao carregar graduação e avaliação do aluno: ' . $e->getMessage());
    $mensagemErro = 'Não foi possível carregar os dados de graduação e avaliação.';
}

function formatarDataGraduacao(string $data): string
{
    return date('d/m/Y', strtotime($data));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Graduação - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <link rel="stylesheet" href="../../assets/css/aluno.css?v=<?= filemtime(__DIR__ . '/../../assets/css/aluno.css'); ?>">
</head>
<body class="aluno-page aluno-theme evolucao-page">
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="historico-header d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <h1>Graduação e avaliação</h1>
                <p>Acompanhe suas conquistas, previsões e orientações dos professores.</p>
            </div>
            <a href="home_aluno.php" class="btn btn-sm ms-auto">Voltar ao início</a>
        </div>

        <?php if ($mensagemErro): ?>
            <div class="alert-erro alerta-historico" role="alert"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="row g-4 align-items-start">
            <!-- COLUNA ESQUERDA -->
            <section class="col-lg-5 d-flex flex-column gap-4" id="graduacoes-previstas" aria-labelledby="graduacoes-heading">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="evolucao-icon" aria-hidden="true"></span>
                            <div>
                                <h3 id="graduacoes-heading" class="h5 fw-bold mb-1">Próximas graduações</h3>
                                <p class="text-muted small mb-0">Previsões informadas pela equipe da academia.</p>
                            </div>
                        </div>
                        <?php if (empty($graduacoesPrevistas)): ?>
                            <p class="text-muted mb-0">Ainda não há uma próxima graduação prevista.</p>
                        <?php else: ?>
                            <div class="d-grid gap-3">
                                <?php foreach ($graduacoesPrevistas as $prevista): ?>
                                    <div class="graduacao-prevista-item">
                                        <span class="small text-muted"><?= htmlspecialchars((string)$prevista['nome_modalidade'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <strong><?= formatarDataGraduacao((string)$prevista['data_prevista']) ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-1">Histórico de graduação</h3>
                        <?php if (empty($historico)): ?>
                            <p class="text-muted mb-0">Nenhuma graduação registrada.</p>
                        <?php else: ?>
                            <div class="historico-tabela historico-tabela--graduacao">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Modalidade</th>
                                            <th>Faixa / grau</th>
                                            <th>Professor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historico as $graduacao): ?>
                                            <tr>
                                                <td><?= formatarDataGraduacao($graduacao->getDataGraduacao()) ?></td>
                                                <td><?= htmlspecialchars($graduacao->getNomeModalidade(), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="graduacao-faixa">
                                                    <?= htmlspecialchars($graduacao->getFaixa(), ENT_QUOTES, 'UTF-8') ?>
                                                    <?= $graduacao->getGrau() ? ' · ' . htmlspecialchars($graduacao->getGrau(), ENT_QUOTES, 'UTF-8') : '' ?>
                                                </td>
                                                <td><?= htmlspecialchars($graduacao->getNomeProfessor(), ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- COLUNA DIREITA -->
            <section class="col-lg-7" aria-labelledby="avaliacoes-heading">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="evolucao-icon" aria-hidden="true"></span>
                            <div>
                                <h3 id="avaliacoes-heading" class="h5 fw-bold mb-1">Avaliações do professor</h3>
                                <p class="text-muted small mb-0">Orientações registradas para acompanhar sua evolução.</p>
                            </div>
                        </div>
                        <?php if (empty($avaliacoes)): ?>
                            <p class="text-muted mb-0 mt-3 pt-3 border-top">Ainda não há avaliações registradas para você.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($avaliacoes)): ?>
                    <div class="d-grid gap-3 mt-4">
                        <?php foreach ($avaliacoes as $avaliacao): ?>
                            <article class="card border-0 shadow-sm avaliacao-card" id="avaliacao-<?= (int)$avaliacao['id_avaliacao'] ?>">
                                <div class="card-body p-4">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                        <div>
                                            <span class="badge text-bg-dark mb-2">Avaliação técnica</span>
                                            <h4 class="h6 fw-bold mb-1"><?= htmlspecialchars($avaliacao['nome_avaliador'], ENT_QUOTES, 'UTF-8') ?></h4>
                                            <p class="small text-muted mb-0">Responsável · <?= formatarDataGraduacao($avaliacao['data_avaliacao']) ?></p>
                                        </div>
                                    </div>
                                    <div class="avaliacao-conteudo mb-3">
                                        <?php if (!empty($avaliacao['nome_modalidade'])): ?>
                                            <h5 class="small fw-bold text-uppercase">Modalidade</h5>
                                            <p><?= htmlspecialchars($avaliacao['nome_modalidade'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php endif; ?>
                                        <h5 class="small fw-bold text-uppercase">Pontos para desenvolver</h5>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($avaliacao['habilidades_melhorar'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    </div>
                                    <?php if (!empty($avaliacao['observacoes'])): ?>
                                        <div class="avaliacao-conteudo mb-3">
                                            <h5 class="small fw-bold text-uppercase">Observações</h5>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($avaliacao['observacoes'], ENT_QUOTES, 'UTF-8')) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>