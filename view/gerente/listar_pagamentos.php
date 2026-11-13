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

// Consolida as cobranças abertas por aluno, mantendo cada mês separado.
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
            'atrasadas' => 0
        ];
    }

    $status = strtoupper((string)$pag['status']);
    $atrasada = $status === 'ATRASADO'
        || ($status === 'PENDENTE' && $pag['data_vencimento'] < $hoje);
    $pag['status_exibicao'] = $status === 'EM_ANALISE'
        ? 'EM_ANALISE'
        : ($atrasada ? 'ATRASADO' : 'PENDENTE');
    $alunosComPendencias[$idAluno]['cobrancas'][] = $pag;
    $alunosComPendencias[$idAluno]['total'] += (float)$pag['valor'];
    if ($atrasada) {
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
            min-width: 900px;
            margin-top: 0;
        }

        #tabela-pagamentos .cobrancas-aluno {
            min-width: 700px;
        }

        #tabela-pagamentos .cobranca-mensal {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) auto minmax(260px, auto);
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        #tabela-pagamentos .cobranca-mensal:last-child {
            border-bottom: 0;
        }

        #tabela-pagamentos .pagamento-actions {
            display: flex;
            align-items: center;
            gap: 0.25rem;
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

        #tabela-pagamentos .pagamento-action-form[hidden] {
            display: none;
        }

        #tabela-pagamentos .pagamento-form-field {
            width: 100%;
            min-width: 130px;
            margin: 0;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        #tabela-pagamentos input.pagamento-form-field {
            min-width: 145px;
        }

        #tabela-pagamentos .btn.action-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 2.2rem;
            margin: 0 !important;
            padding: 0;
            border: 0 !important;
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
            background-color: rgba(13, 110, 253, 0.15) !important;
        }

        #tabela-pagamentos .btn.action-disable:hover {
            background-color: rgba(220, 53, 69, 0.15) !important;
        }

        #tabela-pagamentos .btn.action-reactivate:hover {
            background-color: rgba(25, 135, 84, 0.15) !important;
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
                <p class="text-muted">Cada aluno aparece uma vez, com suas mensalidades pendentes e atrasadas.</p>
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
                <p class="text-center text-muted py-4">Nenhuma cobrança pendente ou atrasada.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="tabela-pagamentos" class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Aluno</th>
                                <th>Mensalidades em aberto</th>
                                <th class="text-end">Total em aberto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alunosComPendencias as $aluno): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($aluno['aluno_nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <div class="small mt-1">
                                            <?php if ($aluno['pendentes'] > 0): ?>
                                                <span class="badge text-bg-warning"><?= $aluno['pendentes'] ?> pendente(s)</span>
                                            <?php endif; ?>
                                            <?php if ($aluno['atrasadas'] > 0): ?>
                                                <span class="badge text-bg-danger"><?= $aluno['atrasadas'] ?> atrasada(s)</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="cobrancas-aluno">
                                        <?php foreach ($aluno['cobrancas'] as $cobranca): ?>
                                            <?php
                                            $idPagamento = (int)$cobranca['id_pagamento'];
                                            $status = $cobranca['status_exibicao'];
                                            $nomeAluno = htmlspecialchars($aluno['aluno_nome'], ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <div class="cobranca-mensal">
                                                <div>
                                                    <strong><?= date('m/Y', strtotime($cobranca['mes_ano'] . '-01')) ?></strong>
                                                    <span class="text-muted"> · vence <?= date('d/m/Y', strtotime($cobranca['data_vencimento'])) ?></span>
                                                    <div class="small text-muted">
                                                        <?= htmlspecialchars(implode(', ', array_unique($cobranca['nomes_planos'])), ENT_QUOTES, 'UTF-8') ?>
                                                    </div>
                                                </div>
                                                <div class="text-nowrap">
                                                    <span class="fw-semibold">R$ <?= number_format((float)$cobranca['valor'], 2, ',', '.') ?></span>
                                                    <?php if ($status === 'ATRASADO'): ?>
                                                        <span class="badge text-bg-danger">Atrasado</span>
                                                    <?php elseif ($status === 'EM_ANALISE'): ?>
                                                        <span class="badge text-bg-info">Em análise</span>
                                                    <?php else: ?>
                                                        <span class="badge text-bg-warning">Pendente</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <select class="form-select form-select-sm pagamento-form-field"
                                                            name="forma_pagamento" form="form-receber-<?= $idPagamento ?>" required
                                                            aria-label="Forma de pagamento de <?= $nomeAluno ?>">
                                                        <option value="DINHEIRO">Dinheiro</option>
                                                        <option value="PIX">PIX</option>
                                                        <option value="CARTAO">Cartão</option>
                                                        <option value="OUTRO">Outro</option>
                                                    </select>
                                                    <input type="date" class="form-control form-control-sm pagamento-form-field"
                                                           name="data_pagamento" form="form-receber-<?= $idPagamento ?>"
                                                           value="<?= date('Y-m-d') ?>" required
                                                           aria-label="Data de pagamento de <?= $nomeAluno ?>">
                                                    <form id="form-receber-<?= $idPagamento ?>"
                                                          action="../../controller/FinanceiroController.php" method="POST"
                                                          class="pagamento-action-form"
                                                          onsubmit="return confirm('Confirmar o recebimento desta mensalidade?');">
                                                        <input type="hidden" name="acao" value="baixar_pagamento">
                                                        <input type="hidden" name="id_pagamento" value="<?= $idPagamento ?>">
                                                        <button type="submit" class="btn btn-sm action-icon-btn action-reactivate"
                                                                title="Confirmar recebimento" aria-label="Confirmar recebimento de <?= $nomeAluno ?>">
                                                            <i class="bi bi-check-circle" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                    <?php if ($status !== 'EM_ANALISE'): ?>
                                                        <button type="button" class="btn btn-sm action-icon-btn action-edit"
                                                                title="Editar vencimento" aria-label="Editar vencimento"
                                                                data-bs-toggle="modal" data-bs-target="#modalEditarVencimento"
                                                                data-pagamento-id="<?= $idPagamento ?>"
                                                                data-vencimento="<?= htmlspecialchars((string)$cobranca['data_vencimento'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </td>
                                    <td class="text-end fw-semibold">R$ <?= number_format($aluno['total'], 2, ',', '.') ?></td>
                                </tr>
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