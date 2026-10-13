<?php
// view/gerente/editar_aluno.php
session_start();
require_once __DIR__ . '/../../model/dao/Conexao.php';
require_once __DIR__ . '/../../model/dao/ModalidadeDAO.php';

// Valida se o usuário é gerente (perfil 2)
if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idUsuario) {
    header('Location: listar_usuarios.php?erro=id_invalido');
    exit;
}

$pdo = Conexao::getConexao();

// Busca os dados do aluno e o seu plano ativo (usando id_usuario_aluno conforme o teu SQL)
$sql = "SELECT u.*, p.id_plano, p.id_modalidade, p.nome_plano, p.valor, p.status as status_plano 
        FROM usuario u 
        LEFT JOIN plano p ON u.id_usuario = p.id_usuario_aluno AND p.status = 'ATIVO' 
        WHERE u.id_usuario = :id AND u.id_academia = :id_academia
        ORDER BY p.id_plano ASC
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $idUsuario,
    ':id_academia' => $_SESSION['usuario']['id_academia']
]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);
$modalidades = (new ModalidadeDAO())->listarPorAcademia((int)$_SESSION['usuario']['id_academia']);
$stmtModalidadesAluno = $pdo->prepare("
    SELECT DISTINCT id_modalidade
    FROM plano
    WHERE id_usuario_aluno = :id_usuario_aluno
      AND status = 'ATIVO'
      AND id_modalidade IS NOT NULL
    ORDER BY id_modalidade
");
$stmtModalidadesAluno->execute([':id_usuario_aluno' => $idUsuario]);
$idsModalidadesAluno = array_map('intval', $stmtModalidadesAluno->fetchAll(PDO::FETCH_COLUMN));

if (!$aluno) {
    header('Location: listar_usuarios.php?erro=nao_encontrado');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aluno - Dojify</title>
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
                <h2 class="fs-4 fw-bold">Editar Dados do Aluno</h2>
                <p class="text-muted">Atualize as informações cadastrais do aluno.</p>
            </div>

            <form action="../../controller/UsuarioController.php?acao=atualizar_aluno" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <!-- ID Oculto do Aluno -->
                <input type="hidden" name="id_usuario" value="<?php echo $aluno['id_usuario']; ?>">
                <!-- ID Oculto do Plano (caso exista para atualização) -->
                <input type="hidden" name="id_plano" value="<?php echo $aluno['id_plano'] ?? ''; ?>">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="nome" class="form-label">Nome Completo: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($aluno['nome']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="cpf" class="form-label">CPF (Não editável):</label>
                        <input type="text" class="form-control" id="cpf" value="<?php echo htmlspecialchars($aluno['cpf']); ?>" disabled>
                    </div>

                    <div class="col-md-4">
                        <label for="data_nascimento" class="form-label">Data de Nascimento (Não editável):</label>
                        <input type="date" class="form-control" id="data_nascimento" value="<?php echo htmlspecialchars($aluno['data_nascimento']); ?>" disabled>
                    </div>

                    <div class="col-md-4">
                        <label for="email" class="form-label">E-mail: <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($aluno['email']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="telefone" class="form-label">Telefone / WhatsApp:</label>
                        <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($aluno['telefone']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="responsavel" class="form-label">Responsável (Caso seja menor de idade):</label>
                        <input type="text" class="form-control" id="responsavel" name="responsavel" value="<?php echo htmlspecialchars($aluno['responsavel'] ?? ''); ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status do Aluno: <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="ATIVO" <?php echo ($aluno['status'] === 'ATIVO') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="INATIVO" <?php echo ($aluno['status'] === 'INATIVO') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="SUSPENSO" <?php echo ($aluno['status'] === 'SUSPENSO') ? 'selected' : ''; ?>>Suspenso</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <label for="observacao" class="form-label">Observações:</label>
                        <textarea class="form-control" id="observacao" name="observacao" rows="3"><?php echo htmlspecialchars($aluno['observacao'] ?? ''); ?></textarea>
                    </div>

                    <div class="col-12 mt-4">
                        <h3 class="fs-5 mb-3 text-primary border-bottom pb-2">Informações do Plano</h3>
                    </div>

                    <?php for ($numeroModalidade = 1; $numeroModalidade <= 3; $numeroModalidade++): ?>
                        <div class="col-md-4">
                            <label for="id_modalidade_<?= $numeroModalidade; ?>" class="form-label">
                                Modalidade <?= $numeroModalidade; ?><?= $numeroModalidade === 1 ? ' <span class="text-danger">*</span>' : ' (opcional)'; ?>:
                            </label>
                            <select class="form-select" id="id_modalidade_<?= $numeroModalidade; ?>" name="id_modalidades[]" <?= $numeroModalidade === 1 ? 'required' : ''; ?>>
                                <option value="">Selecione a modalidade...</option>
                                <?php foreach ($modalidades as $modalidade): ?>
                                    <option value="<?= (int)$modalidade['id_modalidade']; ?>" <?= (int)$modalidade['id_modalidade'] === ($idsModalidadesAluno[$numeroModalidade - 1] ?? 0) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($modalidade['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endfor; ?>

                    <div class="col-md-8">
                        <label for="nome_plano" class="form-label">Plano Contratado: <span class="text-danger">*</span></label>
                        <select class="form-select" id="nome_plano" name="nome_plano" required>
                            <option value="">Selecione o plano...</option>
                            <optgroup label="Planos Mensais">
                                <option value="Mensal 2x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Mensal 2x/semana') ? 'selected' : ''; ?>>Mensal (2x/semana)</option>
                                <option value="Mensal 3x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Mensal 3x/semana') ? 'selected' : ''; ?>>Mensal (3x/semana)</option>
                                <option value="Mensal Ilimitado" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Mensal Ilimitado') ? 'selected' : ''; ?>>Mensal (Ilimitado)</option>
                            </optgroup>
                            <optgroup label="Planos Trimestrais">
                                <option value="Trimestral 2x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Trimestral 2x/semana') ? 'selected' : ''; ?>>Trimestral (2x/semana)</option>
                                <option value="Trimestral 3x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Trimestral 3x/semana') ? 'selected' : ''; ?>>Trimestral (3x/semana)</option>
                                <option value="Trimestral Ilimitado" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Trimestral Ilimitado') ? 'selected' : ''; ?>>Trimestral (Ilimitado)</option>
                            </optgroup>
                            <optgroup label="Planos Anuais">
                                <option value="Anual 2x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Anual 2x/semana') ? 'selected' : ''; ?>>Anual (2x/semana)</option>
                                <option value="Anual 3x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Anual 3x/semana') ? 'selected' : ''; ?>>Anual (3x/semana)</option>
                                <option value="Anual Ilimitado" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Anual Ilimitado') ? 'selected' : ''; ?>>Anual (Ilimitado)</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="valor_plano" class="form-label">Valor do Plano (R$):</label>
                        <input type="text" class="form-control" id="valor_plano" name="valor_plano" value="<?php echo isset($aluno['valor']) ? number_format($aluno['valor'], 2, ',', '') : ''; ?>" placeholder="0,00">
                    </div>

                    <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                        <a href="listar_usuarios.php" class="btn btn-secondary px-4 m-0 w-auto">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 m-0 w-auto">
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