<?php
// view/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$notificacoes = ['total_atrasados' => 0, 'valor_atrasados' => 0, 'lista' => []];

// Se for um Gerente logado, busca as notificações financeiras
if (isset($_SESSION['usuario']) && (int)$_SESSION['usuario']['perfil_id'] === 2 && isset($_SESSION['id_academia'])) {
    require_once __DIR__ . '/../../model/dao/PagamentoDAO.php';
    $pagamentoDAO = new \PagamentoDAO();
    $notificacoes = $pagamentoDAO->obterNotificacoesFinanceiras((int)$_SESSION['id_academia']);
}
?>

<header class="navbar">
    <div class="navbar-brand">
        <a href="../gerente/home_gerente.php" class="logo-link" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
            <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo" style="height: 40px;">
            <div>
                <h1 style="margin: 0; font-size: 1.5rem; color: var(--text-primary);">Dojify</h1>
            </div>
        </a>
    </div>

    <div class="navbar-user" style="display: flex; align-items: center; gap: 20px;">
        
        <!-- ÍCONE DE NOTIFICAÇÕES -->
        <?php if (isset($_SESSION['usuario']) && (int)$_SESSION['usuario']['perfil_id'] === 2): ?>
            <div class="notificacao-container" style="position: relative;">
                <button type="button" id="btnNotificacao" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; position: relative; padding: 4px; display: flex; align-items: center;">
                    🔔
                    <?php if ($notificacoes['total_atrasados'] > 0): ?>
                        <span style="position: absolute; top: -2px; right: -4px; background-color: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 0.75rem; font-weight: bold; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                            <?= $notificacoes['total_atrasados'] ?>
                        </span>
                    <?php endif; ?>
                </button>

                <!-- MENU DROPDOWN DE NOTIFICAÇÕES -->
                <div id="dropdownNotificacoes" style="display: none; position: absolute; right: 0; top: 45px; width: 320px; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 2000; padding: 0; overflow: hidden;">
                    
                    <!-- Cabeçalho do Dropdown -->
                    <div style="display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc; padding: 12px 16px; border-bottom: 1px solid var(--border-color, #e5e7eb);">
                        <strong style="font-size: 0.95rem; color: var(--text-primary, #111827);">Notificações</strong>
                        <span style="font-size: 0.75rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 4px 8px; border-radius: 12px; font-weight: 600;">
                            <?= $notificacoes['total_atrasados'] ?> pendências
                        </span>
                    </div>

                    <!-- Lista de Atrasados -->
                    <div style="max-height: 250px; overflow-y: auto; padding: 8px 0;">
                        <?php if (empty($notificacoes['lista'])): ?>
                            <p style="font-size: 0.85rem; color: var(--text-muted, #6b7280); text-align: center; margin: 20px 0;">
                                Tudo em dia! Nenhuma pendência. 🎉
                            </p>
                        <?php else: ?>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach ($notificacoes['lista'] as $item): ?>
                                    <li style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary, #111827);">
                                            <?= htmlspecialchars($item['aluno_nome']) ?>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #ef4444; margin-top: 4px;">
                                            <span>Venceu: <?= date('d/m/Y', strtotime($item['data_vencimento'])) ?></span>
                                            <strong>R$ <?= number_format($item['valor'], 2, ',', '.') ?></strong>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Rodapé do Dropdown -->
                    <?php if (!empty($notificacoes['lista'])): ?>
                        <div style="padding: 12px; background-color: #f8fafc; border-top: 1px solid var(--border-color, #e5e7eb); text-align: center;">
                            <a href="../gerente/pagamentos.php" class="btn btn-success" style="width: 100%; font-size: 0.85rem; padding: 8px; margin: 0; text-decoration: none;">
                                Resolver Pendências
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Dados do Utilizador -->
        <span class="user-greeting">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Usuário') ?></strong></span>
        <a href="../gerente/home_gerente.php" class="btn btn-sm" style="background-color: var(--border-color); color: var(--text-primary) !important; text-decoration: none;">Início</a>
        <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger" style="text-decoration: none;">Sair</a>
    </div>
</header>

<!-- SCRIPT PARA ABRIR/FECHAR O DROPDOWN -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnNotificacao');
    const dropdown = document.getElementById('dropdownNotificacoes');

    if (btn && dropdown) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation(); // Evita que o clique feche imediatamente
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Clica fora do menu para fechar
        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && e.target !== btn) {
                dropdown.style.display = 'none';
            }
        });
    }
});
</script>