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
    <style>
        .btn.action-icon-btn {
            width: 2.2rem;
            height: 2.2rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            opacity: 1;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            transition: background-color 0.2s ease-in-out;
        }

        .btn.action-edit {
            color: #0d6efd !important;
        }

        .btn.action-disable {
            color: #dc3545 !important;
        }

        .btn.action-reactivate {
            color: #198754 !important;
        }

        .btn.action-edit:hover {
            background-color: rgba(13, 110, 253, 0.15) !important;
        }

        .btn.action-disable:hover {
            background-color: rgba(220, 53, 69, 0.15) !important;
        }

        .btn.action-reactivate:hover {
            background-color: rgba(25, 135, 84, 0.15) !important;
        }

        .btn.action-create-academy,
        .btn.action-create-manager {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            transition: background-color 0.2s ease-in-out;
        }

        .btn.action-create-academy {
            color: #198754 !important;
        }

        .btn.action-create-manager {
            color: #0d6efd !important;
        }

        .btn.action-create-academy:hover {
            background-color: rgba(25, 135, 84, 0.15) !important;
        }

        .btn.action-create-manager:hover {
            background-color: rgba(13, 110, 253, 0.15) !important;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="cadastrar_academia.php" class="btn btn-success btn-sm action-create-academy">
                    <i class="bi bi-building-add me-1"></i> Cadastrar Academia
                </a>
                <a href="cadastrar_gerente.php" class="btn btn-info btn-sm text-white action-create-manager">
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
            <div class="mb-4" data-live-search-container>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" id="inputPesquisa" class="form-control" data-live-search
                           data-search-target="table tbody tr"
                           placeholder="Pesquisar por nome da academia, documento, e-mail ou gerente..." autocomplete="off">
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
                                        <div class="d-flex flex-nowrap gap-1 justify-content-center align-items-center">
                                            <a href="editar_academia.php?id=<?= $linha['id_academia'] ?>"
                                               class="btn btn-sm action-icon-btn action-edit"
                                               title="Editar academia"
                                               aria-label="Editar academia">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            <a href="../../controller/AcademiaController.php?acao=desativar&id=<?= $linha['id_academia'] ?>"
                                               class="btn btn-sm action-icon-btn action-disable"
                                               title="Desativar academia"
                                               aria-label="Desativar academia"
                                               onclick="return confirm('Tem certeza que deseja desativar esta academia?')">
                                                <i class="bi bi-slash-circle"></i>
                                            </a>

                                            <?php if (!empty($linha['id_gerente'])): ?>
                                                <a href="editar_gerente.php?id=<?= $linha['id_gerente'] ?>"
                                                   class="btn btn-sm action-icon-btn action-edit"
                                                   title="Editar gerente"
                                                   aria-label="Editar gerente">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <?php if (isset($linha['gerente_status']) && strtoupper($linha['gerente_status']) === 'INATIVO'): ?>
                                                    <a href="../../controller/UsuarioController.php?acao=reativar&id=<?= $linha['id_gerente'] ?>"
                                                       class="btn btn-sm action-icon-btn action-reactivate"
                                                       title="Reativar gerente"
                                                       aria-label="Reativar gerente"
                                                       onclick="return confirm('Tem certeza que deseja reativar este gerente?')">
                                                        <i class="bi bi-person-check"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="../../controller/UsuarioController.php?acao=desativar&id=<?= $linha['id_gerente'] ?>"
                                                       class="btn btn-sm action-icon-btn action-disable"
                                                       title="Desativar gerente"
                                                       aria-label="Desativar gerente"
                                                       onclick="return confirm('Tem certeza que deseja desativar este gerente?')">
                                                        <i class="bi bi-person-x"></i>
                                                    </a>
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
                                        <div class="d-flex flex-nowrap gap-1 justify-content-center align-items-center">
                                            <a href="../../controller/AcademiaController.php?acao=reativar&id=<?= $linha['id_academia'] ?>"
                                               class="btn btn-sm action-icon-btn action-reactivate"
                                               title="Reativar academia"
                                               aria-label="Reativar academia"
                                               onclick="return confirm('Tem certeza que deseja reativar esta academia?')">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </a>
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
                                        <div class="d-flex flex-nowrap gap-1 justify-content-center align-items-center">
                                            <a href="../../controller/UsuarioController.php?acao=reativar&id=<?= $gi['id_usuario'] ?>"
                                               class="btn btn-sm action-icon-btn action-reactivate"
                                               title="Reativar gerente"
                                               aria-label="Reativar gerente"
                                               onclick="return confirm('Tem certeza que deseja reativar este gerente?')">
                                                <i class="bi bi-person-check"></i>
                                            </a>
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

    <script src="../../assets/js/busca.js"></script>

    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>