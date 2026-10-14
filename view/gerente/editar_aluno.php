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

// Busca os dados do aluno
$sql = "SELECT u.* FROM usuario u WHERE u.id_usuario = :id AND u.id_academia = :id_academia LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $idUsuario,
    ':id_academia' => $_SESSION['usuario']['id_academia']
]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    header('Location: listar_usuarios.php?erro=nao_encontrado');
    exit;
}

$modalidades = (new ModalidadeDAO())->listarPorAcademia((int)$_SESSION['usuario']['id_academia']);

// Busca os planos ativos do aluno associados às modalidades
$stmtPlanosAluno = $pdo->prepare("
    SELECT id_plano, id_modalidade, nome_plano, valor
    FROM plano
    WHERE id_usuario_aluno = :id_usuario_aluno AND status = 'ATIVO'
    ORDER BY id_plano ASC
");
$stmtPlanosAluno->execute([':id_usuario_aluno' => $idUsuario]);
$planosAtivos = $stmtPlanosAluno->fetchAll(PDO::FETCH_ASSOC);

// Organiza os planos por índice (0, 1, 2) para preencher os campos correspondentes
$modalidadesAlunoPreenchidas = [];
$planosAlunoPreenchidos = [];
foreach ($planosAtivos as $index => $p) {
    if ($index < 3) {
        $modalidadesAlunoPreenchidas[$index] = (int)$p['id_modalidade'];
        $planosAlunoPreenchidos[$index] = [
            'nome_plano' => $p['nome_plano'],
            'valor' => $p['valor']
        ];
    }
}

$opcoesPlano = [
    'Mensal' => [
        'Mensal 2x/semana' => 'Mensal (2x/semana)',
        'Mensal 3x/semana' => 'Mensal (3x/semana)',
        'Mensal Ilimitado' => 'Mensal (Ilimitado)',
    ],
    'Trimestral' => [
        'Trimestral 2x/semana' => 'Trimestral (2x/semana)',
        'Trimestral 3x/semana' => 'Trimestral (3x/semana)',
        'Trimestral Ilimitado' => 'Trimestral (Ilimitado)',
    ],
    'Anual' => [
        'Anual 2x/semana' => 'Anual (2x/semana)',
        'Anual 3x/semana' => 'Anual (3x/semana)',
        'Anual Ilimitado' => 'Anual (Ilimitado)',
    ],
    'All Inclusive' => [
        'All Inclusive 2x/semana' => 'All Inclusive (2x/semana)',
        'All Inclusive 4x/semana' => 'All Inclusive (4x/semana)',
        'All Inclusive Ilimitado' => 'All Inclusive (Ilimitado)',
    ],
];
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
                <p class="text-muted">Atualize as informações cadastrais e planos do aluno.</p>
            </div>

            <form action="../../controller/UsuarioController.php?acao=atualizar_aluno" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent" autocomplete="off">
                <!-- ID Oculto do Aluno -->
                <input type="hidden" name="id_usuario" value="<?php echo $aluno['id_usuario']; ?>">

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
                        <h3 class="fs-5 mb-3 text-primary border-bottom pb-2">Modalidades e Planos do Aluno</h3>
                    </div>

                    <!-- Modalidades -->
                    <?php for ($numeroModalidade = 1; $numeroModalidade <= 3; $numeroModalidade++): ?>
                        <?php $indice = $numeroModalidade - 1; ?>
                        <div class="col-md-4">
                            <label for="id_modalidade_<?= $numeroModalidade; ?>" class="form-label">
                                Modalidade <?= $numeroModalidade; ?><?= $numeroModalidade === 1 ? ' <span class="text-danger">*</span>' : ' (opcional)'; ?>:
                            </label>
                            <select class="form-select" id="id_modalidade_<?= $numeroModalidade; ?>" name="id_modalidades[]" <?= $numeroModalidade === 1 ? 'required' : ''; ?>>
                                <option value="">Selecione...</option>
                                <?php foreach ($modalidades as $modalidade): ?>
                                    <option value="<?= (int)$modalidade['id_modalidade']; ?>" <?= (isset($modalidadesAlunoPreenchidas[$indice]) && $modalidadesAlunoPreenchidas[$indice] === (int)$modalidade['id_modalidade']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($modalidade['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endfor; ?>

                    <div class="col-12 mt-4">
                        <p class="form-text text-muted mb-3">Configure o plano e o valor correspondente para cada modalidade selecionada acima.</p>
                        
                        <?php for ($numeroModalidade = 1; $numeroModalidade <= 3; $numeroModalidade++): ?>
                            <?php 
                            $indice = $numeroModalidade - 1; 
                            $temModalidade = isset($modalidadesAlunoPreenchidas[$indice]);
                            $planoAtual = $planosAlunoPreenchidos[$indice] ?? ['nome_plano' => '', 'valor' => ''];
                            ?>
                            <div class="plano-modalidade p-3 border rounded mb-3 bg-light" data-plano-modalidade="<?= $indice; ?>" <?= !$temModalidade ? 'hidden' : ''; ?>>
                                <h4 class="h6 fw-bold mb-3">Plano da Modalidade <?= $numeroModalidade; ?></h4>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label for="nome_plano_<?= $numeroModalidade; ?>" class="form-label">Plano Contratado:</label>
                                        <select class="form-select" id="nome_plano_<?= $numeroModalidade; ?>" name="planos[<?= $indice; ?>][nome_plano]" <?= !$temModalidade ? 'disabled' : 'required'; ?>>
                                            <option value="">Selecione o plano...</option>
                                            <?php foreach ($opcoesPlano as $grupoPlano => $opcoes): ?>
                                                <optgroup label="Planos <?= htmlspecialchars($grupoPlano, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php foreach ($opcoes as $valorPlanoOpcao => $rotuloPlano): ?>
                                                        <option value="<?= htmlspecialchars($valorPlanoOpcao, ENT_QUOTES, 'UTF-8'); ?>" <?= ($planoAtual['nome_plano'] === $valorPlanoOpcao) ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($rotuloPlano, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="valor_plano_<?= $numeroModalidade; ?>" class="form-label">Valor (R$):</label>
                                        <input type="number" class="form-control" step="0.01" min="0" id="valor_plano_<?= $numeroModalidade; ?>" name="planos[<?= $indice; ?>][valor]" value="<?= $planoAtual['valor'] !== '' ? number_format((float)$planoAtual['valor'], 2, '.', '') : ''; ?>" placeholder="Ex: 150.00" <?= !$temModalidade ? 'disabled' : 'required'; ?>>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
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
    
    <!-- Script para dinamismo das caixas de planos por modalidade -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const selectsModalidade = [...document.querySelectorAll('[name="id_modalidades[]"]')];
            const camposPlanoPorModalidade = [...document.querySelectorAll('[data-plano-modalidade]')];

            function atualizarModalidadesDisponiveis() {
                selectsModalidade.forEach(select => {
                    const selecionadasEmOutros = new Set(
                        selectsModalidade
                            .filter(outroSelect => outroSelect !== select)
                            .map(outroSelect => outroSelect.value)
                            .filter(Boolean)
                    );

                    Array.from(select.options).forEach(opcao => {
                        opcao.hidden = opcao.value !== '' && selecionadasEmOutros.has(opcao.value);
                    });
                });
            }

            function atualizarPlanosPorModalidade() {
                camposPlanoPorModalidade.forEach((grupo, indice) => {
                    const modalidadeSelecionada = Boolean(selectsModalidade[indice].value);
                    grupo.hidden = !modalidadeSelecionada;
                    grupo.querySelectorAll('select, input').forEach(campo => {
                        campo.disabled = !modalidadeSelecionada;
                        if (modalidadeSelecionada) {
                            campo.setAttribute('required', 'required');
                        } else {
                            campo.removeAttribute('required');
                        }
                    });
                });
            }

            selectsModalidade.forEach(select => {
                select.addEventListener('change', function () {
                    atualizarModalidadesDisponiveis();
                    atualizarPlanosPorModalidade();
                });
            });

            atualizarModalidadesDisponiveis();
            atualizarPlanosPorModalidade();
        });
    </script>
</body>
</html>