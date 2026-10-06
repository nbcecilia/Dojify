<?php
// view/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$notifications = [
    'total_atrasados' => 0,
    'valor_atrasados' => 0,
    'lista' => [],
    'avaliacoes' => ['total' => 0, 'lista' => []],
];
$perfilUsuario = (int)($_SESSION['usuario']['perfil_id'] ?? 0);
$is_aluno = isset($_SESSION['usuario']) && (int)$_SESSION['usuario']['perfil_id'] === 4;
$idUsuarioHeader = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
$nomeUsuarioHeader = (string)($_SESSION['usuario']['nome'] ?? 'Usuário');
$iniciaisUsuarioHeader = '';
$quantidadeIniciaisHeader = 0;
foreach (preg_split('/\s+/u', trim($nomeUsuarioHeader), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $parteNome) {
    preg_match('/^./u', $parteNome, $letraInicial);
    $letraInicial = $letraInicial[0] ?? substr($parteNome, 0, 1);
    $iniciaisUsuarioHeader .= function_exists('mb_strtoupper')
        ? mb_strtoupper($letraInicial, 'UTF-8')
        : strtoupper($letraInicial);
    $quantidadeIniciaisHeader++;
    if ($quantidadeIniciaisHeader >= 2) {
        break;
    }
}
$iniciaisUsuarioHeader = $iniciaisUsuarioHeader !== '' ? $iniciaisUsuarioHeader : 'U';
$avatarUsuarioUrl = null;
foreach (['webp', 'png', 'jpg', 'jpeg'] as $extensaoAvatar) {
    $arquivoAvatar = __DIR__ . '/../../assets/uploads/avatars/user-' . $idUsuarioHeader . '.' . $extensaoAvatar;
    if ($idUsuarioHeader > 0 && is_file($arquivoAvatar)) {
        $avatarUsuarioUrl = '../../assets/uploads/avatars/user-' . $idUsuarioHeader . '.' . $extensaoAvatar
            . '?v=' . filemtime($arquivoAvatar);
        break;
    }
}
if (isset($_SESSION['usuario']) && !isset($_SESSION['avatar_csrf_token'])) {
    $_SESSION['avatar_csrf_token'] = bin2hex(random_bytes(32));
}
$avatarFeedback = $_SESSION['avatar_feedback'] ?? null;
unset($_SESSION['avatar_feedback']);
$homeUrl = isset($_SESSION['usuario']) && (int) ($_SESSION['usuario']['perfil_id'] ?? 0) === 4
    ? '../aluno/home_aluno.php'
    : ($perfilUsuario === 3 ? '../professor/home_professor.php' : '../gerente/home_gerente.php');

// Se for um Gerente logado, busca as notificações financeiras
if (isset($_SESSION['usuario']) && $perfilUsuario === 2 && isset($_SESSION['id_academia'])) {
    require_once __DIR__ . '/../../model/dao/PagamentoDAO.php';
    $pagamentoDAO = new \PagamentoDAO();
    $notifications = $pagamentoDAO->obterNotificacoesFinanceiras((int)$_SESSION['id_academia']);
}

if (isset($_SESSION['usuario']) && in_array($perfilUsuario, [2, 3], true)) {
    require_once __DIR__ . '/../../model/dao/AvaliacaoDAO.php';
    $avaliacaoDAOHeader = new AvaliacaoDAO();
    $notifications['avaliacoes'] = $avaliacaoDAOHeader->listarNotificacoesEquipe(
        (int)$_SESSION['usuario']['id_usuario']
    );
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

    <div class="navbar-user<?= $is_aluno ? ' navbar-user-aluno' : ''; ?>">

        <!-- ÍCONE DE NOTIFICAÇÕES -->
        <?php if (isset($_SESSION['usuario']) && in_array($perfilUsuario, [2, 3], true)): ?>
            <div class="notificacao-container">
                <button type="button" id="btnNotificacao" class="btn-notificacao">
                    🔔
                    <?php
                    $totalNotificacoesEquipe = (int)($notifications['avaliacoes']['total'] ?? 0)
                        + ($perfilUsuario === 2 ? (int)($notifications['total_atrasados'] ?? 0) : 0);
                    ?>
                    <?php if ($totalNotificacoesEquipe > 0): ?>
                        <span class="notificacao-badge">
                            <?= $totalNotificacoesEquipe ?>
                        </span>
                    <?php endif; ?>
                </button>
                

                <!-- MENU DROPDOWN DE NOTIFICAÇÕES -->
                <div id="dropdownNotificacoes" class="notificacao-dropdown">

                    <!-- Cabeçalho do Dropdown -->
                    <div class="notificacao-dropdown-header">
                        <strong>Notificações</strong>
                        <span class="notificacao-count-badge">
                            <?= $totalNotificacoesEquipe ?> pendência(s)
                        </span>
                    </div>

                    <!-- Lista de Atrasados -->
                    <div class="notificacao-lista">
                        <?php if (empty($notifications['lista']) && empty($notifications['avaliacoes']['lista'])): ?>
                            <p class="notificacao-vazio">
                                Você não tem notificações no momento.
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
                                <?php foreach (array_slice($notifications['avaliacoes']['lista'] ?? [], 0, 10) as $item): ?>
                                    <li>
                                        <div class="notificacao-item-nome">
                                            Novo comentário de <?= htmlspecialchars((string)$item['nome_aluno'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <div class="notificacao-item-info">
                                            <span><?= date('d/m/Y H:i', strtotime((string)$item['data_comentario'])) ?></span>
                                            <a href="../professor/avaliacoes.php?comentario=<?= (int)$item['id_avaliacao_comentario'] ?>#avaliacao-<?= (int)$item['id_avaliacao'] ?>">
                                                Abrir avaliação
                                            </a>
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
$notificacoes_aluno = $is_aluno && isset($notificacoes) && is_array($notificacoes)
    ? $notificacoes
    : [];
if ($is_aluno) {
    require_once __DIR__ . '/../../model/dao/AvaliacaoDAO.php';
    $graduacoesPrevistasHeader = (new AvaliacaoDAO())->listarGraduacoesPrevistasAluno(
        (int)$_SESSION['usuario']['id_usuario']
    );
    $idsNotificacoesExistentes = array_column($notificacoes_aluno, 'id');
    foreach ($graduacoesPrevistasHeader as $prevista) {
        $idNotificacaoGraduacao = 'graduacao-' . (int)$prevista['id_modalidade'] . '-' . $prevista['data_prevista'];
        if (in_array($idNotificacaoGraduacao, $idsNotificacoesExistentes, true)) {
            continue;
        }
        $notificacoes_aluno[] = [
            'id' => $idNotificacaoGraduacao,
            'acao' => 'graduacao',
            'icone' => '🥋',
            'titulo' => 'Graduação prevista',
            'mensagem' => 'Sua graduação em ' . $prevista['nome_modalidade'] . ' está prevista para ' . date('d/m/Y', strtotime((string)$prevista['data_prevista'])) . '.',
        ];
    }
}
$total_notif_header = count($notificacoes_aluno);
?>

<?php if ($is_aluno): ?>
    <div class="dropdown aluno-header-notifications">
        <button class="btn aluno-notification-button"
                type="button"
                id="dropdownNotifAlunoHeader"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                aria-expanded="false"
                aria-label="Abrir notificações">
            <i class="bi bi-bell-fill" aria-hidden="true"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                  data-notification-unread-badge
                  style="<?= $total_notif_header === 0 ? 'display: none;' : ''; ?>">
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
                                <?php elseif ($tipoNotificacao === 'graduacao'): ?>
                                    <a class="btn btn-sm btn-dark py-0 px-2"
                                       href="historico_graduacao.php#graduacoes-previstas"
                                       data-notification-action
                                       data-bs-toggle="tooltip"
                                       data-bs-placement="top"
                                       title="Ver previsão de graduação"
                                       aria-label="Ver previsão de graduação">
                                        <i class="bi bi-award" aria-hidden="true"></i>
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
        <!-- Perfil do utilizador -->
        <?php if (isset($_SESSION['usuario'])): ?>
            <details class="profile-avatar-picker" data-profile-avatar data-user-id="<?= $idUsuarioHeader; ?>">
                <summary class="profile-avatar-summary" aria-label="Perfil de <?= htmlspecialchars($nomeUsuarioHeader, ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="profile-avatar" data-profile-avatar-display aria-hidden="true">
                        <?php if ($avatarUsuarioUrl !== null): ?>
                            <img src="<?= htmlspecialchars($avatarUsuarioUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="">
                        <?php else: ?>
                            <span><?= htmlspecialchars($iniciaisUsuarioHeader, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="user-greeting">Olá, <strong><?= htmlspecialchars($nomeUsuarioHeader, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <i class="bi bi-chevron-down profile-avatar-chevron" aria-hidden="true"></i>
                </summary>
                <div class="profile-avatar-menu">
                    <strong class="profile-avatar-menu-title">Personalizar perfil</strong>
                    <?php if ($perfilUsuario === 4): ?>
                        <a class="profile-avatar-profile-link" href="../aluno/perfil.php">
                            <i class="bi bi-person-vcard" aria-hidden="true"></i>
                            Meu perfil
                        </a>
                    <?php endif; ?>
                    <?php if ($avatarFeedback !== null): ?>
                        <p class="profile-avatar-feedback" role="status">
                            <?= $avatarFeedback === 'sucesso'
                                ? 'Foto de perfil atualizada.'
                                : ($avatarFeedback === 'sucesso_remocao'
                                    ? 'Foto removida. Sua inicial voltou a ser exibida.'
                                    : 'Não foi possível atualizar. Envie JPG, PNG ou WebP de até 2 MB.'); ?>
                        </p>
                    <?php endif; ?>
                    <form action="../../controller/ProfileAvatarController.php" method="POST" enctype="multipart/form-data" class="profile-avatar-upload">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$_SESSION['avatar_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="return_to" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        <label for="profileAvatarFile">Escolher foto</label>
                        <input id="profileAvatarFile" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required>
                        <small>JPG, PNG ou WebP · até 2 MB</small>
                        <button type="submit" class="btn btn-sm btn-dark">Enviar foto</button>
                    </form>
                    <?php if ($avatarUsuarioUrl !== null): ?>
                        <form action="../../controller/ProfileAvatarController.php" method="POST" class="profile-avatar-remove">
                            <input type="hidden" name="acao" value="remover">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$_SESSION['avatar_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="return_to" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Remover foto e usar inicial</button>
                        </form>
                    <?php endif; ?>
                    <div class="profile-avatar-colors">
                        <span>Ou escolha uma cor para a inicial</span>
                        <div role="group" aria-label="Cor do avatar">
                            <button type="button" data-avatar-color="#2563eb" aria-label="Azul"></button>
                            <button type="button" data-avatar-color="#7c3aed" aria-label="Roxo"></button>
                            <button type="button" data-avatar-color="#059669" aria-label="Verde"></button>
                            <button type="button" data-avatar-color="#ea580c" aria-label="Laranja"></button>
                            <button type="button" data-avatar-color="#db2777" aria-label="Rosa"></button>
                            <button type="button" data-avatar-color="#475569" aria-label="Cinza"></button>
                        </div>
                    </div>
                </div>
            </details>
        <?php else: ?>
            <span class="user-greeting">Olá, <strong><?= htmlspecialchars($nomeUsuarioHeader, ENT_QUOTES, 'UTF-8'); ?></strong></span>
        <?php endif; ?>
        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline">Início</a>
        <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
    </div>
</header>

<!-- SCRIPT PARA ABRIR/FECHAR O DROPDOWN -->

<script src="../../assets/js/main.js?v=<?= filemtime(__DIR__ . '/../../assets/js/main.js'); ?>"></script>
<script>
    document.querySelectorAll('[data-profile-avatar]').forEach((avatarPicker) => {
        const userId = avatarPicker.dataset.userId;
        const avatar = avatarPicker.querySelector('[data-profile-avatar-display]');
        const colorKey = `dojify-avatar-color-${userId}`;
        const defaultColors = ['#2563eb', '#7c3aed', '#059669', '#ea580c', '#db2777', '#475569'];
        let savedColor = null;
        try {
            savedColor = localStorage.getItem(colorKey);
        } catch (error) {
            savedColor = null;
        }
        const selectedColor = defaultColors.includes(savedColor)
            ? savedColor
            : defaultColors[Number(userId) % defaultColors.length];
        avatar.style.setProperty('--profile-avatar-color', selectedColor);

        avatarPicker.querySelectorAll('[data-avatar-color]').forEach((colorButton) => {
            colorButton.addEventListener('click', () => {
                const color = colorButton.dataset.avatarColor;
                avatar.style.setProperty('--profile-avatar-color', color);
                try {
                    localStorage.setItem(colorKey, color);
                } catch (error) {
                    // Keep the selected color visible for the current page.
                }
                avatarPicker.querySelectorAll('[data-avatar-color]').forEach((button) => {
                    button.setAttribute('aria-pressed', String(button === colorButton));
                });
            });
            colorButton.setAttribute(
                'aria-pressed',
                String(colorButton.dataset.avatarColor === selectedColor)
            );
        });
    });
</script>
