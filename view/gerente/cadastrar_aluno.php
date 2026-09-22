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

        <!-- Alerta de erro nativo do seu estilo.css -->
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-erro">
                ⚠️ Não foi possível concluir o registo. Verifique os campos preenchidos ou se o CPF/E-mail já se encontram registados.
            </div>
        <?php endif; ?>

        <form action="../../controller/UsuarioController.php?acao=cadastrar_aluno" method="POST">
            <div class="text-center" style="margin-bottom: 20px;">
                <img src="../../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Registrar Novo Aluno</h2>
            </div>
            
            <div>
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" placeholder="Ex: João da Silva" required>
            </div>

            <div>
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" placeholder="Apenas números" required>
            </div>

            <div>
                <label for="data_nascimento">Data de Nascimento:</label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>
            </div>

            <div>
                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone" placeholder="Ex: (11) 98888-7777" required>
            </div>

            <div>
                <label for="email">E-mail de Acesso:</label>
                <input type="email" id="email" name="email" placeholder="exemplo@email.com" required>
            </div>

            <div>
                <label for="senha">Palavra-passe (Senha):</label>
                <input type="password" id="senha" name="senha" placeholder="Mínimo de 6 caracteres" required>
            </div>

            <div>
                <label for="nome_plano">Plano Contratado:</label>
                <select id="nome_plano" name="nome_plano" required>
                    <option value="">Selecione o plano...</option>
                    <option value="Mensal 2x/semana">Mensal 2x/semana</option>
                    <option value="Mensal 3x/semana">Mensal 3x/semana</option>
                    <option value="Mensal 5x/semana">Mensal 5x/semana</option>
                </select>
            </div>

            <div>
                <label for="valor_plano">Valor do Plano (R$):</label>
                <input type="number" step="0.01" min="0" id="valor_plano" name="valor_plano" placeholder="Ex: 150.00" required>
                <small class="text-muted form-hint">💡 Insira no formato decimal usando <strong>ponto</strong>(Ex: <code>150.00</code>).</small>
            </div>

            <button type="submit">Concluir Matrícula</button>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>