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
$stmtModalidades = $pdo->prepare("SELECT id_modalidade, nome FROM modalidade WHERE id_academia = :id_academia AND status = 'ATIVA'");
$stmtModalidades->execute([':id_academia' => $idAcademiaLogada]);
$modalidades = $stmtModalidades->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Professor - Dojify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="listar_usuarios.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="text-center mb-4">
                <h2 class="fs-4 fw-bold">Editar Dados do Professor</h2>
                <p class="text-muted">Atualize as informações cadastrais do instrutor.</p>
            </div>

            <form action="../../controller/UsuarioController.php?acao=atualizar_professor" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <input type="hidden" name="id_usuario" value="<?= $professor['id_usuario']; ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome Completo: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($professor['nome']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="cpf" class="form-label">CPF (Não editável):</label>
                        <input type="text" class="form-control" id="cpf" value="<?= htmlspecialchars($professor['cpf'] ?? ''); ?>" disabled>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail de Acesso: <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($professor['email']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefone" class="form-label">Telefone: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($professor['telefone']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="especialidade" class="form-label">Especialidade / Arte Marcial: <span class="text-danger">*</span></label>
                        <select class="form-select" id="especialidade" name="especialidade" required>
                            <option value="">Selecione a especialidade...</option>
                            <?php foreach ($modalidades as $mod): ?>
                                <option value="<?= htmlspecialchars($mod['nome']); ?>" <?= (($professor['especialidade'] ?? '') === $mod['nome']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($mod['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="Outras" <?= (($professor['especialidade'] ?? '') === 'Outras') ? 'selected' : ''; ?>>Outras</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status do Professor: <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="ATIVO" <?= ($professor['status'] === 'ATIVO') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="INATIVO" <?= ($professor['status'] === 'INATIVO') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="SUSPENSO" <?= ($professor['status'] === 'SUSPENSO') ? 'selected' : ''; ?>>Suspenso</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                        <a href="listar_usuarios.php" class="btn btn-secondary m-0 form-action-button">Cancelar</a>
                        <button type="submit" class="btn btn-success m-0 form-action-button">
                            <i class="bi bi-check-circle me-1"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>