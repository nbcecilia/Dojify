<?php
// view/professor/registrar_frequencia.php
session_start();

// Validação de acesso (Gerente ou Professor)
if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['perfil_id'], [2, 3])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/PresencaDAO.php';

$dao = new PresencaDAO();
$mensagemErro = '';

$perfilId = (int)$_SESSION['usuario']['perfil_id'];
$idAcademia = $_SESSION['usuario']['id_academia'] ?? 0;
$idProfessor = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']['id_usuario'] ?? 0;

// Processamento do formulário (Padrão PRG: Post / Redirect / Get)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataAula = $_POST['data_aula'] ?? date('Y-m-d');
    $idTurmaPost = $_POST['id_turma'] ?? '';
    $presencas = $_POST['presenca'] ?? [];

    if (!empty($presencas)) {
        $salvou = $dao->salvarChamada($dataAula, $presencas);
        if ($salvou) {
            header("Location: registrar_frequencia.php?id_turma={$idTurmaPost}&data={$dataAula}&sucesso=1");
            exit;
        } else {
            $mensagemErro = "Ocorreu um erro ao guardar a chamada. Tenta novamente.";
        }
    } else {
        $mensagemErro = "Nenhum registo de presença foi selecionado.";
    }
}

// Parâmetros de filtro obtidos via GET
$idTurma = filter_input(INPUT_GET, 'id_turma', FILTER_VALIDATE_INT) ?? '';
$dataAula = $_GET['data'] ?? date('Y-m-d');

// Regra de perfil: Gerente vê todas as turmas da academia; Professor vê apenas as suas turmas
if ($perfilId === 2) {
    $listaTurmas = $dao->listarTurmasPorAcademia($idAcademia);
} else {
    $listaTurmas = $dao->listarTurmasPorProfessor($idProfessor);
}

// Busca os alunos agendados para a chamada caso haja uma turma selecionada
$alunosChamada = !empty($idTurma) ? $dao->listarAlunosParaChamada($idTurma, $dataAula) : [];
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar Chamada - Dojify</title>
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

                <a href="listar_frequencia.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Histórico de Chamada
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            
            <?php if (!empty($mensagemErro)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($mensagemErro) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <strong>Chamada guardada com sucesso!</strong> Os dados de presença foram registados.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Título e Descrição -->
            <div class="mb-4">
                <h2>Registar Chamada</h2>
                <p class="text-muted">Efetue o controlo de presenças e registe a chamada das turmas.</p>
            </div>

            <!-- Filtro por Turma e Data -->
            <form method="GET" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="id_turma" class="form-label">Turma <span class="text-danger">*</span></label>
                        <select name="id_turma" id="id_turma" class="form-select" required>
                            <option value="">Selecione a turma...</option>
                            <?php foreach ($listaTurmas as $turma): ?>
                                <option value="<?= (int)$turma['id_turma'] ?>" <?= $idTurma == $turma['id_turma'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($turma['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="data" class="form-label">Data da Aula <span class="text-danger">*</span></label>
                        <input type="date" name="data" id="data" class="form-control" value="<?= $dataAula ?>" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="bi bi-search me-1"></i> Carregar Alunos
                        </button>
                    </div>
                </div>
            </form>

            <!-- Formulário de Chamada -->
            <form method="POST" action="" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <input type="hidden" name="data_aula" value="<?= htmlspecialchars($dataAula) ?>">
                <input type="hidden" name="id_turma" value="<?= htmlspecialchars($idTurma) ?>">
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Nome do Aluno</th>
                                <th class="text-center" style="width: 200px;">Presença</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($idTurma)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">Selecione uma turma e clique em "Carregar Alunos".</td>
                                </tr>
                            <?php elseif (empty($alunosChamada)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">Nenhum aluno com agendamento confirmado encontrado para esta turma e data.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($alunosChamada as $aluno): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($aluno['aluno_nome']) ?></strong></td>
                                        <td class="text-center">
                                            <?php 
                                                $statusAtual = isset($aluno['status_presenca']) ? (int)$aluno['status_presenca'] : 1; 
                                            ?>
                                            <div class="btn-group" role="group">
                                                <input type="radio" class="btn-check" name="presenca[<?= $aluno['id_agendamento'] ?>]" id="presente_<?= $aluno['id_agendamento'] ?>" value="1" <?= $statusAtual === 1 ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-success btn-sm" for="presente_<?= $aluno['id_agendamento'] ?>">Presente</label>

                                                <input type="radio" class="btn-check" name="presenca[<?= $aluno['id_agendamento'] ?>]" id="ausente_<?= $aluno['id_agendamento'] ?>" value="0" <?= $statusAtual === 0 ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-danger btn-sm" for="ausente_<?= $aluno['id_agendamento'] ?>">Ausente</label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($alunosChamada)): ?>
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-dark btn-sm btn-confirmar-chamada" style="width: auto; margin-top: 0; padding: 5px 10px; font-size: 0.8rem;">
                            <i class="bi bi-check-circle me-1"></i> Confirmar Chamada
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>