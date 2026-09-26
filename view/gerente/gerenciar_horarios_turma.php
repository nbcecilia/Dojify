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
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Horários - Dojify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Teu CSS personalizado (sobrescreve o Bootstrap mantendo a identidade visual) -->
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>

    <!-- Topo dinâmico -->
    <?php include '../includes/header.php'; ?>

    <!-- Conteúdo principal -->
    <main class="container">

        <!-- Cabeçalho flexível -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Gerenciar Horários</h1>
                <p class="text-muted mb-0">
                    Turma: <strong><?= htmlspecialchars($turmaAtual['nome']) ?></strong> | Modalidade: <?= htmlspecialchars($turmaAtual['modalidade_nome'] ?? '') ?>
                </p>
            </div>
            <div>
                <a href="listar_turma.php" class="btn btn-secondary">
                    ⬅ Voltar para Turmas
                </a>
            </div>
        </div>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-success py-2">Horário cadastrado com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-danger py-2">Não foi possível cadastrar o horário. Verifique os dados.</div>
        <?php endif; ?>

        <!-- Formulário para Adicionar Horário com Bootstrap Grid -->
        <div class="card p-4 mb-4 shadow-sm">
            <h3 class="h5 mb-3">Adicionar Novo Horário</h3>
            <form action="../../controller/HorarioTurmaController.php?acao=salvar" method="POST" class="m-0 p-0 border-0 shadow-none bg-transparent">
                <input type="hidden" name="id_turma" value="<?= $idTurma ?>">

                <div class="row g-3 align-items-end">
                    <!-- Dia da Semana (Ocupa mais espaço) -->
                    <div class="col-md-4">
                        <label for="dia_semana" class="form-label">Dia da Semana</label>
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

                    <!-- Hora Início -->
                    <div class="col-md-3">
                        <label for="hora_inicio" class="form-label">Hora Início</label>
                        <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required>
                    </div>

                    <!-- Hora Fim -->
                    <div class="col-md-3">
                        <label for="hora_fim" class="form-label">Hora Fim</label>
                        <input type="time" name="hora_fim" id="hora_fim" class="form-control" required>
                    </div>

                    <!-- Botão Adicionar -->
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">Adicionar</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabela de Horários Cadastrados -->
        <h3 class="h5 mb-3">Horários Definidos</h3>

        <?php if (empty($horarios)): ?>
            <p class="text-muted">Nenhum horário cadastrado para esta turma.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
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
                                <td><?= htmlspecialchars($h['dia_semana']) ?></td>
                                <td><?= htmlspecialchars($h['hora_inicio']) ?></td>
                                <td><?= htmlspecialchars($h['hora_fim']) ?></td>
                                <td class="text-end">
                                    <a href="../../controller/HorarioTurmaController.php?acao=excluir&id_horario=<?= $h['id_horario'] ?>&id_turma=<?= $idTurma ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Deseja realmente remover este horário?');">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </main>

    <!-- Rodapé -->
    <?php include '../includes/footer.php'; ?>

</body>
</html>