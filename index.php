<?php
// index.php (Página Principal / Landing Page do Dojify)
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dojify - Gestão Inteligente para Academias de Artes Marciais</title>
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

    <!-- Menu Fixo Superior -->
    <header class="navbar">
        <a href="index.php" class="navbar-brand">
            <img src="assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
            <h1>Dojify</h1>
        </a>

        <!-- Links e Botões para Desktop -->
        <nav class="nav-links">
            <a href="#recursos">Recursos</a>
            <a href="#sobre">Sobre</a>
            <a href="#contato">Contato</a>
            <div class="nav-auth-buttons">
                <a href="view/login.php" class="btn-login">Login</a>
                <a href="controller/UsuarioController.php?acao=novo_cadastro" class="btn-cadastrar">Cadastrar</a>
            </div>
        </nav>

        <!-- Botão Sanduíche para Mobile -->
        <button class="menu-toggle" id="menuToggle" aria-label="Abrir Menu">
            <span>Sobre</span>
            <span>Recursos</span>
            <span>Contato</span>
        </button>
    </header>

    <!-- Menu Dropdown Mobile -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="#recursos">Recursos</a>
        <a href="#sobre">Sobre</a>
        <a href="#contato">Contato</a>
        <div class="mobile-auth">
            <a href="view/login.php" class="btn-login-mobile">Login</a>
            <a href="controller/UsuarioController.php?acao=novo_cadastro" class="btn-cadastrar-mobile">Cadastrar</a>
        </div>
    </div>

    <!-- Conteúdo Principal / Hero -->
    <main class="main-content">
        <section class="hero">
            <div class="hero-text">
                <h2>A evolução na gestão da sua academia</h2>
                <p>Simplifique o controle de graduações, alunos, turmas e frequências com uma plataforma moderna, segura e focada na simplicidade do tatame.</p>
                <div class="hero-buttons">
                    <a href="controller/UsuarioController.php?acao=novo_cadastro" class="btn-primary">Começar Agora</a>
                    <a href="#recursos" class="btn-secondary">Ver Recursos</a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-card-preview">
                    <!-- Imagem do Painel Inteligente no Hero -->
                    <img src="assets/img/dashboard-preview.png" alt="Painel Inteligente Dojify" class="preview-img">
                    <span class="preview-badge">Painel Inteligente</span>
                </div>
            </div>
        </section>

        <!-- Seção de Recursos com Imagens nos Cards -->
        <section id="recursos" class="features-section">
            <h3>Tudo o que sua academia precisa!</h3>
            <div class="features-grid">
                
                <!-- Card 1: Alunos -->
                <div class="feature-card">
                    <div class="card-img-container">
                        <img src="assets/img/recurso-alunos.jpg" alt="Controle de Alunos" class="card-img">
                    </div>
                    <h4>Controle de Alunos</h4>
                    <p>Gerencie cadastros, faixas e evoluções de forma rápida e centralizada no tatame.</p>
                </div>

                <!-- Card 2: Graduações -->
                <div class="feature-card">
                    <div class="card-img-container">
                        <img src="assets/img/recurso-graduacoes.jpg" alt="Graduações e Prazos" class="card-img">
                    </div>
                    <h4>Graduações e Prazos</h4>
                    <p>Acompanhe exames, requisitos e histórico de graduação sem planilhas complexas.</p>
                </div>

                <!-- Card 3: Interface -->
                <div class="feature-card">
                    <div class="card-img-container">
                        <img src="assets/img/recurso-interface.png" alt="Interface Minimalista" class="card-img-logo">
                    </div>
                    <h4>Interface Minimalista</h4>
                    <p>Foco total na usabilidade, navegação fluida e design profissional em preto e branco.</p>
                </div>

            </div>
        </section>

        <!-- Seção Sobre -->
        <section id="sobre" class="section-block">
            <h3>Sobre o Dojify</h3>
            <p>O Dojify nasceu com uma missão clara: retirar a burocracia do caminho de professores e gestores de artes marciais. Sabemos que o tempo mais valioso de um sensei ou gerente é o dedicado ao tatame e ao desenvolvimento dos alunos, por isso criamos um ambiente digital limpo, intuitivo e extremamente direto ao ponto.</p>
        </section>

        <!-- Seção Contato -->
        <section id="contato" class="section-block">
            <h3>Contato e Suporte</h3>
            <p>Tem dúvidas sobre implantação, parcerias ou precisa de suporte técnico para a sua academia? Nossa equipe está pronta para atender você.</p>
            <p><strong>E-mail:</strong> suporte@dojify.com.br</p>
            <p><strong>Telefone:</strong> (00) 00000-0000</p>
        </section>
    </main>

    <!-- Rodapé -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

    <!-- Script do Menu Sanduíche Mobile -->
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const mobileMenu = document.getElementById('mobileMenu');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });
    </script>
</body>
</html>