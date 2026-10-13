<?php
// view/gerente/gerenciar_horarios_turma.php
session_start();

if (
    !isset($_SESSION['usuario']) ||
    (int)$_SESSION['usuario']['perfil_id'] !== 2 ||
    !isset($_SESSION['usuario']['id_academia'])
) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/TurmaDAO.php';
require_once __DIR__ . '/../../model/dao/HorarioTurmaDAO.php';

$idTurma = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idTurma) {
    header("Location: listar_turma.php?erro=id_invalido");
    exit;
}

$idAcademia = (int) $_SESSION['usuario']['id_academia'];
$turmaDAO = new TurmaDAO();
$turmaAtual = $turmaDAO->buscarPorId($idTurma, $idAcademia);

if (!$turmaAtual) {
    header("Location: listar_turma.php?erro=nao_encontrado");
    exit;
}

$horarioDAO = new HorarioTurmaDAO();
$horarios = $horarioDAO->listarPorTurma($idTurma);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Horários - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="listar_turma.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para Turmas
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4 mb-4">
            <div class="mb-4">
                <h2>Gerenciar Horários</h2>
                <p class="text-muted">
                    Turma: <strong><?= htmlspecialchars($turmaAtual['nome']) ?></strong> | Modalidade: <?= htmlspecialchars($turmaAtual['modalidade_nome'] ?? '') ?>
                </p>
            </div>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Horário cadastrado com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Não foi possível cadastrar o horário. Verifique os dados.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h3 class="h5 mb-3">Adicionar Novo Horário</h3>
            <form action="../../controller/HorarioTurmaController.php?acao=salvar" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <input type="hidden" name="id_turma" value="<?= $idTurma ?>">

                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="dia_semana" class="form-label">Dia da Semana <span class="text-danger">*</span></label>
                        <select name="dia_semana" id="dia_semana" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="Segunda-Feira">Segunda-Feira</option>
                            <option value="Terça-Feira">Terça-Feira</option>
                            <option value="Quarta-Feira">Quarta-Feira</option>
                            <option value="Quinta-Feira">Quinta-Feira</option>
                            <option value="Sexta-Feira">Sexta-Feira</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="hora_inicio" class="form-label">Hora Início <span class="text-danger">*</span></label>
                        <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label for="hora_fim" class="form-label">Hora Fim <span class="text-danger">*</span></label>
                        <input type="time" name="hora_fim" id="hora_fim" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle me-1"></i> Adicionar
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="card shadow-sm border-0 p-4">
            <div class="mb-3">
                <h3 class="h5 mb-0">Horários Definidos</h3>
            </div>

            <?php if (empty($horarios)): ?>
                <p class="text-center text-muted py-4 mb-0">Nenhum horário cadastrado para esta turma.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Dia da Semana</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($horarios as $h): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($h['dia_semana']) ?></strong></td>
                                    <td><?= htmlspecialchars($h['hora_inicio']) ?></td>
                                    <td><?= htmlspecialchars($h['hora_fim']) ?></td>
                                    <td class="text-end">
                                        <a href="../../controller/HorarioTurmaController.php?acao=excluir&id_horario=<?= $h['id_horario'] ?>&id_turma=<?= $idTurma ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Deseja realmente remover este horário?');">
                                            <i class="bi bi-trash me-1"></i> Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>