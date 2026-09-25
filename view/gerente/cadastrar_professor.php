<?php
// view/gerente/cadastrar_professor.php
session_start();
require_once __DIR__ . '/../../model/dao/Conexao.php';

// Valida se o usuário é gerente (perfil 2)
if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$pdo = Conexao::getConexao();

// Busca as modalidades da academia para associar à especialidade/foco do professor, se necessário
$stmtModalidades =$pdo->prepare("SELECT id_modalidade, nome FROM modalidade WHERE id_academia = :id_academia");
$stmtModalidades->execute([':id_academia' =>$_SESSION['usuario']['id_academia']]);
$modalidades =$stmtModalidades->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Professor - Dojify</title>
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
        
        <!-- Container onde o JavaScript vai injetar o alerta de erro se necessário -->
        <div id="alerta-container"></div>

        <form action="../../controller/UsuarioController.php?acao=cadastrar_professor" method="POST">
             <div class="text-center" style="margin-bottom: 20px;">
                <img src="../../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Cadastrar Novo Professor</h2>
            </div>
            <div>
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" placeholder="Ex: Mestre Carlos" required>
            </div>

            <div>
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" placeholder="Somente números" maxlength="11" required>
            </div>

            <div>
                <label for="data_nascimento">Data de Nascimento:</label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>
            </div>

            <div>
                <label for="email">E-mail de Acesso:</label>
                <input type="email" id="email" name="email" placeholder="professor@email.com" required>
            </div>

            <div>
                <label for="senha">Senha temporária de Acesso:</label>
                <input type="password" id="senha" name="senha" placeholder="Mínimo de 6 caracteres" required>
            </div>

            <div>
                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
            </div>

            <div>
                <label for="especialidade">Especialidade / Arte Marcial:</label>
                <select id="especialidade" name="especialidade" required>
                    <option value="">Selecione a especialidade...</option>
                    <?php foreach ($modalidades as$mod): ?>
                        <option value="<?= htmlspecialchars($mod['nome']); ?>"><?= htmlspecialchars($mod['nome']); ?></option>
                    <?php endforeach; ?>
                    <option value="Outras">Outras</option>
                </select>
            </div>

            <div>
                <label for="data_admissao">Data de Admissão:</label>
                <input type="date" id="data_admissao" name="data_admissao" value="<?= date('Y-m-d'); ?>" required>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <a href="listar_usuarios.php" class="btn" style="flex: 1; text-align: center; background-color: var(--border-color); color: var(--text-primary) !important; text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancelar</a>
                <button type="submit" style="margin-top: 0; flex: 1;">Cadastrar Professor</button>
            </div>
        </form>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const erro = urlParams.get('erro');
            const container = document.getElementById('alerta-container');

            if (erro && container) {
                let mensagem = "Ocorreu um erro ao processar o cadastro.";
                
                if (erro === 'cpf_duplicado') {
                    mensagem = "⚠️ **Atenção:** Este CPF já se encontra registado no sistema para outro utilizador!";
                } else if (erro === 'falha_cadastro') {
                    mensagem = "❌ **Erro:** Falha técnica ao salvar os dados na base de dados. Tente novamente.";
                }

                // Cria o elemento visual do alerta com estilos amigáveis
                const alertaDiv = document.createElement('div');
                alertaDiv.style.cssText = "background-color: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 6px; margin-bottom: 20px; font-family: inherit; font-size: 14px; display: flex; justify-content: space-between; align-items: center;";
                
                alertaDiv.innerHTML = `
                    <span>${mensagem}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #721c24;">&times;</button>
                `;

                container.appendChild(alertaDiv);
            }
        });
    </script>
</body>
</html>