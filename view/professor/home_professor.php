<?php
// view/professor/home_professor.php

session_start();

// Validação de acesso (Perfil 3 = Professor, ou perfil 2 = Gerente caso também lecione)
if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['perfil_id'], [2, 3])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/Conexao.php';

$nomeProfessor = htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Professor');
$idAcademia  = $_SESSION['usuario']['id_academia'] ?? 0;
$idProfessor = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']['id_usuario'] ?? 0;

// Variáveis para os KPIs
$totalTurmas = 0;
$totalAlunos = 0;
$totalAvaliacoes = 0;
$aulasHoje = 0;

try {
    $pdo = Conexao::getConexao();

    // 1. Minhas Turmas (total de turmas do professor)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM turma WHERE id_usuario_professor = ? AND status = 'ATIVA'");
    $stmt->execute([$idProfessor]);
    $totalTurmas = (int)$stmt->fetchColumn();

    // 2. Alunos Atendidos (total de alunos únicos agendados nas turmas deste professor)
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT a.id_usuario_aluno) 
                           FROM agendamento a 
                           JOIN turma t ON t.id_turma = a.id_turma 
                           WHERE t.id_usuario_professor = ? AND a.status = 'CONFIRMADO'");
    $stmt->execute([$idProfessor]);
    $totalAlunos = (int)$stmt->fetchColumn();

    // 3. Avaliações (total de avaliações registadas pelo professor)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM avaliacao WHERE id_usuario_professor = ?");
    $stmt->execute([$idProfessor]);
    $totalAvaliacoes = (int)$stmt->fetchColumn();

    // 4. Aulas Hoje (agendamentos para a data atual nas turmas do professor)
    $dataHoje = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) 
                           FROM agendamento a 
                           JOIN turma t ON t.id_turma = a.id_turma 
                           WHERE t.id_usuario_professor = ? 
                           AND DATE(a.data_agendamento) = ?");
    $stmt->execute([$idProfessor, $dataHoje]);
    $aulasHoje = (int)$stmt->fetchColumn();

} catch (Exception $e) {
    // Em caso de exceção na base de dados, mantém os contadores a 0
}
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
    <link rel="stylesheet" href="../../assets/css/gerente.css?v=2">
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
                <strong class="gerente-kpi-value"><?= $totalTurmas ?></strong>
                <small class="gerente-kpi-note">Turmas sob a sua responsabilidade</small>
            </article>

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Alunos Atendidos</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-people"></i>
                    </span>
                </div>
                <strong class="gerente-kpi-value"><?= $totalAlunos ?></strong>
                <small class="gerente-kpi-note">Total de alunos matriculados</small>
            </article>

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Avaliações Pendentes</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-clipboard2-check"></i>
                    </span>
                </div>
                <strong class="gerente-kpi-value"><?= $totalAvaliacoes ?></strong>
                <small class="gerente-kpi-note">Registos a atualizar este mês</small>
            </article>

            <article class="gerente-kpi">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Aulas Hoje</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-clock-history"></i>
                    </span>
                </div>
                <strong class="gerente-kpi-value"><?= $aulasHoje ?></strong>
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
                    <h3>Registar Chamada</h3>
                    <p>
                        Efetue o controle de presenças e registe a chamada das turmas de forma rápida.
                    </p>
                    <div class="gerente-card-action">
                        <a href="registrar_frequencia.php" class="btn gerente-btn">Registar chamada</a>
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
                        <a href="listar_avaliacao.php" class="btn gerente-btn">Visualizar avaliações</a>
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
                        <a href="listar_graduacao.php" class="btn gerente-btn">Visualizar graduações</a>
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
                <a href="listar_frequencia.php" class="gerente-quick-action">
                    <i class="bi bi-calendar-check"></i>
                    <div>
                        <strong>Consultar presenças</strong>
                        <span>Histórico por turma</span>
                    </div>
                </a>
             
                <a href="cadastrar_avaliacao.php" class="gerente-quick-action">
                    <i class="bi bi-clipboard2-plus"></i>
                    <div>
                        <strong>Nova avaliação</strong>
                        <span>Registar evolução técnica</span>
                    </div>
                </a>

                <a href="cadastrar_graduacao.php" class="gerente-quick-action">
                    <i class="bi bi-check2-square"></i>
                    <div>
                        <strong>Cadastrar Graduação</strong>
                        <span>Registrar graduação do aluno</span>
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