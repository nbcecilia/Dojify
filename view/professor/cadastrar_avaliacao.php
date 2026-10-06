<?php
// view/professor/cadastrar_avaliacao.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../controller/AvaliacaoController.php';

if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['perfil_id'], [2, 3])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$idAcademia = $_SESSION['usuario']['id_academia'] ?? 0;
$idProfessor = $_SESSION['usuario']['id_usuario'] ?? 0;

$avaliacaoController = new AvaliacaoController();
$alunos = $avaliacaoController->listarAlunosPorAcademia($idAcademia);

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_aluno = $_POST['id_usuario_aluno'] ?? '';
    $data_avaliacao = $_POST['data_avaliacao'] ?? '';
    $habilidades = $_POST['habilidades_melhorar'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    $resposta = $avaliacaoController->cadastrar($idProfessor, $id_aluno, $data_avaliacao, $habilidades, $observacoes);

    if ($resposta['status']) {
        $sucesso = $resposta['mensagem'];
    } else {
        $erro = $resposta['mensagem'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar Avaliação - Dojify</title>
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
                <a href="listar_avaliacao.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($sucesso)): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($sucesso) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                 <div>
                    <h2>Nova Avaliação Técnica</h2>
                    <p class="text-muted">Registe o desempenho, pontos a melhorar e observações sobre o aluno.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="id_usuario_aluno" class="form-label">Aluno <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_usuario_aluno" name="id_usuario_aluno" required>
                            <option value="">Selecione o aluno...</option>
                            <?php foreach ($alunos as $aluno): ?>
                                <option value="<?= $aluno['id_usuario'] ?>"><?= htmlspecialchars($aluno['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="data_avaliacao" class="form-label">Data da Avaliação <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="data_avaliacao" name="data_avaliacao" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="habilidades_melhorar" class="form-label">Habilidades a Melhorar <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="habilidades_melhorar" name="habilidades_melhorar" rows="4" placeholder="Descreva os aspetos técnicos que o aluno precisa treinar..." required></textarea>
                    </div>

                    <div class="col-12">
                        <label for="observacoes" class="form-label">Observações Gerais / Evolução</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="4" placeholder="Notas sobre postura, disciplina, evolução física ou comportamental (opcional)"></textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-check-circle me-1"></i> Guardar Avaliação
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>