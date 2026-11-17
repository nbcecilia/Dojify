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
$dao->gerarMensalidadesRecorrentes($idAcademia);
$pagamentosBrutos = $dao->listarPagamentosDaAcademia($idAcademia);

// Consolida as mensalidades por aluno, mantendo cada mês separado.
$alunosComPendencias = [];
$hoje = date('Y-m-d');
foreach ($pagamentosBrutos as $pag) {
    $pag['nomes_planos'] = !empty($pag['nome_plano']) ? explode(', ', $pag['nome_plano']) : [];
    $pag['ids_pagamentos'] = !empty($pag['ids_pagamentos'])
        ? array_map('intval', explode(',', $pag['ids_pagamentos']))
        : [];

    $idAluno = (int)$pag['id_aluno'];
    if (!isset($alunosComPendencias[$idAluno])) {
        $alunosComPendencias[$idAluno] = [
            'aluno_nome' => $pag['aluno_nome'],
            'cobrancas' => [],
            'total' => 0.0,
            'pendentes' => 0,
            'atrasadas' => 0,
            'pagas' => 0
        ];
    }

    $status = strtoupper((string)$pag['status']);
    $atrasada = $status === 'ATRASADO'
        || ($status === 'PENDENTE' && $pag['data_vencimento'] < $hoje);
    $pag['status_exibicao'] = $status === 'PAGO'
        ? 'PAGO'
        : ($status === 'EM_ANALISE' ? 'EM_ANALISE' : ($atrasada ? 'ATRASADO' : 'PENDENTE'));
    $alunosComPendencias[$idAluno]['cobrancas'][] = $pag;
    $alunosComPendencias[$idAluno]['total'] += (float)$pag['valor'];
    if ($status === 'PAGO') {
        $alunosComPendencias[$idAluno]['pagas']++;
    } elseif ($atrasada) {
        $alunosComPendencias[$idAluno]['atrasadas']++;
    } else {
        $alunosComPendencias[$idAluno]['pendentes']++;
    }
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
    <link rel="stylesheet" href="../../assets/css/estilo.css?v=<?= filemtime(__DIR__ . '/../../assets/css/estilo.css'); ?>">
    <style>
        #tabela-pagamentos {
            min-width: 1020px;
            margin-top: 0;
            --bs-table-bg: #fff;
            --bs-table-hover-bg: #f7f8fa;
        }

        #tabela-pagamentos thead th {
            padding: 0.65rem 0.45rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            white-space: normal;
        }

        #tabela-pagamentos thead th:last-child {
            text-align: center;
        }

        #tabela-pagamentos tbody td {
            padding: 0.7rem 0.45rem;
            border-color: #e9ecef;
            vertical-align: middle;
        }

        #tabela-pagamentos .payment-student {
            min-width: 160px;
        }

        #tabela-pagamentos .payment-student-name {
            display: block;
            color: #212529;
            font-weight: 650;
        }

        #tabela-pagamentos .payment-student-plan {
            display: block;
            max-width: 220px;
            margin-top: 0.2rem;
            color: #6c757d;
            font-size: 0.78rem;
        }

        #tabela-pagamentos .payment-reference,
        #tabela-pagamentos .payment-due,
        #tabela-pagamentos .payment-value,
        #tabela-pagamentos .payment-method,
        #tabela-pagamentos .payment-date {
            white-space: nowrap;
        }

        #tabela-pagamentos .payment-value {
            font-weight: 600;
        }

        #tabela-pagamentos .payment-actions-cell {
            min-width: 315px;
        }

        #tabela-pagamentos .pagamento-actions {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 0.2rem;
            white-space: nowrap;
        }

        #tabela-pagamentos .pagamento-action-form {
            display: inline-flex;
            align-items: center;
            width: auto;
            max-width: none;
            margin: 0;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }

        #tabela-pagamentos .pagamento-receber-form {
            display: inline-flex;
            flex: 0 0 auto;
            gap: 0.2rem;
        }

        #tabela-pagamentos .pagamento-form-field {
            width: 100%;
            min-width: 0;
            margin: 0;
            padding: 0.3rem 0.4rem;
            font-size: 0.78rem;
        }

        #tabela-pagamentos select.pagamento-form-field {
            flex: 0 0 88px;
            width: 88px;
        }

        #tabela-pagamentos input.pagamento-form-field {
            flex: 0 0 112px;
            width: 112px;
        }

        #tabela-pagamentos .btn.action-icon-btn {
            display: inline-flex;
            flex: 0 0 2.2rem;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 2.2rem;
            margin: 0 !important;
            padding: 0;
            border: 0 !important;
            border-radius: 0.4rem;
            background: transparent !important;
            box-shadow: none !important;
            font-size: 1rem;
            transition: background-color 0.2s ease-in-out;
        }

        #tabela-pagamentos .btn.action-edit {
            color: #0d6efd !important;
        }

        #tabela-pagamentos .btn.action-disable {
            color: #dc3545 !important;
        }

        #tabela-pagamentos .btn.action-reactivate {
            color: #198754 !important;
        }

        #tabela-pagamentos .btn.action-edit:hover {
            background-color: rgba(13, 110, 253, 0.12) !important;
        }

        #tabela-pagamentos .btn.action-disable:hover {
            background-color: rgba(220, 53, 69, 0.12) !important;
        }

        #tabela-pagamentos .btn.action-reactivate:hover {
            background-color: rgba(25, 135, 84, 0.12) !important;
        }

        #modalEditarPagamento form,
        #modalEditarVencimento form {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }

        #modalEditarPagamento .modal-footer > button[type="submit"],
        #modalEditarVencimento .modal-footer > button[type="submit"] {
            width: auto;
            margin: 0;
            padding: 8px 16px;
        }

        @media (max-width: 768px) {
            #tabela-pagamentos .payment-actions-cell {
                min-width: 315px;
            }
        }
    </style>
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
                <p class="text-muted">Mensalidades da academia, com vencimentos e recebimentos organizados por aluno.</p>
            </div>

            <div class="mb-4" data-live-search-container>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" data-live-search
                           data-search-target="#tabela-pagamentos tbody tr"
                           placeholder="Pesquisar por nome do aluno..." autocomplete="off">
                </div>
            </div>

            <?php
            $toastTipo = null;
            $toastTexto = '';
            if (isset($_GET['sucesso'])) {
                $toastTipo = 'sucesso';
                $mensagensSucesso = [
                    'recebido' => 'Recebimento registado com sucesso!',
                    'atualizado' => 'Pagamento atualizado com sucesso!',
                    'cancelado' => 'Pagamento cancelado com sucesso!'
                ];
                $toastTexto = $mensagensSucesso[$_GET['sucesso']] ?? 'Operação concluída com sucesso!';
            } elseif (isset($_GET['erro'])) {
                $toastTipo = 'erro';
                $toastTexto = 'Não foi possível concluir a operação. Verifique os dados e tente novamente.';
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

            <?php if (empty($alunosComPendencias)): ?>
                <p class="text-center text-muted py-4">Nenhuma mensalidade encontrada.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="tabela-pagamentos" class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Aluno</th>
                                <th>Referência</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Forma de pagamento</th>
                                <th>Data de pagamento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alunosComPendencias as $aluno): ?>
                                <?php foreach ($aluno['cobrancas'] as $cobranca): ?>
                                    <?php
                                    $idPagamento = (int)$cobranca['id_pagamento'];
                                    $status = $cobranca['status_exibicao'];
                                    $nomeAluno = htmlspecialchars($aluno['aluno_nome'], ENT_QUOTES, 'UTF-8');
                                    $nomesPlanos = implode(', ', array_unique($cobranca['nomes_planos']));
                                    $formasPagamento = [
                                        'DINHEIRO' => 'Dinheiro',
                                        'PIX' => 'Pix',
                                        'CARTAO' => 'Cartão',
                                        'OUTRO' => 'Outro'
                                    ];
                                    $formaPagamento = strtoupper(trim((string)($cobranca['forma_pagamento'] ?? '')));
                                    $formaExibicao = $status === 'PAGO'
                                        ? ($formasPagamento[$formaPagamento] ?? ($formaPagamento !== '' ? $formaPagamento : '—'))
                                        : '—';
                                    $dataPagamento = $status === 'PAGO' && !empty($cobranca['data_pagamento'])
                                        ? date('d/m/Y', strtotime($cobranca['data_pagamento']))
                                        : '—';
                                    $statusRotulos = [
                                        'ATRASADO' => 'Atrasado',
                                        'PAGO' => 'Pago',
                                        'EM_ANALISE' => 'Em análise',
                                        'PENDENTE' => 'Pendente'
                                    ];
                                    $statusClasses = [
                                        'ATRASADO' => 'text-bg-danger',
                                        'PAGO' => 'text-bg-success',
                                        'EM_ANALISE' => 'text-bg-info',
                                        'PENDENTE' => 'text-bg-warning'
                                    ];
                                    ?>
                                    <tr>
                                        <td class="payment-student">
                                            <span class="payment-student-name"><?= $nomeAluno ?></span>
                                            <?php if ($nomesPlanos !== ''): ?>
                                                <span class="payment-student-plan"><?= htmlspecialchars($nomesPlanos, ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="payment-reference"><?= date('m/Y', strtotime($cobranca['mes_ano'] . '-01')) ?></td>
                                        <td class="payment-due"><?= date('d/m/Y', strtotime($cobranca['data_vencimento'])) ?></td>
                                        <td class="payment-value">R$ <?= number_format((float)$cobranca['valor'], 2, ',', '.') ?></td>
                                        <td><span class="badge <?= $statusClasses[$status] ?? 'text-bg-secondary' ?>"><?= $statusRotulos[$status] ?? htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td class="payment-method"><?= htmlspecialchars($formaExibicao, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="payment-date"><?= htmlspecialchars($dataPagamento, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="payment-actions-cell">
                                            <div class="pagamento-actions">
                                                <?php if ($status === 'PAGO'): ?>
                                                    <button type="button" class="btn btn-sm action-icon-btn action-edit"
                                                            title="Editar pagamento" aria-label="Editar pagamento de <?= $nomeAluno ?>"
                                                            data-bs-toggle="modal" data-bs-target="#modalEditarPagamento"
                                                            data-pagamento-id="<?= $idPagamento ?>"
                                                            data-valor="<?= htmlspecialchars((string)$cobranca['valor'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-vencimento="<?= htmlspecialchars((string)$cobranca['data_vencimento'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-data-pagamento="<?= htmlspecialchars((string)($cobranca['data_pagamento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                            data-forma-pagamento="<?= htmlspecialchars($formaPagamento, ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                                    </button>
                                                    <form action="../../controller/FinanceiroController.php" method="POST"
                                                          class="pagamento-action-form"
                                                          onsubmit="return confirm('Deseja cancelar este pagamento?');">
                                                        <input type="hidden" name="acao" value="cancelar_pagamento">
                                                        <input type="hidden" name="id_pagamento" value="<?= $idPagamento ?>">
                                                        <button type="submit" class="btn btn-sm action-icon-btn action-disable"
                                                                title="Cancelar pagamento" aria-label="Cancelar pagamento de <?= $nomeAluno ?>">
                                                            <i class="bi bi-x-circle" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form id="form-receber-<?= $idPagamento ?>"
                                                          action="../../controller/FinanceiroController.php" method="POST"
                                                          class="pagamento-action-form pagamento-receber-form"
                                                          onsubmit="return confirm('Confirmar o recebimento desta mensalidade?');">
                                                        <input type="hidden" name="acao" value="baixar_pagamento">
                                                        <input type="hidden" name="id_pagamento" value="<?= $idPagamento ?>">
                                                        <select class="form-select form-select-sm pagamento-form-field"
                                                                name="forma_pagamento" required
                                                                aria-label="Forma de pagamento de <?= $nomeAluno ?>">
                                                            <option value="DINHEIRO">Dinheiro</option>
                                                            <option value="PIX">Pix</option>
                                                            <option value="CARTAO">Cartão</option>
                                                            <option value="OUTRO">Outro</option>
                                                        </select>
                                                        <input type="date" class="form-control form-control-sm pagamento-form-field"
                                                               name="data_pagamento" value="<?= date('Y-m-d') ?>" required
                                                               aria-label="Data de recebimento de <?= $nomeAluno ?>">
                                                        <button type="submit" class="btn btn-sm action-icon-btn action-reactivate"
                                                                title="Confirmar recebimento" aria-label="Confirmar recebimento de <?= $nomeAluno ?>">
                                                            <i class="bi bi-check-circle" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                    <?php if ($status !== 'EM_ANALISE'): ?>
                                                        <button type="button" class="btn btn-sm action-icon-btn action-edit"
                                                                title="Editar vencimento" aria-label="Editar vencimento de <?= $nomeAluno ?>"
                                                                data-bs-toggle="modal" data-bs-target="#modalEditarVencimento"
                                                                data-pagamento-id="<?= $idPagamento ?>"
                                                                data-vencimento="<?= htmlspecialchars((string)$cobranca['data_vencimento'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <i class="bi bi-calendar2-event" aria-hidden="true"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <div class="modal fade" id="modalEditarPagamento" tabindex="-1" aria-labelledby="modalEditarPagamentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="../../controller/FinanceiroController.php" method="POST">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="modalEditarPagamentoLabel">Editar pagamento</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="editar_pagamento">
                        <input type="hidden" name="id_pagamento" id="editar-id-pagamento">
                        <div class="mb-3">
                            <label for="editar-valor" class="form-label">Valor total (R$)</label>
                            <input type="number" class="form-control" id="editar-valor" name="valor" min="0.01" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar-data-vencimento" class="form-label">Data de vencimento</label>
                            <input type="date" class="form-control" id="editar-data-vencimento" name="data_vencimento" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar-forma-pagamento" class="form-label">Forma de pagamento</label>
                            <select class="form-select" id="editar-forma-pagamento" name="forma_pagamento">
                                <option value="">Não informada</option>
                                <option value="DINHEIRO">Dinheiro</option>
                                <option value="PIX">PIX</option>
                                <option value="CARTAO">Cartão</option>
                                <option value="OUTRO">Outro</option>
                            </select>
                        </div>
                        <div>
                            <label for="editar-data-pagamento" class="form-label">Data de pagamento</label>
                            <input type="date" class="form-control" id="editar-data-pagamento" name="data_pagamento">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarVencimento" tabindex="-1" aria-labelledby="modalEditarVencimentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="../../controller/FinanceiroController.php" method="POST">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="modalEditarVencimentoLabel">Editar data de vencimento</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="editar_vencimento">
                        <input type="hidden" name="id_pagamento" id="editar-vencimento-id-pagamento">
                        <label for="editar-vencimento-data" class="form-label">Data de vencimento</label>
                        <input type="date" class="form-control" id="editar-vencimento-data" name="data_vencimento" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                        <button type="submit" class="btn btn-primary">Salvar alteração</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/busca.js"></script>
    <script>
        document.getElementById('modalEditarPagamento').addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;
            document.getElementById('editar-id-pagamento').value = botao.dataset.pagamentoId;
            document.getElementById('editar-valor').value = botao.dataset.valor;
            document.getElementById('editar-data-vencimento').value = botao.dataset.vencimento;
            document.getElementById('editar-data-pagamento').value = botao.dataset.dataPagamento;
            document.getElementById('editar-forma-pagamento').value = botao.dataset.formaPagamento.toUpperCase();
        });

        document.getElementById('modalEditarVencimento').addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;
            document.getElementById('editar-vencimento-id-pagamento').value = botao.dataset.pagamentoId;
            document.getElementById('editar-vencimento-data').value = botao.dataset.vencimento;
        });
    </script>
</body>
</html>