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
$pagamentos = $dao->listarPagamentosDaAcademia($idAcademia);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão Financeira - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

<<<<<<< HEAD
    <main class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="margin: 0;">Gestão de Pagamentos</h2>
            <div style="display: flex; gap: 10px;">
                <a href="relatorio_financeiro.php" class="btn btn-info" style="color: white !important;">📊 Emitir Relatório</a>
                <a href="home_gerente.php" class="btn" style="background-color: var(--border-color); color: var(--text-primary) !important;">⬅ Voltar ao Painel</a>
=======
    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="relatorio_financeiro.php" class="btn btn-info text-white">
                    <i class="bi bi-bar-chart-fill me-1"></i> Emitir Relatório
                </a>
>>>>>>> 0427ff754a7c53facfa2e94d3bb0b7f7441f52aa
            </div>
            <div>
                <a href="home_gerente.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar ao Painel
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="mb-4">
                <h2>Gestão de Pagamentos</h2>
                <p class="text-muted">Controlo das mensalidades dos alunos.</p>
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
                <p class="text-center text-muted py-4">Nenhum pagamento registado nesta academia.</p>
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
                                <th>Ações / Registo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagamentos as $pag): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pag['aluno_nome']) ?></strong></td>
                                    <td><?= htmlspecialchars($pag['nome_plano']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($pag['data_vencimento'])) ?></td>
                                    <td>R$ <?= number_format($pag['valor'], 2, ',', '.') ?></td>
                                    
                                    <td>
                                        <?php if ($pag['status'] === 'PAGO'): ?>
                                            <span class="text-success fw-semibold">Pago em <?= date('d/m/y', strtotime($pag['data_pagamento'])) ?></span>
                                            <br><small class="text-muted"><?= htmlspecialchars($pag['forma_pagamento']) ?></small>
                                        <?php elseif ($pag['status'] === 'ATRASADO' || (strtotime($pag['data_vencimento']) < time() && $pag['status'] === 'PENDENTE')): ?>
                                            <span class="text-danger fw-semibold">Atrasado</span>
                                        <?php else: ?>
                                            <span class="text-warning fw-semibold">Pendente</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($pag['status'] !== 'PAGO'): ?>
                                            <form action="../../controller/FinanceiroController.php" method="POST" class="p-0 m-0 border-0 shadow-none bg-transparent d-flex gap-2 align-items-center w-auto">
                                                
                                                <input type="hidden" name="acao" value="baixar_pagamento">
                                                <input type="hidden" name="id_pagamento" value="<?= $pag['id_pagamento'] ?>">
                                                
                                                <select name="forma_pagamento" class="form-select form-select-sm" style="width: 120px;" required>
                                                    <option value="PIX">PIX</option>
                                                    <option value="CARTAO_DEBITO">Cartão débito</option>
                                                    <option value="CARTAO_CREDITO">Cartão crédito</option>
                                                    <option value="DINHEIRO">Dinheiro</option>
                                                </select>
                                                
                                                <button type="submit" class="btn btn-sm btn-success text-nowrap" onclick="return confirm('Confirmar o recebimento deste pagamento?');">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>