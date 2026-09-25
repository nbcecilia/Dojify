<?php
// view/gerente/editar_professor.php
session_start();
require_once __DIR__ . '/../../model/dao/Conexao.php';

// Valida se o usuário é Gerente (perfil 2)
if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$idProfessor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idProfessor) {
    header('Location: listar_usuarios.php?erro=id_invalido');
    exit;
}

$pdo = Conexao::getConexao();
$idAcademiaLogada = $_SESSION['usuario']['id_academia'];

// Busca dados do professor garantindo que ele pertence à mesma academia e é perfil 3 (professor)
$sql = "SELECT * FROM usuario WHERE id_usuario = :id AND perfil_id = 3 AND id_academia = :id_academia";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $idProfessor,
    ':id_academia' => $idAcademiaLogada
]);
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    header('Location: listar_usuarios.php?erro=nao_encontrado');
    exit;
}

// Busca modalidades para a especialidade, caso necessário
$stmtModalidades = $pdo->prepare("SELECT id_modalidade, nome FROM modalidade WHERE id_academia = :id_academia");
$stmtModalidades->execute([':id_academia' => $idAcademiaLogada]);
$modalidades = $stmtModalidades->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Professor - Dojify</title>
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
            <a href="listar_usuarios.php" class="btn btn-sm">Voltar</a>
            <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <div class="container">
        <h2>Editar Dados do Professor</h2>
        <p class="text-center text-muted" style="margin-bottom: 24px;">Atualize as informações cadastrais do instrutor.</p>

        <form action="../../controller/UsuarioController.php?acao=atualizar_professor" method="POST">
            <input type="hidden" name="id_usuario" value="<?= $professor['id_usuario']; ?>">

            <div>
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($professor['nome']); ?>" required>
            </div>

            <div>
                <label for="cpf">CPF (Não editável):</label>
                <input type="text" id="cpf" value="<?= htmlspecialchars($professor['cpf'] ?? ''); ?>" disabled>
            </div>

            <div>
                <label for="email">E-mail de Acesso:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($professor['email']); ?>" required>
            </div>

            <div>
                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($professor['telefone']); ?>" required>
            </div>

            <div>
                <label for="especialidade">Especialidade / Arte Marcial:</label>
                <select id="especialidade" name="especialidade" required>
                    <option value="">Selecione a especialidade...</option>
                    <?php foreach ($modalidades as $mod): ?>
                        <option value="<?= htmlspecialchars($mod['nome']); ?>" <?= (($professor['especialidade'] ?? '') === $mod['nome']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($mod['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="Outras" <?= (($professor['especialidade'] ?? '') === 'Outras') ? 'selected' : ''; ?>>Outras</option>
                </select>
            </div>

            <div>
                <label for="status">Status do Professor:</label>
                <select id="status" name="status" required>
                    <option value="ATIVO" <?= ($professor['status'] === 'ATIVO') ? 'selected' : ''; ?>>Ativo</option>
                    <option value="INATIVO" <?= ($professor['status'] === 'INATIVO') ? 'selected' : ''; ?>>Inativo</option>
                    <option value="SUSPENSO" <?= ($professor['status'] === 'SUSPENSO') ? 'selected' : ''; ?>>Suspenso</option>
                </select>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <a href="listar_usuarios.php" class="btn" style="flex: 1; text-align: center; background-color: var(--border-color); color: var(--text-primary) !important; text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancelar</a>
                <button type="submit" style="margin-top: 0; flex: 1;">Salvar Alterações</button>
            </div>
        </form>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>