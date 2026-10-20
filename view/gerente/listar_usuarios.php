<?php
// view/gerente/listar_usuarios.php
session_start();

if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/UsuarioDAO.php';

$idAcademia = $_SESSION['usuario']['id_academia'];
$idGerenteLogado = $_SESSION['usuario']['id_usuario'] ?? $_SESSION['usuario']['id'] ?? 0;

$usuarioDAO = new UsuarioDAO();
$usuarios = $usuarioDAO->listarPorAcademia($idAcademia);

// Captura o termo digitado na pesquisa
$termoBusca = trim($_GET['busca'] ?? '');

$gerentes = [];
$professores = [];
$alunos = [];

foreach ($usuarios as $u) {
    // Se houver termo de busca, filtra por nome ou e-mail (ignorando maiúsculas/minúsculas)
    if ($termoBusca !== '') {
        $nomeMatch = mb_stripos($u['nome'] ?? '', $termoBusca) !== false;
        $emailMatch = mb_stripos($u['email'] ?? '', $termoBusca) !== false;
        if (!$nomeMatch && !$emailMatch) {
            continue;
        }
    }

    $perfil = strtolower($u['perfil_nome'] ?? '');
    $perfilId = (int)($u['perfil_id'] ?? 0);

    if ($perfilId === 2 || str_contains($perfil, 'gerente')) {
        $gerentes[] = $u;
    } 
    elseif ($perfilId === 3 || str_contains($perfil, 'professor') || str_contains($perfil, 'instrutor')) {
        $professores[] = $u;
    } 
    else {
        $alunos[] = $u;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Alunos e Professores - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>    
    <?php include '../includes/header.php'; ?> 

    <main class="container my-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="cadastrar_aluno_prof.php" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i> Cadastrar Novo Usuário
                </a>
            </div>
        </header>

        <section class="card shadow-sm border-0 p-4 mb-4">
            <div class="mb-4">
                <h2>Gestão de Utilizadores</h2>
                <p class="text-muted">Administração de professores e alunos registados na sua academia.</p>
            </div>

            <!-- Barra de Pesquisa Padronizada (.busca-container) -->
            <div class="busca-container">
                <form method="GET" action="listar_usuarios.php" class="busca-form">
                    <div class="input-group busca-input">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="busca" class="form-control" placeholder="Pesquisar por nome ou e-mail..." value="<?= htmlspecialchars($termoBusca) ?>">
                    </div>
                    <button type="submit" class="btn btn-dark busca-btn">
                        Filtrar
                    </button>
                    <?php if ($termoBusca !== ''): ?>
                        <a href="listar_usuarios.php" class="btn btn-outline-secondary busca-btn" title="Limpar pesquisa">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </section>

        <!-- SECÇÃO DE GERENTES -->
        <section class="card shadow-sm border-0 p-4 mb-4">
            <div class="mb-3">
                <h3 class="h5 mb-0">Gerente</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th>Perfil</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Estado</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($gerentes)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhum gerente registado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($gerentes as $u): ?>
                                <?php $isPropraConta = ((int)$u['id_usuario'] === (int)$idGerenteLogado); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($u['nome']); ?></strong>
                                        <?php if ($isPropraConta): ?>
                                            <span class="text-muted small">(Sua conta)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span><?php echo htmlspecialchars($u['perfil_nome'] ?? 'Gerente'); ?></span></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['telefone']); ?></td>
                                    <td>
                                        <?php if ($u['status'] === 'ATIVO'): ?>
                                            <span class="text-success fw-semibold">Ativo</span>
                                        <?php elseif ($u['status'] === 'SUSPENSO'): ?>
                                            <span class="text-warning fw-semibold">Suspenso</span>
                                        <?php else: ?>
                                            <span class="text-danger fw-semibold">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($isPropraConta): ?>
                                            <span class="text-muted small">Não aplicável</span>
                                        <?php else: ?>
                                            <div class="d-flex justify-content-end gap-1 flex-wrap">
                                                <?php if ($u['status'] !== 'ATIVO'): ?>
                                                    <a href="../../controller/UsuarioController.php?acao=alternar_status&id=<?php echo $u['id_usuario']; ?>&status=ATIVO" class="btn btn-sm btn-success">Ativar</a>
                                                <?php endif; ?>
                                                <?php if ($u['status'] !== 'SUSPENSO'): ?>
                                                    <a href="../../controller/UsuarioController.php?acao=alternar_status&id=<?php echo $u['id_usuario']; ?>&status=SUSPENSO" class="btn btn-sm btn-warning">Suspender</a>
                                                <?php endif; ?>
                                                <?php if ($u['status'] !== 'INATIVO'): ?>
                                                    <a href="../../controller/UsuarioController.php?acao=alternar_status&id=<?php echo $u['id_usuario']; ?>&status=INATIVO" class="btn btn-sm btn-danger">Inativar</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECÇÃO DE PROFESSORES -->
        <section class="card shadow-sm border-0 p-4 mb-4">
            <div class="mb-3">
                <h3 class="h5 mb-0">Professores</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th>Perfil</th>
                            <th>Especialidade</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Estado</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($professores)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Nenhum professor registado nesta academia.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($professores as $u): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($u['nome']); ?></strong></td>
                                    <td><span><?php echo htmlspecialchars($u['perfil_nome'] ?? 'Professor'); ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($u['especialidade'] ?? 'Geral'); ?></span></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['telefone']); ?></td>
                                    <td>
                                        <?php if ($u['status'] === 'ATIVO'): ?>
                                            <span class="text-success fw-semibold">Ativo</span>
                                        <?php elseif ($u['status'] === 'SUSPENSO'): ?>
                                            <span class="text-warning fw-semibold">Suspenso</span>
                                        <?php else: ?>
                                            <span class="text-danger fw-semibold">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario" data-edit-url="editar_professor.php?id=<?php echo (int)$u['id_usuario']; ?>" data-edit-title="Editar Professor">
                                                <i class="bi bi-pencil me-1"></i>Editar
                                            </button>
                                            <?php if ($u['status'] !== 'ATIVO'): ?>
                                                <a href="../../controller/UsuarioController.php?acao=alternar_status&id=<?php echo $u['id_usuario']; ?>&status=ATIVO" class="btn btn-sm btn-success">Ativar</a>
                                            <?php endif; ?>
                                            <?php if ($u['status'] !== 'SUSPENSO'): ?>
                                                <a href="../../controller/UsuarioController.php?acao=alternar_status&id=<?php echo $u['id_usuario']; ?>&status=SUSPENSO" class="btn btn-sm btn-warning">Suspender</a>
                                            <?php endif; ?>
                                            <?php if ($u['status'] !== 'INATIVO'): ?>
                                                <a href="../../controller/UsuarioController.php?acao=alternar_status&id=<?php echo $u['id_usuario']; ?>&status=INATIVO" class="btn btn-sm btn-danger">Inativar</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECÇÃO DE ALUNOS -->
        <section class="card shadow-sm border-0 p-4">
            <div class="mb-3">
                <h3 class="h5 mb-0">Alunos</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th>Perfil</th>
                            <th>Modalidade</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Estado</th>
                            <th>Contrato</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($alunos)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Nenhum aluno registado nesta academia.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($alunos as $u): ?>
                                <?php $contratoAceito = (int)($u['primeiro_acesso'] ?? 1) === 0; ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($u['nome']); ?></strong>
                                        <?php if (!empty($u['responsavel'])): ?>
                                            <br><span class="text-muted small">Resp: <?php echo htmlspecialchars($u['responsavel']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span><?php echo htmlspecialchars($u['perfil_nome'] ?? 'Aluno'); ?></span></td>
                                    <td><span class="badge bg-dark"><?php echo htmlspecialchars($u['modalidade_nome'] ?? $u['modalidade'] ?? 'Geral'); ?></span></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['telefone']); ?></td>
                                    <td>
                                        <?php if ($u['status'] === 'ATIVO'): ?>
                                            <span class="text-success fw-semibold">Ativo</span>
                                        <?php elseif ($u['status'] === 'SUSPENSO'): ?>
                                            <span class="text-warning fw-semibold">Suspenso</span>
                                        <?php else: ?>
                                            <span class="text-danger fw-semibold">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($contratoAceito): ?>
                                            <span class="badge bg-success">Aceito</span>
                                            <a href="visualizar_contrato_aluno.php?id=<?php echo (int)$u['id_usuario']; ?>" class="btn btn-sm btn-info ms-1">Ver contrato</a>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pendente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario" data-edit-url="editar_aluno.php?id=<?php echo (int)$u['id_usuario']; ?>" data-edit-title="Editar Aluno">
                                                <i class="bi bi-pencil me-1"></i>Editar
                                            </button>
                                            <?php if ($u['status'] !== 'ATIVO'): ?>
                                                <a href="../../controller/UsuarioController.php?acao=alternar_status&id=<?php echo $u['id_usuario']; ?>&status=ATIVO" class="btn btn-sm btn-success">Ativar</a>
                                            <?php endif; ?>
                                            <?php if ($u['status'] !== 'SUSPENSO'): ?>
                                                <a href="../../controller/UsuarioController.php?acao=alternar_status&id=<?php echo $u['id_usuario']; ?>&status=SUSPENSO" class="btn btn-sm btn-warning">Suspender</a>
                                            <?php endif; ?>
                                            <?php if ($u['status'] !== 'INATIVO'): ?>
                                                <a href="../../controller/UsuarioController.php?acao=alternar_status&id=<?php echo $u['id_usuario']; ?>&status=INATIVO" class="btn btn-sm btn-danger">Inativar</a>
                                            <?php endif; ?>
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

    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="modalEditarUsuarioLabel">Editar Usuário</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="frameEditarUsuario" title="Formulário de edição do usuário" class="edit-user-frame"></iframe>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalEditarUsuario = document.getElementById('modalEditarUsuario');
        const frameEditarUsuario = document.getElementById('frameEditarUsuario');
        const tituloModalEditarUsuario = document.getElementById('modalEditarUsuarioLabel');

        modalEditarUsuario.addEventListener('show.bs.modal', event => {
            const botaoEditar = event.relatedTarget;
            frameEditarUsuario.src = botaoEditar.dataset.editUrl;
            tituloModalEditarUsuario.textContent = botaoEditar.dataset.editTitle;
        });

        frameEditarUsuario.addEventListener('load', () => {
            try {
                const urlAtual = new URL(frameEditarUsuario.contentWindow.location.href);
                if (urlAtual.pathname.endsWith('/listar_usuarios.php')) {
                    bootstrap.Modal.getOrCreateInstance(modalEditarUsuario).hide();
                    window.location.reload();
                }
            } catch (erro) {
                return;
            }
        });

        modalEditarUsuario.addEventListener('hidden.bs.modal', () => {
            frameEditarUsuario.src = 'about:blank';
        });
    </script>
</body>
</html>