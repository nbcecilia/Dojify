<?php
/** ESTOU EM DUVIDA QUANTO A ESSE ARQUIVO, SE QUISER ACHO Q ELE CABE DENTRO DE OUTRO
 * Quadro da equipe: permite definir previsões de graduação e acompanhar,
 * dentro de cada avaliação já existente, os comentários enviados pelos alunos.
 * O cadastro/edição da avaliação técnica pelo professor ou gerente ainda será
 * integrado como fluxo separado nesta área.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$perfilId = (int)($_SESSION['usuario']['perfil_id'] ?? 0);
if (!isset($_SESSION['usuario']) || !in_array($perfilId, [2, 3], true)) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../model/dao/AvaliacaoDAO.php';

$idStaff = (int)$_SESSION['usuario']['id_usuario'];
$idAcademia = (int)($_SESSION['id_academia'] ?? $_SESSION['usuario']['id_academia'] ?? 0);
$avaliacaoDAO = new \AvaliacaoDAO();
$notificacoes = [];
$comentarios = [];
$alunosModalidades = [];
$notificacoesEquipe = ['total' => 0, 'lista' => []];
$mensagemErro = null;

try {
    if ($idAcademia <= 0) {
        throw new RuntimeException('O usuário não possui uma academia associada.');
    }
    $comentarios = $avaliacaoDAO->listarComentariosEquipe($idStaff, $idAcademia, $perfilId);
    $alunosModalidades = $avaliacaoDAO->listarAlunosModalidadesAcademia($idAcademia);
    $notificacoesEquipe = $avaliacaoDAO->listarNotificacoesEquipe($idStaff);
} catch (Throwable $e) {
    error_log('Erro ao carregar comentários de avaliações: ' . $e->getMessage());
    $mensagemErro = 'Não foi possível carregar os comentários e as previsões de graduação.';
}

$mensagensSucesso = [
    'previsao_salva' => 'A previsão da graduação foi salva.',
    'notificacao_lida' => 'Notificação marcada como lida.',
];
$mensagensErro = [
    'dados_invalidos' => 'Confira os dados informados. A previsão precisa ser uma data futura.',
    'token_invalido' => 'Sua sessão expirou. Atualize a página e tente novamente.',
    'interno' => 'Não foi possível concluir a solicitação. Tente novamente mais tarde.',
];
$mensagemSucesso = $mensagensSucesso[(string)($_GET['sucesso'] ?? '')] ?? null;
$mensagemErroForm = $mensagensErro[(string)($_GET['erro'] ?? '')] ?? null;
$avaliacoesComComentarios = [];
foreach ($comentarios as $comentario) {
    $idAvaliacao = (int)$comentario['id_avaliacao'];
    if (!isset($avaliacoesComComentarios[$idAvaliacao])) {
        $avaliacoesComComentarios[$idAvaliacao] = [
            'id_avaliacao' => $idAvaliacao,
            'data_avaliacao' => (string)$comentario['data_avaliacao'],
            'habilidades_melhorar' => (string)$comentario['habilidades_melhorar'],
            'nome_aluno' => (string)$comentario['nome_aluno'],
            'nome_avaliador' => (string)$comentario['nome_avaliador'],
            'comentarios' => [],
        ];
    }
    $avaliacoesComComentarios[$idAvaliacao]['comentarios'][] = $comentario;
}
$notificacoesPorComentario = [];
foreach ($notificacoesEquipe['lista'] as $itemNotificacao) {
    $notificacoesPorComentario[(int)$itemNotificacao['id_avaliacao_comentario']] = $itemNotificacao;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações e graduações - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <link rel="stylesheet" href="../../assets/css/aluno.css?v=<?= filemtime(__DIR__ . '/../../assets/css/aluno.css'); ?>">
</head>
<body class="aluno-page aluno-theme evolucao-page">
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="historico-header">
            <div>
                <h2>Avaliações e graduações</h2>
                <p>Acompanhe avaliações e comentários dos alunos no quadro de cada avaliação.</p>
            </div>
            <a href="<?= $perfilId === 2 ? '../gerente/home_gerente.php' : '../professor/avaliacoes.php' ?>" class="btn btn-sm">Voltar</a>
        </div>

        <?php if ($mensagemSucesso): ?>
            <div class="alert alert-success" role="status"><?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($mensagemErroForm): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($mensagemErroForm, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($mensagemErro): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php else: ?>
            <section class="card border-0 shadow-sm mb-4" aria-labelledby="previsoes-heading">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <h3 id="previsoes-heading" class="h5 fw-bold mb-1">Previsões de graduação</h3>
                        <p class="text-muted small mb-0">Defina uma data futura para cada modalidade do aluno. A previsão será exibida no painel do aluno e no sino de notificações.</p>
                    </div>
                    <?php if (empty($alunosModalidades)): ?>
                        <p class="text-muted mb-0">Não há alunos ativos com modalidades associadas nesta academia.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Aluno</th>
                                        <th scope="col">Modalidade</th>
                                        <th scope="col">Data prevista</th>
                                        <th scope="col"><span class="visually-hidden">Ação</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alunosModalidades as $linha): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= htmlspecialchars((string)$linha['nome_aluno'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string)$linha['nome_modalidade'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td colspan="2">
                                                <form method="POST" action="../../controller/AvaliacaoController.php?acao=salvar_previsao" class="graduacao-prevista-form d-flex flex-wrap gap-2 align-items-center">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="id_aluno" value="<?= (int)$linha['id_aluno']; ?>">
                                                    <input type="hidden" name="id_modalidade" value="<?= (int)$linha['id_modalidade']; ?>">
                                                    <label class="visually-hidden" for="previsao-<?= (int)$linha['id_aluno']; ?>-<?= (int)$linha['id_modalidade']; ?>">Data prevista para <?= htmlspecialchars((string)$linha['nome_aluno'] . ' - ' . (string)$linha['nome_modalidade'], ENT_QUOTES, 'UTF-8'); ?></label>
                                                    <input
                                                        class="form-control form-control-sm graduacao-prevista-date"
                                                        type="date"
                                                        id="previsao-<?= (int)$linha['id_aluno']; ?>-<?= (int)$linha['id_modalidade']; ?>"
                                                        name="data_prevista"
                                                        aria-label="Data prevista para <?= htmlspecialchars((string)$linha['nome_aluno'] . ' - ' . (string)$linha['nome_modalidade'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        min="<?= date('Y-m-d', strtotime('+1 day')); ?>"
                                                        value="<?= htmlspecialchars((string)($linha['data_prevista'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                        required>
                                                    <button type="submit" class="btn btn-sm btn-dark fw-bold">Salvar data</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section aria-labelledby="comentarios-heading">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                    <div>
                        <h3 id="comentarios-heading" class="h5 fw-bold mb-1">Quadros de avaliação com comentários</h3>
                        <p class="text-muted small mb-0">Os comentários dos alunos aparecem junto à avaliação a que se referem.</p>
                    </div>
                    <span class="badge rounded-pill text-bg-secondary"><?= count($avaliacoesComComentarios); ?> avaliação(ões)</span>
                </div>

                <?php if (empty($avaliacoesComComentarios)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 text-muted">Ainda não há comentários de alunos nas avaliações.</div>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-3">
                        <?php foreach ($avaliacoesComComentarios as $avaliacao): ?>
                            <article class="card border-0 shadow-sm avaliacao-card" id="avaliacao-<?= (int)$avaliacao['id_avaliacao']; ?>">
                                <div class="card-body p-4">
                                    <span class="badge text-bg-dark mb-2">Avaliação técnica</span>
                                    <h4 class="h6 fw-bold mb-1">
                                        <?= htmlspecialchars((string)$avaliacao['nome_aluno'], ENT_QUOTES, 'UTF-8'); ?>
                                        <span class="text-muted fw-normal">· <?= date('d/m/Y', strtotime((string)$avaliacao['data_avaliacao'])); ?></span>
                                    </h4>
                                    <p class="small text-muted mb-3">Avaliado por <?= htmlspecialchars((string)$avaliacao['nome_avaliador'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <div class="avaliacao-conteudo mb-3">
                                        <h5 class="small fw-bold text-uppercase">Pontos para desenvolver</h5>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars((string)$avaliacao['habilidades_melhorar'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    </div>
                                    <div class="avaliacao-feedback-alunos">
                                        <h5 class="small fw-bold text-uppercase mb-3">Comentários do aluno</h5>
                                        <div class="d-grid gap-2">
                                            <?php foreach ($avaliacao['comentarios'] as $comentario): ?>
                                                <?php
                                                $idComentario = (int)$comentario['id_comentario'];
                                                $notificacao = $notificacoesPorComentario[$idComentario] ?? null;
                                                ?>
                                                <div class="comentario-aluno" id="comentario-<?= $idComentario; ?>">
                                                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                                                        <strong class="small"><?= htmlspecialchars((string)$comentario['nome_aluno'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <time class="small text-muted" datetime="<?= htmlspecialchars((string)$comentario['data_comentario'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?= date('d/m/Y H:i', strtotime((string)$comentario['data_comentario'])); ?>
                                                        </time>
                                                    </div>
                                                    <p class="mb-0"><?= nl2br(htmlspecialchars((string)$comentario['comentario'], ENT_QUOTES, 'UTF-8')); ?></p>
                                                    <?php if ($notificacao !== null): ?>
                                                        <form method="POST" action="../../controller/AvaliacaoController.php?acao=marcar_lida" class="mt-2 text-end">
                                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="id_notificacao" value="<?= (int)$notificacao['id_notificacao']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Marcar como lido</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
