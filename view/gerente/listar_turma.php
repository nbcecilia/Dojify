<?php
// view/gerente/listar_turma.php
session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/TurmaDAO.php';
require_once __DIR__ . '/../../model/dao/HorarioTurmaDAO.php';

$dao = new \TurmaDAO();
$idAcademia = (int) $_SESSION['id_academia'];
$turmas = $dao->listarPorAcademia($idAcademia);
$professores = $dao->listarProfessoresPorAcademia($idAcademia);
$modalidades = $dao->listarModalidadesPorAcademia($idAcademia);
$horarioDAO = new \HorarioTurmaDAO();
$horariosPorTurma = [];
foreach ($turmas as $turma) {
    $horariosPorTurma[(int) $turma['id_turma']] = $horarioDAO->listarPorTurma((int) $turma['id_turma']);
}
if (!isset($_SESSION['turma_status_csrf_token'])) {
    $_SESSION['turma_status_csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turmas - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <style>
        .turma-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }

        .turma-status-form {
            display: inline-flex;
            align-items: center;
            width: auto;
            max-width: none;
            margin: 0;
            padding: 0;
            border: 0;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
        }

        .table .turma-status-form > button[type="submit"] {
            width: auto;
            margin: 0;
            padding: 5px 10px;
            font-size: 0.8rem;
            line-height: 1.5;
            border-radius: 4px;
            border: 1px solid transparent;
            font-weight: 500;
            white-space: nowrap;
        }

        .table .turma-status-form > button.btn-danger[type="submit"] {
            background-color: var(--action-danger);
            color: #ffffff;
        }

        .table .turma-status-form > button.btn-danger[type="submit"]:hover {
            background-color: var(--action-danger-hover);
        }

        .table .turma-status-form > button.btn-success[type="submit"] {
            background-color: var(--action-submit);
            color: #ffffff;
        }

        .table .turma-status-form > button.btn-success[type="submit"]:hover {
            background-color: var(--action-submit-hover);
        }
    </style>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovaTurma">
                    <i class="bi bi-plus-circle me-1"></i> Cadastrar Turma
                </button>
            </div>

        </header>

        <section class="card shadow-sm border-0 p-4">
            <?php if (isset($_GET['sucesso']) && !isset($_GET['modal_horario'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Operação realizada com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['erro']) && !isset($_GET['modal_horario']) && !isset($_GET['modal_editar']) && !isset($_GET['modal'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Não foi possível realizar a operação.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h2>Gestão de Turmas e Horários</h2>
                <p class="text-muted">Gestão das turmas e horários da academia.</p>
            </div>

            <?php if (empty($turmas)): ?>
                <p class="text-center text-muted py-4">Nenhuma turma cadastrada.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Turma</th>
                                <th>Professor</th>
                                <th>Modalidade</th>
                                <th>Capacidade</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($turmas as $turma): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($turma['nome']) ?></strong></td>
                                    <td><?= htmlspecialchars($turma['professor_nome']) ?></td>
                                    <td><?= htmlspecialchars($turma['modalidade_nome']) ?></td>
                                    <td><?= htmlspecialchars($turma['capacidade']) ?></td>
                                    <td><?= htmlspecialchars($turma['status']) ?></td>
                                    <td class="text-center">
                                        <div class="turma-actions">
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalHorarios<?= (int) $turma['id_turma'] ?>">
                                            <i class="bi bi-clock me-1"></i> Gerir Horário
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarTurma<?= (int) $turma['id_turma'] ?>">
                                            <i class="bi bi-pencil me-1"></i> Editar
                                        </button>
                                        <?php if (strtoupper((string)$turma['status']) === 'INATIVA'): ?>
                                            <form action="../../controller/TurmaController.php?acao=reativar" method="POST" class="turma-status-form" onsubmit="return confirm('Deseja reativar esta turma?');">
                                                <input type="hidden" name="id" value="<?= (int)$turma['id_turma'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['turma_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Ativar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="../../controller/TurmaController.php?acao=desativar" method="POST" class="turma-status-form" onsubmit="return confirm('Deseja desativar esta turma?');">
                                                <input type="hidden" name="id" value="<?= (int)$turma['id_turma'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['turma_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-power me-1"></i> Desativar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <div class="modal fade" id="modalNovaTurma" tabindex="-1" aria-labelledby="modalNovaTurmaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <form action="../../controller/TurmaController.php" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title fs-5" id="modalNovaTurmaLabel">Cadastrar Nova Turma</h2>
                            <p class="text-muted small mb-0">Informe os dados da nova turma da academia.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="cadastrar">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nova_turma_nome" class="form-label">Nome da turma <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nova_turma_nome" name="nome" maxlength="100" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nova_turma_professor" class="form-label">Professor <span class="text-danger">*</span></label>
                                <select class="form-select" id="nova_turma_professor" name="id_usuario_professor" required>
                                    <option value="">Selecione o professor</option>
                                    <?php foreach ($professores as $professor): ?>
                                        <option value="<?= (int)$professor['id_usuario'] ?>">
                                            <?= htmlspecialchars($professor['nome'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="nova_turma_modalidade" class="form-label">Modalidade <span class="text-danger">*</span></label>
                                <select class="form-select" id="nova_turma_modalidade" name="id_modalidade" required>
                                    <option value="">Selecione a modalidade</option>
                                    <?php foreach ($modalidades as $modalidade): ?>
                                        <option value="<?= (int)$modalidade['id_modalidade'] ?>">
                                            <?= htmlspecialchars($modalidade['nome'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="nova_turma_capacidade" class="form-label">Capacidade <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="nova_turma_capacidade" name="capacidade" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nova_turma_nivel" class="form-label">Nível (opcional)</label>
                                <select class="form-select" id="nova_turma_nivel" name="nivel">
                                    <option value="">Selecione o nível</option>
                                    <option value="INICIANTE">Iniciante</option>
                                    <option value="INTERMEDIARIO">Intermediário</option>
                                    <option value="AVANCADO">Avançado</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="nova_turma_status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="nova_turma_status" name="status" required>
                                    <option value="ATIVA">Ativa</option>
                                    <option value="INATIVA">Inativa</option>
                                </select>
                            </div>
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

    <?php foreach ($turmas as $turma): ?>
        <?php $idTurma = (int) $turma['id_turma']; ?>
        <div class="modal fade" id="modalEditarTurma<?= $idTurma ?>" tabindex="-1" aria-labelledby="modalEditarTurmaLabel<?= $idTurma ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <form action="../../controller/TurmaController.php?acao=atualizar" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title fs-5" id="modalEditarTurmaLabel<?= $idTurma ?>">Editar Turma</h2>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($turma['nome'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (isset($_GET['modal_editar']) && (int) $_GET['modal_editar'] === $idTurma && isset($_GET['erro'])): ?>
                                <div class="alert alert-danger" role="alert">Não foi possível salvar as alterações. Verifique os dados e tente novamente.</div>
                            <?php endif; ?>
                            <input type="hidden" name="id_turma" value="<?= $idTurma ?>">
                            <input type="hidden" name="retorno" value="listar_turma">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editar_nome_<?= $idTurma ?>" class="form-label">Nome da turma <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editar_nome_<?= $idTurma ?>" name="nome" maxlength="100" value="<?= htmlspecialchars($turma['nome'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editar_professor_<?= $idTurma ?>" class="form-label">Professor <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editar_professor_<?= $idTurma ?>" name="id_usuario_professor" required>
                                        <option value="">Selecione o professor</option>
                                        <?php foreach ($professores as $professor): ?>
                                            <option value="<?= (int) $professor['id_usuario'] ?>" <?= (int) $professor['id_usuario'] === (int) $turma['id_usuario_professor'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($professor['nome'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editar_modalidade_<?= $idTurma ?>" class="form-label">Modalidade <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editar_modalidade_<?= $idTurma ?>" name="id_modalidade" required>
                                        <option value="">Selecione a modalidade</option>
                                        <?php foreach ($modalidades as $modalidade): ?>
                                            <option value="<?= (int) $modalidade['id_modalidade'] ?>" <?= (int) $modalidade['id_modalidade'] === (int) $turma['id_modalidade'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($modalidade['nome'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editar_capacidade_<?= $idTurma ?>" class="form-label">Capacidade <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="editar_capacidade_<?= $idTurma ?>" name="capacidade" min="1" value="<?= htmlspecialchars((string) $turma['capacidade'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editar_nivel_<?= $idTurma ?>" class="form-label">Nível <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editar_nivel_<?= $idTurma ?>" name="nivel" required>
                                        <option value="">Selecione o nível</option>
                                        <option value="INICIANTE" <?= $turma['nivel'] === 'INICIANTE' ? 'selected' : '' ?>>Iniciante</option>
                                        <option value="INTERMEDIARIO" <?= $turma['nivel'] === 'INTERMEDIARIO' ? 'selected' : '' ?>>Intermediário</option>
                                        <option value="AVANCADO" <?= $turma['nivel'] === 'AVANCADO' ? 'selected' : '' ?>>Avançado</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editar_status_<?= $idTurma ?>" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editar_status_<?= $idTurma ?>" name="status" required>
                                        <option value="ATIVA" <?= $turma['status'] === 'ATIVA' ? 'selected' : '' ?>>Ativa</option>
                                        <option value="INATIVA" <?= $turma['status'] === 'INATIVA' ? 'selected' : '' ?>>Inativa</option>
                                    </select>
                                </div>
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
    <?php endforeach; ?>

    <?php foreach ($turmas as $turma): ?>
        <?php $idTurma = (int) $turma['id_turma']; ?>
        <div class="modal fade" id="modalHorarios<?= $idTurma ?>" tabindex="-1" aria-labelledby="modalHorariosLabel<?= $idTurma ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title fs-5" id="modalHorariosLabel<?= $idTurma ?>">Gerir Horário</h2>
                            <p class="text-muted small mb-0">
                                <?= htmlspecialchars($turma['nome'], ENT_QUOTES, 'UTF-8') ?>
                                <span aria-hidden="true">·</span>
                                <?= htmlspecialchars($turma['modalidade_nome'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (isset($_GET['modal_horario']) && (int) $_GET['modal_horario'] === $idTurma && isset($_GET['sucesso'])): ?>
                            <div class="alert alert-success" role="alert">Horário atualizado com sucesso.</div>
                        <?php elseif (isset($_GET['modal_horario']) && (int) $_GET['modal_horario'] === $idTurma && isset($_GET['erro'])): ?>
                            <div class="alert alert-danger" role="alert">Não foi possível realizar a operação. Verifique os dados e tente novamente.</div>
                        <?php endif; ?>

                        <h3 class="h6 mb-3">Horários definidos</h3>
                        <?php if (empty($horariosPorTurma[$idTurma])): ?>
                            <p class="text-muted">Nenhum horário cadastrado para esta turma.</p>
                        <?php else: ?>
                            <div class="table-responsive mb-4">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Dia da semana</th>
                                            <th>Início</th>
                                            <th>Fim</th>
                                            <th class="text-end">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($horariosPorTurma[$idTurma] as $horario): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($horario['dia_semana'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars(substr((string) $horario['hora_inicio'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars(substr((string) $horario['hora_fim'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="text-end">
                                                    <a href="../../controller/HorarioTurmaController.php?acao=excluir&amp;id_horario=<?= (int) $horario['id_horario'] ?>&amp;id_turma=<?= $idTurma ?>&amp;retorno=listar_turma" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente remover este horário?');">
                                                        <i class="bi bi-trash me-1"></i> Excluir
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <hr>
                        <h3 class="h6 mb-3">Adicionar horário</h3>
                        <form action="../../controller/HorarioTurmaController.php?acao=salvar" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                            <input type="hidden" name="id_turma" value="<?= $idTurma ?>">
                            <input type="hidden" name="retorno" value="listar_turma">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="dia_semana_<?= $idTurma ?>" class="form-label">Dia da semana <span class="text-danger">*</span></label>
                                    <select name="dia_semana" id="dia_semana_<?= $idTurma ?>" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        <option value="Segunda-Feira">Segunda-feira</option>
                                        <option value="Terça-Feira">Terça-feira</option>
                                        <option value="Quarta-Feira">Quarta-feira</option>
                                        <option value="Quinta-Feira">Quinta-feira</option>
                                        <option value="Sexta-Feira">Sexta-feira</option>
                                        <option value="Sábado">Sábado</option>
                                        <option value="Domingo">Domingo</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="hora_inicio_<?= $idTurma ?>" class="form-label">Início <span class="text-danger">*</span></label>
                                    <input type="time" name="hora_inicio" id="hora_inicio_<?= $idTurma ?>" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="hora_fim_<?= $idTurma ?>" class="form-label">Fim <span class="text-danger">*</span></label>
                                    <input type="time" name="hora_fim" id="hora_fim_<?= $idTurma ?>" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-plus-circle me-1"></i> Adicionar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($_GET['modal_editar']) && filter_var($_GET['modal_editar'], FILTER_VALIDATE_INT)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('modalEditarTurma<?= (int) $_GET['modal_editar'] ?>');
                if (modal) {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    <?php elseif (isset($_GET['modal_horario']) && filter_var($_GET['modal_horario'], FILTER_VALIDATE_INT)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('modalHorarios<?= (int) $_GET['modal_horario'] ?>');
                if (modal) {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    <?php elseif (isset($_GET['erro']) && isset($_GET['modal'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNovaTurma')).show();
            });
        </script>
    <?php endif; ?>
</body>
</html>