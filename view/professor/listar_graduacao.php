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
$graduacoesGeral = $controller->listarPorPerfilUsuario($perfilId, $idAcademia, $idUsuario);
$alunos = $controller->listarAlunosPorPerfilUsuario($perfilId, $idAcademia, $idUsuario);

// Captura o termo de busca digitado
$termoBusca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Se houver termo de busca, filtra por nome do aluno ou faixa
if (!empty($termoBusca)) {
    $graduacoes = array_filter($graduacoesGeral, function($grad) use ($termoBusca) {
        return (
            stripos($grad['nome_aluno'] ?? '', $termoBusca) !== false ||
            stripos($grad['faixa'] ?? '', $termoBusca) !== false
        );
    });
    $graduacoes = array_values($graduacoes);
} else {
    $graduacoes = $graduacoesGeral;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = (string)($_POST['acao'] ?? '');
    $mensagem = '';
    $sucesso = false;

    if ($acao === 'cadastrar') {
        $alunoModalidade = explode('|', (string)($_POST['id_usuario_aluno_modalidade'] ?? ''), 2);
        $idAluno = filter_var($alunoModalidade[0] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $idModalidade = filter_var($alunoModalidade[1] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $resposta = $controller->cadastrar(
            $idAluno,
            (int)$idUsuario,
            $idModalidade,
            trim((string)($_POST['data_graduacao'] ?? '')),
            trim((string)($_POST['faixa'] ?? '')),
            trim((string)($_POST['grau'] ?? ''))
        );
        $mensagem = $resposta['mensagem'];
        $sucesso = $resposta['status'];
    } elseif ($acao === 'atualizar' && in_array($perfilId, [2, 3], true)) {
        $idGraduacao = filter_var($_POST['id_graduacao'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $resposta = $controller->atualizar(
            $idGraduacao,
            (int)$idUsuario,
            trim((string)($_POST['data_graduacao'] ?? '')),
            trim((string)($_POST['faixa'] ?? '')),
            trim((string)($_POST['grau'] ?? '')),
            $perfilId
        );
        $mensagem = $resposta['mensagem'];
        $sucesso = $resposta['status'];
    } else {
        $mensagem = 'Ação inválida.';
    }

    $status = $sucesso ? 'sucesso' : 'erro';
    header('Location: listar_graduacao.php?' . $status . '=' . rawurlencode($mensagem));
    exit;
}

$mensagem = '';
$tipoMensagem = '';
if (isset($_GET['sucesso'])) {
    $mensagem = (string)$_GET['sucesso'];
    $tipoMensagem = 'success';
} elseif (isset($_GET['erro'])) {
    $mensagem = (string)$_GET['erro'];
    $tipoMensagem = 'danger';
}
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
    <style>
        #modalNovaGraduacao .modal-content > form {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        #modalNovaGraduacao .modal-body {
            flex: 1 1 auto;
            overflow-y: auto;
        }

        .graduacao-modal-action-button {
            box-sizing: border-box !important;
            display: inline-flex !important;
            width: 150px !important;
            height: 36px !important;
            min-height: 36px !important;
            max-height: 36px !important;
            margin: 0 !important;
            padding: 0 10px !important;
            font-size: 0.8rem !important;
            line-height: 1 !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .graduacao-modal-action-footer {
            gap: 8px !important;
        }
    </style>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalNovaGraduacao">
                    <i class="bi bi-plus-circle me-1"></i> Registar Nova Graduação
                </button>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <?php if ($mensagem !== ''): ?>
                <div class="alert alert-<?= $tipoMensagem ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h2>Histórico de Graduações</h2>
                <p class="text-muted">Consulte as graduações atribuídas aos alunos nas suas modalidades.</p>

                <!-- Barra de Pesquisa Padronizada (.busca-container) -->
                <div class="busca-container mt-3">
                    <form method="GET" action="listar_graduacao.php" class="busca-form d-flex gap-2">
                        <div class="input-group busca-input">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="busca" class="form-control" placeholder="Pesquisar por nome do aluno ou faixa..." value="<?= htmlspecialchars($termoBusca) ?>">
                        </div>
                        <button type="submit" class="btn btn-dark busca-btn">
                            Filtrar
                        </button>
                    </form>
                </div>
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
                            <?php if (in_array($perfilId, [2, 3], true)): ?><th class="text-end">Ações</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($graduacoes)): ?>
                            <tr>
                                <td colspan="<?= in_array($perfilId, [2, 3], true) ? 6 : 5 ?>" class="text-center text-muted py-4">Nenhuma graduação encontrada.</td>
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
                                    <?php if (in_array($perfilId, [2, 3], true)): ?>
                                        <td class="text-end">
                                            <?php if ($perfilId === 2 || (int)$grad['id_usuario_professor'] === (int)$idUsuario): ?>
                                                <?php if ($perfilId === 2): ?>
                                                    <a href="../gerente/editar_graduacao.php?id=<?= (int)$grad['id_graduacao'] ?>" class="btn btn-info btn-sm">
                                                        <i class="bi bi-pencil me-1"></i> Editar
                                                    </a>
                                                <?php else: ?>
                                                <button type="button" class="btn btn-info btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarGraduacao"
                                                        data-id="<?= (int)$grad['id_graduacao'] ?>"
                                                        data-data="<?= htmlspecialchars($grad['data_graduacao'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-faixa="<?= htmlspecialchars($grad['faixa'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-grau="<?= htmlspecialchars($grad['grau'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="bi bi-pencil me-1"></i> Editar
                                                </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div class="modal fade" id="modalNovaGraduacao" tabindex="-1" aria-labelledby="modalNovaGraduacaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="listar_graduacao.php" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title fs-5" id="modalNovaGraduacaoLabel">Registar Graduação</h2>
                            <p class="text-muted small mb-0">Atribua uma nova faixa ou grau ao aluno.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="cadastrar">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="nova_graduacao_aluno_modalidade" class="form-label">Aluno e modalidade <span class="text-danger">*</span></label>
                                <select class="form-select" id="nova_graduacao_aluno_modalidade" name="id_usuario_aluno_modalidade" required>
                                    <option value="">Selecione o aluno e a modalidade...</option>
                                    <?php foreach ($alunos as $aluno): ?>
                                        <option value="<?= (int)$aluno['id_usuario'] ?>|<?= (int)$aluno['id_modalidade'] ?>">
                                            <?= htmlspecialchars($aluno['nome'] . ' - ' . $aluno['nome_modalidade'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="nova_graduacao_data" class="form-label">Data da Graduação <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="nova_graduacao_data" name="data_graduacao" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nova_graduacao_faixa" class="form-label">Faixa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nova_graduacao_faixa" name="faixa" maxlength="50" placeholder="Ex: Azul, Roxa, Marrom..." required>
                            </div>
                            <div class="col-md-6">
                                <label for="nova_graduacao_grau" class="form-label">Grau</label>
                                <input type="text" class="form-control" id="nova_graduacao_grau" name="grau" maxlength="20" placeholder="Ex: 1º Grau, 2º Grau (opcional)">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer graduacao-modal-action-footer">
                        <button type="button" class="btn btn-secondary graduacao-modal-action-button" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success graduacao-modal-action-button">Guardar Graduação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (in_array($perfilId, [2, 3], true)): ?>
        <div class="modal fade" id="modalEditarGraduacao" tabindex="-1" aria-labelledby="modalEditarGraduacaoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="listar_graduacao.php" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title fs-5" id="modalEditarGraduacaoLabel">Editar Graduação</h2>
                                <p class="text-muted small mb-0">Atualize a faixa, o grau ou a data atribuída.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="acao" value="atualizar">
                            <input type="hidden" name="id_graduacao" id="editar_graduacao_id">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editar_graduacao_data" class="form-label">Data da Graduação <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="editar_graduacao_data" name="data_graduacao" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editar_graduacao_faixa" class="form-label">Faixa <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editar_graduacao_faixa" name="faixa" maxlength="50" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editar_graduacao_grau" class="form-label">Grau</label>
                                    <input type="text" class="form-control" id="editar_graduacao_grau" name="grau" maxlength="20">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Salvar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php include '../includes/footer.php'; ?>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (in_array($perfilId, [2, 3], true)): ?>
        <script>
            document.getElementById('modalEditarGraduacao').addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                this.querySelector('#editar_graduacao_id').value = button.dataset.id;
                this.querySelector('#editar_graduacao_data').value = button.dataset.data;
                this.querySelector('#editar_graduacao_faixa').value = button.dataset.faixa;
                this.querySelector('#editar_graduacao_grau').value = button.dataset.grau;
            });
        </script>
    <?php endif; ?>
</body>
</html>