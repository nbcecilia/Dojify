<?php
// view/gerente/cadastrar_aluno_professor.php
session_start();
require_once __DIR__ . '/../../model/dao/Conexao.php';

// Valida se o usuário é gerente (perfil 2)
if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$pdo = Conexao::getConexao();

// Busca as modalidades da academia para usar no select
$stmtModalidades = $pdo->prepare("SELECT id_modalidade, nome FROM modalidade WHERE id_academia = :id_academia");
$stmtModalidades->execute([':id_academia' => $_SESSION['usuario']['id_academia']]);
$modalidades = $stmtModalidades->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <script>
        function alternarCampos(perfilId) {
            const camposProfessor = document.getElementById('campos-professor');
            const camposAluno = document.getElementById('campos-aluno');
            
            if (perfilId === '3') { // Professor
                camposProfessor.style.display = 'block';
                camposAluno.style.display = 'none';
                
                document.getElementById('especialidade').required = true;
                document.getElementById('id_modalidade_aluno').required = false;
                document.getElementById('nome_plano').required = false;
                document.getElementById('valor_plano').required = false;
            } else if (perfilId === '4') { // Aluno
                camposProfessor.style.display = 'none';
                camposAluno.style.display = 'block';
                
                document.getElementById('especialidade').required = false;
                document.getElementById('id_modalidade_aluno').required = true;
                document.getElementById('nome_plano').required = true;
                document.getElementById('valor_plano').required = true;
            } else {
                camposProfessor.style.display = 'none';
                camposAluno.style.display = 'none';
            }
        }
    </script>
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
        <form action="../../controller/UsuarioController.php?acao=cadastrar_usuario_gerente" method="POST">
            <div class="text-center" style="margin-bottom: 20px;">
                <img src="../../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Cadastrar Novo Usuário</h2>
            </div>

            <div>
                <label for="perfil_id">Selecione o Perfil:</label>
                <select id="perfil_id" name="perfil_id" onchange="alternarCampos(this.value)" required>
                    <option value="">Selecione o perfil...</option>
                    <option value="3">Professor</option>
                    <option value="4">Aluno</option>
                </select>
            </div>
            
            <div>
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" placeholder="Ex: João da Silva" required>
            </div>

            <div>
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" placeholder="Apenas números" maxlength="11" required>
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
                <label for="senha">Senha temporária de Acesso:</label>
                <input type="password" id="senha" name="senha" placeholder="Mínimo de 6 caracteres" required>
            </div>

            <!-- Campos específicos para Professor (Perfil 3) -->
            <div id="campos-professor" style="display: none; border-top: 1px solid var(--border-color); margin-top: 20px; padding-top: 15px;">
                <h3>Informações do Professor</h3>
                <div>
                    <label for="especialidade">Especialidade / Arte Marcial:</label>
                    <select id="especialidade" name="especialidade">
                        <option value="">Selecione a especialidade...</option>
                        <?php foreach ($modalidades as $mod): ?>
                            <option value="<?= htmlspecialchars($mod['nome']); ?>"><?= htmlspecialchars($mod['nome']); ?></option>
                        <?php endforeach; ?>
                        <option value="Outras">Outras</option>
                    </select>
                </div>

                <div>
                    <label for="data_admissao">Data de Admissão:</label>
                    <input type="date" id="data_admissao" name="data_admissao" value="<?= date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- Campos específicos para Aluno (Perfil 4) -->
            <div id="campos-aluno" style="display: none; border-top: 1px solid var(--border-color); margin-top: 20px; padding-top: 15px;">
                <h3>Informações de Matrícula e Plano</h3>
                
                <div>
                    <label for="data_matricula">Data da Matrícula:</label>
                    <input type="date" id="data_matricula" name="data_matricula" value="<?= date('Y-m-d'); ?>">
                </div>

                <div>
                    <label for="responsavel">Responsável Legal (Caso seja menor):</label>
                    <input type="text" id="responsavel" name="responsavel" placeholder="Nome do pai ou responsável">
                </div>

                <div>
                    <label for="observacao">Observações Médicas / Alergias:</label>
                    <textarea id="observacao" name="observacao" placeholder="Ex: Alérgico a dipirona, lesão antiga no joelho..."></textarea>
                </div> 

                <div>
                    <label for="id_modalidade_aluno">Modalidade Principal:</label>
                    <select id="id_modalidade_aluno" name="id_modalidade">
                        <option value="">Selecione a modalidade...</option>
                        <?php foreach ($modalidades as $mod): ?>
                            <option value="<?= $mod['id_modalidade']; ?>"><?= htmlspecialchars($mod['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="nome_plano">Plano Contratado:</label>
                    <select id="nome_plano" name="nome_plano">
                        <option value="">Selecione o plano...</option>
                        <optgroup label="Planos Mensais">
                            <option value="Mensal 2x/semana">Mensal (2x/semana)</option>
                            <option value="Mensal 3x/semana">Mensal (3x/semana)</option>
                            <option value="Mensal Ilimitado">Mensal (Ilimitado)</option>
                        </optgroup>
                        <optgroup label="Planos Trimestrais">
                            <option value="Trimestral 2x/semana">Trimestral (2x/semana)</option>
                            <option value="Trimestral 3x/semana">Trimestral (3x/semana)</option>
                            <option value="Trimestral Ilimitado">Trimestral (Ilimitado)</option>
                        </optgroup>
                        <optgroup label="Planos Anuais">
                            <option value="Anual 2x/semana">Anual (2x/semana)</option>
                            <option value="Anual 3x/semana">Anual (3x/semana)</option>
                            <option value="Anual Ilimitado">Anual (Ilimitado)</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label for="valor_plano">Valor do Plano (R$):</label>
                    <input type="number" step="0.01" min="0" id="valor_plano" name="valor_plano" placeholder="Ex: 150.00">
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <a href="listar_usuarios.php" class="btn" style="flex: 1; text-align: center; background-color: var(--border-color); color: var(--text-primary) !important; text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancelar</a>
                <button type="submit" style="margin-top: 0; flex: 1;">Concluir Cadastro</button>
            </div>
        </form>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
</body>
</html>