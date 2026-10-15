<?php
// view/gerente/relatorio_financeiro.php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 2 || !isset($_SESSION['id_academia'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/PagamentoDAO.php';

$dao = new \PagamentoDAO();
$idAcademia = (int) $_SESSION['id_academia'];
$dadosRelatorio = [];

// Valores padrão para os filtros
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01'); // 1º dia do mês atual
$dataFim = $_GET['data_fim'] ?? date('Y-m-t'); // Último dia do mês atual
$status = $_GET['status'] ?? 'TODOS';
$totalValor = 0;

// Se o formulário de filtro foi submetido, busca os dados
if (isset($_GET['filtrar'])) {
    $dadosRelatorio = $dao->relatorioPagamentos($idAcademia, $dataInicio, $dataFim, $status);
    foreach ($dadosRelatorio as $pag) {
        if (strtoupper($pag['status']) === 'PAGO') {
            $totalValor += $pag['valor'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Financeiro - Dojify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilo Global Dojify -->
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <style>
        /* Regras exclusivas para quando o utilizador imprimir a página ou gerar PDF */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
            .card { box-shadow: none !important; border: none !important; padding: 0 !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #ccc !important; padding: 8px !important; font-size: 12px !important; }
            th { background-color: #f1f5f9 !important; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .badge { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <?php include '../includes/header.php'; ?>
    </div>

    <main class="container my-4 flex-grow-1">
        
        <header class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <a href="listar_pagamentos.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Gestão de Pagamentos
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            
            <div class="mb-4 no-print">
                <h2 class="fs-4 fw-bold">Emissão de Relatório Financeiro</h2>
                <p class="text-muted mb-0">Filtre por período e estado para gerar relatórios detalhados das mensalidades.</p>
            </div>

            <!-- Formulário de Filtros com largura total em Bootstrap -->
            <form method="GET" class="no-print w-100 mb-4" style="background: transparent !important; max-width: 100% !important; box-shadow: none !important; border: none !important; padding: 0 !important; margin: 0 0 24px 0 !important;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="data_inicio" class="form-label fw-medium">Data Início</label>
                        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= htmlspecialchars($dataInicio) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="data_fim" class="form-label fw-medium">Data Fim</label>
                        <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= htmlspecialchars($dataFim) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label fw-medium">Estado</label>
                        <select name="status" id="status" class="form-select">
                            <option value="TODOS" <?= $status === 'TODOS' ? 'selected' : '' ?>>Todos</option>
                            <option value="PAGO" <?= $status === 'PAGO' ? 'selected' : '' ?>>Pagos</option>
                            <option value="PENDENTE" <?= $status === 'PENDENTE' ? 'selected' : '' ?>>Pendentes</option>
                            <option value="EM_ANALISE" <?= $status === 'EM_ANALISE' ? 'selected' : '' ?>>Comprovante em análise</option>
                            <option value="ATRASADO" <?= $status === 'ATRASADO' ? 'selected' : '' ?>>Atrasados</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="filtrar" value="1" class="btn btn-dark w-100">
                            <i class="bi bi-funnel me-1"></i> Gerar
                        </button>
                    </div>
                </div>
            </form>

            <!-- Área do Relatório (Aparece na impressão) -->
            <?php if (isset($_GET['filtrar'])): ?>
                
                <div class="text-center mb-4">
                    <h3 class="fs-5 fw-bold mb-1">Relatório de Mensalidades - Dojify</h3>
                    <p class="text-muted small mb-0">
                        Período: <strong><?= date('d/m/Y', strtotime($dataInicio)) ?></strong> a <strong><?= date('d/m/Y', strtotime($dataFim)) ?></strong> | 
                        Estado: <strong><?= $status === 'TODOS' ? 'Geral' : ucfirst(strtolower($status)) ?></strong>
                    </p>
                </div>

                <?php if (empty($dadosRelatorio)): ?>
                    <p class="text-center text-muted py-4">Nenhum registo encontrado para este período.</p>
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
                                    <th>Forma de Pag.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dadosRelatorio as $pag): ?>
                                    <?php 
                                        $statusUpper = strtoupper($pag['status']);
                                        if ($statusUpper === 'PAGO') {
                                            $badgeClass = 'bg-success';
                                        } elseif ($statusUpper === 'PENDENTE') {
                                            $badgeClass = 'bg-warning text-dark';
                                        } elseif ($statusUpper === 'ATRASADO') {
                                            $badgeClass = 'bg-danger';
                                        } elseif ($statusUpper === 'EM_ANALISE') {
                                            $badgeClass = 'bg-info text-dark';
                                        } else {
                                            $badgeClass = 'bg-secondary';
                                        }
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($pag['aluno_nome']) ?></strong></td>
                                        <td><?= htmlspecialchars($pag['nome_plano']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($pag['data_vencimento'])) ?></td>
                                        <td>R$ <?= number_format($pag['valor'], 2, ',', '.') ?></td>
                                        <td>
                                            <span class="badge <?= $badgeClass ?> px-3 py-2">
                                                <?= htmlspecialchars($pag['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $pag['forma_pagamento'] ? htmlspecialchars($pag['forma_pagamento']) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
                        <h4 class="fs-5 fw-bold mb-0">Total recebido: <span class="text-success">R$ <?= number_format($totalValor, 2, ',', '.') ?></span></h4>
                    </div>

                    <div class="no-print text-center mt-4">
                        <button onclick="window.print()" class="btn btn-success px-4 py-2">
                            <i class="bi bi-printer me-2"></i> Imprimir / Guardar PDF
                        </button>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </section>
    </main>

  
        <?php include '../includes/footer.php'; ?>
  

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>