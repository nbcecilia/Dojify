<?php
// view/gerente/listar_turma.php

session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/TurmaDAO.php';

$dao = new \TurmaDAO();

$idAcademia = (int) $_SESSION['id_academia'];

$turmas = $dao->listarPorAcademia($idAcademia);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turmas - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body>

    <!-- 1. Adicionado o topo dinâmico (com logotipo e sino de notificações) -->
    <?php include '../includes/header.php'; ?>

    <!-- 2. Alterada a classe para 'container' (para não encostar nas bordas) -->
    <main class="container" style="padding-top: 24px; padding-bottom: 24px;">

        <!-- 3. Novo cabeçalho flexível: Título à esquerda, botões à direita -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="margin: 0;">Turmas</h1>
            
            <div style="display: flex; gap: 12px;">
                <!-- O seu botão de Cadastrar original -->
                <a href="cadastrar_turma.php" class="btn btn-success">
                    + Cadastrar Turma
                </a>
                
                <!-- Novo botão para voltar à página principal sem precisar do menu lateral -->
                <a href="home_gerente.php" class="btn" style="background-color: var(--border-color); color: var(--text-primary) !important; text-decoration: none;">
                    ⬅ Voltar ao Painel
                </a>
            </div>
        </div>

        <?php if (isset($_GET['sucesso'])): ?>
            <p class="alert-sucesso">
                Operação realizada com sucesso!
            </p>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <p class="alert-erro">
                Não foi possível realizar a operação.
            </p>
        <?php endif; ?>

        <?php if (empty($turmas)): ?>
            <p>Nenhuma turma cadastrada.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Turma</th>
                        <th>Professor</th>
                        <th>Modalidade</th>
                        <th>Capacidade</th>
                        <th>Nível</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($turmas as $turma): ?>
                        <tr>
                            <td><?= htmlspecialchars($turma['nome']) ?></td>
                            <td><?= htmlspecialchars($turma['professor_nome']) ?></td>
                            <td><?= htmlspecialchars($turma['modalidade_nome']) ?></td>
                            <td><?= htmlspecialchars($turma['capacidade']) ?></td>
                            <td><?= htmlspecialchars($turma['nivel']) ?></td>
                            <td><?= htmlspecialchars($turma['status']) ?></td>
                            <td>
                                <a href="editar_turma.php?id=<?= $turma['id_turma'] ?>" class="btn btn-info btn-sm">
                                    Editar
                                </a>
                                <a href="../../controller/TurmaController.php?acao=excluir&id=<?= $turma['id_turma'] ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Deseja realmente excluir esta turma?');">
                                    Excluir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </main>

    <!-- Adicionado o rodapé para manter o padrão -->
    <?php include '../includes/footer.php'; ?>

</body>
</html>