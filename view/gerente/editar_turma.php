```php
<?php
// view/turma/editar_turma.php

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

$id = (int) ($_GET['id'] ?? 0);
$idAcademia = (int) $_SESSION['id_academia'];

if ($id <= 0) {
    header('Location: listar_turma.php');
    exit;
}

$dao = new TurmaDAO();

$turma = $dao->buscarPorId($id, $idAcademia);

if (!$turma) {
    header('Location: listar_turma.php?erro=1');
    exit;
}

$professores = $dao->listarProfessoresPorAcademia($idAcademia);
$modalidades = $dao->listarModalidadesPorAcademia($idAcademia);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar Turma - Dojify</title>

    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <main class="conteudo">

        <h1>Editar Turma</h1>

        <form action="../../controller/TurmaController.php" method="POST">

            <input type="hidden" name="acao" value="atualizar">

            <input
                type="hidden"
                name="id_turma"
                value="<?= $turma['id_turma'] ?>"
            >

            <div class="campo">

                <label for="nome">
                    Nome da turma
                </label>

                <input
                    type="text"
                    id="nome"
                    name="nome"
                    maxlength="100"
                    value="<?= htmlspecialchars($turma['nome']) ?>"
                    required
                >

            </div>

            <div class="campo">

                <label for="id_usuario_professor">
                    Professor
                </label>

                <select
                    id="id_usuario_professor"
                    name="id_usuario_professor"
                    required
                >

                    <option value="">
                        Selecione o professor
                    </option>

                    <?php foreach ($professores as $professor): ?>

                        <option
                            value="<?= $professor['id_usuario'] ?>"
                            <?= $professor['id_usuario'] == $turma['id_usuario_professor'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($professor['nome']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="campo">

                <label for="id_modalidade">
                    Modalidade
                </label>

                <select
                    id="id_modalidade"
                    name="id_modalidade"
                    required
                >

                    <option value="">
                        Selecione a modalidade
                    </option>

                    <?php foreach ($modalidades as $modalidade): ?>

                        <option
                            value="<?= $modalidade['id_modalidade'] ?>"
                            <?= $modalidade['id_modalidade'] == $turma['id_modalidade'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($modalidade['nome']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="campo">

                <label for="capacidade">
                    Capacidade
                </label>

                <input
                    type="number"
                    id="capacidade"
                    name="capacidade"
                    min="1"
                    value="<?= htmlspecialchars($turma['capacidade']) ?>"
                    required
                >

            </div>

            <div class="campo">

                <label for="nivel">
                    Nível
                </label>

                <select
                    id="nivel"
                    name="nivel"
                    required
                >

                    <option value="">
                        Selecione o nível
                    </option>

                    <option
                        value="INICIANTE"
                        <?= $turma['nivel'] == 'INICIANTE' ? 'selected' : '' ?>
                    >
                        Iniciante
                    </option>

                    <option
                        value="INTERMEDIARIO"
                        <?= $turma['nivel'] == 'INTERMEDIARIO' ? 'selected' : '' ?>
                    >
                        Intermediário
                    </option>

                    <option
                        value="AVANCADO"
                        <?= $turma['nivel'] == 'AVANCADO' ? 'selected' : '' ?>
                    >
                        Avançado
                    </option>

                </select>

            </div>

            <div class="campo">

                <label for="status">
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                    required
                >

                    <option
                        value="ATIVA"
                        <?= $turma['status'] == 'ATIVA' ? 'selected' : '' ?>
                    >
                        Ativa
                    </option>

                    <option
                        value="INATIVA"
                        <?= $turma['status'] == 'INATIVA' ? 'selected' : '' ?>
                    >
                        Inativa
                    </option>

                </select>

            </div>

            <button type="submit">
                Salvar alterações
            </button>

            <a href="listar_turma.php">
                Cancelar
            </a>

        </form>

    </main>

</body>

</html>
```
