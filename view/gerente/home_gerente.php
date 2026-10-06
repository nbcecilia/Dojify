<?php
// view/gerente/home_gerente.php

session_start();

if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

// Importações necessárias para buscar os dados no banco
require_once __DIR__ . '/../../model/dao/Conexao.php';
require_once __DIR__ . '/../../model/dao/UsuarioDAO.php';

$nomeGerente = htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Gerente');
$idAcademia  = $_SESSION['usuario']['id_academia'] ?? 0;

// Busca os indicadores (KPIs) da academia
$usuarioDAO = new UsuarioDAO();
$kpis = $usuarioDAO->buscarIndicadoresGerente($idAcademia);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Painel do Gerente - Dojify</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <link rel="stylesheet" href="../../assets/css/gerente.css">
</head>

<body class="gerente-page">

    <?php include '../includes/header.php'; ?>

    <main class="gerente-dashboard">

        <header class="gerente-welcome">
            <div>
                <h2>Olá, <?= $nomeGerente ?>!</h2>
                <p>Tenha uma visão geral da academia e acesse rapidamente suas principais funções.</p>
            </div>

            <span class="gerente-role">
                <i class="bi bi-shield-check"></i>
                Gerente
            </span>
        </header>


        <!-- INDICADORES -->
        <section class="gerente-kpis" aria-label="Resumo da academia">

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Alunos ativos</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-people"></i>
                    </span>
                </div>

                <strong class="gerente-kpi-value"><?= $kpis['alunos_ativos'] ?></strong>
                <small class="gerente-kpi-note">Total de alunos com matrícula ativa</small>
            </article>

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Professores</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-person-workspace"></i>
                    </span>
                </div>

                <strong class="gerente-kpi-value"><?= $kpis['professores'] ?></strong>
                <small class="gerente-kpi-note">Professores cadastrados</small>
            </article>

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Pagamentos pendentes</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-cash-stack"></i>
                    </span>
                </div>

                <strong class="gerente-kpi-value"><?= $kpis['pagamentos_pendentes'] ?></strong>
                <small class="gerente-kpi-note">Mensalidades pendentes ou em atraso</small>
            </article>

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Turmas</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-calendar3"></i>
                    </span>
                </div>

                <strong class="gerente-kpi-value"><?= $kpis['turmas'] ?></strong>
                <small class="gerente-kpi-note">Turmas cadastradas na academia</small>
            </article>

        </section>


        <!-- FUNÇÕES QUE O GERENTE HERDA DO PROFESSOR -->
        <section href="../professor/listar_frequencia.php" class="gerente-section">

            <div class="gerente-section-header">
                <div>
                    <h3 class="gerente-section-title">Atividades pedagógicas</h3>
                    <p class="gerente-section-subtitle">
                        Funções que o gerente também possui como professor.
                    </p>
                </div>
            </div>

            <div class="gerente-grid">

                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-check2-square"></i>
                    </div>

                    <h3>Frequência</h3>

                    <p>
                        Registre e consulte a frequência dos alunos e acompanhe
                        o histórico de assiduidade.
                    </p>

                    <div class="gerente-card-action">
                        <a href="../professor/listar_frequencia.php" class="btn gerente-btn"> Acessar frequência </a>
                    </div>
                </article>


                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-clipboard2-check"></i>
                    </div>

                    <h3>Avaliações</h3>

                    <p>
                        Registre avaliações, habilidades a melhorar e observações
                        sobre o desempenho dos alunos.
                    </p>

                    <div class="gerente-card-action">
                        <a href="../professor/listar_avaliacao.php" class="btn gerente-btn">Acessar avaliações</a>
                    </div>
                </article>


                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-award"></i>
                    </div>

                    <h3>Graduações</h3>

                    <p>
                        Registre e acompanhe a evolução técnica, faixas e graus
                        dos alunos por modalidade.
                    </p>

                    <div class="gerente-card-action">
                        <a href="listar_graduacao.php"
                           class="btn gerente-btn">
                            Acessar graduações
                        </a>
                    </div>
                </article>

            </div>
        </section>


        <!-- ADMINISTRAÇÃO -->
        <section class="gerente-section">

            <div class="gerente-section-header">
                <div>
                    <h3 class="gerente-section-title">Gestão da academia</h3>
                    <p class="gerente-section-subtitle">
                        Cadastros e configurações administrativas.
                    </p>
                </div>
            </div>

            <div class="gerente-grid">

                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>

                    <h3>Alunos e professores</h3>

                    <p>
                        Cadastre novos alunos e professores no sistema.
                    </p>

                    <div class="gerente-card-action">
                        <a href="cadastrar_aluno_prof.php"
                           class="btn gerente-btn">
                            Cadastrar
                        </a>
                    </div>
                </article>


                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-people"></i>
                    </div>

                    <h3>Usuários</h3>

                    <p>
                        Consulte e gerencie os usuários cadastrados na academia.
                    </p>

                    <div class="gerente-card-action">
                        <a href="listar_usuarios.php"
                           class="btn gerente-btn">
                            Gerenciar usuários
                        </a>
                    </div>
                </article>


                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>

                    <h3>Modalidades</h3>

                    <p>
                        Cadastre e mantenha as modalidades oferecidas pela academia.
                    </p>

                    <div class="gerente-card-action">
                        <a href="listar_modalidade.php"
                           class="btn gerente-btn">
                            Gerenciar modalidades
                        </a>
                    </div>
                </article>


                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-collection"></i>
                    </div>

                    <h3>Turmas e horários</h3>

                    <p>
                        Organize turmas, professores, horários e atividades.
                    </p>

                    <div class="gerente-card-action">
                        <a href="listar_turma.php"
                           class="btn gerente-btn">
                            Gerenciar turmas
                        </a>
                    </div>
                </article>


                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-card-list"></i>
                    </div>

                    <h3>Planos de matrícula</h3>

                    <p>
                        Cadastre planos, valores e acompanhe os vínculos dos alunos.
                    </p>

                    <div class="gerente-card-action">
                        <a href="listar_plano.php"
                           class="btn gerente-btn">
                            Gerenciar planos
                        </a>
                    </div>
                </article>


                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>

                    <h3>Financeiro</h3>

                    <p>
                        Registre pagamentos e acompanhe situações de inadimplência.
                    </p>

                    <div class="gerente-card-action">
                        <a href="listar_pagamentos.php"
                           class="btn gerente-btn">
                            Acessar financeiro
                        </a>
                    </div>
                </article>

            </div>
        </section>


        <!-- AÇÕES RÁPIDAS -->
        <section class="gerente-section">

            <div class="gerente-section-header">
                <div>
                    <h3 class="gerente-section-title">Ações rápidas</h3>
                    <p class="gerente-section-subtitle">
                        Atalhos para as tarefas mais utilizadas.
                    </p>
                </div>
            </div>

            <div class="gerente-quick-grid">

                <a href="cadastrar_aluno_prof.php" class="gerente-quick-action">
                    <i class="bi bi-person-plus"></i>
                    <div>
                        <strong>Novo aluno/professor</strong>
                        <span>Cadastrar usuário</span>
                    </div>
                </a>

                <a href="../professor/registrar_frequencia.php" class="gerente-quick-action">
                    <i class="bi bi-check2-square"></i>
                    <div>
                        <strong>Registrar frequência</strong>
                        <span>Controle de presença</span>
                    </div>
                </a>

                <a href="listar_pagamentos.php" class="gerente-quick-action">
                    <i class="bi bi-cash-stack"></i>
                    <div>
                        <strong>Ver pagamentos</strong>
                        <span>Controle financeiro</span>
                    </div>
                </a>

            </div>
        </section>


        <!-- RESUMO FINANCEIRO -->
        <section class="gerente-section">

            <div class="gerente-section-header">
                <div>
                    <h3 class="gerente-section-title">Resumo financeiro</h3>
                    <p class="gerente-section-subtitle">
                        Área preparada para receber os dados reais do banco.
                    </p>
                </div>
            </div>

            <div class="gerente-finance-box">

                <div class="gerente-panel">
                    <h4 class="gerente-panel-title">Situação das mensalidades</h4>

                    <div class="gerente-status-row">
                        <span>Pagamentos realizados</span>
                        <span class="gerente-status-value">—</span>
                    </div>

                    <div class="gerente-status-row">
                        <span>Pagamentos pendentes</span>
                        <span class="gerente-status-value"><?= $kpis['pagamentos_pendentes'] ?></span>
                    </div>

                    <div class="gerente-status-row">
                        <span>Pagamentos em atraso</span>
                        <span class="gerente-status-value">—</span>
                    </div>
                </div>

                <div class="gerente-panel">
                    <h4 class="gerente-panel-title">Relatórios</h4>

                    <p class="gerente-section-subtitle mb-3">
                        Consulte os dados consolidados da academia.
                    </p>

                    <a href="relatorio_financeiro.php" class="btn gerente-btn w-100">
                        <i class="bi bi-bar-chart me-2"></i>
                        Emitir relatórios
                    </a>
                </div>

            </div>
        </section>

    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>