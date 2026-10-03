<?php
// view/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$notifications = ['total_atrasados' => 0, 'valor_atrasados' => 0, 'lista' => []];
$homeUrl = isset($_SESSION['usuario']) && (int) ($_SESSION['usuario']['perfil_id'] ?? 0) === 4
    ? '../aluno/home_aluno.php'
    : '../gerente/home_gerente.php';

// Se for um Gerente logado, busca as notificações financeiras
if (isset($_SESSION['usuario']) && (int)$_SESSION['usuario']['perfil_id'] === 2 && isset($_SESSION['id_academia'])) {
    require_once __DIR__ . '/../../model/dao/PagamentoDAO.php';
    $pagamentoDAO = new \PagamentoDAO();
    $notifications = $pagamentoDAO->obterNotificacoesFinanceiras((int)$_SESSION['id_academia']);
}
?>

<header class="navbar">
    <div class="navbar-brand">
        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="logo-link">
            <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
            <div>
                <h1>Dojify</h1>
            </div>
        </a>
    </div>

    <div class="navbar-user">

        <!-- ÍCONE DE NOTIFICAÇÕES -->
        <?php if (isset($_SESSION['usuario']) && (int)$_SESSION['usuario']['perfil_id'] === 2): ?>
            <div class="notificacao-container">
                <button type="button" id="btnNotificacao" class="btn-notificacao">
                    🔔
                    <?php if ($notifications['total_atrasados'] > 0): ?>
                        <span class="notificacao-badge">
                            <?= $notifications['total_atrasados'] ?>
                        </span>
                    <?php endif; ?>
                </button>
                

                <!-- MENU DROPDOWN DE NOTIFICAÇÕES -->
                <div id="dropdownNotificacoes" class="notificacao-dropdown">

                    <!-- Cabeçalho do Dropdown -->
                    <div class="notificacao-dropdown-header">
                        <strong>Notificações</strong>
                        <span class="notificacao-count-badge">
                            <?= $notifications['total_atrasados'] ?> pendências
                        </span>
                    </div>

                    <!-- Lista de Atrasados -->
                    <div class="notificacao-lista">
                        <?php if (empty($notifications['lista'])): ?>
                            <p class="notificacao-vazio">
                                Tudo em dia! Nenhuma pendência. 🎉
                            </p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($notifications['lista'] as $item): ?>
                                    <li>
                                        <div class="notificacao-item-nome">
                                            <?= htmlspecialchars($item['aluno_nome']) ?>
                                        </div>
                                        <div class="notificacao-item-info">
                                            <span>Venceu: <?= date('d/m/Y', strtotime($item['data_vencimento'])) ?></span>
                                            <strong>R$ <?= number_format($item['valor'], 2, ',', '.') ?></strong>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Rodapé do Dropdown -->
                    <?php if (!empty($notifications['lista'])): ?>
                        <div class="notificacao-dropdown-footer">
                            <a href="../gerente/listar_pagamentos.php" class="btn btn-success">
                                Resolver Pendências
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
<?php 
// Verifica se o utilizador logado é Aluno (perfil_id 4)
$is_aluno = isset($_SESSION['usuario']) && (int)$_SESSION['usuario']['perfil_id'] === 4;
$notificacoes_aluno = $is_aluno && isset($notificacoes) && is_array($notificacoes)
    ? $notificacoes
    : [];
$total_notif_header = count($notificacoes_aluno);
?>

<?php if ($is_aluno): ?>
    <div class="dropdown me-3 d-inline-block">
        <button class="btn btn-dark position-relative rounded-circle p-2 shadow-sm d-flex align-items-center justify-content-center"
                type="button"
                id="dropdownNotifAlunoHeader"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                aria-expanded="false"
                aria-label="Abrir notificações"
                style="width: 40px; height: 40px; background-color: #212529; border: 1px solid #495057;">
            <span style="font-size: 1rem;">🔔</span>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                  data-notification-unread-badge
                  <?= $total_notif_header === 0 ? 'hidden' : ''; ?>>
                <?= $total_notif_header; ?>
            </span>
        </button>
        
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 mt-2"
            aria-labelledby="dropdownNotifAlunoHeader"
            data-notification-menu
            data-storage-key="dojify-notificacoes-aluno-<?= (int)$_SESSION['usuario']['id_usuario']; ?>"
            style="width: min(340px, calc(100vw - 2rem)); font-size: 0.85rem;">
            <li class="dropdown-header fw-bold text-dark border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                <span>Notificações</span>
                <span class="badge bg-danger rounded-pill" data-notification-unread-count>
                    <?= $total_notif_header; ?> <?= $total_notif_header === 1 ? 'nova' : 'novas'; ?>
                </span>
            </li>
            <?php if (!empty($notificacoes_aluno)): ?>
                <li class="px-2 pb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-notification-mark-all>
                        Marcar todas como lidas
                    </button>
                </li>
                <?php foreach ($notificacoes_aluno as $notificacao): ?>
                    <?php
                    $idNotificacao = (string)($notificacao['id'] ?? '');
                    $tipoNotificacao = (string)($notificacao['acao'] ?? '');
                    ?>
                    <li class="notification-item p-2 mb-2 bg-light rounded border-start border-4 border-warning"
                        data-notification-id="<?= htmlspecialchars($idNotificacao, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <span class="fw-bold text-dark">
                                <?= htmlspecialchars((string)($notificacao['icone'] ?? '🔔'), ENT_QUOTES, 'UTF-8'); ?>
                                <?= htmlspecialchars((string)($notificacao['titulo'] ?? 'Notificação'), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <div class="d-flex flex-shrink-0 gap-1">
                                <?php if ($tipoNotificacao === 'pagamento'): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-danger py-0 px-2"
                                            data-notification-action
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalPagamento"
                                            data-notification-tooltip
                                            data-bs-placement="top"
                                            title="Resolver pendência"
                                            aria-label="Resolver pendência">
                                        <i class="bi bi-cash-coin" aria-hidden="true"></i>
                                    </button>
                                <?php elseif ($tipoNotificacao === 'agenda'): ?>
                                    <a class="btn btn-sm btn-dark py-0 px-2"
                                       href="#agendaSemanalTitulo"
                                       data-notification-action
                                       data-bs-toggle="tooltip"
                                       data-bs-placement="top"
                                       title="Ver agenda"
                                       aria-label="Ver agenda">
                                        <i class="bi bi-calendar-week" aria-hidden="true"></i>
                                    </a>
                                <?php endif; ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary py-0 px-2"
                                        data-notification-mark-read
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Marcar como lida"
                                        aria-label="Marcar como lida">
                                    <i class="bi bi-envelope-check" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <span class="text-muted d-block mb-2" style="font-size: 0.75rem;">
                            <?= htmlspecialchars((string)($notificacao['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="p-2 text-muted">Você não tem notificações no momento.</li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>
        <!-- Dados do Utilizador -->
        <span class="user-greeting">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Usuário') ?></strong></span>
        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline">Início</a>
        <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
    </div>
</header>

<!-- SCRIPT PARA ABRIR/FECHAR O DROPDOWN -->

<script src="../../assets/js/main.js"></script>
