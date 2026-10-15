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

$modalidadeDAO = new ModalidadeDAO();
$modalidades =$modalidadeDAO->listarPorAcademia($idAcademia);$opcoesPlano = [
    'Mensal' => [
        'Mensal 2x/semana' => 'Mensal (2x/semana)',
        'Mensal 3x/semana' => 'Mensal (3x/semana)',
        'Mensal Ilimitado' => 'Mensal (Ilimitado)',
    ],
    'Trimestral' => [
        'Trimestral 2x/semana' => 'Trimestral (2x/semana)',
        'Trimestral 3x/semana' => 'Trimestral (3x/semana)',
        'Trimestral Ilimitado' => 'Trimestral (Ilimitado)',
    ],
    'Anual' => [
        'Anual 2x/semana' => 'Anual (2x/semana)',
        'Anual 3x/semana' => 'Anual (3x/semana)',
        'Anual Ilimitado' => 'Anual (Ilimitado)',
    ],
    'All Inclusive' => [
        'All Inclusive 2x/semana' => 'All Inclusive (2x/semana)',
        'All Inclusive 4x/semana' => 'All Inclusive (4x/semana)',
        'All Inclusive Ilimitado' => 'All Inclusive (Ilimitado)',
    ],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Utilizador - Dojify</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilo Global Dojify -->
    <link rel="stylesheet" href="../../assets/css/estilo.css?v=<?= filemtime(__DIR__ . '/../../assets/css/estilo.css'); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <!-- Container para alertas de erro enviados via URL -->
                <div id="alerta-container"></div>

                <header class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="listar_usuarios.php" class="btn btn-dark btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Lista de Utilizadores
                        </a>
                    </div>
                </header>

                <section class="card shadow-sm border-0 p-4">
                    
                    <div class="mb-4">
                        <h2 class="fs-4 fw-bold">Cadastrar Novo Utilizador</h2>
                        <p class="text-muted">Cadastrar novos professores e alunos na sua academia.</p>
                    </div>

                    <form id="form-cadastro" action="../../controller/UsuarioController.php?acao=cadastrar_aluno" method="POST" data-cep-form autocomplete="off" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                        
                        <div class="row g-3">
                            <!-- SELETOR DO TIPO DE UTILIZADOR -->
                            <div class="col-12">
                                <label for="tipo_usuario" class="form-label">Tipo de Cadastro: <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                    <option value="aluno" selected>Aluno</option>
                                    <option value="professor">Professor</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <hr class="text-muted my-2">
                            </div>

                            <!-- ================= CAMPOS COMUNS ================= -->
                            <div class="col-12">
                                <label for="nome" class="form-label">Nome Completo: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: João da Silva" required>
                            </div>

                            <div class="col-md-6">
                                <label for="cpf" class="form-label">CPF: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telefone" class="form-label">Telefone: <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" inputmode="numeric" maxlength="15" pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}" placeholder="(61) 98888-7777" required>
                            </div>

                            <div class="col-md-6">
                                <label for="data_nascimento" class="form-label">Data de Nascimento: <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="sexo" class="form-label">Sexo: <span class="text-danger">*</span></label>
                                <select class="form-select" id="sexo" name="sexo" required>
                                    <option value="">Selecione...</option>
                                    <option value="Feminino">Feminino</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Outro">Outro</option>
                                    <option value="Prefiro não informar">Prefiro não informar</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">E-mail de Acesso: <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="exemplo@email.com" required>
                            </div>

                            <div class="col-md-4">
                                <label for="cep" class="form-label">CEP: <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="cep" name="cep" inputmode="numeric" autocomplete="postal-code" maxlength="9" pattern="[0-9]{5}-?[0-9]{3}" placeholder="00000-000" required>
                                    <button type="button" class="btn btn-outline-secondary" data-cep-lookup>Buscar</button>
                                </div>
                                <p id="cep-status" class="form-text text-muted mb-0" aria-live="polite"></p>
                            </div>

                            <div class="col-md-8">
                                <label for="endereco" class="form-label">Endereço: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="endereco" name="endereco" autocomplete="street-address" maxlength="100" required>
                            </div>

                            <div class="col-md-8">
                                <label for="cidade" class="form-label">Cidade: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cidade" name="cidade" autocomplete="address-level2" maxlength="45" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="estado" class="form-label">Estado (UF): <span class="text-danger">*</span></label>
                                <select class="form-select" id="estado" name="estado" autocomplete="off" required>
                                    <option value="" selected>Selecione...</option>
                                    <option value="AC">AC</option><option value="AL">AL</option>
                                    <option value="AP">AP</option><option value="AM">AM</option>
                                    <option value="BA">BA</option><option value="CE">CE</option>
                                    <option value="DF">DF</option><option value="ES">ES</option>
                                    <option value="GO">GO</option><option value="MA">MA</option>
                                    <option value="MT">MT</option><option value="MS">MS</option>
                                    <option value="MG">MG</option><option value="PA">PA</option>
                                    <option value="PB">PB</option><option value="PR">PR</option>
                                    <option value="PE">PE</option><option value="PI">PI</option>
                                    <option value="RJ">RJ</option><option value="RN">RN</option>
                                    <option value="RS">RS</option><option value="RO">RO</option>
                                    <option value="RR">RR</option><option value="SC">SC</option>
                                    <option value="SP">SP</option><option value="SE">SE</option>
                                    <option value="TO">TO</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="senha" class="form-label">Senha temporária de Acesso:</label>
                                <input type="password" class="form-control bg-info-subtle text-primary fw-bold border-info-subtle" id="senha" value="aluno123" readonly>
                            </div>
                        </div>

                        <!-- ================= CAMPOS ESPECÍFICOS DE ALUNO ================= -->
                        <div id="secao-aluno" class="secao-especifica mt-4">
                            <h3 class="fs-5 mb-3 text-primary border-bottom pb-2">Informações do Aluno</h3>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="data_matricula" class="form-label">Data da Matrícula:</label>
                                    <input type="date" class="form-control" id="data_matricula" name="data_matricula" value="<?= date('Y-m-d'); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label for="responsavel" class="form-label">Responsável Legal (Caso menor):</label>
                                    <input type="text" class="form-control" id="responsavel" name="responsavel" placeholder="Nome do pai ou responsável">
                                </div>

                                <div class="col-12">
                                    <label for="observacao" class="form-label">Observações Médicas / Alergias:</label>
                                    <textarea class="form-control" id="observacao" name="observacao" rows="2" placeholder="Ex: Alérgico a dipirona, lesão antiga no joelho..."></textarea>
                                </div> 

                                <!-- Modalidades Inline com Bootstrap Grid -->
                                <?php for ($numeroModalidade = 1; $numeroModalidade <= 3; $numeroModalidade++): ?>
                                    <div class="col-md-4">
                                        <label for="id_modalidade_<?= $numeroModalidade; ?>" class="form-label">
                                            Modalidade <?= $numeroModalidade; ?><?=$numeroModalidade === 1 ? ' <span class="text-danger">*</span>' : ' (opcional)'; ?>
                                        </label>
                                        <select class="form-select" id="id_modalidade_<?= $numeroModalidade; ?>" name="id_modalidades[]" <?= $numeroModalidade === 1 ? 'required' : ''; ?>>
                                            <option value="">Selecione...</option>
                                            <?php foreach ($modalidades as$modalidade): ?>
                                                <option value="<?= (int)$modalidade['id_modalidade']; ?>">
                                                    <?= htmlspecialchars($modalidade['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endfor; ?>

                                <div class="col-12 mt-4">
                                    <p class="form-text text-muted mb-3">Configure um plano e valor para cada modalidade selecionada.</p>
                                    
                                    <?php for ($numeroModalidade = 1; $numeroModalidade <= 3; $numeroModalidade++): ?>
                                        <?php $indiceModalidade =$numeroModalidade - 1; ?>
                                        <div class="plano-modalidade p-3 border rounded mb-3 bg-light" data-plano-modalidade="<?= $indiceModalidade; ?>" hidden>
                                            <h4 class="h6 fw-bold mb-3">Plano da Modalidade <?= $numeroModalidade; ?></h4>
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label for="nome_plano_<?= $numeroModalidade; ?>" class="form-label">Plano Contratado:</label>
                                                    <select class="form-select" id="nome_plano_<?= $numeroModalidade; ?>" name="planos[<?= $indiceModalidade; ?>][nome_plano]" disabled>
                                                        <option value="">Selecione o plano...</option>
                                                        <?php foreach ($opcoesPlano as $grupoPlano =>$opcoes): ?>
                                                            <optgroup label="Planos <?= htmlspecialchars($grupoPlano, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?php foreach ($opcoes as $valorPlanoOpcao =>$rotuloPlano): ?>
                                                                    <option value="<?= htmlspecialchars($valorPlanoOpcao, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <?= htmlspecialchars($rotuloPlano, ENT_QUOTES, 'UTF-8'); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </optgroup>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="valor_plano_<?= $numeroModalidade; ?>" class="form-label">Valor (R$):</label>
                                                    <input type="number" class="form-control" step="0.01" min="0" id="valor_plano_<?= $numeroModalidade; ?>" name="planos[<?= $indiceModalidade; ?>][valor]" placeholder="Ex: 150.00" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

                        <!-- ================= CAMPOS ESPECÍFICOS DE PROFESSOR ================= -->
                        <div id="secao-professor" class="secao-especifica mt-4 d-none">
                            <h3 class="fs-5 mb-3 text-primary border-bottom pb-2">Informações do Professor</h3>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="especialidade" class="form-label">Especialidade / Arte Marcial: <span class="text-danger">*</span></label>
                                    <select class="form-select" id="especialidade" name="especialidade">
                                        <option value="">Selecione a especialidade...</option>
                                        <?php foreach ($modalidades as$mod): ?>
                                            <option value="<?= htmlspecialchars($mod['nome']); ?>"><?= htmlspecialchars($mod['nome']); ?></option>
                                        <?php endforeach; ?>
                                        <option value="Outras">Outras</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="data_admissao" class="form-label">Data de Admissão: <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="data_admissao" name="data_admissao" value="<?= date('Y-m-d'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- BOTÕES DE AÇÃO -->
                        <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                            <a href="listar_usuarios.php" class="btn btn-secondary px-4 m-0 w-auto">Cancelar</a>
                            <button type="submit" class="btn btn-success px-4 m-0 w-auto" id="btn-submit">
                                <i class="bi bi-check-circle me-1"></i> Concluir Matrícula
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/cep.js" defer></script>

    <!-- SCRIPT PARA ALTERNÂNCIA DINÂMICA DE CAMPOS E VALIDAÇÃO -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const selectTipo = document.getElementById('tipo_usuario');
            const secaoAluno = document.getElementById('secao-aluno');
            const secaoProfessor = document.getElementById('secao-professor');
            const form = document.getElementById('form-cadastro');
            const btnSubmit = document.getElementById('btn-submit');
            const senhaTemporaria = document.getElementById('senha');
            const telefone = document.getElementById('telefone');

            telefone.addEventListener('input', function () {
                const posicaoOriginal = telefone.selectionStart ?? telefone.value.length;
                const digitosAntesDoCursor = telefone.value.slice(0, posicaoOriginal).replace(/\D/g, '').length;
                const digitos = telefone.value.replace(/\D/g, '').slice(0, 11);
                const tamanhoNumero = digitos.length > 10 ? 5 : 4;
                let telefoneFormatado = digitos ? '(' + digitos.slice(0, 2) : '';

                if (digitos.length > 2) {
                    telefoneFormatado += ') ' + digitos.slice(2, 2 + tamanhoNumero);
                }
                if (digitos.length > 2 + tamanhoNumero) {
                    telefoneFormatado += '-' + digitos.slice(2 + tamanhoNumero);
                }

                telefone.value = telefoneFormatado;
                let novaPosicao = 0;
                let digitosContados = 0;
                while (novaPosicao < telefoneFormatado.length && digitosContados < digitosAntesDoCursor) {
                    if (/\d/.test(telefoneFormatado[novaPosicao])) {
                        digitosContados++;
                    }
                    novaPosicao++;
                }
                telefone.setSelectionRange(novaPosicao, novaPosicao);
            });

            const camposAluno = [
                ...document.querySelectorAll('[name="id_modalidades[]"]'),
                ...document.querySelectorAll('[name^="planos["]')
            ];
            const camposProfessor = [
                document.getElementById('especialidade')
            ];
            const selectsModalidade = [...document.querySelectorAll('[name="id_modalidades[]"]')];
            const camposPlanoPorModalidade = [...document.querySelectorAll('[data-plano-modalidade]')];

            function atualizarModalidadesDisponiveis() {
                selectsModalidade.forEach(select => {
                    const selecionadasEmOutros = new Set(
                        selectsModalidade
                            .filter(outroSelect => outroSelect !== select)
                            .map(outroSelect => outroSelect.value)
                            .filter(Boolean)
                    );

                    Array.from(select.options).forEach(opcao => {
                        opcao.hidden = opcao.value !== '' && selecionadasEmOutros.has(opcao.value);
                    });
                });
            }

            function alternarFormulario() {
                const tipo = selectTipo.value;
                senhaTemporaria.value = tipo === 'professor' ? 'professor123' : 'aluno123';

                if (tipo === 'aluno') {
                    secaoAluno.classList.remove('d-none');
                    secaoProfessor.classList.add('d-none');
                    
                    form.action = '../../controller/UsuarioController.php?acao=cadastrar_aluno';
                    btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i> Concluir Matrícula';

                    camposProfessor.forEach(campo => {
                        if(campo) campo.removeAttribute('required');
                    });
                    
                    const modalidade1 = document.getElementById('id_modalidade_1');
                    if(modalidade1) modalidade1.setAttribute('required', 'required');

                } else if (tipo === 'professor') {
                    secaoAluno.classList.add('d-none');
                    secaoProfessor.classList.remove('d-none');
                    
                    form.action = '../../controller/UsuarioController.php?acao=cadastrar_professor';
                    btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i> Cadastrar Professor';

                    camposProfessor.forEach(campo => {
                        if(campo) campo.setAttribute('required', 'required');
                    });
                    
                    camposAluno.forEach(campo => {
                        if(campo) campo.removeAttribute('required');
                    });
                }
                atualizarPlanosPorModalidade();
            }

            function atualizarPlanosPorModalidade() {
                camposPlanoPorModalidade.forEach((grupo, indice) => {
                    const modalidadeSelecionada = Boolean(selectsModalidade[indice].value);
                    grupo.hidden = !modalidadeSelecionada;
                    grupo.querySelectorAll('select, input').forEach(campo => {
                        campo.disabled = !modalidadeSelecionada;
                        if (modalidadeSelecionada && selectTipo.value === 'aluno') {
                            campo.setAttribute('required', 'required');
                        } else {
                            campo.removeAttribute('required');
                        }
                    });
                });
            }

            selectsModalidade.forEach(select => {
                select.addEventListener('change', function () {
                    atualizarModalidadesDisponiveis();
                    atualizarPlanosPorModalidade();
                });
            });
            
            atualizarModalidadesDisponiveis();
            selectTipo.addEventListener('change', alternarFormulario);
            alternarFormulario();

            const urlParams = new URLSearchParams(window.location.search);
            const erro = urlParams.get('erro');
            const container = document.getElementById('alerta-container');

            if (erro && container) {
                let mensagem = "Ocorreu um erro ao processar o cadastro.";
                if (erro === 'cpf_duplicado') {
                    mensagem = "⚠️ Atenção: Este CPF já se encontra registado no sistema para outro utilizador!";
                } else if (erro === 'email_duplicado') {
                    mensagem = "⚠️ Atenção: Este e-mail já se encontra registado no sistema. Informe outro e-mail.";
                } else if (erro === 'modalidade_invalida') {
                    mensagem = "⚠️ Selecione até três modalidades diferentes e escolha ao menos uma.";
                } else if (erro === 'plano_invalido') {
                    mensagem = "⚠️ Selecione um plano e informe o valor para cada modalidade escolhida.";
                } else if (erro === 'falha_cadastro') {
                    mensagem = "❌ Erro: Falha técnica ao salvar os dados na base de dados. Tente novamente.";
                }

                const alertaDiv = document.createElement('div');
                alertaDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertaDiv.setAttribute('role', 'alert');
                alertaDiv.innerHTML = `
                    ${mensagem}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                container.appendChild(alertaDiv);
            }
        });
    </script>
    <script src="../../assets/js/cpf.js?v=<?= filemtime(__DIR__ . '/../../assets/js/cpf.js'); ?>"></script>
</body>
</html>