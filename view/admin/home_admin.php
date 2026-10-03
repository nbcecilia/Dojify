<?php
// view/admin/home_admin.php

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 1) {
    header('Location: ../login.php');
    exit;
}

$nomeAdmin = htmlspecialchars($_SESSION['usuario']['nome']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - Dojify</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS principal do Dojify -->
    <link rel="stylesheet" href="../../assets/css/estilo.css">

    <!-- CSS das dashboards -->
    <link rel="stylesheet" href="../../assets/css/gerente.css">
</head>

<body class="gerente-page">

    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <header class="navbar">

        <div class="navbar-brand">

            <a href="home_admin.php" class="logo-link">

                <img
                    src="../../assets/img/Dojify_original2.png"
                    alt="Dojify Logo"
                    class="navbar-logo"
                >

                <div>
                    <h1>Dojify</h1>
                </div>

            </a>

        </div>


        <div class="navbar-user">

            <span class="user-greeting">
                Logado como:
                <strong><?= $nomeAdmin ?></strong>
            </span>

            <a
                href="../../controller/LoginController.php?acao=logout"
                class="btn btn-sm btn-danger"
            >
                Sair
            </a>

        </div>

    </header>


    <!-- =====================================================
         DASHBOARD
    ====================================================== -->

    <main class="gerente-dashboard">


        <!-- =================================================
             BOAS-VINDAS
        ================================================== -->

        <header class="gerente-welcome">

            <div>
                <h2>
                    Olá, <?= $nomeAdmin ?>!
                </h2>
                <p>
                    Gerencie as academias e seus respectivos gerentes no Dojify.
                </p> 

            </div>


            <span class="gerente-role">

                <i class="bi bi-shield-lock"></i>

                Suporte Técnico

            </span>

        </header>



        <!-- =================================================
             INDICADORES
        ================================================== -->

        <section class="gerente-kpis">


            <!-- Academias -->

            <article class="gerente-kpi">

                <div class="gerente-kpi-top">

                    <span class="gerente-kpi-label">
                        Academias
                    </span>

                    <span class="gerente-kpi-icon">

                        <i class="bi bi-building"></i>

                    </span>

                </div>


                <strong class="gerente-kpi-value">
                    —
                </strong>


                <small class="gerente-kpi-note">
                    Academias cadastradas no Dojify
                </small>

            </article>



            <!-- Gerentes -->

            <article class="gerente-kpi">

                <div class="gerente-kpi-top">

                    <span class="gerente-kpi-label">
                        Gerentes
                    </span>

                    <span class="gerente-kpi-icon">

                        <i class="bi bi-person-badge"></i>

                    </span>

                </div>


                <strong class="gerente-kpi-value">
                    —
                </strong>


                <small class="gerente-kpi-note">
                    Gerentes cadastrados no sistema
                </small>

            </article>



            <!-- Sistema -->

            <article class="gerente-kpi">

                <div class="gerente-kpi-top">

                    <span class="gerente-kpi-label">
                        Sistema
                    </span>

                    <span class="gerente-kpi-icon">

                        <i class="bi bi-check-circle"></i>

                    </span>

                </div>


                <strong class="gerente-kpi-value">
                    Ativo
                </strong>


                <small class="gerente-kpi-note">
                    Sistema Dojify em funcionamento
                </small>

            </article>

        </section>



        <!-- =================================================
             GESTÃO DO SISTEMA
        ================================================== -->

        <section class="gerente-section">


            <div class="gerente-section-header">

                <div>

                    <h3 class="gerente-section-title">
                        Gestão do sistema
                    </h3>

                    <p class="gerente-section-subtitle">
                        Gerencie as academias e os responsáveis cadastrados.
                    </p>

                </div>

            </div>



            <div class="gerente-grid">


                <!-- =================================================
                     CADASTRAR ACADEMIA
                ================================================== -->

                <article class="gerente-card">

                    <div class="gerente-card-icon">

                        <i class="bi bi-building-add"></i>

                    </div>


                    <h3>
                        Cadastrar academia
                    </h3>


                    <p>
                        Adicione uma nova academia ao sistema Dojify,
                        registrando suas informações institucionais.
                    </p>


                    <div class="gerente-card-action">

                        <a
                            href="cadastrar_academia.php"
                            class="btn gerente-btn"
                        >
                            Cadastrar academia
                        </a>

                    </div>

                </article>



                <!-- =================================================
                     CADASTRAR GERENTE
                ================================================== -->

                <article class="gerente-card">

                    <div class="gerente-card-icon">

                        <i class="bi bi-person-plus"></i>

                    </div>


                    <h3>
                        Cadastrar gerente
                    </h3>


                    <p>
                        Cadastre um novo gerente e vincule-o à academia
                        pela qual será responsável.
                    </p>


                    <div class="gerente-card-action">

                        <a
                            href="cadastrar_gerente.php"
                            class="btn gerente-btn"
                        >
                            Cadastrar gerente
                        </a>

                    </div>

                </article>



                <!-- =================================================
                     GERENCIAR ACADEMIAS E GERENTES
                ================================================== -->

                <article class="gerente-card">

                    <div class="gerente-card-icon">

                        <i class="bi bi-building-gear"></i>

                    </div>


                    <h3>
                        Academias e gerentes
                    </h3>


                    <p>
                        Consulte os cadastros existentes, pesquise
                        academias e gerentes e realize as alterações
                        necessárias.
                    </p>


                    <div class="gerente-card-action">

                        <a
                            href="listar_academias_gerentes.php"
                            class="btn gerente-btn"
                        >
                            Gerenciar cadastros
                        </a>

                    </div>

                </article>


            </div>

        </section>



        <!-- =================================================
             VISÃO GERAL
        ================================================== -->

        <section class="gerente-section">


            <div class="gerente-section-header">

                <div>

                    <h3 class="gerente-section-title">
                        Visão geral
                    </h3>

                    <p class="gerente-section-subtitle">
                        Resumo da administração do sistema.
                    </p>

                </div>

            </div>



            <div class="gerente-finance-box">


                <!-- Resumo -->

                <div class="gerente-panel">

                    <h4 class="gerente-panel-title">
                        Administração do Dojify
                    </h4>


                    <div class="gerente-status-row">

                        <span>
                            Academias cadastradas
                        </span>

                        <span class="gerente-status-value">
                            —
                        </span>

                    </div>


                    <div class="gerente-status-row">

                        <span>
                            Gerentes cadastrados
                        </span>

                        <span class="gerente-status-value">
                            —
                        </span>

                    </div>


                    <div class="gerente-status-row">

                        <span>
                            Status do sistema
                        </span>

                        <span class="gerente-status-value">
                            Ativo
                        </span>

                    </div>

                </div>



                <!-- Informações -->

                <div class="gerente-panel">

                    <h4 class="gerente-panel-title">
                        Área administrativa
                    </h4>


                    <p class="gerente-section-subtitle mb-0">

                        O administrador é responsável pelo gerenciamento
                        das academias cadastradas na plataforma e dos
                        respectivos gerentes.

                    </p>

                </div>


            </div>

        </section>


    </main>



    <!-- =====================================================
         RODAPÉ
    ====================================================== -->

    <footer class="footer">

        <p>
            &copy; <?= date('Y'); ?>
            Dojify. Todos os direitos reservados.
        </p>

    </footer>



    <!-- Bootstrap JS -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>

</body>

</html>