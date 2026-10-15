<?php
//view/admin/editar_academia.php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 1) { 
    header('Location: ../login.php'); 
    exit; 
}

require_once '../../model/dao/AcademiaDAO.php';
$dao = new AcademiaDAO();
$id = (int)($_GET['id'] ?? 0);
$academia = $dao->buscarPorId($id);

if (!$academia) {
    header('Location: home_admin.php?erro=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Academia - Dojify</title>
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
                <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div>
                    <h1>Dojify</h1>
                </div>
            </a>
        </div>
        
        <div class="navbar-user">
            <span class="user-greeting">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></span>
            <a href="../alterar_senha.php" class="btn btn-sm btn-voltar">Alterar senha</a>
            <a href="home_admin.php" class="btn btn-sm btn-voltar">Voltar</a>
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
                    Erro ao atualizar a academia. Verifique os dados fornecidos.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h2 class="fs-4 fw-bold">Editar Dados da Academia</h2>
            </div>

            <form action="../../controller/AcademiaController.php" method="POST" data-cep-form autocomplete="off" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="id_academia" value="<?= htmlspecialchars($academia['id_academia']) ?>">
                
                <div class="row g-3">
                    <div class="col-12">
                        <label for="nome" class="form-label">Nome da Academia: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($academia['nome']) ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="documento" class="form-label">Documento (CNPJ ou CPF do responsável): <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="documento" name="documento" value="<?= htmlspecialchars($academia['documento']) ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="cep" class="form-label">CEP: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cep" name="cep" value="<?= htmlspecialchars($academia['cep'] ?? '') ?>" inputmode="numeric" autocomplete="postal-code" maxlength="9" pattern="[0-9]{5}-?[0-9]{3}" placeholder="00000-000" required>
                            <button type="button" class="btn btn-outline-secondary" data-cep-lookup>Buscar CEP</button>
                        </div>
                        <p id="cep-status" class="form-text text-muted mb-0" aria-live="polite"></p>
                    </div>

                    <div class="col-md-8">
                        <label for="endereco" class="form-label">Endereço: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="endereco" name="endereco" value="<?= htmlspecialchars($academia['endereco']) ?>" autocomplete="street-address" maxlength="100" required>
                    </div>

                    <div class="col-md-8">
                        <label for="cidade" class="form-label">Cidade: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cidade" name="cidade" value="<?= htmlspecialchars($academia['cidade'] ?? '') ?>" autocomplete="address-level2" maxlength="100" required>
                    </div>

                    <div class="col-md-4">
                        <label for="estado" class="form-label">Estado (UF): <span class="text-danger">*</span></label>
                        <select class="form-select" id="estado" name="estado" autocomplete="off" required>
                            <option value="">Selecione o estado...</option>
                            <?php 
                            $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                            $estadoAtual = $academia['estado'] ?? '';
                            foreach($ufs as $uf) {
                                $sel = ($estadoAtual === $uf) ? 'selected' : '';
                                echo "<option value=\"$uf\" $sel>$uf</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="telefone" class="form-label">Telefone Principal: <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($academia['telefone']) ?>" pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail de Contato: <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($academia['email']) ?>" required>
                    </div>

                    <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                        <a href="home_admin.php" class="btn btn-secondary px-4 m-0 w-auto">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 m-0 w-auto">
                            <i class="bi bi-check-circle me-1"></i> Salvar Alterações
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