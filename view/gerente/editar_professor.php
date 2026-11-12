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
$cepDigitos = preg_replace('/\D/', '', (string)($professor['cep'] ?? ''));
$cepFormatado = strlen($cepDigitos) === 8
    ? substr($cepDigitos, 0, 5) . '-' . substr($cepDigitos, 5)
    : (string)($professor['cep'] ?? '');
$ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
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
    <link rel="stylesheet" href="../../assets/css/estilo.css?v=<?= filemtime(__DIR__ . '/../../assets/css/estilo.css'); ?>">
    <script src="../../assets/js/cep.js" defer></script>
</head>
<body class="user-edit-modal-page">
    <main class="container-fluid p-0">
            <form action="../../controller/UsuarioController.php?acao=atualizar_professor" method="POST" class="w-100 mw-100 p-3 p-md-4 m-0 border-0 shadow-none bg-transparent" data-cep-form>
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
                        <label for="data_nascimento" class="form-label">Data de Nascimento (Não editável):</label>
                        <input type="date" class="form-control" id="data_nascimento" value="<?= htmlspecialchars($professor['data_nascimento'] ?? ''); ?>" max="9999-12-31" disabled>
                    </div>

                    <div class="col-md-6">
                        <label for="sexo" class="form-label">Sexo: <span class="text-danger">*</span></label>
                        <select class="form-select" id="sexo" name="sexo" required>
                            <option value="">Selecione...</option>
                            <?php foreach (['Feminino', 'Masculino', 'Outro', 'Prefiro não informar'] as $sexo): ?>
                                <option value="<?= htmlspecialchars($sexo, ENT_QUOTES, 'UTF-8'); ?>" <?= ($professor['sexo'] ?? '') === $sexo ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($sexo, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail de Acesso: <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($professor['email']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefone" class="form-label">Telefone: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($professor['telefone']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="cep" class="form-label">CEP: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cep" name="cep" inputmode="numeric" autocomplete="postal-code" maxlength="9" pattern="[0-9]{5}-?[0-9]{3}" value="<?= htmlspecialchars($cepFormatado, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <button type="button" class="btn btn-outline-secondary" data-cep-lookup>Buscar</button>
                        </div>
                        <p id="cep-status" class="form-text text-muted mb-0" aria-live="polite"></p>
                    </div>

                    <div class="col-md-8">
                        <label for="endereco" class="form-label">Endereço: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="endereco" name="endereco" autocomplete="street-address" maxlength="100" value="<?= htmlspecialchars($professor['endereco'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="col-md-8">
                        <label for="cidade" class="form-label">Cidade: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cidade" name="cidade" autocomplete="address-level2" maxlength="45" value="<?= htmlspecialchars($professor['cidade'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="estado" class="form-label">Estado (UF): <span class="text-danger">*</span></label>
                        <select class="form-select" id="estado" name="estado" autocomplete="off" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($ufs as $uf): ?>
                                <option value="<?= $uf; ?>" <?= strtoupper((string)($professor['estado'] ?? '')) === $uf ? 'selected' : ''; ?>><?= $uf; ?></option>
                            <?php endforeach; ?>
                        </select>
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
                        <label for="data_admissao" class="form-label">Data de Admissão:</label>
                        <input type="date" class="form-control" id="data_admissao" name="data_admissao" value="<?= htmlspecialchars($professor['data_admissao'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" max="9999-12-31">
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status do Professor: <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="ATIVO" <?= ($professor['status'] === 'ATIVO') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="INATIVO" <?= ($professor['status'] === 'INATIVO') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="SUSPENSO" <?= ($professor['status'] === 'SUSPENSO') ? 'selected' : ''; ?>>Suspenso</option>
                        </select>
                    </div>

                    <div class="col-12 user-edit-modal-actions mt-4">
                        <a href="listar_usuarios.php" class="btn btn-secondary btn-sm user-edit-modal-button">Cancelar</a>
                        <button type="submit" class="btn btn-success btn-sm user-edit-modal-button">
                            <i class="bi bi-check-circle me-1"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>
    </main>

</body>
</html>