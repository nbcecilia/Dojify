<?php
// view/professor/home_professor.php

session_start();

// Validação de acesso (Perfil 3 = Professor, ou perfil 2 = Gerente caso também lecione)
if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['perfil_id'], [2, 3])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$nomeProfessor = htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Professor');
$idAcademia  = $_SESSION['usuario']['id_academia'] ?? 0;
$idProfessor = $_SESSION['usuario']['id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Painel do Professor - Dojify</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Folhas de Estilo Dojify -->
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <link rel="stylesheet" href="../../assets/css/gerente.css">
</head>

<body class="gerente-page">

    <?php include '../includes/header.php'; ?>

    <main class="gerente-dashboard">

        <!-- Cabeçalho de Boas-Vindas -->
        <header class="gerente-welcome">
            <div>
                <h2>Olá, <?= $nomeProfessor ?>!</h2>
                <p>Consulte as suas turmas, efetue registos de frequência e acompanhe a evolução dos alunos.</p>
            </div>

            <span class="gerente-role">
                <i class="bi bi-person-workspace"></i>
                Professor
            </span>
        </header>

        <!-- INDICADORES (KPIS DO PROFESSOR) -->
        <section class="gerente-kpis" aria-label="Resumo do professor">

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Minhas Turmas</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-calendar3"></i>
                    </span>
                </div>
                <strong class="gerente-kpi-value">—</strong>
                <small class="gerente-kpi-note">Turmas sob a sua responsabilidade</small>
            </article>

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Alunos Atendidos</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-people"></i>
                    </span>
                </div>
                <strong class="gerente-kpi-value">—</strong>
                <small class="gerente-kpi-note">Total de alunos matriculados</small>
            </article>

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Avaliações Pendentes</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-clipboard2-check"></i>
                    </span>
                </div>
                <strong class="gerente-kpi-value">—</strong>
                <small class="gerente-kpi-note">Registos a atualizar este mês</small>
            </article>

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Aulas Hoje</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-clock-history"></i>
                    </span>
                </div>
                <strong class="gerente-kpi-value">—</strong>
                <small class="gerente-kpi-note">Sessões agendadas para hoje</small>
            </article>

        </section>

        <!-- ATIVIDADES PEDAGÓGICAS PRINCIPAIS -->
        <section class="gerente-section">
            <div class="gerente-section-header">
                <div>
                    <h3 class="gerente-section-title">Controle pedagógico</h3>
                    <p class="gerente-section-subtitle">
                        Módulos principais para a gestão das aulas e desempenho dos alunos.
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
                        Registe e consulte a frequência dos alunos nas aulas e acompanhe o histórico de assiduidade.
                    </p>
                    <div class="gerente-card-action">
                        <a href="listar_frequencia.php" class="btn gerente-btn">Aceder frequência</a>
                    </div>
                </article>

                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-clipboard2-check"></i>
                    </div>
                    <h3>Avaliações</h3>
                    <p>
                        Registe avaliações, pontos fortes, habilidades a melhorar e observações sobre os alunos.
                    </p>
                    <div class="gerente-card-action">
                        <a href="listar_avaliacao.php" class="btn gerente-btn">Aceder avaliações</a>
                    </div>
                </article>

                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <h3>Graduações</h3>
                    <p>
                        Acompanhe a evolução técnica, histórico de faixas e graus dos alunos por modalidade.
                    </p>
                    <div class="gerente-card-action">
                        <a href="listar_graduacao.php" class="btn gerente-btn">Aceder graduações</a>
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
                        Atalhos de acesso direto para as tarefas mais comuns.
                    </p>
                </div>
            </div>

            <div class="gerente-quick-grid">

                <a href="registrar_frequencia.php" class="gerente-quick-action">
                    <i class="bi bi-check2-square"></i>
                    <div>
                        <strong>Registar chamada</strong>
                        <span>Controlo rápido de presenças</span>
                    </div>
                </a>

                <a href="listar_frequencia.php" class="gerente-quick-action">
                    <i class="bi bi-calendar-check"></i>
                    <div>
                        <strong>Consultar presenças</strong>
                        <span>Histórico por turma</span>
                    </div>
                </a>

                <a href="listar_avaliacao.php" class="gerente-quick-action">
                    <i class="bi bi-clipboard2-plus"></i>
                    <div>
                        <strong>Nova avaliação</strong>
                        <span>Registar evolução</span>
                    </div>
                </a>

            </div>
        </section>

    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>