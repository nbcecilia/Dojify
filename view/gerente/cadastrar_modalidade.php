<?php
// view/gerente/cadastrar_modalidade.php

session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar Modalidade - Dojify</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <!-- Link Voltar estilizado como botão no topo -->
                <a href="listar_modalidade.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="text-center mb-4">
                <h2 class="fs-4 fw-bold">Cadastrar Modalidade</h2>
            </div>

            <form action="../../controller/ModalidadeController.php" method="POST" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                
                <!-- Seu input oculto exato -->
                <input type="hidden" name="acao" value="cadastrar">

                <div class="row g-3">
                    
                    <!-- Seu campo de Nome -->
                    <div class="col-12">
                        <label for="nome" class="form-label">Nome da modalidade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" maxlength="100" required>
                    </div>

                    <!-- Seu campo de Descrição -->
                    <div class="col-12">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="5"></textarea>
                    </div>

                    <!-- Botões de Ação lado a lado e centralizados -->
                    <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                        <a href="listar_modalidade.php" class="btn btn-secondary px-4 m-0 w-auto">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-success px-4 m-0 w-auto">
                            <i class="bi bi-check-circle me-1"></i> Cadastrar
                        </button>
                    </div>

                </div>
            </form>
        </section>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>