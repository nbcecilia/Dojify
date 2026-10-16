<?php
// view/gerente/cadastrar_plano.php
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
$alunos = $dao->listarAlunosPorAcademia($idAcademia);
$modalidades = (new ModalidadeDAO())->listarPorAcademia($idAcademia);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Plano - Dojify</title>
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
                <a href="listar_plano.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="text-center mb-4">
                <h2 class="fs-4 fw-bold">Vincular Novo Plano</h2>
                <p class="text-muted">Vincular novo plano para um aluno na sua academia.</p>
            </div>

            <form action="../../controller/PlanoController.php?acao=salvar" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                
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

                    <!-- Botões de Ação -->
                    <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                        <a href="listar_plano.php" class="btn btn-secondary px-4 m-0 w-auto">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 m-0 w-auto">
                            <i class="bi bi-check-circle me-1"></i> Cadastrar Plano
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