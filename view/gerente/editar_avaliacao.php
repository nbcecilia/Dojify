<?php
// view/gerente/editar_avaliacao.php
session_start();

if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['perfil_id'] ?? 0) !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../controller/AvaliacaoController.php';

$idAcademia = (int)($_SESSION['usuario']['id_academia'] ?? 0);
$idGerente = (int)($_SESSION['usuario']['id_usuario'] ?? $_SESSION['usuario']['id'] ?? 0);
$idAvaliacao = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($idAcademia < 1 || $idGerente < 1 || !$idAvaliacao) {
    header('Location: ../professor/listar_avaliacao.php?erro=id_invalido');
    exit;
}

$controller = new AvaliacaoController();
$avaliacoes = $controller->listarPorAcademia($idAcademia);
$avaliacao = null;
foreach ($avaliacoes as $item) {
    if ((int)$item->getIdAvaliacao() === (int)$idAvaliacao) {
        $avaliacao = $item;
        break;
    }
}

if (!$avaliacao) {
    header('Location: ../professor/listar_avaliacao.php?erro=nao_encontrado');
    exit;
}

$alunos = $controller->listarAlunosPorAcademia($idAcademia);
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alunoModalidade = explode('|', (string)($_POST['id_usuario_aluno_modalidade'] ?? ''), 2);
    $idAluno = filter_var($alunoModalidade[0] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $idModalidade = filter_var($alunoModalidade[1] ?? null, FILTER_VALIDATE_INT) ?: 0;

    try {
        $resposta = $controller->atualizar(
            $idGerente,
            (int)$idAvaliacao,
            $idAluno,
            $idModalidade,
            trim((string)($_POST['data_avaliacao'] ?? '')),
            trim((string)($_POST['data_prevista'] ?? '')) ?: null,
            (string)($_POST['habilidades_melhorar'] ?? ''),
            (string)($_POST['observacoes'] ?? ''),
            2
        );
        if ($resposta['status']) {
            header('Location: ../professor/listar_avaliacao.php?sucesso=' . rawurlencode($resposta['mensagem']));
            exit;
        }
        $erro = $resposta['mensagem'];
    } catch (DomainException $e) {
        $erro = 'O aluno ou a modalidade selecionada não tem matrícula ativa nesta academia.';
    } catch (Throwable $e) {
        error_log('Erro do gerente ao atualizar avaliação: ' . $e->getMessage());
        $erro = 'Não foi possível atualizar a avaliação. Tente novamente.';
    }
}

$valorCampo = static function (string $nome, $valorAtual): string {
    $valor = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST[$nome] ?? $valorAtual) : $valorAtual;
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};
$idAlunoSelecionado = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (int)(explode('|', (string)($_POST['id_usuario_aluno_modalidade'] ?? '|'))[0] ?? 0)
    : (int)$avaliacao->getIdUsuarioAluno();
$idModalidadeSelecionada = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (int)(explode('|', (string)($_POST['id_usuario_aluno_modalidade'] ?? '|'))[1] ?? 0)
    : (int)$avaliacao->getIdModalidade();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar avaliação - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="container my-4">
        <section class="card shadow-sm border-0 p-4">
            <header class="text-center mb-4">
                <h1 class="fs-4 fw-bold">Editar avaliação técnica</h1>
                <p class="text-muted mb-0">Atualize os dados da avaliação do aluno.</p>
            </header>
            <?php if ($erro !== ''): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form method="POST" action="editar_avaliacao.php?id=<?= (int)$idAvaliacao ?>" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="id_usuario_aluno_modalidade" class="form-label">Aluno e modalidade <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_usuario_aluno_modalidade" name="id_usuario_aluno_modalidade" required>
                            <option value="">Selecione o aluno e a modalidade...</option>
                            <?php foreach ($alunos as $aluno): ?>
                                <?php $valorOpcao = (int)$aluno['id_usuario'] . '|' . (int)$aluno['id_modalidade']; ?>
                                <option value="<?= $valorOpcao ?>" <?= (int)$aluno['id_usuario'] === $idAlunoSelecionado && (int)$aluno['id_modalidade'] === $idModalidadeSelecionada ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($aluno['nome'] . ' - ' . $aluno['nome_modalidade'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="data_avaliacao" class="form-label">Data da avaliação <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="data_avaliacao" name="data_avaliacao" value="<?= $valorCampo('data_avaliacao', $avaliacao->getDataAvaliacao()) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="data_prevista" class="form-label">Previsão da próxima graduação</label>
                        <input type="date" class="form-control" id="data_prevista" name="data_prevista" value="<?= $valorCampo('data_prevista', $avaliacao->getDataPrevista() ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label for="habilidades_melhorar" class="form-label">Habilidades a melhorar <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="habilidades_melhorar" name="habilidades_melhorar" rows="4" required><?= $valorCampo('habilidades_melhorar', $avaliacao->getHabilidadesMelhorar()) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label for="observacoes" class="form-label">Observações gerais / evolução</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?= $valorCampo('observacoes', $avaliacao->getObservacoes()) ?></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="../professor/listar_avaliacao.php" class="btn btn-secondary m-0">Cancelar</a>
                    <button type="submit" class="btn btn-success m-0">Salvar avaliação</button>
                </div>
            </form>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
