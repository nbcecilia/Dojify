<?php
// view/admin/listar_academias_gerentes.php
session_start();

// Verifica se é Administrador (perfil_id = 1)
if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 1) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/AcademiaDAO.php';

$dao = new AcademiaDAO();
$lista = $dao->listarAcademiasEGerentes();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academias e Gerentes - Dojify Admin</title>
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
            <span class="user-greeting">Logado como: <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></span>
            <a href="home_admin.php" class="btn btn-sm">Dashboard</a>
            <a href="../../controller/LoginController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Gestão de Academias e Gerentes</h2>
            <div style="display: flex; gap: 10px;">
                <a href="cadastrar_academia.php" class="btn btn-success btn-sm">+ Academia</a>
                <a href="cadastrar_gerente.php" class="btn btn-info btn-sm">+ Gerente</a>
            </div>
        </div>
        <p class="text-center text-muted" style="margin-bottom: 24px;">Visão integrada de todas as academias registadas e os respetivos gerentes responsáveis.</p>

        <!-- Barra de Pesquisa em Tempo Real -->
        <div class="busca-container">
            <div class="busca-form">
                <input type="text" id="inputPesquisa" placeholder="Pesquisar por nome da academia, documento, e-mail ou gerente..." class="busca-input">
            </div>
        </div>

        <!-- Mensagens de Feedback -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert-sucesso">Operação realizada com sucesso!</div>
        <?php endif; ?>
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-erro">Não foi possível realizar a operação.</div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Academia (CNPJ/CPF)</th>
                    <th>Contato da Academia</th>
                    <th>Gerente Responsável</th>
                    <th>Contato do Gerente</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lista)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nenhuma academia ou gerente encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lista as $linha): ?>
                        <tr>
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
                                    <strong><?= htmlspecialchars($linha['gerente_nome']) ?></strong>
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
                                <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                    <a href="editar_academia.php?id=<?= $linha['id_academia'] ?>" class="btn btn-sm btn-warning">Editar Academia</a>
                                    
                                    <a href="../../controller/AcademiaController.php?acao=excluir&id=<?= $linha['id_academia'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir esta academia e os dados vinculados?')">Excluir Academia</a>

                                    <?php if (!empty($linha['id_gerente'])): ?>
                                        <a href="editar_gerente.php?id=<?= $linha['id_gerente'] ?>" class="btn btn-sm btn-info">Editar Gerente</a>
                                        
                                        <a href="../../controller/UsuarioController.php?acao=excluir&id=<?= $linha['id_gerente'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Tem certeza que deseja excluir este gerente?')">Excluir Gerente</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Script de Pesquisa em Tempo Real -->
    <script>
    document.getElementById('inputPesquisa').addEventListener('keyup', function() {
        const termo = this.value.toLowerCase().trim();
        const linhas = document.querySelectorAll('table tbody tr');
        
        linhas.forEach(linha => {
            if (linha.cells.length === 1) return; 

            const textoLinha = linha.textContent.toLowerCase();
            if (textoLinha.includes(termo)) {
                linha.style.display = ''; 
            } else {
                linha.style.display = 'none'; 
            }
        });
    });
    </script>

    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>