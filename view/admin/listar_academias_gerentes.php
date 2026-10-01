<?php
// view/admin/listar_academias_gerentes.php
// AQUI PRECISAMOS MUDAR DEPOIS A QUESTÃO DAS MENSAGENS DE FEEDBACK, QUE ACREDITO QUE DEVERIAM ESTAR EM JS, MAS ESTÃO NO CSS EU ACHO
session_start();

// Verifica se é Administrador (perfil_id = 1)
if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 1) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/AcademiaDAO.php';
require_once __DIR__ . '/../../model/dao/UsuarioDAO.php';

$dao = new AcademiaDAO();
$lista = $dao->listarAcademiasEGerentes();

$usuarioDAO = new UsuarioDAO();
$gerentesInativos = $usuarioDAO->listarGerentesInativos();

// Separação entre academias ativas e inativas
$ativas = [];
$inativas = [];

foreach ($lista as $linha) {
    $status = strtoupper($linha['status'] ?? 'ATIVO');
    if ($status === 'INATIVO') {
        $inativas[] = $linha;
    } else {
        $ativas[] = $linha;
    }
}
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
            <a href="home_admin.php" class="btn btn-sm">Início</a>
            <a href="../../controller/LoginController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h2>Gestão de Academias e Gerentes</h2>
            <div>
                <a href="cadastrar_academia.php" class="btn btn-success btn-sm">+ Cadastrar Academia</a>
                <a href="cadastrar_gerente.php" class="btn btn-info btn-sm">+ Cadastrar Gerente</a>
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
            <div class="alert-sucesso">
                <?php 
                    switch ($_GET['sucesso']) {
                        case 'reativado':
                            echo 'Gerente reativado com sucesso!';
                            break;
                        case 'desativado':
                            echo 'Utilizador desativado com sucesso!';
                            break;
                        case 'gerente_atualizado':
                            echo 'Dados do gerente atualizados com sucesso!';
                            break;
                        default:
                            echo 'Operação realizada com sucesso!';
                            break;
                    }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-erro">
                <?php 
                    switch ($_GET['erro']) {
                        case 'academia_ja_tem_gerente':
                            echo 'Não é possível reativar este gerente porque a academia associada já possui um gerente ativo!';
                            break;
                        case 'id_invalido':
                            echo 'O ID informado é inválido.';
                            break;
                        case 'nao_encontrado':
                            echo 'O registo selecionado não foi encontrado.';
                            break;
                        case 'falha_reativacao':
                            echo 'Não foi possível concluir a reativação devido a um erro.';
                            break;
                        default:
                            echo 'Não foi possível realizar a operação.';
                            break;
                    }
                ?>
            </div>
        <?php endif; ?>

        <!-- TABELA 1: ACADEMIAS ATIVAS -->
        <h3 style="margin-top: 30px; margin-bottom: 15px; color: #2c3e50;">Academias Ativas</h3>
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
                <?php if (empty($ativas)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nenhuma academia ativa encontrada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ativas as $linha): ?>
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
                                    <?php if (isset($linha['gerente_status']) && strtoupper($linha['gerente_status']) === 'INATIVO'): ?>
                                        <br><small class="text-danger">(Inativo)</small>
                                    <?php endif; ?>
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
                                <div class="table-actions">
                                    <a href="editar_academia.php?id=<?= $linha['id_academia'] ?>" class="btn btn-sm btn-warning">Editar Academia</a>
                                    
                                    <a href="../../controller/AcademiaController.php?acao=desativar&id=<?= $linha['id_academia'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja desativar esta academia?')">Desativar Academia</a>

                                    <?php if (!empty($linha['id_gerente'])): ?>
                                        <a href="editar_gerente.php?id=<?= $linha['id_gerente'] ?>" class="btn btn-sm btn-info">Editar Gerente</a>
                                        
                                        <?php if (isset($linha['gerente_status']) && strtoupper($linha['gerente_status']) === 'INATIVO'): ?>
                                            <a href="../../controller/UsuarioController.php?acao=reativar&id=<?= $linha['id_gerente'] ?>" 
                                               class="btn btn-sm btn-success" 
                                               onclick="return confirm('Tem certeza que deseja reativar este gerente?')">Reativar Gerente</a>
                                        <?php else: ?>
                                            <a href="../../controller/UsuarioController.php?acao=desativar&id=<?= $linha['id_gerente'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Tem certeza que deseja desativar este gerente?')">Desativar Gerente</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- TABELA 2: ACADEMIAS INATIVAS -->
        <h3 style="margin-top: 40px; margin-bottom: 15px; color: #7f8c8d;">Academias Inativas</h3>
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
                <?php if (empty($inativas)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nenhuma academia inativa encontrada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($inativas as $linha): ?>
                        <tr style="opacity: 0.85; background-color: #fdfdfd;">
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
                                <div class="table-actions">
                                    <a href="../../controller/AcademiaController.php?acao=reativar&id=<?= $linha['id_academia'] ?>" 
                                       class="btn btn-sm btn-success" 
                                       onclick="return confirm('Tem certeza que deseja reativar esta academia?')">Reativar Academia</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- TABELA 3: GERENTES INATIVOS -->
        <h3 style="margin-top: 40px; margin-bottom: 15px; color: #7f8c8d;">Gerentes Inativos / Anteriores</h3>
        <table>
            <thead>
                <tr>
                    <th>Nome do Gerente</th>
                    <th>E-mail / Telefone</th>
                    <th>Última Academia Vinculada</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($gerentesInativos)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Nenhum gerente inativo encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($gerentesInativos as $gi): ?>
                        <tr style="opacity: 0.85; background-color: #fdfdfd;">
                            <td>
                                <strong><?= htmlspecialchars($gi['nome']) ?></strong><br>
                                <small class="text-muted">CPF: <?= htmlspecialchars($gi['cpf']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($gi['email']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($gi['telefone']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($gi['academia_nome'] ?? 'Nenhuma') ?>
                            </td>
                            <td class="text-center">
                                <div class="table-actions">
                                    <a href="../../controller/UsuarioController.php?acao=reativar&id=<?= $gi['id_usuario'] ?>" 
                                       class="btn btn-sm btn-success" 
                                       onclick="return confirm('Tem certeza que deseja reativar este gerente?')">Reativar Gerente</a>
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