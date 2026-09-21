<?php
// view/cadastrar_usuario.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário - Dojify</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <script src="../assets/js/usuario.js" defer></script>
</head>
<body>

    <!-- Navbar Global -->
    <header class="navbar">
        <div class="navbar-brand">
            <a href="../index.php" class="logo-link">
                <img src="../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div>
                    <h1>Dojify</h1>
                </div>
            </a>
        </div>
        <div class="navbar-user">
            <a href="../index.php" class="btn btn-sm">Início</a>
            <a href="../view/login.php" class="btn btn-sm btn-success">Login</a>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container" style="flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px;">
        
        <form action="../controller/UsuarioController.php" method="POST" style="max-width: 500px; width: 100%;">
            
            <div class="text-center" style="margin-bottom: 20px;">
                <img src="../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 90px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Cadastrar Usuário</h2>
            </div>

            <input type="hidden" name="acao" value="cadastrar">
            
            <!-- Perfil (3 = Professor, 4 = Aluno conforme tabela perfil do SQL) -->
            <label for="perfil_id">Perfil</label>
            <select name="perfil_id" id="perfil_id" onchange="alternarCampos(this.value)" required>
                <option value="" disabled selected>Selecione o perfil</option>
                <option value="3">Professor</option>
                <option value="4">Aluno</option>
            </select>

            <!-- Academia (Select Dinâmico baseado na tabela academia) -->
            <label for="id_academia">Academia</label>
            <select name="id_academia" id="id_academia" required>
                <option value="" disabled selected>Selecione a academia</option>
                <?php if (isset($academias) && !empty($academias)): ?>
                    <?php foreach ($academias as $academia): ?>
                        <option value="<?php echo $academia['id_academia']; ?>">
                            <?php echo htmlspecialchars($academia['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="1">A TOKKA - Escola de Lutas (Exemplo)</option>
                <?php endif; ?>
            </select>

            <label for="nome">Nome Completo</label>
            <input type="text" id="nome" name="nome" placeholder="Ex: Carlos Silva" required>

            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" placeholder="Apenas números" required>

            <label for="data_nascimento">Data de Nascimento</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required>

            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" placeholder="exemplo@email.com" required>

            <label for="senha">Senha Inicial</label>
            <input type="password" id="senha" name="senha" placeholder="••••••••" required>

            <!-- Secção Dinâmica para Professor -->
            <div id="campos-professor" style="display: none; margin-top: 15px; border-top: 1px solid var(--border-color); padding-top: 15px;">
                <h3>Dados do Professor</h3>
                
                <label for="especialidade">Especialidade / Arte Marcial</label>
                <input type="text" id="especialidade" name="especialidade" placeholder="Ex: Jiu-Jitsu">

                <label for="data_admissao">Data de Admissão</label>
                <input type="date" id="data_admissao" name="data_admissao">
            </div>

            <!-- Secção Dinâmica para Aluno -->
            <div id="campos-aluno" style="display: none; margin-top: 15px; border-top: 1px solid var(--border-color); padding-top: 15px;">
                <h3>Dados do Aluno</h3>
                
                <label for="data_matricula">Data da Matrícula</label>
                <input type="date" id="data_matricula" name="data_matricula">

                <label for="responsavel">Responsável Legal (se menor)</label>
                <input type="text" id="responsavel" name="responsavel" placeholder="Nome do responsável">

                <label for="observacao">Observações (Médicas / Gerais)</label>
                <textarea id="observacao" name="observacao" rows="3" placeholder="Restrições médicas, lesões..." style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-primary);"></textarea>
            </div>

            <!-- Botão de Submissão -->
            <button type="submit" style="margin-top: 20px;">Salvar Cadastro</button>

            <!-- Botão Voltar -->
            <a href="../index.php" class="btn" style="width: 100%; margin-top: 10px; background-color: transparent; border: 1px solid var(--border-color); color: var(--text-primary) !important; text-align: center; display: block; text-decoration: none;">
                Voltar
            </a>

        </form>
    </main>

    <!-- Rodapé -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

</body>
</html>