<?php
// view/admin/listar_academias_gerentes.php
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
            <div class="dropdown">
                <button
                    class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2 p-0 border-0"
                    type="button"
                    id="userDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="bi bi-person-circle fs-5"></i>
                    <strong><?= htmlspecialchars($_SESSION['usuario']['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="../alterar_senha.php">
                            <i class="bi bi-key"></i> Alterar senha
                        </a>
                    </li>
                </ul>
            </div>
            <a
                href="../../controller/LoginController.php?acao=logout"
                class="btn btn-sm btn-danger header-logout-button"
                aria-label="Sair"
                title="Sair"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10 17l5-5-5-5M15 12H3"/>
                    <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>
                </svg>
            </a>
        </div>
    </header>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="cadastrar_academia.php" class="btn btn-success btn-sm">
                    <i class="bi bi-building-add me-1"></i> Cadastrar Academia
                </a>
                <a href="cadastrar_gerente.php" class="btn btn-info btn-sm text-white">
                    <i class="bi bi-person-plus me-1"></i> Cadastrar Gerente
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4">
            <div class="mb-4">
                <h2 class="fs-4 fw-bold">Gestão de Academias e Gerentes</h2>
                <p class="text-muted mb-0">Visão integrada de todas as academias registadas e os respetivos gerentes responsáveis.</p>
            </div>

            <!-- Barra de Pesquisa em Tempo Real -->
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" id="inputPesquisa" placeholder="Pesquisar por nome da academia, documento, e-mail ou gerente..." class="form-control">
                </div>
            </div>

            <!-- Mensagens de Feedback -->
            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
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
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- TABELA 1: ACADEMIAS ATIVAS -->
            <h3 class="fs-5 fw-bold text-dark mb-3 mt-2">Academias Ativas</h3>
            <div class="table-responsive mb-4">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
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
                            <tr class="linha-vazia">
                                <td colspan="5" class="text-center text-muted py-4">Nenhuma academia ativa encontrada.</td>
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
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            <a href="editar_academia.php?id=<?= $linha['id_academia'] ?>" class="btn btn-sm btn-warning">Editar Academia</a>
                                            
                                            <a href="../../controller/AcademiaController.php?acao=desativar&id=<?= $linha['id_academia'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Tem certeza que deseja desativar esta academia?')">Desativar Academia</a>

                                            <?php if (!empty($linha['id_gerente'])): ?>
                                                <a href="editar_gerente.php?id=<?= $linha['id_gerente'] ?>" class="btn btn-sm btn-info text-white">Editar Gerente</a>
                                                
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
            </div>

            <!-- TABELA 2: ACADEMIAS INATIVAS -->
            <h3 class="fs-5 fw-bold text-secondary mb-3 mt-4">Academias Inativas</h3>
            <div class="table-responsive mb-4">
                <table class="table table-hover align-middle">
                    <thead class="table-secondary">
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
                            <tr class="linha-vazia">
                                <td colspan="5" class="text-center text-muted py-4">Nenhuma academia inativa encontrada.</td>
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
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
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
            </div>

            <!-- TABELA 3: GERENTES INATIVOS -->
            <h3 class="fs-5 fw-bold text-secondary mb-3 mt-4">Gerentes Inativos / Anteriores</h3>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Nome do Gerente</th>
                            <th>E-mail / Telefone</th>
                            <th>Última Academia Vinculada</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($gerentesInativos)): ?>
                            <tr class="linha-vazia">
                                <td colspan="4" class="text-center text-muted py-4">Nenhum gerente inativo encontrado.</td>
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
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
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
        </section>
    </main>

    <!-- Script de Pesquisa em Tempo Real -->
    <script>
    document.getElementById('inputPesquisa').addEventListener('keyup', function() {
        const termo = this.value.toLowerCase().trim();
        const linhas = document.querySelectorAll('table tbody tr');
        
        linhas.forEach(linha => {
            if (linha.classList.contains('linha-vazia') || linha.cells.length === 1) return; 

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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>