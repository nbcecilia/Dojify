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
require_once __DIR__ . '/../../model/dao/ModalidadeDAO.php';

$dao = new PlanoDAO();
$idAcademia = (int) $_SESSION['id_academia'];
$planosGeral = $dao->listarPorAcademia($idAcademia);

// Captura o termo de busca digitado
$termoBusca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Se houver termo de busca, filtra os planos por nome do plano ou nome do aluno
if (!empty($termoBusca)) {
    $planos = array_filter($planosGeral, function($p) use ($termoBusca) {
        return (
            stripos($p['nome_plano'], $termoBusca) !== false ||
            stripos($p['aluno_nome'], $termoBusca) !== false
        );
    });
    $planos = array_values($planos);
} else {
    $planos = $planosGeral;
}

// Dados necessários para carregar as opções nos Modals
$alunos = $dao->listarAlunosPorAcademia($idAcademia);
$modalidades = (new ModalidadeDAO())->listarPorAcademia($idAcademia);

if (!isset($_SESSION['plano_status_csrf_token'])) {
    $_SESSION['plano_status_csrf_token'] = bin2hex(random_bytes(32));
}

// Agrupar planos por aluno
$alunosAgrupados = [];
foreach ($planos as $plano) {
    $idAluno = $plano['id_usuario_aluno'];
    if (!isset($alunosAgrupados[$idAluno])) {
        $alunosAgrupados[$idAluno] = [
            'nome' => $plano['aluno_nome'],
            'planos' => []
        ];
    }
    $alunosAgrupados[$idAluno]['planos'][] = $plano;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
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
                <!-- Botão aciona o Modal de Vincular -->
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalVincularPlano">
                    <i class="bi bi-plus-circle me-1"></i> Vincular Plano
                </button>
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
                <h2>Planos Contratados</h2>
                <p class="text-muted">Consulte e gerencie os planos ativos dos alunos na academia.</p>

                <!-- Barra de Pesquisa Padronizada (.busca-container) -->
                <div class="busca-container mt-3">
                    <form method="GET" action="listar_plano.php" class="busca-form d-flex gap-2">
                        <div class="input-group busca-input">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="busca" class="form-control" placeholder="Pesquisar por nome do aluno ou plano..." value="<?= htmlspecialchars($termoBusca) ?>">
                        </div>
                        <button type="submit" class="btn btn-dark busca-btn">
                            Filtrar
                        </button>
                    </form>
                </div>
            </div>

            <?php if (empty($alunosAgrupados)): ?>
                <p class="text-center text-muted py-4">Nenhum plano encontrado.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Aluno</th>
                                <th>Plano</th>
                                <th>Modalidade</th>
                                <th>Valor (R$)</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alunosAgrupados as $dadosAluno): ?>
                                <?php 
                                $totalPlanos = count($dadosAluno['planos']);
                                foreach ($dadosAluno['planos'] as $index => $plano): 
                                ?>
                                    <tr <?= $index > 0 ? 'class="aluno-sub-linha"' : '' ?>>
                                        <?php if ($index === 0): ?>
                                            <td rowspan="<?= $totalPlanos ?>" class="align-middle bg-white">
                                                <strong><?= htmlspecialchars($dadosAluno['nome']) ?></strong>
                                            </td>
                                        <?php endif; ?>
                                        <td><?= htmlspecialchars($plano['nome_plano']) ?></td>
                                        <td>
                                            <span class="badge bg-dark">
                                                <?= htmlspecialchars($plano['modalidade_nome'] ?? $plano['nome_modalidade'] ?? $plano['modalidade'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td>R$ <?= number_format($plano['valor'], 2, ',', '.') ?></td>
                                        <td><?= date('d/m/Y', strtotime($plano['data_inicio'])) ?></td>
                                        <td><?= $plano['data_fim'] ? date('d/m/Y', strtotime($plano['data_fim'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($plano['status']) ?></td>
                                        <td class="text-center">
                                            <div class="plano-actions d-flex justify-content-center gap-2">
                                                <!-- Botão Editar aciona o Modal de Edição preenchendo os dados automaticamente -->
                                                <button type="button" class="btn btn-info btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditarPlano"
                                                    data-id="<?= $plano['id_plano'] ?>"
                                                    data-aluno="<?= $plano['id_usuario_aluno'] ?>"
                                                    data-modalidade="<?= $plano['id_modalidade'] ?? '' ?>"
                                                    data-nome="<?= htmlspecialchars($plano['nome_plano'], ENT_QUOTES) ?>"
                                                    data-valor="<?= number_format($plano['valor'], 2, ',', '') ?>"
                                                    data-inicio="<?= $plano['data_inicio'] ?>"
                                                    data-fim="<?= $plano['data_fim'] ?? '' ?>"
                                                    data-status="<?= $plano['status'] ?>">
                                                    <i class="bi bi-pencil me-1"></i> Editar
                                                </button>

                                                <?php if (strtoupper((string)$plano['status']) === 'INATIVO'): ?>
                                                    <form action="../../controller/PlanoController.php?acao=reativar" method="POST" class="plano-status-form" onsubmit="return confirm('Deseja reativar este plano?');">
                                                        <input type="hidden" name="id" value="<?= (int)$plano['id_plano'] ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['plano_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Ativar
                                                        </button>
                                                    </form>
                                                <?php elseif (strtoupper((string)$plano['status']) === 'ATIVO'): ?>
                                                    <form action="../../controller/PlanoController.php?acao=desativar" method="POST" class="plano-status-form" onsubmit="return confirm('Deseja desativar este plano?');">
                                                        <input type="hidden" name="id" value="<?= (int)$plano['id_plano'] ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['plano_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="bi bi-power me-1"></i> Desativar
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- MODAL VINCULAR NOVO PLANO -->
    <div class="modal fade" id="modalVincularPlano" tabindex="-1" aria-labelledby="modalVincularPlanoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <div class="modal-body px-4 pb-4 pt-0">
                    <form action="../../controller/PlanoController.php?acao=salvar" method="POST" style="max-width: 680px; margin: 0 auto;" class="p-0 border-0 shadow-none bg-transparent">
                        <div class="text-center mb-4">
                            <h3 class="fs-4 fw-bold">Vincular Novo Plano</h3>
                            <p class="text-muted small">Vincular novo plano para um aluno na sua academia.</p>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="id_usuario_aluno" class="form-label">Aluno: <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_usuario_aluno" name="id_usuario_aluno" required>
                                    <option value="">Selecione o aluno...</option>
                                    <?php foreach ($alunos as $aluno): ?>
                                        <option value="<?= $aluno['id_usuario'] ?>"><?= htmlspecialchars($aluno['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="id_modalidade" class="form-label">Modalidade: <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_modalidade" name="id_modalidade" required>
                                    <option value="">Selecione a modalidade...</option>
                                    <?php foreach ($modalidades as $modalidade): ?>
                                        <option value="<?= (int)$modalidade['id_modalidade']; ?>">
                                            <?= htmlspecialchars($modalidade['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="nome_plano" class="form-label">Plano Contratado: <span class="text-danger">*</span></label>
                                <select class="form-select" id="nome_plano" name="nome_plano" required>
                                    <option value="">Selecione o plano...</option>
                                    <optgroup label="Planos Mensais">
                                        <option value="Mensal 2x/semana">Mensal (2x/semana)</option>
                                        <option value="Mensal 3x/semana">Mensal (3x/semana)</option>
                                        <option value="Mensal Ilimitado">Mensal (Ilimitado)</option>
                                    </optgroup>
                                    <optgroup label="Planos Trimestrais">
                                        <option value="Trimestral 2x/semana">Trimestral (2x/semana)</option>
                                        <option value="Trimestral 3x/semana">Trimestral (3x/semana)</option>
                                        <option value="Trimestral Ilimitado">Trimestral (Ilimitado)</option>
                                    </optgroup>
                                    <optgroup label="Planos Anuais">
                                        <option value="Anual 2x/semana">Anual (2x/semana)</option>
                                        <option value="Anual 3x/semana">Anual (3x/semana)</option>
                                        <option value="Anual Ilimitado">Anual (Ilimitado)</option>
                                    </optgroup>
                                    <optgroup label="Planos All Inclusive">
                                        <option value="All Inclusive 2x/semana">All Inclusive (2x/semana)</option>
                                        <option value="All Inclusive 4x/semana">All Inclusive (4x/semana)</option>
                                        <option value="All Inclusive Ilimitado">All Inclusive (Ilimitado)</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="valor" class="form-label">Valor do Plano (R$): <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="valor" name="valor" placeholder="0,00" required>
                            </div>

                            <div class="col-md-4">
                                <label for="data_inicio" class="form-label">Data de Início: <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="data_fim" class="form-label">Data de Término (Opcional):</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim">
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label">Status do Plano: <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="ATIVO" selected>Ativo</option>
                                    <option value="INATIVO">Inativo</option>
                                    <option value="CANCELADO">Cancelado</option>
                                </select>
                            </div>

                            <div class="col-12 mt-4 d-flex gap-2 justify-content-end align-items-center">
                                <button type="button" class="btn btn-dark m-0 w-auto px-4 d-inline-flex justify-content-center align-items-center text-center" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn btn-success m-0 w-auto px-4 d-inline-flex justify-content-center align-items-center text-center">
                                    <i class="bi bi-check-circle me-1"></i> Vincular Plano
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR PLANO -->
    <div class="modal fade" id="modalEditarPlano" tabindex="-1" aria-labelledby="modalEditarPlanoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <div class="modal-body px-4 pb-4 pt-0">
                    <form action="../../controller/PlanoController.php?acao=atualizar" method="POST" style="max-width: 680px; margin: 0 auto;" class="p-0 border-0 shadow-none bg-transparent">
                        <input type="hidden" id="edit_id_plano" name="id_plano">

                        <div class="text-center mb-4">
                            <h3 class="fs-4 fw-bold">Editar Plano</h3>
                            <p class="text-muted small">Edite as informações do plano do aluno.</p>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_id_usuario_aluno" class="form-label">Aluno: <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_id_usuario_aluno" name="id_usuario_aluno" required>
                                    <option value="">Selecione o aluno...</option>
                                    <?php foreach ($alunos as $aluno): ?>
                                        <option value="<?= $aluno['id_usuario'] ?>"><?= htmlspecialchars($aluno['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_id_modalidade" class="form-label">Modalidade: <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_id_modalidade" name="id_modalidade" required>
                                    <option value="">Selecione a modalidade...</option>
                                    <?php foreach ($modalidades as $modalidade): ?>
                                        <option value="<?= (int)$modalidade['id_modalidade']; ?>">
                                            <?= htmlspecialchars($modalidade['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_nome_plano" class="form-label">Plano Contratado: <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_nome_plano" name="nome_plano" required>
                                    <option value="">Selecione o plano...</option>
                                    <optgroup label="Planos Mensais">
                                        <option value="Mensal 2x/semana">Mensal (2x/semana)</option>
                                        <option value="Mensal 3x/semana">Mensal (3x/semana)</option>
                                        <option value="Mensal Ilimitado">Mensal (Ilimitado)</option>
                                    </optgroup>
                                    <optgroup label="Planos Trimestrais">
                                        <option value="Trimestral 2x/semana">Trimestral (2x/semana)</option>
                                        <option value="Trimestral 3x/semana">Trimestral (3x/semana)</option>
                                        <option value="Trimestral Ilimitado">Trimestral (Ilimitado)</option>
                                    </optgroup>
                                    <optgroup label="Planos Anuais">
                                        <option value="Anual 2x/semana">Anual (2x/semana)</option>
                                        <option value="Anual 3x/semana">Anual (3x/semana)</option>
                                        <option value="Anual Ilimitado">Anual (Ilimitado)</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_valor" class="form-label">Valor do Plano (R$): <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_valor" name="valor" placeholder="0,00" required>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_data_inicio" class="form-label">Data de Início: <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_data_inicio" name="data_inicio" required>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_data_fim" class="form-label">Data de Término (Opcional):</label>
                                <input type="date" class="form-control" id="edit_data_fim" name="data_fim">
                            </div>

                            <div class="col-md-4">
                                <label for="edit_status" class="form-label">Status do Plano: <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="ATIVO">Ativo</option>
                                    <option value="INATIVO">Inativo</option>
                                    <option value="CANCELADO">Cancelado</option>
                                </select>
                            </div>

                            <div class="col-12 mt-4 d-flex gap-2 justify-content-end align-items-center">
                                <button type="button" class="btn btn-dark m-0 w-auto px-4 d-inline-flex justify-content-center align-items-center text-center" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn btn-success m-0 w-auto px-4 d-inline-flex justify-content-center align-items-center text-center">
                                    <i class="bi bi-check-circle me-1"></i> Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para preencher automaticamente o modal de edição com os dados da linha correspondente
        const modalEditarPlano = document.getElementById('modalEditarPlano');
        if (modalEditarPlano) {
            modalEditarPlano.addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget;
                
                modalEditarPlano.querySelector('#edit_id_plano').value = button.getAttribute('data-id');
                modalEditarPlano.querySelector('#edit_id_usuario_aluno').value = button.getAttribute('data-aluno');
                modalEditarPlano.querySelector('#edit_id_modalidade').value = button.getAttribute('data-modalidade');
                modalEditarPlano.querySelector('#edit_nome_plano').value = button.getAttribute('data-nome');
                modalEditarPlano.querySelector('#edit_valor').value = button.getAttribute('data-valor');
                modalEditarPlano.querySelector('#edit_data_inicio').value = button.getAttribute('data-inicio');
                modalEditarPlano.querySelector('#edit_data_fim').value = button.getAttribute('data-fim');
                modalEditarPlano.querySelector('#edit_status').value = button.getAttribute('data-status');
            });
        }
    </script>
</body>
</html>