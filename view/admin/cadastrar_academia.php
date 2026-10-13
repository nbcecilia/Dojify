<?php
// view/admin/cadastrar_academia.php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 1) { 
    header('Location: ../login.php'); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Academia - Dojify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilo Global Dojify -->
    <link rel="stylesheet" href="../../assets/css/estilo.css?v=<?= filemtime(__DIR__ . '/../../assets/css/estilo.css'); ?>">
</head>
<body>
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
            <span class="user-greeting">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></span>
            <a href="home_admin.php" class="btn btn-sm btn-voltar">Voltar</a>
            <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="listar_academias_gerentes.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Gestão de Academias & Gerentes
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Erro ao cadastrar. Verifique os dados fornecidos ou se o documento já está cadastrado.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h2 class="fs-4 fw-bold">Cadastrar Nova Academia</h2>
                <p>Cadastrar uma nova academia no sistema.</p>
            </div>

            <form action="../../controller/AcademiaController.php" method="POST" data-cep-form autocomplete="off" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <input type="hidden" name="acao" value="cadastrar">
                
                <div class="row g-3">
                    <div class="col-12">
                        <label for="nome" class="form-label">Nome da Academia: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="col-12">
                        <label for="documento" class="form-label">Documento (CNPJ ou CPF do responsável): <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="documento" name="documento" placeholder="Apenas números" required>
                    </div>

                    <div class="col-md-4">
                        <label for="cep" class="form-label">CEP: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cep" name="cep" inputmode="numeric" autocomplete="postal-code" maxlength="9" pattern="[0-9]{5}-?[0-9]{3}" placeholder="00000-000" required>
                            <button type="button" class="btn btn-outline-secondary" data-cep-lookup>Buscar CEP</button>
                        </div>
                        <p id="cep-status" class="form-text text-muted mb-0" aria-live="polite"></p>
                    </div>

                    <div class="col-md-8">
                        <label for="endereco" class="form-label">Endereço: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="endereco" name="endereco" autocomplete="street-address" maxlength="100" required>
                    </div>

                    <div class="col-md-8">
                        <label for="cidade" class="form-label">Cidade: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cidade" name="cidade" autocomplete="address-level2" maxlength="100" required>
                    </div>

                    <div class="col-md-4">
                        <label for="estado" class="form-label">Estado (UF): <span class="text-danger">*</span></label>
                        <select class="form-select" id="estado" name="estado" autocomplete="off" required>
                            <option value="" selected>Selecione o estado...</option>
                            <option value="AC">AC</option><option value="AL">AL</option>
                            <option value="AP">AP</option><option value="AM">AM</option>
                            <option value="BA">BA</option><option value="CE">CE</option>
                            <option value="DF">DF</option><option value="ES">ES</option>
                            <option value="GO">GO</option><option value="MA">MA</option>
                            <option value="MT">MT</option><option value="MS">MS</option>
                            <option value="MG">MG</option><option value="PA">PA</option>
                            <option value="PB">PB</option><option value="PR">PR</option>
                            <option value="PE">PE</option><option value="PI">PI</option>
                            <option value="RJ">RJ</option><option value="RN">RN</option>
                            <option value="RS">RS</option><option value="RO">RO</option>
                            <option value="RR">RR</option><option value="SC">SC</option>
                            <option value="SP">SP</option><option value="SE">SE</option>
                            <option value="TO">TO</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="telefone" class="form-label">Telefone: <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="telefone" name="telefone" pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail de Contato: <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                        <a href="home_admin.php" class="btn btn-secondary px-4 m-0 w-auto">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 m-0 w-auto">
                            <i class="bi bi-check-circle me-1"></i> Cadastrar Academia
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/cep.js" defer></script>
</body>
</html>