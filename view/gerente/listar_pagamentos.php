<?php
// view/gerente/listar_pagamentos.php
session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/PagamentoDAO.php';

$dao = new \PagamentoDAO();
$idAcademia = (int) $_SESSION['id_academia'];
$pagamentosBrutos = $dao->listarPagamentosDaAcademia($idAcademia);

// Captura o termo digitado na pesquisa
$termoBusca = trim($_GET['busca'] ?? '');

// Mantém uma cobrança consolidada por aluno e mês, com os IDs de cada plano.
$pagamentos = [];
foreach ($pagamentosBrutos as $pag) {
    // Se houver termo de busca, filtra ignorando maiúsculas/minúsculas
    if ($termoBusca !== '' && mb_stripos($pag['aluno_nome'], $termoBusca) === false) {
        continue;
    }

    $pag['nomes_planos'] = !empty($pag['nome_plano']) ? explode(', ', $pag['nome_plano']) : [];
    $pag['ids_pagamentos'] = !empty($pag['ids_pagamentos'])
        ? array_map('intval', explode(',', $pag['ids_pagamentos']))
        : [];
    $pagamentos[] = $pag;
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão Financeira - Dojify</title>
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
                <a href="relatorio_financeiro.php" class="btn btn-info text-white">
                    <i class="bi bi-bar-chart-fill me-1"></i> Emitir Relatório
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="mb-4">
                <h2>Gestão de Pagamentos</h2>
                <p class="text-muted">Controle das mensalidades dos alunos.</p>
            </div>

            <!-- Barra de Pesquisa Padronizada (.busca-container) -->
            <div class="busca-container">
                <form method="GET" action="listar_pagamentos.php" class="busca-form">
                    <div class="input-group busca-input">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="busca" class="form-control" placeholder="Pesquisar por nome do aluno..." value="<?= htmlspecialchars($termoBusca) ?>">
                    </div>
                    <button type="submit" class="btn btn-dark busca-btn">
                        Filtrar
                    </button>
                    <?php if ($termoBusca !== ''): ?>
                        <a href="listar_pagamentos.php" class="btn btn-outline-secondary busca-btn" title="Limpar pesquisa">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php
            $toastTipo = null;
            $toastTexto = '';
            if (isset($_GET['sucesso'])) {
                $toastTipo = 'sucesso';
                $toastTexto = 'Recebimento registado com sucesso!';
            } elseif (isset($_GET['erro'])) {
                $toastTipo = 'erro';
                $toastTexto = 'Não foi possível registar o recebimento. Tente novamente.';
            }
            ?>

            <?php if ($toastTipo): ?>
                <div id="toast" class="toast toast--<?= $toastTipo ?> mb-3"><?= htmlspecialchars($toastTexto) ?></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const toast = document.getElementById('toast');
                        if (!toast) return;
                        requestAnimationFrame(() => toast.classList.add('toast-show'));
                        setTimeout(() => {
                            toast.classList.remove('toast-show');
                            setTimeout(() => toast.remove(), 300);
                        }, 4000);
                    });
                </script>
            <?php endif; ?>

            <?php if (empty($pagamentos)): ?>
                <p class="text-center text-muted py-4">Nenhum pagamento encontrado com esse nome.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Aluno</th>
                                <th>Plano</th>
                                <th>Vencimento</th>
                                <th>Valor (R$)</th>
                                <th>Estado</th>
                                <th class="text-center">Ações / Registo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagamentos as $pag): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pag['aluno_nome']) ?></strong></td>
                                    <td><?= htmlspecialchars(implode(', ', array_unique($pag['nomes_planos']))) ?></td>
                                    <td><?= date('d/m/Y', strtotime($pag['data_vencimento'])) ?></td>
                                    <td class="fw-semibold">R$ <?= number_format($pag['valor'], 2, ',', '.') ?></td>
                                    
                                    <td>
                                        <?php if ($pag['status'] === 'PAGO'): ?>
                                            <span class="text-success fw-semibold">Pago em <?= date('d/m/y', strtotime($pag['data_pagamento'])) ?></span>
                                            <br><small class="text-muted"><?= htmlspecialchars($pag['forma_pagamento']) ?></small>
                                        <?php elseif ($pag['status'] === 'ATRASADO' || (strtotime($pag['data_vencimento']) < time() && $pag['status'] === 'PENDENTE')): ?>
                                            <span class="text-danger fw-semibold">Atrasado</span>
                                        <?php elseif ($pag['status'] === 'EM_ANALISE'): ?>
                                            <span class="text-info-emphasis fw-semibold">Em análise</span>
                                        <?php else: ?>
                                            <span class="text-warning fw-semibold">Pendente</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($pag['status'] !== 'PAGO'): ?>
                                            <form action="../../controller/FinanceiroController.php" method="POST" class="form-pagamento-inline">
                                                
                                                <input type="hidden" name="acao" value="baixar_pagamento">
                                                <!-- Passa todos os IDs de pagamento associados a este agrupamento -->
                                                <input type="hidden" name="ids_pagamentos" value="<?= htmlspecialchars(implode(',', $pag['ids_pagamentos'])) ?>">
                                                
                                                <!-- Seletor de Forma de Pagamento -->
                                                <select name="forma_pagamento" class="form-select form-select-sm" style="width: 110px;" required>
                                                    <option value="DINHEIRO">Dinheiro</option>
                                                    <option value="PIX">PIX</option>
                                                    <option value="CARTAO">Cartão</option>
                                                    <option value="OUTRO">Outro</option>
                                                </select>

                                                <!-- Calendário para marcar a data do recebimento -->
                                                <input type="date" name="data_pagamento" class="form-control form-control-sm" style="width: 135px;" value="<?= date('Y-m-d') ?>" required title="Data do Recebimento">
                                                
                                                <!-- Botão Receber -->
                                                <button type="submit" class="btn btn-sm btn-success text-nowrap px-2 py-1" onclick="return confirm('Confirmar o recebimento deste pagamento?');">
                                                    <i class="bi bi-check-circle me-1"></i> Receber
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">Fechado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>