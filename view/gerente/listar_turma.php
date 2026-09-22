```php
<?php
// view/turma/listar_turma.php

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

$dao = new TurmaDAO();

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

    <?php include '../includes/sidebar.php'; ?>

    <main class="conteudo">

        <h1>Turmas</h1>

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

        <a href="cadastrar_turma.php" class="btn btn-success">
            Cadastrar turma
        </a>

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

                            <td>
                                <?= htmlspecialchars($turma['nome']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($turma['professor_nome']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($turma['modalidade_nome']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($turma['capacidade']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($turma['nivel']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($turma['status']) ?>
                            </td>

                            <td>

                                <a
                                    href="editar_turma.php?id=<?= $turma['id_turma'] ?>"
                                    class="btn btn-info btn-sm"
                                >
                                    Editar
                                </a>

                                <a
                                    href="../../controller/TurmaController.php?acao=excluir&id=<?= $turma['id_turma'] ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Deseja realmente excluir esta turma?');"
                                >
                                    Excluir
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </main>

</body>

</html>

