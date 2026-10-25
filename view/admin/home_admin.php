<?php
// view/admin/home_admin.php

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 1) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/AcademiaDAO.php';
require_once __DIR__ . '/../../model/dao/UsuarioDAO.php';

$nomeAdmin = htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Suporte Técnico');

$totalAcademias = 0;
$dadosGrafico = [];

try {
    $academiaDAO = new AcademiaDAO();
    $totalAcademias = $academiaDAO->contarTotal();
    
    // Pega os dados reais agrupados por mês do banco
    $dadosGrafico = $academiaDAO->contarAcademiasPorMes();
} catch (Exception $e) {
    $dadosGrafico = [];
}

// Prepara os arrays para o JavaScript do Chart.js
$mesesLabels = [];
$totaisValores = [];

foreach ($dadosGrafico as $linha) {
    $partes = explode('-', $linha['mes_ano']);
    $ano = $partes[0];
    $mesNum = $partes[1] ?? '01';
    
    $mesesNomes = ['01'=>'Jan', '02'=>'Fev', '03'=>'Mar', '04'=>'Abr', '05'=>'Mai', '06'=>'Jun', '07'=>'Jul', '08'=>'Ago', '09'=>'Set', '10'=>'Out', '11'=>'Nov', '12'=>'Dez'];
    $nomeMes = $mesesNomes[$mesNum] ?? $mesNum;

    $mesesLabels[] = $nomeMes . '/' . $ano;
    $totaisValores[] = (int) $linha['total'];
}

// Fallback caso a tabela esteja vazia
if (empty($mesesLabels)) {
    $mesesLabels = ['Atual'];
    $totaisValores = [$totalAcademias];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Painel do Suporte Técnico</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS principal do Dojify -->
    <link rel="stylesheet" href="../../assets/css/estilo.css">

    <!-- CSS das dashboards e suporte -->
    <link rel="stylesheet" href="../../assets/css/gerente.css">
    <link rel="stylesheet" href="../../assets/css/suporte.css?v=5">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="gerente-page">

    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->
    <header class="navbar">
        <div class="navbar-brand">
            <a href="home_admin.php" class="logo-link">
                <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div>
                    <h1>Dojify</h1>
                </div>
            </a>
        </div>

        <div class="navbar-user">
            <!-- Dropdown Menu de Usuário/Suporte -->
            <div class="dropdown">
                <button 
                    class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2 p-0 border-0" 
                    type="button" 
                    id="userDropdown" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"
                >
                    <i class="bi bi-person-circle fs-5"></i>
                    <strong><?= $nomeAdmin ?></strong>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="../alterar_senha.php">
                            <i class="bi bi-key"></i> Alterar senha
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Botão de Sair -->
            <a 
                href="../../controller/LoginController.php?acao=logout" 
                class="btn btn-sm btn-danger header-logout-button" 
                aria-label="Sair" 
                title="Sair"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10 17l5-5-5-5M15 12H3"/>
                    <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>
                </svg>
            </a>
        </div>
    </header>


    <!-- =====================================================
         DASHBOARD
    ====================================================== -->
    <main class="gerente-dashboard gerente-home-layout">

        <!-- BOAS-VINDAS -->
        <header class="gerente-welcome gerente-home-welcome">
            <div>
                <h2>Olá, <?= $nomeAdmin ?>!</h2>
                <p>Gerencie as academias e seus respectivos gerentes no Dojify.</p> 
            </div>
        </header>

        <!-- INDICADOR ÚNICO COM GRÁFICO REAL -->
        <section class="gerente-kpis admin-kpis-top">
            <article class="gerente-kpi w-100">
                <div class="gerente-kpi-top">
                    <span class="gerente-kpi-label">Visão Geral da Plataforma</span>
                    <span class="gerente-kpi-icon">
                        <i class="bi bi-building"></i>
                    </span>
                </div>
                
                <div class="d-flex align-items-center justify-content-between gap-4 mt-2">
                    <div>
                        <strong class="gerente-kpi-value d-block"><?= $totalAcademias ?></strong>
                        <small class="gerente-kpi-note">Academias cadastradas no Dojify</small>
                    </div>

                    <!-- Mini gráfico de barras com dados do BD -->
                    <div style="width: 140px; height: 50px;">
                        <canvas id="graficoAcademiasMensais"></canvas>
                    </div>
                </div>
            </article>
        </section>

        <!-- GESTÃO DO SISTEMA -->
        <section class="gerente-section-box gerente-home-gestao admin-home-gestao">
            <div class="gerente-section-header">
                <div>
                    <h3 class="gerente-section-title">Gestão do sistema</h3>
                    <p class="gerente-section-subtitle">Gerencie as academias e os responsáveis cadastrados.</p>
                </div>
            </div>

            <div class="gerente-grid">
                <!-- CADASTRAR ACADEMIA -->
                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-building-add"></i>
                    </div>
                    <h3>Cadastrar academia</h3>
                    <p>Adicione uma nova academia ao sistema Dojify, registrando suas informações institucionais.</p>
                    <div class="gerente-card-action">
                        <a href="cadastrar_academia.php" class="btn gerente-btn">Cadastrar academia</a>
                    </div>
                </article>

                <!-- CADASTRAR GERENTE -->
                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <h3>Cadastrar gerente</h3>
                    <p>Cadastre um novo gerente e vincule-o à academia pela qual será responsável.</p>
                    <div class="gerente-card-action">
                        <a href="cadastrar_gerente.php" class="btn gerente-btn">Cadastrar gerente</a>
                    </div>
                </article>

                <!-- GERENCIAR ACADEMIAS E GERENTES -->
                <article class="gerente-card">
                    <div class="gerente-card-icon">
                        <i class="bi bi-building-gear"></i>
                    </div>
                    <h3>Academias e gerentes</h3>
                    <p>Consulte os cadastros existentes, pesquise academias e gerentes e realize as alterações necessárias.</p>
                    <div class="gerente-card-action">
                        <a href="listar_academias_gerentes.php" class="btn gerente-btn">Gerenciar cadastros</a>
                    </div>
                </article>
            </div>
        </section>

    </main>

    <!-- =====================================================
         RODAPÉ
    ====================================================== -->
    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para renderizar o Gráfico com Dados Reais do Banco -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('graficoAcademiasMensais');
            if (ctx) {
                const labelsMeses = <?= json_encode($mesesLabels); ?>;
                const dadosTotais = <?= json_encode($totaisValores); ?>;

                new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labelsMeses,
                        datasets: [{
                            data: dadosTotais,
                            backgroundColor: '#212529',
                            borderRadius: 2,
                            barThickness: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: true }
                        },
                        scales: {
                            x: { 
                                grid: { display: false },
                                ticks: { font: { size: 9 }, color: '#888' }
                            },
                            y: { 
                                display: false,
                                grid: { display: false } 
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>