<?php
// view/gerente/editar_turma.php
session_start();

// Valida se o usuário é gerente (perfil 2) e se possui academia na sessão
if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2 || !isset($_SESSION['usuario']['id_academia'])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/TurmaDAO.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$idAcademia = (int) $_SESSION['usuario']['id_academia'];

if (!$id) {
    header('Location: listar_turma.php?erro=id_invalido');
    exit;
}

$dao = new TurmaDAO();
$turma = $dao->buscarPorId($id, $idAcademia);

if (!$turma) {
    header('Location: listar_turma.php?erro=nao_encontrado');
    exit;
}

$professores = $dao->listarProfessoresPorAcademia($idAcademia);
$modalidades = $dao->listarModalidadesPorAcademia($idAcademia);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Turma - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar-brand">
            <a href="home_gerente.php" class="logo-link">
                <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div>
                    <h1>Dojify</h1>
                </div>
            </a>
        </div>
        
        <div class="navbar-user">
            <span class="user-greeting">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></span>
            <a href="listar_turma.php" class="btn btn-sm">Voltar</a>
            <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <div class="container">
        <form action="../../controller/TurmaController.php?acao=atualizar" method="POST">
            <h2>Editar Turma</h2>
            
            <!-- ID Oculto da Turma -->
            <input type="hidden" name="id_turma" value="<?= $turma['id_turma'] ?>">

            <label for="nome">Nome da Turma:</label>
            <input type="text" id="nome" name="nome" maxlength="100" value="<?= htmlspecialchars($turma['nome']) ?>" required>

            <label for="id_usuario_professor">Professor:</label>
            <select id="id_usuario_professor" name="id_usuario_professor" required>
                <option value="">Selecione o professor</option>
                <?php foreach ($professores as $professor): ?>
                    <option value="<?= $professor['id_usuario'] ?>" <?= $professor['id_usuario'] == $turma['id_usuario_professor'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($professor['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_modalidade">Modalidade:</label>
            <select id="id_modalidade" name="id_modalidade" required>
                <option value="">Selecione a modalidade</option>
                <?php foreach ($modalidades as $modalidade): ?>
                    <option value="<?= $modalidade['id_modalidade'] ?>" <?= $modalidade['id_modalidade'] == $turma['id_modalidade'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($modalidade['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="capacidade">Capacidade:</label>
            <input type="number" id="capacidade" name="capacidade" min="1" value="<?= htmlspecialchars($turma['capacidade']) ?>" required>

            <label for="nivel">Nível:</label>
            <select id="nivel" name="nivel" required>
                <option value="">Selecione o nível</option>
                <option value="INICIANTE" <?= $turma['nivel'] == 'INICIANTE' ? 'selected' : '' ?>>Iniciante</option>
                <option value="INTERMEDIARIO" <?= $turma['nivel'] == 'INTERMEDIARIO' ? 'selected' : '' ?>>Intermediário</option>
                <option value="AVANCADO" <?= $turma['nivel'] == 'AVANCADO' ? 'selected' : '' ?>>Avançado</option>
            </select>

            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="ATIVA" <?= $turma['status'] == 'ATIVA' ? 'selected' : '' ?>>Ativa</option>
                <option value="INATIVA" <?= $turma['status'] == 'INATIVA' ? 'selected' : '' ?>>Inativa</option>
            </select>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <a href="listar_turma.php" class="btn" style="flex: 1; text-align: center; background-color: var(--border-color); color: var(--text-primary) !important; text-decoration: none;">Cancelar</a>
                <button type="submit" style="margin-top: 0; flex: 1;">Salvar Alterações</button>
            </div>
        </form>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>