<?php
// view/gerente/listar_plano.php
session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/PlanoDAO.php';

$dao = new PlanoDAO();
$idAcademia = (int) $_SESSION['id_academia'];
$planos = $dao->listarPorAcademia($idAcademia);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar-brand">
            <a href="home_gerente.php" class="logo-link">
                <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div><h1>Dojify</h1></div>
            </a>
        </div>
        <div class="navbar-user">
            <span class="user-greeting">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></span>
            <a href="home_gerente.php" class="btn btn-sm">Início</a>
            <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 m-0">Planos dos Alunos</h1>
            <div class="d-flex gap-2">
                <a href="cadastrar_plano.php" class="btn btn-success">+ Cadastrar Plano</a>
                <a href="home_gerente.php" class="btn btn-secondary">⬅ Voltar ao Painel</a>
            </div>
        </div>

        <?php if (isset($_GET['sucesso'])): ?>
            <p class="alert-sucesso">Operação realizada com sucesso!</p>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <p class="alert-erro">Não foi possível realizar a operação.</p>
        <?php endif; ?>

        <?php if (empty($planos)): ?>
            <p class="text-center text-muted">Nenhum plano cadastrado.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Plano</th>
                            <th>Valor (R$)</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($planos as $plano): ?>
                            <tr>
                                <td><?= htmlspecialchars($plano['aluno_nome']) ?></td>
                                <td><?= htmlspecialchars($plano['nome_plano']) ?></td>
                                <td>R$ <?= number_format($plano['valor'], 2, ',', '.') ?></td>
                                <td><?= date('d/m/Y', strtotime($plano['data_inicio'])) ?></td>
                                <td><?= $plano['data_fim'] ? date('d/m/Y', strtotime($plano['data_fim'])) : '-' ?></td>
                                <td><?= htmlspecialchars($plano['status']) ?></td>
                                <td class="text-end">
                                    <a href="editar_plano.php?id=<?= $plano['id_plano'] ?>" class="btn btn-info btn-sm">Editar</a>
                                    <a href="../../controller/PlanoController.php?acao=excluir&id=<?= $plano['id_plano'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir este plano?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>