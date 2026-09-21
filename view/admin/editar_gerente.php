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

    <div class="container">
    
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-erro">Erro ao atualizar o gerente. Verifique os dados fornecidos.</div>
        <?php endif; ?>

        <form action="../../controller/UsuarioController.php" method="POST">
             <div class="text-center" style="margin-bottom: 20px;">
                <img src="../../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Editar Dados do Gerente</h2>
            </div>
            <input type="hidden" name="acao" value="atualizar">
            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($gerente['id_usuario']) ?>">
            <input type="hidden" name="perfil_id" value="2">
            
            <label>Nome Completo:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($gerente['nome']) ?>" required>

            <label>CPF (somente números):</label>
            <input type="text" name="cpf" value="<?= htmlspecialchars($gerente['cpf']) ?>" maxlength="11" required>

            <label>Data de Nascimento:</label>
            <input type="date" name="data_nascimento" value="<?= htmlspecialchars($gerente['data_nascimento']) ?>" required>

            <label>Telefone:</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($gerente['telefone']) ?>" required>

            <label>E-mail de Acesso:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($gerente['email']) ?>" required>

            <label>Status:</label>
            <select name="status" required>
                <option value="ATIVO" <?= $gerente['status'] === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                <option value="INATIVO" <?= $gerente['status'] === 'INATIVO' ? 'selected' : '' ?>>INATIVO</option>
                <option value="SUSPENSO" <?= $gerente['status'] === 'SUSPENSO' ? 'selected' : '' ?>>SUSPENSO</option>
            </select>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
    <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>