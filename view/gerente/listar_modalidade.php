<?php
// view/gerente/listar_modalidade.php

session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['perfil_id'] != 2 ||
    !isset($_SESSION['id_academia'])
) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../model/dao/ModalidadeDAO.php';

$dao = new ModalidadeDAO();
$idAcademia = (int) $_SESSION['id_academia'];
$modalidades = $dao->listarPorAcademia($idAcademia);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modalidades - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body>

    <!-- Topo dinâmico -->
    <?php include '../includes/header.php'; ?>

    <!-- Conteúdo principal -->
    <main class="container" style="padding-top: 24px; padding-bottom: 24px;">

        <!-- Cabeçalho flexível: Título à esquerda, botões à direita -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="margin: 0;">Modalidades</h1>
            
            <div style="display: flex; gap: 12px;">
                <!-- Botão de Cadastrar Modalidade -->
                <a href="cadastrar_modalidade.php" class="btn btn-success">
                    + Cadastrar Modalidade
                </a>
                
                <!-- Botão para voltar ao painel -->
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

        <?php if (empty($modalidades)): ?>
            <p>Nenhuma modalidade cadastrada.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modalidades as $modalidade): ?>
                        <tr>
                            <td><?= htmlspecialchars($modalidade['nome']) ?></td>
                            <td><?= htmlspecialchars($modalidade['descricao'] ?? '') ?></td>
                            <td>
                                <a href="editar_modalidade.php?id=<?= $modalidade['id_modalidade'] ?>" class="btn btn-info btn-sm">
                                    Editar
                                </a>
                                <a href="../../controller/ModalidadeController.php?acao=excluir&id=<?= $modalidade['id_modalidade'] ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Deseja realmente excluir esta modalidade?');">
                                    Excluir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </main>

    <!-- Rodapé -->
    <?php include '../includes/footer.php'; ?>

</body>

</html>