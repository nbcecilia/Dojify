<?php
//  view/gerente/home_gerente.php

session_start();

if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Gerente - Dojify</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body style="background-color: var(--bg-body, #f8f9fa);">

    <?php include '../includes/header.php'; ?>

    <main class="container py-4">
        
        <h2 class="text-center mb-2">Painel do Gerente</h2>
        <p class="text-muted text-center mb-5">Painel de controlo e gestão da sua academia.</p>

      
        <div class="row g-4 justify-content-center mb-4">
            
            <!-- Cartão 1: Cadastrar Aluno -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm border p-3 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-2">Cadastrar Aluno</h3>
                            <p class="text-muted small mb-4">Registe um novo aluno no sistema.</p>
                        </div>
                        <a href="cadastrar_aluno.php" class="btn btn-success w-100">+ Cadastrar Aluno</a>
                    </div>
                </div>
            </div>

            <!-- Cartão 2: Cadastrar Professor -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm border p-3 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-2">Cadastrar Professor</h3>
                            <p class="text-muted small mb-4">Registe um novo professor na academia.</p>
                        </div>
                        <a href="cadastrar_professor.php" class="btn btn-info w-100 text-white">+ Cadastrar Professor</a>
                    </div>
                </div>
            </div>

            <!-- Cartão 3: Gerir Utilizadores -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm border p-3 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-2">Alunos & Professores</h3>
                            <p class="text-muted small mb-4">Gerencie todos os utilizadores.</p>
                        </div>
                        <a href="listar_usuarios.php" class="btn btn-warning w-100">Gerir Utilizadores</a>
                    </div>
                </div>
            </div>

            <!-- Cartão 4: Planos e Mensalidades -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm border p-3 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-2">Planos</h3>
                            <p class="text-muted small mb-4">Gerir planos e vigências dos alunos.</p>
                        </div>
                        <a href="listar_plano.php" class="btn btn-primary w-100">Gerir Planos</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEGUNDA LINHA -->
        <div class="row g-4 justify-content-center mb-4">
            
            <!-- Cartão 5: Modalidades -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border p-3 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-2">Modalidades</h3>
                            <p class="text-muted small mb-4">Configure as artes marciais oferecidas.</p>
                        </div>
                        <a href="listar_modalidade.php" class="btn btn-secondary w-100">Gerir Modalidades</a>
                    </div>
                </div>
            </div>

            <!-- Cartão 6: Turmas -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border p-3 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-2">Turmas</h3>
                            <p class="text-muted small mb-4">Crie turmas, horários e professores.</p>
                        </div>
                        <a href="listar_turma.php" class="btn btn-dark w-100">Gerir Turmas</a>
                    </div>
                </div>
            </div>

            <!-- Cartão 7: Financeiro -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border p-3 text-center">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h5 card-title mb-2">Financeiro</h3>
                            <p class="text-muted small mb-4">Controle de mensalidades e pagamentos.</p>
                        </div>
                        <a href="listar_pagamentos.php" class="btn btn-success w-100">Gerir Pagamentos</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>