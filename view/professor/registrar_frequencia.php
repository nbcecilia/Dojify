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

// Processamento do formulário (Padrão PRG: Post / Redirect / Get)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataAula = $_POST['data_aula'] ?? date('Y-m-d');
    $idTurmaPost = $_POST['id_turma'] ?? 1;
    $presencas = $_POST['presenca'] ?? [];

    if (!empty($presencas)) {
        $salvou = $dao->salvarChamada($dataAula, $presencas);
        if ($salvou) {
            // Redireciona de volta preservando os filtros e acionando a mensagem de sucesso
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
$idTurma = filter_input(INPUT_GET, 'id_turma', FILTER_VALIDATE_INT) ?? 1;
$dataAula = $_GET['data'] ?? date('Y-m-d');

// Busca os alunos agendados para a chamada
$alunosChamada = $dao->listarAlunosParaChamada($idTurma, $dataAula);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dojify - Registar Chamada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Registar Chamada</h2>
            <a href="../gerente/home_gerente.php" class="btn btn-secondary btn-sm">Voltar ao Painel</a>
        </div>

        <!-- Alerta de Sucesso -->
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <strong>Chamada guardada com sucesso!</strong> Os dados de presença foram registados.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Alerta de Erro -->
        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($mensagemErro) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Filtro por Turma e Data -->
        <form method="GET" class="card p-3 mb-4 shadow-sm bg-white">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="id_turma" class="form-label">ID da Turma:</label>
                    <input type="number" name="id_turma" id="id_turma" class="form-control" value="<?= $idTurma ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="data" class="form-label">Data da Aula:</label>
                    <input type="date" name="data" id="data" class="form-control" value="<?= $dataAula ?>" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Carregar Alunos</button>
                </div>
            </div>
        </form>

        <!-- Formulário de Chamada -->
        <form method="POST" action="">
            <input type="hidden" name="data_aula" value="<?= htmlspecialchars($dataAula) ?>">
            <input type="hidden" name="id_turma" value="<?= htmlspecialchars($idTurma) ?>">
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Nome do Aluno</th>
                                <th class="text-center" style="width: 200px;">Presença</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($alunosChamada)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">Nenhum aluno agendado encontrado para esta turma e data.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($alunosChamada as $aluno): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($aluno['aluno_nome']) ?></strong></td>
                                        <td class="text-center">
                                            <?php 
                                                // Mantém o estado atual se já foi guardado antes; caso contrário, assume 1 (Presente)
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

                    <?php if (!empty($alunosChamada)): ?>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-success px-4">Confirmar Chamada</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>