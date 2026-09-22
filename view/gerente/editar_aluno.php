<?php
// view/gerente/editar_aluno.php
session_start();
require_once __DIR__ . '/../../model/dao/Conexao.php';

// Valida se o usuário é gerente (perfil 2)
if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idUsuario) {
    header('Location: listar_usuarios.php?erro=id_invalido');
    exit;
}

$pdo = Conexao::getConexao();

// Busca os dados do aluno e o seu plano ativo (usando id_usuario_aluno conforme o teu SQL)
$sql = "SELECT u.*, p.id_plano, p.nome_plano, p.valor, p.status as status_plano 
        FROM usuario u 
        LEFT JOIN plano p ON u.id_usuario = p.id_usuario_aluno AND p.status = 'ATIVO' 
        WHERE u.id_usuario = :id AND u.id_academia = :id_academia";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $idUsuario,
    ':id_academia' => $_SESSION['usuario']['id_academia']
]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    header('Location: listar_usuarios.php?erro=nao_encontrado');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aluno - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar-brand">
            <a href="home_gerente.php" class="logo-link">
                <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div>
                    <h1>Dojify</h1>
                </div>
            </a>
        </div>
        
        <div class="navbar-user">
            <span class="user-greeting">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></span>
            <a href="listar_usuarios.php" class="btn btn-sm">Voltar</a>
            <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <div class="container">
       

        <form action="../../controller/UsuarioController.php?acao=atualizar_aluno" method="POST">
             <h2>Editar Dados do Aluno</h2>
            <!-- ID Oculto do Aluno -->
            <input type="hidden" name="id_usuario" value="<?php echo $aluno['id_usuario']; ?>">
            <!-- ID Oculto do Plano (caso exista para atualização) -->
            <input type="hidden" name="id_plano" value="<?php echo $aluno['id_plano'] ?? ''; ?>">

            <label for="nome">Nome Completo:</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($aluno['nome']); ?>" required>

            <label for="cpf">CPF (Não editável):</label>
            <input type="text" id="cpf" value="<?php echo htmlspecialchars($aluno['cpf']); ?>" disabled>

            <label for="data_nascimento">Data de Nascimento (Não editável):</label>
            <input type="date" id="data_nascimento" value="<?php echo htmlspecialchars($aluno['data_nascimento']); ?>" disabled>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($aluno['email']); ?>" required>

            <label for="telefone">Telefone / WhatsApp:</label>
            <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($aluno['telefone']); ?>">

            <label for="responsavel">Responsável (Caso seja menor de idade):</label>
            <input type="text" id="responsavel" name="responsavel" value="<?php echo htmlspecialchars($aluno['responsavel'] ?? ''); ?>">
            
            <label for="observacao">Observações:</label>
            <textarea id="observacao" name="observacao" rows="3"><?php echo htmlspecialchars($aluno['observacao'] ?? ''); ?></textarea>

            <hr style="margin: 24px 0; border: none; border-top: 1px solid var(--border-color);">
            <h3 style="font-size: 1rem; margin-bottom: 4px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">Informações do Plano</h3>

            <label for="nome_plano">Plano Contratado:</label>
            <select id="nome_plano" name="nome_plano" required>
                <option value="">Selecione o plano...</option>
                
                <optgroup label="Planos Mensais">
                    <option value="Mensal 2x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Mensal 2x/semana') ? 'selected' : ''; ?>>Mensal (2x/semana)</option>
                    <option value="Mensal 3x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Mensal 3x/semana') ? 'selected' : ''; ?>>Mensal (3x/semana)</option>
                    <option value="Mensal Ilimitado" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Mensal Ilimitado') ? 'selected' : ''; ?>>Mensal (Ilimitado)</option>
                </optgroup>

                <optgroup label="Planos Trimestrais">
                    <option value="Trimestral 2x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Trimestral 2x/semana') ? 'selected' : ''; ?>>Trimestral (2x/semana)</option>
                    <option value="Trimestral 3x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Trimestral 3x/semana') ? 'selected' : ''; ?>>Trimestral (3x/semana)</option>
                    <option value="Trimestral Ilimitado" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Trimestral Ilimitado') ? 'selected' : ''; ?>>Trimestral (Ilimitado)</option>
                </optgroup>

                <optgroup label="Planos Anuais">
                    <option value="Anual 2x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Anual 2x/semana') ? 'selected' : ''; ?>>Anual (2x/semana)</option>
                    <option value="Anual 3x/semana" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Anual 3x/semana') ? 'selected' : ''; ?>>Anual (3x/semana)</option>
                    <option value="Anual Ilimitado" <?php echo (isset($aluno['nome_plano']) && $aluno['nome_plano'] === 'Anual Ilimitado') ? 'selected' : ''; ?>>Anual (Ilimitado)</option>
                </optgroup>
            </select>

            <label for="valor_plano">Valor do Plano (R$):</label>
            <input type="text" id="valor_plano" name="valor_plano" value="<?php echo isset($aluno['valor']) ? number_format($aluno['valor'], 2, ',', '') : ''; ?>" placeholder="0,00">

            <label for="status">Status do Aluno:</label>
            <select id="status" name="status" required>
                <option value="ATIVO" <?php echo ($aluno['status'] === 'ATIVO') ? 'selected' : ''; ?>>Ativo</option>
                <option value="INATIVO" <?php echo ($aluno['status'] === 'INATIVO') ? 'selected' : ''; ?>>Inativo</option>
                <option value="SUSPENSO" <?php echo ($aluno['status'] === 'SUSPENSO') ? 'selected' : ''; ?>>Suspenso</option>
            </select>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <a href="listar_usuarios.php" class="btn" style="flex: 1; text-align: center; background-color: var(--border-color); color: var(--text-primary) !important; text-decoration: none;">Cancelar</a>
                <button type="submit" style="margin-top: 0; flex: 1;">Salvar Alterações</button>
            </div>
        </form>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>