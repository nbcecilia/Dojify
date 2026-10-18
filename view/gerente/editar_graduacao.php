<?php
// view/gerente/editar_graduacao.php
session_start();

if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['perfil_id'] ?? 0) !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../controller/GraduacaoController.php';

$idAcademia = (int)($_SESSION['usuario']['id_academia'] ?? 0);
$idGerente = (int)($_SESSION['usuario']['id_usuario'] ?? $_SESSION['usuario']['id'] ?? 0);
$idGraduacao = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($idAcademia < 1 || $idGerente < 1 || !$idGraduacao) {
    header('Location: ../professor/listar_graduacao.php?erro=id_invalido');
    exit;
}

$controller = new GraduacaoController();
$graduacoes = $controller->listarPorPerfilUsuario(2, $idAcademia, $idGerente);
$graduacao = null;
foreach ($graduacoes as $item) {
    if ((int)$item['id_graduacao'] === (int)$idGraduacao) {
        $graduacao = $item;
        break;
    }
}

if (!$graduacao) {
    header('Location: ../professor/listar_graduacao.php?erro=nao_encontrado');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resposta = $controller->atualizar(
        (int)$idGraduacao,
        $idGerente,
        trim((string)($_POST['data_graduacao'] ?? '')),
        trim((string)($_POST['faixa'] ?? '')),
        trim((string)($_POST['grau'] ?? '')),
        2
    );
    if ($resposta['status']) {
        header('Location: ../professor/listar_graduacao.php?sucesso=' . rawurlencode($resposta['mensagem']));
        exit;
    }
    $erro = $resposta['mensagem'];
}

$valorCampo = static function (string $nome, $valorAtual): string {
    $valor = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST[$nome] ?? $valorAtual) : $valorAtual;
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar graduação - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="container my-4">
        <section class="card shadow-sm border-0 p-4">
            <header class="text-center mb-4">
                <h1 class="fs-4 fw-bold">Editar graduação</h1>
                <p class="text-muted mb-0"><?= htmlspecialchars($graduacao['nome_aluno'] . ' - ' . $graduacao['nome_modalidade'], ENT_QUOTES, 'UTF-8'); ?></p>
            </header>
            <?php if ($erro !== ''): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form method="POST" action="editar_graduacao.php?id=<?= (int)$idGraduacao ?>" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="data_graduacao" class="form-label">Data da graduação <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="data_graduacao" name="data_graduacao" value="<?= $valorCampo('data_graduacao', $graduacao['data_graduacao']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="faixa" class="form-label">Faixa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="faixa" name="faixa" maxlength="50" value="<?= $valorCampo('faixa', $graduacao['faixa']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="grau" class="form-label">Grau</label>
                        <input type="text" class="form-control" id="grau" name="grau" maxlength="20" value="<?= $valorCampo('grau', $graduacao['grau'] ?? '') ?>">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="../professor/listar_graduacao.php" class="btn btn-secondary m-0">Cancelar</a>
                    <button type="submit" class="btn btn-success m-0">Salvar graduação</button>
                </div>
            </form>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
