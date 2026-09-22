<!-- view/gerente/cadastrar_aluno.php -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Aluno - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="card form-container">
            <h2>Registar Novo Aluno</h2>
            <form action="../../controller/UsuarioController.php?acao=cadastrar_aluno" method="POST" class="form-cadastro">
                
                <div class="form-group">
                    <label for="nome">Nome Completo:</label>
                    <input type="text" id="nome" name="nome" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="cpf">CPF:</label>
                    <input type="text" id="cpf" name="cpf" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento:</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone:</label>
                    <input type="text" id="telefone" name="telefone" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email">E-mail de Acesso:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="senha">Palavra-passe (Senha):</label>
                    <input type="password" id="senha" name="senha" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="nome_plano">Plano Contratado:</label>
                    <select id="nome_plano" name="nome_plano" class="form-control" required>
                        <option value="">Selecione o plano...</option>
                        <option value="Mensal 2x/semana">Mensal 2x/semana</option>
                        <option value="Mensal 3x/semana">Mensal 3x/semana</option>
                        <option value="Mensal 5x/semana">Mensal 5x/semana</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="valor_plano">Valor do Plano (R$):</label>
                    <input type="number" step="0.01" id="valor_plano" name="valor_plano" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Concluir Matrícula</button>
            </form>
        </div>
    </div>
     <a href="home_gerente.php">Voltar</a>

    <?php include '../includes/footer.php'; ?>
</body>
</html>