<?php
// view/gerente/listar_modalidade.php
session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/ModalidadeDAO.php';

$dao = new ModalidadeDAO();
$idAcademia = (int) $_SESSION['id_academia'];
$modalidades = $dao->listarTodasPorAcademia($idAcademia);

if (!isset($_SESSION['modalidade_status_csrf_token'])) {
    $_SESSION['modalidade_status_csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modalidades - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <style>
        .modalidade-status-form {
            display: inline-flex !important;
            align-items: center !important;
            width: auto !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            vertical-align: middle;
        }

        .modalidade-status-button {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: auto !important;
            min-width: 0 !important;
            margin: 0 !important;
            padding: 5px 10px !important;
            border: 0 !important;
            border-radius: 4px !important;
            box-shadow: none !important;
            color: #fff !important;
            font-size: 0.8rem !important;
            font-weight: 500 !important;
            line-height: 1.5 !important;
            white-space: nowrap;
        }

        .modalidade-status-button.btn-danger {
            background-color: var(--action-danger) !important;
        }

        .modalidade-status-button.btn-danger:hover {
            background-color: var(--action-danger-hover) !important;
        }

        .modalidade-status-button.btn-success {
            background-color: var(--action-submit) !important;
        }

        .modalidade-status-button.btn-success:hover {
            background-color: var(--action-submit-hover) !important;
        }
    </style>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCadastrarModalidade">
                    <i class="bi bi-plus-circle me-1"></i> Cadastrar Modalidade
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

            <?php if (isset($_GET['erro']) && !isset($_GET['modal'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Não foi possível realizar a operação.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h2>Gestão de Modalidades</h2>
                <p class="text-muted">Gestão das modalidades oferecidas pela academia.</p>
            </div>

            <?php if (empty($modalidades)): ?>
                <p class="text-center text-muted py-4">Nenhuma modalidade cadastrada.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modalidades as $modalidade): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($modalidade['nome']) ?></strong></td>
                                    <td><?= htmlspecialchars($modalidade['descricao'] ?? '') ?></td>
                                    <td>
                                        <span class="badge <?= $modalidade['status'] === 'ATIVA' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($modalidade['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($modalidade['status'] === 'ATIVA'): ?>
                                        <button type="button" class="btn btn-info btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarModalidade"
                                                data-id="<?= (int)$modalidade['id_modalidade'] ?>"
                                                data-nome="<?= htmlspecialchars($modalidade['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-descricao="<?= htmlspecialchars($modalidade['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="bi bi-pencil me-1"></i> Editar
                                        </button>
                                        <?php endif; ?>
                                        <form action="../../controller/ModalidadeController.php?acao=<?= $modalidade['status'] === 'ATIVA' ? 'desativar' : 'reativar' ?>"
                                              method="POST" class="modalidade-status-form"
                                              onsubmit="return confirm('<?= $modalidade['status'] === 'ATIVA' ? 'Deseja realmente desativar esta modalidade?' : 'Deseja reativar esta modalidade?' ?>');">
                                            <input type="hidden" name="id" value="<?= (int)$modalidade['id_modalidade'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['modalidade_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-sm modalidade-status-button <?= $modalidade['status'] === 'ATIVA' ? 'btn-danger' : 'btn-success' ?>">
                                                <i class="bi <?= $modalidade['status'] === 'ATIVA' ? 'bi-power' : 'bi-arrow-counterclockwise' ?> me-1" aria-hidden="true"></i><?= $modalidade['status'] === 'ATIVA' ? 'Desativar' : 'Reativar' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <div class="modal fade" id="modalCadastrarModalidade" tabindex="-1" aria-labelledby="modalCadastrarModalidadeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="../../controller/ModalidadeController.php" method="POST" class="p-0 border-0 shadow-none bg-transparent">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title fs-5" id="modalCadastrarModalidadeLabel">Cadastrar Modalidade</h2>
                            <p class="text-muted small mb-0">Informe os dados da modalidade oferecida pela academia.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="cadastrar">
                        <?php if (isset($_GET['modal']) && $_GET['modal'] === 'cadastrar' && isset($_GET['erro'])): ?>
                            <div class="alert alert-danger" role="alert">Não foi possível cadastrar a modalidade. Verifique os dados e tente novamente.</div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="nova_modalidade_nome" class="form-label">Nome da modalidade <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nova_modalidade_nome" name="nome" maxlength="100" required>
                        </div>
                        <div>
                            <label for="nova_modalidade_descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="nova_modalidade_descricao" name="descricao" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Cadastrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarModalidade" tabindex="-1" aria-labelledby="modalEditarModalidadeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="../../controller/ModalidadeController.php" method="POST" class="p-0 border-0 shadow-none bg-transparent">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="modalEditarModalidadeLabel">Editar Modalidade</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="atualizar">
                        <input type="hidden" name="id_modalidade" id="editar_id_modalidade">
                        <div class="mb-3">
                            <label for="editar_nome_modalidade" class="form-label">Nome da modalidade <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editar_nome_modalidade" name="nome" maxlength="100" required>
                        </div>
                        <div>
                            <label for="editar_descricao_modalidade" class="form-label">Descrição</label>
                            <textarea class="form-control" id="editar_descricao_modalidade" name="descricao" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Salvar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (isset($_GET['modal']) && $_GET['modal'] === 'cadastrar' && isset($_GET['erro'])): ?>
        document.addEventListener('DOMContentLoaded', function () {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCadastrarModalidade')).show();
        });
        <?php endif; ?>

        document.getElementById('modalEditarModalidade').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            this.querySelector('#editar_id_modalidade').value = button.dataset.id;
            this.querySelector('#editar_nome_modalidade').value = button.dataset.nome;
            this.querySelector('#editar_descricao_modalidade').value = button.dataset.descricao;
        });
    </script>
</body>
</html>