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
$avaliacoesGeral = $avaliacaoController->listarPorPerfilUsuario($perfilId, $idAcademia, $idUsuario);
$alunos = $avaliacaoController->listarAlunosPorPerfilUsuario($perfilId, $idAcademia, $idUsuario);

// Captura o termo de busca digitado
$termoBusca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Se houver termo de busca, filtra as avaliações por nome do aluno
if (!empty($termoBusca)) {
    $avaliacoes = array_filter($avaliacoesGeral, function($av) use ($termoBusca) {
        return (
            stripos($av->getNomeAluno(), $termoBusca) !== false
        );
    });
    $avaliacoes = array_values($avaliacoes);
} else {
    $avaliacoes = $avaliacoesGeral;
}

$mensagem = '';
$tipoMensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = (string)($_POST['acao'] ?? '');
    $alunoModalidade = explode('|', (string)($_POST['id_usuario_aluno_modalidade'] ?? ''), 2);
    $idAluno = filter_var($alunoModalidade[0] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $idModalidade = filter_var($alunoModalidade[1] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $dataAvaliacao = trim((string)($_POST['data_avaliacao'] ?? ''));
    $dataPrevista = trim((string)($_POST['data_prevista'] ?? ''));
    $habilidades = (string)($_POST['habilidades_melhorar'] ?? '');
    $observacoes = (string)($_POST['observacoes'] ?? '');

    try {
        if ($acao === 'cadastrar') {
            $resposta = $avaliacaoController->cadastrar(
                (int)$idUsuario,
                $idAluno,
                $idModalidade,
                $dataAvaliacao,
                $dataPrevista !== '' ? $dataPrevista : null,
                $habilidades,
                $observacoes
            );
        } elseif ($acao === 'atualizar' && $perfilId === 3) {
            $idAvaliacao = filter_var($_POST['id_avaliacao'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            $resposta = $avaliacaoController->atualizar(
                (int)$idUsuario,
                $idAvaliacao,
                $idAluno,
                $idModalidade,
                $dataAvaliacao,
                $dataPrevista !== '' ? $dataPrevista : null,
                $habilidades,
                $observacoes
            );
        } else {
            $resposta = ['status' => false, 'mensagem' => 'Ação inválida.'];
        }
    } catch (DomainException $e) {
        $resposta = ['status' => false, 'mensagem' => 'O aluno ou a modalidade selecionada não tem matrícula ativa nesta academia.'];
    } catch (Throwable $e) {
        error_log('Erro ao processar avaliação: ' . $e->getMessage());
        $resposta = ['status' => false, 'mensagem' => 'Não foi possível salvar a avaliação. Tente novamente.'];
    }

    $parametroMensagem = $resposta['status'] ? 'sucesso' : 'erro';
    header('Location: listar_avaliacao.php?' . $parametroMensagem . '=' . rawurlencode($resposta['mensagem']));
    exit;
}

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
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalNovaAvaliacao">
                    <i class="bi bi-plus-circle me-1"></i> Registar Nova Avaliação
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
                <h2>Avaliações Técnicas</h2>
                <p class="text-muted">Consulte o histórico de evolução, pontos fortes e aspetos a melhorar dos alunos.</p>

                <!-- Barra de Pesquisa Padronizada (.busca-container) -->
                <div class="busca-container mt-3">
                    <form method="GET" action="listar_avaliacao.php" class="busca-form d-flex gap-2">
                        <div class="input-group busca-input">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="busca" class="form-control" placeholder="Pesquisar por nome do aluno..." value="<?= htmlspecialchars($termoBusca) ?>">
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
                            <th>Data</th>
                            <th>Aluno</th>
                            <th>Modalidade</th>
                            <th>Professor</th>
                            <th>Habilidades a Melhorar</th>
                            <th>Próxima graduação prevista</th>
                            <?php if ($perfilId === 3): ?><th class="text-end">Ações</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($avaliacoes)): ?>
                            <tr>
                                <td colspan="<?= $perfilId === 3 ? 7 : 6 ?>" class="text-center py-4 text-muted">
                                    Nenhuma avaliação encontrada.
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
                                    <?php if ($perfilId === 3): ?>
                                        <td class="text-end">
                                            <?php if ((int)$av->getIdUsuarioProfessor() === (int)$idUsuario): ?>
                                                <button type="button" class="btn btn-info btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarAvaliacao"
                                                        data-id="<?= (int)$av->getIdAvaliacao() ?>"
                                                        data-aluno-modalidade="<?= (int)$av->getIdUsuarioAluno() ?>|<?= (int)$av->getIdModalidade() ?>"
                                                        data-data="<?= htmlspecialchars((string)$av->getDataAvaliacao(), ENT_QUOTES, 'UTF-8') ?>"
                                                        data-data-prevista="<?= htmlspecialchars((string)($av->getDataPrevista() ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                        data-habilidades="<?= htmlspecialchars((string)$av->getHabilidadesMelhorar(), ENT_QUOTES, 'UTF-8') ?>"
                                                        data-observacoes="<?= htmlspecialchars((string)$av->getObservacoes(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="bi bi-pencil me-1"></i> Editar
                                                </button>
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

    <div class="modal fade" id="modalNovaAvaliacao" tabindex="-1" aria-labelledby="modalNovaAvaliacaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="listar_avaliacao.php" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title fs-5" id="modalNovaAvaliacaoLabel">Nova Avaliação Técnica</h2>
                            <p class="text-muted small mb-0">Registe o desempenho e a evolução do aluno.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="cadastrar">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="nova_avaliacao_aluno_modalidade" class="form-label">Aluno e modalidade <span class="text-danger">*</span></label>
                                <select class="form-select" id="nova_avaliacao_aluno_modalidade" name="id_usuario_aluno_modalidade" required>
                                    <option value="">Selecione o aluno e a modalidade...</option>
                                    <?php foreach ($alunos as $aluno): ?>
                                        <option value="<?= (int)$aluno['id_usuario'] ?>|<?= (int)$aluno['id_modalidade'] ?>">
                                            <?= htmlspecialchars($aluno['nome'] . ' - ' . $aluno['nome_modalidade'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="nova_avaliacao_data" class="form-label">Data da Avaliação <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="nova_avaliacao_data" name="data_avaliacao" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nova_avaliacao_data_prevista" class="form-label">Previsão da próxima graduação</label>
                                <input type="date" class="form-control" id="nova_avaliacao_data_prevista" name="data_prevista" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                            <div class="col-12">
                                <label for="nova_avaliacao_habilidades" class="form-label">Habilidades a Melhorar <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="nova_avaliacao_habilidades" name="habilidades_melhorar" rows="4" required></textarea>
                            </div>
                            <div class="col-12">
                                <label for="nova_avaliacao_observacoes" class="form-label">Observações Gerais / Evolução</label>
                                <textarea class="form-control" id="nova_avaliacao_observacoes" name="observacoes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar avaliação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($perfilId === 3): ?>
        <div class="modal fade" id="modalEditarAvaliacao" tabindex="-1" aria-labelledby="modalEditarAvaliacaoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="listar_avaliacao.php" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title fs-5" id="modalEditarAvaliacaoLabel">Editar Avaliação Técnica</h2>
                                <p class="text-muted small mb-0">Atualize os dados da avaliação selecionada.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="acao" value="atualizar">
                            <input type="hidden" name="id_avaliacao" id="editar_avaliacao_id">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="editar_avaliacao_aluno_modalidade" class="form-label">Aluno e modalidade <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editar_avaliacao_aluno_modalidade" name="id_usuario_aluno_modalidade" required>
                                        <option value="">Selecione o aluno e a modalidade...</option>
                                        <?php foreach ($alunos as $aluno): ?>
                                            <option value="<?= (int)$aluno['id_usuario'] ?>|<?= (int)$aluno['id_modalidade'] ?>">
                                                <?= htmlspecialchars($aluno['nome'] . ' - ' . $aluno['nome_modalidade'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="editar_avaliacao_data" class="form-label">Data da Avaliação <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="editar_avaliacao_data" name="data_avaliacao" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editar_avaliacao_data_prevista" class="form-label">Previsão da próxima graduação</label>
                                    <input type="date" class="form-control" id="editar_avaliacao_data_prevista" name="data_prevista">
                                </div>
                                <div class="col-12">
                                    <label for="editar_avaliacao_habilidades" class="form-label">Habilidades a Melhorar <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="editar_avaliacao_habilidades" name="habilidades_melhorar" rows="4" required></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="editar_avaliacao_observacoes" class="form-label">Observações Gerais / Evolução</label>
                                    <textarea class="form-control" id="editar_avaliacao_observacoes" name="observacoes" rows="3"></textarea>
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
    <?php if ($perfilId === 3): ?>
        <script>
            document.getElementById('modalEditarAvaliacao').addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                this.querySelector('#editar_avaliacao_id').value = button.dataset.id;
                this.querySelector('#editar_avaliacao_aluno_modalidade').value = button.dataset.alunoModalidade;
                this.querySelector('#editar_avaliacao_data').value = button.dataset.data;
                this.querySelector('#editar_avaliacao_data_prevista').value = button.dataset.dataPrevista;
                this.querySelector('#editar_avaliacao_habilidades').value = button.dataset.habilidades;
                this.querySelector('#editar_avaliacao_observacoes').value = button.dataset.observacoes;
            });
        </script>
    <?php endif; ?>
</body>
</html>