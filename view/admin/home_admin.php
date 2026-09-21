<?php
//view/admin/home_admin.php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 1) {
    header('Location: ../login.php');
    exit;
}

require_once '../../model/dao/AcademiaDAO.php';
$dao = new AcademiaDAO();

// Verifica se há termo de busca na URL
$termo = trim($_GET['busca'] ?? '');

if (!empty($termo)) {
    $lista = $dao->buscarAcademiasEGerentes($termo);
} else {
    $lista = $dao->listarAcademiasEGerentes();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <!-- Navbar Padronizada-->
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
            <span class="user-greeting">Logado como: <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></span>
            <a href="../../controller/LoginController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>
    
        <div class="container">
        <!-- BOTÕES CADASTRAR ACADEMIA E GERENTE-->
        <div class="acoes-topbar">
        <a href="cadastrar_academia.php" class="btn btn-success">🏢 Cadastrar Academia</a>
        <a href="cadastrar_gerente.php" class="btn btn-info">👤 Cadastrar Gerente</a>
        </div>

        <!-- BARRA DE PESQUISA -->
        <div class="busca-container">
            <form action="home_admin.php" method="GET" class="busca-form">
                <input type="text" 
                       name="busca" 
                       class="busca-input" 
                       placeholder="Pesquisar por nome da academia, CNPJ/CPF, e-mail ou gerente..." 
                       value="<?= htmlspecialchars($termo) ?>">
                
                <button type="submit" class="busca-btn btn">🔍 Pesquisar</button>
                
                <?php if (!empty($termo)): ?>
                    <a href="home_admin.php" class="btn btn-danger busca-btn">✖ Limpar Filtro</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert-sucesso">Operação realizada com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-erro">Não foi possível realizar a operação. Verifique os dados fornecidos.</div>
        <?php endif; ?>

        <h2>Academias e Gestores Cadastrados</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Academia (CNPJ/CPF)</th>
                    <th>Contato Academia</th>
                    <th>Gerente Responsável</th>
                    <th>Contato Gerente</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lista)): ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            <?= !empty($termo) ? 'Nenhuma academia encontrada para a busca "' . htmlspecialchars($termo) . '".' : 'Nenhuma academia cadastrada.' ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($lista as $linha): ?>
                <tr>
                    <td><?= htmlspecialchars($linha['id_academia']) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($linha['academia_nome']) ?></strong><br>
                        <small class="text-muted">Doc: <?= htmlspecialchars($linha['documento']) ?></small>
                    </td>
                    <td>
                        <?= htmlspecialchars($linha['academia_email']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($linha['academia_telefone']) ?></small>
                    </td>
                    <td>
                        <?php if (!empty($linha['gerente_nome'])): ?>
                            <?= htmlspecialchars($linha['gerente_nome']) ?>
                        <?php else: ?>
                            <span class="text-muted">Sem gerente vinculado</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($linha['gerente_email'])): ?>
                            <?= htmlspecialchars($linha['gerente_email']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($linha['gerente_telefone']) ?></small>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <!-- 1. Editar Academia -->
                        <a href="editar_academia.php?id=<?= $linha['id_academia'] ?>" class="btn btn-sm btn-warning">Editar Academia</a>
                        
                        <!-- 2. Excluir Academia -->
                        <a href="../../controller/AcademiaController.php?acao=excluir&id=<?= $linha['id_academia'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Tem certeza que deseja excluir esta academia e os dados vinculados?')">Excluir Academia</a>

                        <?php if (!empty($linha['id_gerente'])): ?>
                            <!-- 3. Editar Gerente -->
                            <a href="editar_gerente.php?id=<?= $linha['id_gerente'] ?>" class="btn btn-sm btn-info">Editar Gerente</a>
                            
                            <!-- 4. Excluir Gerente -->
                            <a href="../../controller/UsuarioController.php?acao=excluir&id=<?= $linha['id_gerente'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Tem certeza que deseja excluir este gerente?')">Excluir Gerente</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>