<?php
// view/gerente/cadastrar_aluno_prof.php
session_start();
require_once __DIR__ . '/../../model/dao/Conexao.php';
require_once __DIR__ . '/../../model/dao/ModalidadeDAO.php';

// Valida se o utilizador está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$usuario =$_SESSION['usuario'];

// Valida se o utilizador é gerente (perfil 2)
if ((int)$usuario['perfil_id'] !== 2) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

$idAcademia = isset($usuario['id_academia']) ? (int)$usuario['id_academia'] : 0;

$modalidadeDAO = new ModalidadeDAO();$modalidades = $modalidadeDAO->listarPorAcademia($idAcademia);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Utilizador - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <!-- Container para alertas de erro enviados via URL -->
        <div id="alerta-container"></div>

        <!-- O action inicial aponta para aluno como padrão, mas o JS vai alterar dinamicamente -->
        <form id="form-cadastro" action="../../controller/UsuarioController.php?acao=cadastrar_aluno" method="POST">
            
            <div class="text-center" style="margin-bottom: 20px;">
                <img src="../../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Cadastrar Novo Utilizador</h2>
            </div>

            <!-- SELETOR DO TIPO DE UTILIZADOR -->
            <div>
                <label for="tipo_usuario">Tipo de Cadastro:</label>
                <select id="tipo_usuario" name="tipo_usuario" required>
                    <option value="aluno" selected>Aluno</option>
                    <option value="professor">Professor</option>
                </select>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

            <!-- ================= CAMPOS COMUNS ================= -->
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
                <input type="text" id="telefone" name="telefone"  pattern="\([0-9]{2}\) [0-9]{5}-[0-9]{4}" 
        maxlength="15 placeholder="Ex: (61) 98888-7777" required>
            </div>

            <div>
                <label for="email">E-mail de Acesso:</label>
                <input type="email" id="email" name="email" placeholder="exemplo@email.com" required>
            </div>

            <div>
                <label for="senha">Senha temporária de Acesso:</label>
                <input type="password" id="senha" name="senha" placeholder="Mínimo de 6 caracteres" required>
            </div>


            <!-- ================= CAMPOS ESPECÍFICOS DE ALUNO ================= -->
            <div id="secao-aluno" class="secao-especifica">
                <hr style="border: 0; border-top: 1px dashed var(--border-color); margin: 20px 0;">
                <h3 style="margin-bottom: 15px; font-size: 1.1rem; color: var(--text-primary);">Informações do Aluno</h3>

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
                    <label for="id_modalidade">Modalidade:</label>
                    <select id="id_modalidade" name="id_modalidade">
                        <option value="">Selecione a modalidade...</option>
                        <?php foreach ($modalidades as$modalidade): ?>
                            <option value="<?= (int)$modalidade['id_modalidade']; ?>">
                                <?= htmlspecialchars($modalidade['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
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
                        <optgroup label="Planos All Inclusive">
                            <option value="All Inclusive 2x/semana">All Inclusive (2x/semana)</option>
                            <option value="All Inclusive 4x/semana">All Inclusive (4x/semana)</option>
                            <option value="All Inclusive Ilimitado">All Inclusive (Ilimitado)</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label for="valor_plano">Valor do Plano (R$):</label>
                    <input type="number" step="0.01" min="0" id="valor_plano" name="valor_plano" placeholder="Ex: 150.00">
                </div>
            </div>


            <!-- ================= CAMPOS ESPECÍFICOS DE PROFESSOR ================= -->
            <div id="secao-professor" class="secao-especifica" style="display: none;">
                <hr style="border: 0; border-top: 1px dashed var(--border-color); margin: 20px 0;">
                <h3 style="margin-bottom: 15px; font-size: 1.1rem; color: var(--text-primary);">Informações do Professor</h3>

                <div>
                    <label for="especialidade">Especialidade / Arte Marcial:</label>
                    <select id="especialidade" name="especialidade">
                        <option value="">Selecione a especialidade...</option>
                        <?php foreach ($modalidades as$mod): ?>
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

            <!-- BOTÕES DE AÇÃO -->
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <a href="listar_usuarios.php" class="btn" style="flex: 1; text-align: center; background-color: var(--border-color); color: var(--text-primary) !important; text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancelar</a>
                <button type="submit" style="margin-top: 0; flex: 1;" id="btn-submit">Concluir Matrícula</button>
            </div>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- SCRIPT PARA ALTERNÂNCIA DINÂMICA DE CAMPOS E VALIDAÇÃO -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const selectTipo = document.getElementById('tipo_usuario');
            const secaoAluno = document.getElementById('secao-aluno');
            const secaoProfessor = document.getElementById('secao-professor');
            const form = document.getElementById('form-cadastro');
            const btnSubmit = document.getElementById('btn-submit');

            const camposAluno = [
                document.getElementById('id_modalidade'),
                document.getElementById('nome_plano'),
                document.getElementById('valor_plano')
            ];
            const camposProfessor = [
                document.getElementById('especialidade')
            ];

            function alternarFormulario() {
                const tipo = selectTipo.value;

                if (tipo === 'aluno') {
                    secaoAluno.style.display = 'block';
                    secaoProfessor.style.display = 'none';
                    
                    form.action = '../../controller/UsuarioController.php?acao=cadastrar_aluno';
                    btnSubmit.textContent = 'Concluir Matrícula';

                    camposAluno.forEach(campo => campo.setAttribute('required', 'required'));
                    camposProfessor.forEach(campo => campo.removeAttribute('required'));

                } else if (tipo === 'professor') {
                    secaoAluno.style.display = 'none';
                    secaoProfessor.style.display = 'block';
                    
                    form.action = '../../controller/UsuarioController.php?acao=cadastrar_professor';
                    btnSubmit.textContent = 'Cadastrar Professor';

                    camposProfessor.forEach(campo => campo.setAttribute('required', 'required'));
                    camposAluno.forEach(campo => campo.removeAttribute('required'));
                }
            }

            selectTipo.addEventListener('change', alternarFormulario);
            alternarFormulario();

            const urlParams = new URLSearchParams(window.location.search);
            const erro = urlParams.get('erro');
            const container = document.getElementById('alerta-container');

            if (erro && container) {
                let mensagem = "Ocorreu um erro ao processar o cadastro.";
                if (erro === 'cpf_duplicado') {
                    mensagem = "⚠️ Atenção: Este CPF já se encontra registado no sistema para outro utilizador!";
                } else if (erro === 'falha_cadastro') {
                    mensagem = "❌ Erro: Falha técnica ao salvar os dados na base de dados. Tente novamente.";
                }

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