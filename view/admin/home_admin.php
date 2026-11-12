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
    <link rel="stylesheet" href="../../assets/css/estilo.css?v=<?= filemtime(__DIR__ . '/../../assets/css/estilo.css'); ?>">

    <!-- CSS das dashboards e suporte -->
    <link rel="stylesheet" href="../../assets/css/gerente.css">
    <link rel="stylesheet" href="../../assets/css/suporte.css?v=5">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="gerente-page">

    <?php include __DIR__ . '/../includes/header.php'; ?>


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
                        <button type="button" class="btn gerente-btn" data-bs-toggle="modal" data-bs-target="#modalCadastrarAcademia">Cadastrar academia</button>
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
                        <button type="button" class="btn gerente-btn" data-bs-toggle="modal" data-bs-target="#modalCadastrarGerente">Cadastrar gerente</button>
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

    <div class="modal fade" id="modalCadastrarAcademia" tabindex="-1" aria-labelledby="tituloModalCadastrarAcademia" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5" id="tituloModalCadastrarAcademia">Cadastrar Academia</h2>
                        <p class="text-muted small mb-0">Cadastrar uma nova academia no sistema.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe class="edit-user-frame" title="Formulário para cadastrar academia"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCadastrarGerente" tabindex="-1" aria-labelledby="tituloModalCadastrarGerente" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5" id="tituloModalCadastrarGerente">Cadastrar Gerente</h2>
                        <p class="text-muted small mb-0">Preencha os campos abaixo para cadastrar um novo gerente.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe class="edit-user-frame" title="Formulário para cadastrar gerente"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         RODAPÉ
    ====================================================== -->
    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const listPath = new URL('listar_academias_gerentes.php', window.location.href).pathname;

            function configurarModalCadastro(modalId, formPath, sucesso, mensagem) {
                const modalElement = document.getElementById(modalId);
                const frame = modalElement.querySelector('iframe');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

                modalElement.addEventListener('show.bs.modal', function () {
                    frame.src = formPath + '?modal=1';
                });

                frame.addEventListener('load', function () {
                    const frameUrl = new URL(frame.contentWindow.location.href);
                    if (frameUrl.pathname !== listPath) {
                        return;
                    }

                    if (frameUrl.searchParams.get('sucesso') === sucesso) {
                        window.alert(mensagem);
                        modal.hide();
                        window.location.reload();
                        return;
                    }

                    modal.hide();
                });
            }

            configurarModalCadastro(
                'modalCadastrarAcademia',
                'cadastrar_academia.php',
                'academia_cadastrada',
                'Academia cadastrada com sucesso!'
            );
            configurarModalCadastro(
                'modalCadastrarGerente',
                'cadastrar_gerente.php',
                'gerente_cadastrado',
                'Gerente cadastrado com sucesso!'
            );
        });
    </script>

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
                                ticks: { display: false }
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