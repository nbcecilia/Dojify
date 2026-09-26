<?php
// view/gerente/editar_plano.php
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

$idPlano = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$idAcademia = (int) $_SESSION['id_academia'];

if (!$idPlano) {
    header('Location: listar_plano.php?erro=id_invalido');
    exit;
}

$dao = new PlanoDAO();
$plano = $dao->buscarPorId($idPlano, $idAcademia);

if (!$plano) {
    header('Location: listar_plano.php?erro=nao_encontrado');
    exit;
}

$alunos = $dao->listarAlunosPorAcademia($idAcademia);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Plano - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar-brand">
            <a href="home_gerente.php" class="logo-link">
                <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div><h1>Dojify</h1></div>
            </a>
        </div>
        <div class="navbar-user">
            <span class="user-greeting">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></span>
            <a href="listar_plano.php" class="btn btn-sm">Voltar</a>
            <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <div class="container">
        <form action="../../controller/PlanoController.php?acao=atualizar" method="POST">
            <h2>Editar Plano</h2>

            <input type="hidden" name="id_plano" value="<?= $plano['id_plano'] ?>">

            <label for="id_usuario_aluno">Aluno:</label>
            <select id="id_usuario_aluno" name="id_usuario_aluno" required>
                <option value="">Selecione o aluno...</option>
                <?php foreach ($alunos as $aluno): ?>
                    <option value="<?= $aluno['id_usuario'] ?>" <?= $aluno['id_usuario'] == $plano['id_usuario_aluno'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($aluno['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="nome_plano">Plano Contratado:</label>
            <select id="nome_plano" name="nome_plano" required>
                <option value="">Selecione o plano...</option>
                <optgroup label="Planos Mensais">
                    <option value="Mensal 2x/semana" <?= $plano['nome_plano'] === 'Mensal 2x/semana' ? 'selected' : '' ?>>Mensal (2x/semana)</option>
                    <option value="Mensal 3x/semana" <?= $plano['nome_plano'] === 'Mensal 3x/semana' ? 'selected' : '' ?>>Mensal (3x/semana)</option>
                    <option value="Mensal Ilimitado" <?= $plano['nome_plano'] === 'Mensal Ilimitado' ? 'selected' : '' ?>>Mensal (Ilimitado)</option>
                </optgroup>
                <optgroup label="Planos Trimestrais">
                    <option value="Trimestral 2x/semana" <?= $plano['nome_plano'] === 'Trimestral 2x/semana' ? 'selected' : '' ?>>Trimestral (2x/semana)</option>
                    <option value="Trimestral 3x/semana" <?= $plano['nome_plano'] === 'Trimestral 3x/semana' ? 'selected' : '' ?>>Trimestral (3x/semana)</option>
                    <option value="Trimestral Ilimitado" <?= $plano['nome_plano'] === 'Trimestral Ilimitado' ? 'selected' : '' ?>>Trimestral (Ilimitado)</option>
                </optgroup>
                <optgroup label="Planos Anuais">
                    <option value="Anual 2x/semana" <?= $plano['nome_plano'] === 'Anual 2x/semana' ? 'selected' : '' ?>>Anual (2x/semana)</option>
                    <option value="Anual 3x/semana" <?= $plano['nome_plano'] === 'Anual 3x/semana' ? 'selected' : '' ?>>Anual (3x/semana)</option>
                    <option value="Anual Ilimitado" <?= $plano['nome_plano'] === 'Anual Ilimitado' ? 'selected' : '' ?>>Anual (Ilimitado)</option>
                </optgroup>
            </select>

            <label for="valor">Valor do Plano (R$):</label>
            <input type="text" id="valor" name="valor" value="<?= number_format($plano['valor'], 2, ',', '') ?>" placeholder="0,00" required>

            <label for="data_inicio">Data de Início:</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($plano['data_inicio']) ?>" required>

            <label for="data_fim">Data de Término (Opcional):</label>
            <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($plano['data_fim'] ?? '') ?>">

            <label for="status">Status do Plano:</label>
            <select id="status" name="status" required>
                <option value="ATIVO" <?= $plano['status'] === 'ATIVO' ? 'selected' : '' ?>>Ativo</option>
                <option value="INATIVO" <?= $plano['status'] === 'INATIVO' ? 'selected' : '' ?>>Inativo</option>
                <option value="CANCELADO" <?= $plano['status'] === 'CANCELADO' ? 'selected' : '' ?>>Cancelado</option>
            </select>

            <div class="d-flex gap-2 mt-4">
                <a href="listar_plano.php" class="btn btn-secondary w-100 text-decoration-none text-center">Cancelar</a>
                <button type="submit" class="btn btn-success w-100 m-0">Salvar Alterações</button>
            </div>
        </form>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>