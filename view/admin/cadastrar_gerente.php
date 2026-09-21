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

    <div class="container">

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-erro">Erro ao cadastrar gerente. Verifique se o CPF ou E-mail já estão em uso.</div>
        <?php endif; ?>

        <form action="../../controller/UsuarioController.php" method="POST">
             <div class="text-center" style="margin-bottom: 20px;">
                <img src="../../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Cadastrar Gerente</h2>
             </div>
            <input type="hidden" name="acao" value="cadastrar_gerente">
            
            <label>Selecione a Academia:</label>
            <select name="id_academia" required>
                <option value="">-- Selecione uma Academia --</option>
                <?php foreach ($academias as $ac): ?>
                    <option value="<?= $ac['id_academia'] ?>"><?= htmlspecialchars($ac['nome']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Nome Completo do Gerente:</label>
            <input type="text" name="nome" required>

            <label>CPF (apenas números):</label>
            <input type="text" name="cpf" maxlength="11" required>

            <label>Data de Nascimento:</label>
            <input type="date" name="data_nascimento" required>

            <label>Telefone / WhatsApp:</label>
            <input type="text" name="telefone" required>

            <label>E-mail (Será o login de acesso):</label>
            <input type="email" name="email" required>

            <label>Senha de Acesso:</label>
            <input type="password" name="senha" required>

            <button type="submit">Cadastrar Gerente</button>
        </form>
    </div>
    <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>