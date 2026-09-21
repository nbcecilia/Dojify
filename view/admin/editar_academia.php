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
            <div class="alert-erro">Erro ao atualizar a academia. Verifique os dados fornecidos.</div>
        <?php endif; ?>

        <form action="../../controller/AcademiaController.php" method="POST">
             <div class="text-center" style="margin-bottom: 20px;">
                <img src="../../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Editar Dados da Academia</h2>
            </div>
            <input type="hidden" name="acao" value="atualizar">
            <input type="hidden" name="id_academia" value="<?= htmlspecialchars($academia['id_academia']) ?>">
            
            <label>Nome da Academia:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($academia['nome']) ?>" required>

            <label>Documento (CNPJ ou CPF do responsável):</label>
            <input type="text" name="documento" value="<?= htmlspecialchars($academia['documento']) ?>" required>

            <label>Endereço Completo:</label>
            <input type="text" name="endereco" value="<?= htmlspecialchars($academia['endereco']) ?>" required>

            <label>Telefone Principal:</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($academia['telefone']) ?>" required>

            <label>E-mail Institucional:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($academia['email']) ?>" required>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
    <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>