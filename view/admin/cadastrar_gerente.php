<?php
// view/admin/cadastrar_gerente.php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 1) { 
    header('Location: ../login.php'); 
    exit; 
}

require_once '../../model/dao/AcademiaDAO.php';
$academiaDAO = new AcademiaDAO();
$academias = $academiaDAO->listarTodas();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Gerente - Dojify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilo Global Dojify -->
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar-brand">
            <a href="home_admin.php" class="logo-link">
                <!-- Imagem do Logótipo à esquerda -->
                <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div>
                    <h1>Dojify</h1>
                </div>
            </a>
        </div>
        
        <div class="navbar-user">
            <span class="user-greeting">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></span>
            <a href="home_admin.php" class="btn btn-sm btn-voltar">⬅ Voltar</a>
            <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="home_admin.php" class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar ao Painel
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Erro ao cadastrar gerente. Verifique se o CPF ou E-mail já estão em uso.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h2 class="fs-4 fw-bold">Cadastrar Gerente</h2>
            </div>

            <form action="../../controller/UsuarioController.php" method="POST" autocomplete="off" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <input type="hidden" name="acao" value="cadastrar_gerente">
                
                <div class="row g-3">
                    <div class="col-12">
                        <label for="id_academia" class="form-label">Selecione a Academia: <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_academia" name="id_academia" required>
                            <option value="">-- Selecione uma Academia --</option>
                            <?php foreach ($academias as $ac): ?>
                                <option value="<?= $ac['id_academia'] ?>"><?= htmlspecialchars($ac['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="nome" class="form-label">Nome Completo do Gerente: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="col-md-6">
                        <label for="cpf" class="form-label">CPF (apenas números): <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefone" class="form-label">Telefone: <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="telefone" name="telefone" pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="data_nascimento" class="form-label">Data de Nascimento: <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
                    </div>

                    <div class="col-md-6">
                        <label for="sexo" class="form-label">Sexo: <span class="text-danger">*</span></label>
                        <select class="form-select" id="sexo" name="sexo" required>
                            <option value="">Selecione...</option>
                            <option value="Feminino">Feminino</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Outro">Outro</option>
                            <option value="Prefiro não informar">Prefiro não informar</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="email" class="form-label">E-mail de Acesso: <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="col-12">
                        <label for="senha" class="form-label">Senha de Acesso: <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="senha" name="senha" autocomplete="new-password" required>
                    </div>

                    <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                        <a href="home_admin.php" class="btn btn-secondary px-4 m-0 w-auto">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 m-0 w-auto">
                            <i class="bi bi-check-circle me-1"></i> Cadastrar Gerente
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
    <script src="../../assets/js/cpf.js?v=<?= filemtime(__DIR__ . '/../../assets/js/cpf.js'); ?>"></script>
</body>
</html>