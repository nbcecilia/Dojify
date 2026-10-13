<?php
//view/admin/editar_gerente.php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 1) { 
    header('Location: ../login.php'); 
    exit; 
}

require_once '../../model/dao/UsuarioDAO.php';
$dao = new UsuarioDAO();
$id = (int)($_GET['id'] ?? 0);
$gerente = $dao->buscarPorId($id);

if (!$gerente || $gerente['perfil_id'] != 2) {
    header('Location: home_admin.php?erro=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Gerente - Dojify</title>
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
                    Erro ao atualizar o gerente. Verifique os dados fornecidos.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h2 class="fs-4 fw-bold">Editar Dados do Gerente</h2>
            </div>

            <form action="../../controller/UsuarioController.php" method="POST" data-cep-form autocomplete="off" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($gerente['id_usuario']) ?>">
                <input type="hidden" name="perfil_id" value="2">
                
                <div class="row g-3">
                    <div class="col-12">
                        <label for="nome" class="form-label">Nome Completo: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($gerente['nome']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="cpf" class="form-label">CPF: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($gerente['cpf']) ?>" placeholder="000.000.000-00" maxlength="14" required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefone" class="form-label">Telefone: <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($gerente['telefone']) ?>" pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="data_nascimento" class="form-label">Data de Nascimento: <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($gerente['data_nascimento']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status: <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="ATIVO" <?= $gerente['status'] === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                            <option value="INATIVO" <?= $gerente['status'] === 'INATIVO' ? 'selected' : '' ?>>INATIVO</option>
                            <option value="SUSPENSO" <?= $gerente['status'] === 'SUSPENSO' ? 'selected' : '' ?>>SUSPENSO</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="cep" class="form-label">CEP:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cep" name="cep" value="<?= htmlspecialchars($gerente['cep'] ?? '') ?>" inputmode="numeric" autocomplete="postal-code" maxlength="9" pattern="[0-9]{5}-?[0-9]{3}" placeholder="00000-000">
                            <button type="button" class="btn btn-outline-secondary" data-cep-lookup>Buscar</button>
                        </div>
                        <p id="cep-status" class="form-text text-muted mb-0" aria-live="polite"></p>
                    </div>

                    <div class="col-md-8">
                        <label for="endereco" class="form-label">Endereço:</label>
                        <input type="text" class="form-control" id="endereco" name="endereco" value="<?= htmlspecialchars($gerente['endereco'] ?? '') ?>" autocomplete="street-address" maxlength="100">
                    </div>

                    <div class="col-md-8">
                        <label for="cidade" class="form-label">Cidade:</label>
                        <input type="text" class="form-control" id="cidade" name="cidade" value="<?= htmlspecialchars($gerente['cidade'] ?? '') ?>" autocomplete="address-level2" maxlength="45">
                    </div>

                    <div class="col-md-4">
                        <label for="estado" class="form-label">Estado (UF):</label>
                        <select class="form-select" id="estado" name="estado" autocomplete="off">
                            <option value="">Selecione...</option>
                            <?php 
                            $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                            $estadoAtual = $gerente['estado'] ?? '';
                            foreach($ufs as $uf) {
                                $sel = ($estadoAtual === $uf) ? 'selected' : '';
                                echo "<option value=\"$uf\" $sel>$uf</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="email" class="form-label">E-mail de Acesso: <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($gerente['email']) ?>" required>
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
    <script src="../../assets/js/cpf.js?v=<?= filemtime(__DIR__ . '/../../assets/js/cpf.js'); ?>"></script>
</body>
</html>