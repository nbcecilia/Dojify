<?php
// view/professor/cadastrar_graduacao.php

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
$idProfessor = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']['id_usuario'] ?? 0;

$graduacaoController = new GraduacaoController();
$alunos = $graduacaoController->listarAlunosPorPerfilUsuario($perfilId, $idAcademia, $idProfessor);

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alunoModalidade = explode('|', (string)($_POST['id_usuario_aluno_modalidade'] ?? ''), 2);
    $id_aluno = filter_var($alunoModalidade[0] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $id_modalidade = filter_var($alunoModalidade[1] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $data_graduacao = $_POST['data_graduacao'] ?? '';
    $faixa = trim((string)($_POST['faixa'] ?? ''));
    $grau = trim((string)($_POST['grau'] ?? ''));

    $resposta = $graduacaoController->cadastrar($id_aluno, (int)$idProfessor, $id_modalidade, $data_graduacao, $faixa, $grau);

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
    <title>Registar Graduação - Dojify</title>
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
                <a href="listar_graduacao.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Histórico de Graduações
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
                <div class="mb-4">
                    <h2>Registar Graduação</h2>
                    <p class="text-muted">Atribua uma nova faixa ou grau ao aluno.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="id_usuario_aluno_modalidade" class="form-label">Aluno e modalidade <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_usuario_aluno_modalidade" name="id_usuario_aluno_modalidade" required>
                            <option value="">Selecione o aluno e a modalidade...</option>
                            <?php foreach ($alunos as $aluno): ?>
                                <option value="<?= (int)$aluno['id_usuario'] ?>|<?= (int)$aluno['id_modalidade'] ?>">
                                    <?= htmlspecialchars($aluno['nome'] . ' - ' . $aluno['nome_modalidade'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="data_graduacao" class="form-label">Data da Graduação <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="data_graduacao" name="data_graduacao" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="faixa" class="form-label">Faixa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="faixa" name="faixa" placeholder="Ex: Azul, Roxa, Marrom..." required>
                    </div>

                    <div class="col-md-6">
                        <label for="grau" class="form-label">Grau</label>
                        <input type="text" class="form-control" id="grau" name="grau" placeholder="Ex: 1º Grau, 2º Grau (opcional)">
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-dark px-4">
                            <i class="bi bi-check-circle me-1"></i> Guardar Graduação
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>