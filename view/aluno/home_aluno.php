<?php
// view/aluno/home_aluno.php
date_default_timezone_set('America/Sao_Paulo');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 4) {
    header('Location: ../../login.php?erro=acesso_negado');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$arquivoConexao = dirname(__DIR__, 2) . '/model/dao/Conexao.php';
if (file_exists($arquivoConexao) && !class_exists('Conexao', false)) {
    require_once $arquivoConexao;
}
require_once dirname(__DIR__, 2) . '/model/dao/AgendamentoDAO.php';

$calendarioSemanal = [];
$meus_agendamentos = [];
$planoAtivoAgenda = null;
$idModalidadePlano = 0;
$limiteSemanalPlano = 0;
$turmas_brutas = [];
$agendamentosPorSemana = [];
$total_agendamentos_futuros = 0;
$notificacoes = [];
$total_notificacoes = 0;
$mensagem_erro = "";
$mensagem_sucesso = "";
$mensagem_modal_agendamento = "";
$tipo_modal_agendamento = 'success';
$is_primeiro_acesso = false;
$id_aluno = $_SESSION['usuario']['id_usuario'];

// Variáveis padrão para o contrato e financeiro
$c_nome = $c_cpf = $c_data_nasc = $c_sexo = $c_responsavel = $c_cpf_resp = $c_email = $c_tel = $c_endereco = $c_cidade = $c_estado = "---";
$c_luta = "Artes Marciais / Ver agenda"; 
$c_plano = "Não especificado";
$c_infoMedica = $c_especial = $c_obs = "---";
$status_pagamento = 'PENDENTE';
$data_vencimento = '---';
$valor_pagamento = '---';
$id_pagamento_atual = null;
$comprovante_pagamento_enviado = false;
$historico_pagamentos = [];
$num_nivel = 1;
$nome_nivel = 'Iniciante';
$xp_atual = 0;
$xp_min = 0;
$xp_max = 150;
$progresso_xp = 0;

if (($_GET['sucesso'] ?? '') === 'comprovante') {
    $mensagem_sucesso = 'Comprovante enviado ao gerente. O pagamento ficará em análise até a conferência.';
} elseif (($_GET['sucesso'] ?? '') === 'agendado') {
    $mensagem_modal_agendamento = 'Treino agendado com sucesso no tatame! 🥋';
} elseif (($_GET['sucesso'] ?? '') === 'cancelado') {
    $mensagem_modal_agendamento = 'Agendamento cancelado com sucesso.';
} elseif (isset($_GET['erro'])) {
    $mensagensErro = [
        'acesso' => 'Você não tem permissão para enviar esse comprovante.',
        'token' => 'Sua sessão expirou. Atualize a página e tente novamente.',
        'comprovante' => 'Selecione um comprovante válido e tente novamente.',
        'tamanho' => 'O comprovante deve ter até 5 MB.',
        'formato' => 'Envie o comprovante em JPG, PNG ou PDF.',
        'servidor' => 'Não foi possível armazenar o comprovante. Tente novamente mais tarde.',
        'pagamento' => 'Este pagamento não está disponível para envio de comprovante.',
        'token_agendamento' => 'Sua sessão expirou. Atualize a página e tente novamente.',
        'acesso_negado' => 'Você não tem permissão para realizar esta ação.',
        'metodo_invalido' => 'Não foi possível processar a solicitação. Atualize a página e tente novamente.',
        'acao_invalida' => 'Ação de agendamento inválida.',
        'dados_invalidos' => 'Os dados da aula são inválidos. Atualize a página e tente novamente.',
        'data_passada' => 'Não é possível agendar uma aula que já começou ou já passou.',
        'agendamento_duplicado' => 'Você já está agendado nesta turma para este dia!',
        'turma_lotada' => 'Turma lotada! Não há vagas disponíveis para este horário.',
        'limite_semanal' => 'Limite semanal do seu plano atingido.',
        'plano_sem_modalidade' => 'Seu plano ainda não está vinculado a uma modalidade. Entre em contato com a academia para atualizar seu cadastro.',
        'modalidade_incompativel' => 'Esta aula não pertence à modalidade do seu plano.',
        'falha_sistema' => 'Não foi possível concluir a operação. Tente novamente mais tarde.'
    ];
    $codigoErro = (string)$_GET['erro'];
    $errosAgendamento = [
        'acesso_negado',
        'metodo_invalido',
        'token_agendamento',
        'acao_invalida',
        'dados_invalidos',
        'data_passada',
        'agendamento_duplicado',
        'turma_lotada',
        'limite_semanal',
        'plano_sem_modalidade',
        'modalidade_incompativel',
        'falha_sistema'
    ];
    if (in_array($codigoErro, $errosAgendamento, true)) {
        $mensagem_modal_agendamento = $mensagensErro[$codigoErro];
        $tipo_modal_agendamento = 'danger';
    } else {
        $mensagem_erro = $mensagensErro[$codigoErro] ?? 'Não foi possível concluir a solicitação. Verifique os dados e tente novamente.';
    }
}

try {
    $pdo_agenda = \Conexao::getConexao();

    // ==============================================================================
    // 1. VERIFICAR PRIMEIRO ACESSO E BUSCAR DADOS PARA O CONTRATO
    // ==============================================================================
    try {
        $stmt_check = $pdo_agenda->prepare("SELECT primeiro_acesso FROM usuario WHERE id_usuario = ?");
        $stmt_check->execute([$id_aluno]);
        $user_data = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data && (int)$user_data['primeiro_acesso'] === 1) {
            $is_primeiro_acesso = true;
            
            $stmt_aluno = $pdo_agenda->prepare("
                SELECT u.nome, u.email, u.cpf, u.data_nascimento, u.telefone, 
                       u.responsavel, u.observacao, p.nome_plano
                FROM usuario u
                LEFT JOIN plano p ON u.id_usuario = p.id_usuario_aluno AND p.status = 'ATIVO'
                WHERE u.id_usuario = ?
                LIMIT 1
            ");
            $stmt_aluno->execute([$id_aluno]);
            $dados_contrato = $stmt_aluno->fetch(PDO::FETCH_ASSOC);

            if ($dados_contrato) {
                $c_nome = $dados_contrato['nome'] ?: "Não informado";
                $c_email = $dados_contrato['email'] ?: "Não informado";
                $cpf_limpo = $dados_contrato['cpf'];
                $c_cpf = (strlen($cpf_limpo) == 11) ? preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cpf_limpo) : "Não informado";
                $c_data_nasc = $dados_contrato['data_nascimento'] ? date('d/m/Y', strtotime($dados_contrato['data_nascimento'])) : "Não informado";
                $c_tel = !empty($dados_contrato['telefone']) ? $dados_contrato['telefone'] : "Não informado";
                $c_responsavel = $dados_contrato['responsavel'] ?: "O próprio";
                $c_obs = $dados_contrato['observacao'] ?: "Nenhuma observação registrada";
                $c_plano = $dados_contrato['nome_plano'] ?: "Plano Base";
                $c_infoMedica = "Vide observações gerais: " . $c_obs;
                $c_especial = "Não";
            }
        }
    } catch (Exception $e) {
        $is_primeiro_acesso = false;
    }

    // ==============================================================================
    // 2. PROCESSAR O FORMULÁRIO DE PRIMEIRO ACESSO (CONTRATO E SENHA)
    // ==============================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_aceite_contrato'])) {
        $senha_nova = $_POST['senha_nova'] ?? '';
        $senha_confirma = $_POST['senha_confirma'] ?? '';
        $aceite_contrato = ($_POST['aceite_contrato'] ?? '') === '1';
        $consentimento_saude = ($_POST['consentimento_saude'] ?? '') === '1';

        if (!$aceite_contrato) {
            $mensagem_erro = "É necessário aceitar o Contrato de Prestação de Serviços para continuar.";
        } elseif (!$consentimento_saude) {
            $mensagem_erro = "É necessário consentir com o tratamento dos dados de saúde informados para continuar.";
        } elseif (empty($senha_nova) || strlen($senha_nova) < 6) {
            $mensagem_erro = "A nova senha deve ter pelo menos 6 caracteres.";
        } elseif ($senha_nova !== $senha_confirma) {
            $mensagem_erro = "As senhas não coincidem. Tente novamente.";
        } else {
            $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
            $stmt_senha = $pdo_agenda->prepare("UPDATE login SET senha_hash = ? WHERE id_usuario = ?");
            $stmt_acesso = $pdo_agenda->prepare("UPDATE usuario SET primeiro_acesso = 0 WHERE id_usuario = ?");
            
            if ($stmt_senha->execute([$senha_hash, $id_aluno]) && $stmt_acesso->execute([$id_aluno])) {
                header("Location: home_aluno.php");
                exit;
            } else {
                $mensagem_erro = "Erro ao guardar as alterações.";
            }
        }
    }

    // ==============================================================================
    // 3. SE NÃO FOR PRIMEIRO ACESSO, CARREGA O PAINEL E DADOS DINÂMICOS
    // ==============================================================================
    if (!$is_primeiro_acesso) {
        
        // Graduação
        $stmt_grad = $pdo_agenda->prepare("SELECT faixa, grau FROM graduacao WHERE id_usuario_aluno = ? ORDER BY data_graduacao DESC LIMIT 1");
        $stmt_grad->execute([$id_aluno]);
        $dados_grad = $stmt_grad->fetch(PDO::FETCH_ASSOC);
        $faixa_aluno = $dados_grad ? $dados_grad['faixa'] : 'Sem Faixa';
        $grau_aluno = $dados_grad && !empty($dados_grad['grau']) ? $dados_grad['grau'] : 'Iniciante';

        // Gamificação (XP baseado em presenças)
        $stmt_xp = $pdo_agenda->prepare("
            SELECT COUNT(p.id_presenca) as total_presencas 
            FROM presenca p 
            JOIN agendamento a ON p.id_agendamento = a.id_agendamento 
            WHERE a.id_usuario_aluno = ? AND p.status = 1
        ");
        $stmt_xp->execute([$id_aluno]);
        $total_presencas = (int)$stmt_xp->fetchColumn();
        $xp_atual = $total_presencas * 15;

        $tabela_niveis = [
            ['nivel' => 1, 'nome' => 'Iniciante', 'min' => 0, 'max' => 150],
            ['nivel' => 2, 'nome' => 'Aprendiz', 'min' => 151, 'max' => 350],
            ['nivel' => 3, 'nome' => 'Lutador', 'min' => 351, 'max' => 700],
            ['nivel' => 4, 'nome' => 'Guerreiro', 'min' => 701, 'max' => 1500],
            ['nivel' => 5, 'nome' => 'Mestre', 'min' => 1501, 'max' => 999999]
        ];

        $num_nivel = 1; $nome_nivel = 'Iniciante'; $xp_min = 0; $xp_max = 150;
        foreach ($tabela_niveis as $n) {
            if ($xp_atual >= $n['min'] && $xp_atual <= $n['max']) {
                $num_nivel = $n['nivel']; $nome_nivel = $n['nome']; $xp_min = $n['min']; $xp_max = $n['max'];
                break;
            }
        }
        
        $progresso_xp = 0;
        if ($xp_max > $xp_min) { $progresso_xp = (($xp_atual - $xp_min) / ($xp_max - $xp_min)) * 100; }
        $progresso_xp = min(100, max(0, $progresso_xp)); 

        // Matrícula / Plano
        $stmt_plano_card = $pdo_agenda->prepare("SELECT id_plano, nome_plano, status FROM plano WHERE id_usuario_aluno = ? ORDER BY data_inicio DESC LIMIT 1");
        $stmt_plano_card->execute([$id_aluno]);
        $dados_plano_card = $stmt_plano_card->fetch(PDO::FETCH_ASSOC);
        $status_plano = $dados_plano_card ? $dados_plano_card['status'] : 'INATIVO';
        $nome_plano_card = $dados_plano_card ? $dados_plano_card['nome_plano'] : 'Nenhum plano ativo';
        $id_plano_atual = $dados_plano_card ? $dados_plano_card['id_plano'] : null;

        // BUSCAR DADOS FINANCEIROS / PAGAMENTO
        if ($id_plano_atual) {
            $stmt_pag = $pdo_agenda->prepare("SELECT id_pagamento, status, data_vencimento, valor FROM pagamento WHERE id_plano_matricula = ? ORDER BY data_vencimento DESC LIMIT 1");
            $stmt_pag->execute([$id_plano_atual]);
            $dados_pag = $stmt_pag->fetch(PDO::FETCH_ASSOC);
            
            if ($dados_pag) {
                $id_pagamento_atual = (int)$dados_pag['id_pagamento'];
                $status_pagamento = $dados_pag['status'] ?? 'PENDENTE';
                $data_vencimento = $dados_pag['data_vencimento'] ? date('d/m/Y', strtotime($dados_pag['data_vencimento'])) : '---';
                $valor_pagamento = $dados_pag['valor'] ? 'R$ ' . number_format($dados_pag['valor'], 2, ',', '.') : '---';
                $comprovante_pagamento_enviado = strtoupper((string)$status_pagamento) === 'EM_ANALISE';
            }
        }

        $stmt_historico_pagamentos = $pdo_agenda->prepare("
            SELECT pag.valor, pag.data_vencimento, pag.data_pagamento, pag.status
            FROM pagamento pag
            INNER JOIN plano pl ON pl.id_plano = pag.id_plano_matricula
            WHERE pl.id_usuario_aluno = ?
            ORDER BY pag.data_vencimento DESC, pag.id_pagamento DESC
            LIMIT 6
        ");
        $stmt_historico_pagamentos->execute([$id_aluno]);
        $historico_pagamentos = array_reverse($stmt_historico_pagamentos->fetchAll(PDO::FETCH_ASSOC));

        $agendamentoDAO = new AgendamentoDAO();
        $meus_agendamentos = $agendamentoDAO->listarAgendamentosFuturosPorAluno((int)$id_aluno);
        $total_agendamentos_futuros = count($meus_agendamentos);

        // --- NOTIFICAÇÕES DINÂMICAS DO ALUNO ---
        if (strtoupper($status_pagamento) === 'PENDENTE' || strtoupper($status_pagamento) === 'ATRASADO') {
            $notificacoes[] = [
                'tipo' => 'warning',
                'icone' => '⚠️',
                'titulo' => 'Mensalidade Pendente',
                'mensagem' => 'A sua mensalidade está pendente. Efetue o pagamento.'
            ];
        }

        if ($total_agendamentos_futuros > 0) {
            $notificacoes[] = [
                'tipo' => 'success',
                'icone' => '🥋',
                'titulo' => 'Treinos Agendados',
                'mensagem' => 'Tem ' . $total_agendamentos_futuros . ' treino(s) confirmado(s).'
            ];
        }

        $total_notificacoes = count($notificacoes);

        $planoAtivoAgenda = $agendamentoDAO->obterDadosPlanoAtivo((int)$id_aluno);
        $idModalidadePlano = (int)($planoAtivoAgenda['id_modalidade'] ?? 0);
        $turmas_brutas = $agendamentoDAO->listarTurmasAtivasComHorario(
            $idModalidadePlano > 0 ? $idModalidadePlano : null
        );
        $limiteSemanalPlano = $agendamentoDAO->obterLimiteSemanalPlano((int)$id_aluno);
        $agendamentosPorSemana = [];

        $hoje = new DateTime();
        for ($i = 1; $i <= 14; $i++) {
            $diaLoop = clone $hoje; $diaLoop->modify('monday this week')->modify('+' . ($i - 1) . ' days');
            $inicioSemana = (clone $diaLoop)->modify('monday this week')->setTime(0, 0, 0);
            $fimSemana = (clone $inicioSemana)->modify('sunday this week')->setTime(23, 59, 59);
            $chaveSemana = $inicioSemana->format('Y-m-d');
            if (!array_key_exists($chaveSemana, $agendamentosPorSemana)) {
                $agendamentosPorSemana[$chaveSemana] = $agendamentoDAO->contarAgendamentosSemana(
                    (int)$id_aluno,
                    $inicioSemana->format('Y-m-d H:i:s'),
                    $fimSemana->format('Y-m-d H:i:s')
                );
            }
            $nomeDiaPt = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'][(int)$diaLoop->format('w')];
            $calendarioSemanal[$i] = [
                'nome_dia' => $nomeDiaPt,
                'data_iso' => $diaLoop->format('Y-m-d'),
                'data_exibicao' => $diaLoop->format('d/m'),
                'limite_atingido' => $agendamentosPorSemana[$chaveSemana] >= $limiteSemanalPlano,
                'aulas' => []
            ];
            foreach ($turmas_brutas as $turma) {
                $diaTurma = trim(ucfirst(strtolower($turma['dia_semana'] ?? '')));
                if (empty($diaTurma) || $diaTurma === 'Geral' || stripos($diaTurma, $nomeDiaPt) !== false) {
                    $turma['hora_inicio'] = $turma['hora_inicio'] ?? '19:00:00';
                    $calendarioSemanal[$i]['aulas'][] = $turma;
                }
            }
        }
    }

} catch (Throwable $e) {
    error_log('Falha ao carregar o painel do aluno: ' . $e->getMessage());
    $mensagem_erro = 'Não foi possível carregar todos os dados do painel. Atualize a página ou tente novamente mais tarde.';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Aluno - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css?v=<?= filemtime(__DIR__ . '/../../assets/css/estilo.css'); ?>">
</head>

<body class="aluno-theme">

    <div class="d-print-none">
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </div>

    <main class="container py-4">
        
        <?php if ($is_primeiro_acesso): ?>
            <!-- TELA DE PRIMEIRO ACESSO (CONTRATO) -->
            <div class="row justify-content-center d-print-block">
                <div class="col-lg-10">
                    <div class="card shadow border-0 rounded-3">
                        <div class="card-header bg-dark text-white text-center py-3 d-print-none">
                            <h4 class="mb-0 fw-bold">🥋 Bem-vindo à Dojify! Acesso Inicial</h4>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-center text-muted mb-4 d-print-none">Para libertar o seu painel de agendamentos, confirme os seus dados no contrato abaixo, assinale a caixa de aceite e defina a sua nova senha pessoal.</p>
                            <?php if (!empty($mensagem_erro)): ?>
                                <div class="alert alert-secondary py-2 text-center fw-bold d-print-none"><?= $mensagem_erro; ?></div>
                            <?php endif; ?>

                            <div id="area-impressao" class="p-4 mb-4" style="border: 1px solid #dee2e6; border-radius: 0.375rem; background-color: #ffffff; max-height: 450px; overflow-y: auto; color: #333; font-size: 0.9rem; line-height: 1.6;">
                                <div class="text-center mb-4">
                                    <h2 class="fw-bold" style="color: #212529; font-size: 1.5rem; border-bottom: 2px solid #eee; padding-bottom: 10px;">Contrato de Prestação de Serviços de Aulas de Artes Marciais</h2>
                                </div>
                                <h3 class="fw-bold mt-4 mb-2" style="color: #212529; font-size: 1.1rem; border-bottom: 1px solid #eee;">Dados Aluno / Contratante</h3>
                                <div class="row mb-2" style="border-bottom: 1px dashed #eee; padding-bottom: 5px;">
                                    <div class="col-6"><strong>Nome do aluno(a):</strong> <?= htmlspecialchars($c_nome) ?></div>
                                    <div class="col-6"><strong>CPF:</strong> <?= htmlspecialchars($c_cpf) ?></div>
                                </div>
                                <div class="row mb-2" style="border-bottom: 1px dashed #eee; padding-bottom: 5px;">
                                    <div class="col-6"><strong>Data de nascimento:</strong> <?= htmlspecialchars($c_data_nasc) ?></div>
                                    <div class="col-6"><strong>Sexo:</strong> <?= htmlspecialchars($c_sexo) ?></div>
                                </div>
                                <div class="row mb-2" style="border-bottom: 1px dashed #eee; padding-bottom: 5px;">
                                    <div class="col-6"><strong>Nome do Responsável:</strong> <?= htmlspecialchars($c_responsavel) ?></div>
                                    <div class="col-6"><strong>CPF do Responsável:</strong> <?= htmlspecialchars($c_cpf_resp) ?></div>
                                </div>
                                <div class="row mb-2" style="border-bottom: 1px dashed #eee; padding-bottom: 5px;">
                                    <div class="col-6"><strong>E-mail:</strong> <?= htmlspecialchars($c_email) ?></div>
                                    <div class="col-6"><strong>Telefone:</strong> <?= htmlspecialchars($c_tel) ?></div>
                                </div>
                                <div class="row mb-2" style="border-bottom: 1px dashed #eee; padding-bottom: 5px;">
                                    <div class="col-6"><strong>Endereço:</strong> <?= htmlspecialchars($c_endereco) ?></div>
                                    <div class="col-6"><strong>Cidade:</strong> <?= htmlspecialchars($c_cidade) ?> / <?= htmlspecialchars($c_estado) ?></div>
                                </div>
                                <h3 class="fw-bold mt-4 mb-2" style="color: #212529; font-size: 1.1rem; border-bottom: 1px solid #eee;">Modalidade e Plano</h3>
                                <p class="mb-1"><strong>Modalidade:</strong> <?= htmlspecialchars($c_luta) ?></p>
                                <p><strong>Frequência e Plano:</strong> <?= htmlspecialchars($c_plano) ?></p>
                                <h3 class="fw-bold mt-4 mb-2" style="color: #212529; font-size: 1.1rem; border-bottom: 1px solid #eee;">Informações Adicionais</h3>
                                <p class="mb-1"><strong>Informações médicas a serem consideradas?</strong> <?= htmlspecialchars($c_infoMedica) ?></p>
                                <p class="mb-1"><strong>O aluno necessita de alguma adaptação especial?</strong> <?= htmlspecialchars($c_especial) ?></p>
                                <p><strong>Observações gerais:</strong> <?= htmlspecialchars($c_obs) ?></p>
                                <h3 class="fw-bold mt-4 mb-2" style="color: #212529; font-size: 1.1rem; border-bottom: 1px solid #eee;">Dados do Contratado</h3>
                                <p class="mb-1"><strong>Contratado:</strong> A TOKKA – Escola de Lutas</p>
                                <p class="mb-1"><strong>CNPJ:</strong> 66.790.246/0001-12</p>
                                <p class="mb-1"><strong>Endereço:</strong> QNM 08 Conjunto B Lote 34</p>
                                <p class="mb-1"><strong>Telefone:</strong> (61) 99869-3504</p>
                                <p><strong>Instrutor responsável:</strong> Klevisson Araújo</p>
                                <h3 class="fw-bold mt-4 mb-2" style="color: #212529; font-size: 1.1rem; border-bottom: 1px solid #eee;">Termos e Cláusulas</h3>
                                <p>As partes acima identificadas têm, entre si, justo e acertado o presente contrato de prestação de serviços de aulas de artes marciais, que se regerá pelas cláusulas e condições a seguir:</p>
                                <p><strong>CLÁUSULA 1 — DO OBJETO</strong><br>1.1 O presente contrato tem como objeto a prestação de serviços de aulas de artes marciais na modalidade contratada, observada a frequência escolhida pelo(a) CONTRATANTE, em dias e horários previamente disponibilizados pelo CONTRATADO.</p>
                                <p><strong>CLÁUSULA 2 — DO VALOR E FORMA DE PAGAMENTO</strong><br>2.1 O CONTRATANTE pagará ao CONTRATADO o valor correspondente ao plano selecionado, conforme indicado neste instrumento, a serem pagos até o dia 10 (dez) de cada mês, via dinheiro, pix ou transferência bancária.<br>2.2 O inadimplemento poderá acarretar a suspensão da participação do CONTRATANTE nas aulas até a regularização dos valores pendentes.</p>
                                <p><strong>CLÁUSULA 3 — DAS RESPONSABILIDADES</strong><br>3.1 O CONTRATADO compromete-se a ministrar as aulas de acordo com as técnicas próprias da modalidade, observando as normas de segurança e zelando pelo adequado estado de conservação dos equipamentos e instalações.<br>3.2 O CONTRATANTE compromete-se a respeitar as normas internas, regras de vestimenta, higiene, segurança e conduta estabelecidas pela academia.<br>3.3 O CONTRATANTE declara estar apto à prática de atividades físicas e ciente de que a prática de artes marciais envolve esforço físico e riscos inerentes, responsabilizando-se pelas informações de saúde prestadas.<br>3.4 O CONTRATANTE assume responsabilidade por eventuais problemas de saúde ou lesões decorrentes da prática das atividades, isentando o CONTRATADO de responsabilidade nos casos de imprudência, descumprimento das orientações recebidas ou existência de condições médicas não informadas previamente.<br>3.5 Caso o CONTRATANTE seja o responsável legal por menor de idade, declara, para todos os fins de direito, que assume, em nome do menor, integral responsabilidade pelos riscos decorrentes da prática das atividades contratadas, estendendo-se a isenção de responsabilidade aqui prevista ao menor sob sua guarda.</p>
                                <p><strong>CLÁUSULA 4 — DAS FALTAS E REPOSIÇÕES</strong><br>4.1 O não comparecimento do CONTRATANTE às aulas não gera direito à reposição, desconto, compensação ou reembolso de valores.</p>
                                <p><strong>CLÁUSULA 5 — DA DURAÇÃO DO CONTRATO</strong><br>5.1 O presente contrato terá duração mínima de 3 (três) meses, contados a partir da data de sua assinatura.</p>
                                <p><strong>CLÁUSULA 6 — DA RESCISÃO</strong><br>O presente contrato poderá ser rescindido:<br>6.1 Por qualquer das partes, mediante aviso prévio de 30 (trinta) dias.<br>6.2 Em caso de inadimplemento ou descumprimento de cláusulas contratuais.<br>6.3 O CONTRATADO poderá rescindir imediatamente o presente contrato em caso de conduta agressiva, desrespeito às normas internas, comportamento inadequado ou atitudes que coloquem em risco os demais alunos, professores ou colaboradores.<br>6.4 O CONTRATADO não está obrigado à devolução dos valores pagos.</p>
                                <p><strong>CLÁUSULA 7 — DO USO DE IMAGEM</strong><br>7.1 A utilização da imagem do CONTRATANTE ou do RESPONSÁVEL LEGAL (no caso de aluno menor de 18 anos) em fotos ou vídeos para divulgação da academia depende de autorização específica, separada e facultativa, que pode ser concedida ou retirada sem afetar a prestação dos serviços contratados.</p>
                                <p><strong>CLÁUSULA 8 — DAS DISPOSIÇÕES FINAIS</strong><br>8.1 Este contrato é firmado em duas vias de igual teor e forma, assinadas pelas partes para que produza seus efeitos legais.</p>
                                <br>
                                <p class="text-center"><strong>Brasília - DF, <?= date("d/m/Y"); ?>.</strong></p>
                                <br><br>
                                <div class="d-flex justify-content-between text-center mt-4">
                                    <div style="width: 45%;"><hr style="border: 1px solid #000;">ASSINATURA CONTRATANTE</div>
                                    <div style="width: 45%;"><hr style="border: 1px solid #000;">ASSINATURA CONTRATADO</div>
                                </div>
                            </div>
                            <form method="POST" action="" class="d-print-none" onsubmit="document.getElementById('area-impressao').style.maxHeight='none'; document.getElementById('area-impressao').style.overflow='visible'; window.print(); return true;">
                                <input type="hidden" name="acao_aceite_contrato" value="1">
                                <div class="bg-light p-3 border rounded mb-4">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input ms-1 me-2 border-secondary" type="checkbox" name="aceite_contrato" id="aceite_contrato" value="1" required <?= (($_POST['aceite_contrato'] ?? '') === '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold text-dark" for="aceite_contrato">Li e aceito o Contrato de Prestação de Serviços.</label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input ms-1 me-2 border-secondary" type="checkbox" name="consentimento_saude" id="consentimento_saude" value="1" required <?= (($_POST['consentimento_saude'] ?? '') === '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold text-dark" for="consentimento_saude">Consinto com o tratamento dos meus dados de saúde informados na matrícula para fins de segurança durante as aulas.</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input ms-1 me-2 border-secondary" type="checkbox" name="autorizacao_imagem" id="autorizacao_imagem" value="1" <?= (($_POST['autorizacao_imagem'] ?? '') === '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold text-dark" for="autorizacao_imagem">Autorizo o uso da minha imagem em fotos/vídeos para divulgação da academia. (Opcional)</label>
                                    </div>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-secondary">Definir Nova Senha Definitiva</label>
                                        <input type="password" name="senha_nova" class="form-control border-secondary" required placeholder="Mínimo 6 caracteres">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-secondary">Confirmar Senha</label>
                                        <input type="password" name="senha_confirma" class="form-control border-secondary" required placeholder="Repita a senha">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-danger w-100 fw-bold py-3 fs-5 shadow-sm">Assinar Contrato e Entrar no Painel</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
           <div class="mb-3">
    <h2 class="mb-0">Painel do Aluno</h2>
    <p class="text-muted mb-0">Bem-vindo(a), <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>!</p>
</div>

            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alert alert-success text-center py-2"><?= $mensagem_sucesso; ?></div>
            <?php endif; ?>
            <?php if (!empty($mensagem_erro)): ?>
                <div class="alert alert-secondary text-center py-2"><?= $mensagem_erro; ?></div>
            <?php endif; ?>

            <!-- CARTÕES DO TOPO (Graduação, XP e Matrícula) -->
            <div class="row g-3 justify-content-center mb-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border p-2 text-center">
                        <h6 class="text-dark fw-bold mb-1">🥋 Graduação</h6>
                        <p class="text-dark fw-bold fs-5 mb-0"><?= htmlspecialchars($faixa_aluno) ?></p>
                        <span class="badge bg-secondary mt-1 mx-auto" style="width: fit-content;"><?= htmlspecialchars($grau_aluno) ?></span>
                        <a href="historico_graduacao.php" class="btn btn-outline-dark btn-sm fw-bold mt-2">
                            Ver histórico
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border p-2 text-center">
                        <h6 class="text-dark fw-bold mb-1">⭐ Nível <?= $num_nivel ?> - <?= htmlspecialchars($nome_nivel) ?></h6>
                        <p class="text-muted small mb-1"><?= $xp_atual ?> / <?= $xp_max ?> XP</p>
                        <div class="progress mx-auto" style="height: 10px; width: 85%; border-radius: 10px;">
                            <div class="progress-bar bg-warning text-dark fw-bold progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $progresso_xp ?>%;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border p-2 text-center">
                        <h6 class="text-dark fw-bold mb-1">📋 Matrícula</h6>
                        <p class="mb-0 mt-1">
                            <?php if ($status_plano === 'ATIVO'): ?>
                                <span class="badge bg-success px-3 py-2">ATIVO</span>
                            <?php elseif ($status_plano === 'SUSPENSO'): ?>
                                <span class="badge bg-warning text-dark px-3 py-2">SUSPENSO</span>
                            <?php else: ?>
                                <span class="badge bg-secondary px-3 py-2"><?= htmlspecialchars($status_plano) ?></span>
                            <?php endif; ?>
                        </p>
                        <span class="text-muted small mt-1 fw-bold d-block"><?= htmlspecialchars($nome_plano_card) ?></span>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO FINANCEIRA / STATUS DA MENSALIDADE -->
            <?php $statusPagamentoUpper = strtoupper((string)$status_pagamento); ?>
            <?php
                $maiorValorHistorico = 0.0;
                foreach ($historico_pagamentos as $pagamentoHistorico) {
                    $maiorValorHistorico = max($maiorValorHistorico, (float)$pagamentoHistorico['valor']);
                }
            ?>
            <section class="card card-pagamento border-0 shadow-sm mb-4 overflow-hidden" aria-labelledby="estadoFinanceiroTitulo">
                <div class="card-header bg-dark text-white border-0 px-3 py-2">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div>
                            <h5 class="fw-bold mb-0" id="estadoFinanceiroTitulo">💳 Mensalidade</h5>
                        </div>
                        <?php if ($statusPagamentoUpper === 'PAGO'): ?>
                            <span class="badge rounded-pill bg-success px-3 py-2">Pagamento confirmado</span>
                        <?php elseif ($statusPagamentoUpper === 'EM_ANALISE'): ?>
                            <span class="badge rounded-pill bg-info text-dark px-3 py-2">Comprovante em análise</span>
                        <?php elseif ($id_pagamento_atual === null): ?>
                            <span class="badge rounded-pill bg-secondary px-3 py-2">Sem cobrança disponível</span>
                        <?php else: ?>
                            <span class="badge rounded-pill bg-warning text-dark px-3 py-2">
                                <?= $statusPagamentoUpper === 'ATRASADO' ? 'Pagamento atrasado' : 'Pagamento pendente'; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-2 p-md-3">
                    <div class="row g-2 align-items-stretch">
                        <div class="col-lg-4">
                            <div class="payment-current h-100 rounded-3 p-3">
                                <div class="d-flex justify-content-between gap-2">
                                    <div>
                                        <span class="text-muted small text-uppercase fw-bold">Valor</span>
                                        <p class="fs-3 fw-bold text-dark mb-1"><?= htmlspecialchars($valor_pagamento, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted small text-uppercase fw-bold">Vencimento</span>
                                        <p class="fw-semibold text-dark mb-1"><?= htmlspecialchars($data_vencimento, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                </div>
                                <?php if ($statusPagamentoUpper !== 'PAGO' && $statusPagamentoUpper !== 'EM_ANALISE' && $id_pagamento_atual !== null && !$comprovante_pagamento_enviado): ?>
                                    <button type="button" class="btn btn-danger btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalPagamento">
                                        Pagar com PIX
                                    </button>
                                <?php elseif ($statusPagamentoUpper === 'EM_ANALISE' || $comprovante_pagamento_enviado): ?>
                                    <p class="small text-info-emphasis mb-0">
                                        Comprovante enviado; aguardando conferência.
                                    </p>
                                <?php elseif ($statusPagamentoUpper === 'PAGO'): ?>
                                    <p class="small text-success mb-0">Pagamento confirmado.</p>
                                <?php elseif ($id_pagamento_atual === null): ?>
                                    <p class="small text-muted mb-0">Nenhuma cobrança disponível.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="payment-history h-100 rounded-3 p-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <h6 class="fw-bold mb-0">Histórico de pagamentos</h6>
                                    <div class="payment-history-legend small" aria-hidden="true">
                                        <span><i class="payment-legend-dot payment-bar--paid"></i>Pago</span>
                                        <span><i class="payment-legend-dot payment-bar--pending"></i>Pendente</span>
                                        <span><i class="payment-legend-dot payment-bar--late"></i>Atrasado</span>
                                        <span><i class="payment-legend-dot payment-bar--review"></i>Em análise</span>
                                    </div>
                                </div>
                                <?php if (empty($historico_pagamentos)): ?>
                                    <p class="small text-muted mb-0 py-3">Ainda não há pagamentos registrados.</p>
                                <?php else: ?>
                                    <ul class="payment-history-chart list-unstyled mb-0" aria-label="Gráfico dos seis pagamentos mais recentes">
                                        <?php foreach ($historico_pagamentos as $pagamentoHistorico): ?>
                                            <?php
                                                $statusHistorico = strtoupper((string)($pagamentoHistorico['status'] ?? 'PENDENTE'));
                                                if ($statusHistorico === 'PAGO') {
                                                    $classeBarra = 'payment-bar--paid';
                                                    $rotuloStatus = 'Pago';
                                                } elseif ($statusHistorico === 'ATRASADO') {
                                                    $classeBarra = 'payment-bar--late';
                                                    $rotuloStatus = 'Atrasado';
                                                } elseif ($statusHistorico === 'EM_ANALISE') {
                                                    $classeBarra = 'payment-bar--review';
                                                    $rotuloStatus = 'Em análise';
                                                } else {
                                                    $classeBarra = 'payment-bar--pending';
                                                    $rotuloStatus = 'Pendente';
                                                }
                                                $valorHistorico = (float)$pagamentoHistorico['valor'];
                                                $alturaBarra = $maiorValorHistorico > 0
                                                    ? max(8, ($valorHistorico / $maiorValorHistorico) * 72)
                                                    : 8;
                                                $dataHistorico = $pagamentoHistorico['data_pagamento'] ?: $pagamentoHistorico['data_vencimento'];
                                            ?>
                                            <li class="payment-history-item" aria-label="<?= htmlspecialchars(date('d/m/Y', strtotime($dataHistorico)) . ', ' . $rotuloStatus . ', R$ ' . number_format($valorHistorico, 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?>">
                                                <span class="payment-history-value" aria-hidden="true">R$ <?= number_format($valorHistorico, 0, ',', '.'); ?></span>
                                                <span class="payment-history-bar <?= $classeBarra ?>" style="height: <?= number_format($alturaBarra, 2, '.', ''); ?>%" aria-hidden="true"></span>
                                                <span class="payment-history-date" aria-hidden="true"><?= date('m/y', strtotime($pagamentoHistorico['data_vencimento'])); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CALENDÁRIO SEMANAL COMPACTO -->
            <div class="card shadow-sm border p-3 mb-4">
                <h5 class="text-uppercase fw-bold text-dark mb-1 text-center" style="font-size: 1rem;">📅 Agenda Semanal de Treinos</h5>
                <p class="text-muted small mb-3 text-center">Escolha a sua turma e clique em agendar no dia respetivo.</p>
                <?php if ($idModalidadePlano <= 0): ?>
                    <div class="alert alert-warning small text-center py-2" role="alert">
                        O seu plano ainda não está vinculado a uma modalidade. Peça à academia para atualizar o cadastro para liberar as aulas.
                    </div>
                <?php else: ?>
                    <p class="text-muted small mb-3 text-center">
                        Modalidade do seu plano: <strong><?= htmlspecialchars((string)$planoAtivoAgenda['modalidade_nome'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>
                <?php endif; ?>

                <div class="d-flex overflow-auto pb-2 mx-auto" style="gap: 10px; width: fit-content; max-width: 100%;">
                    <?php foreach ($calendarioSemanal as $dia): ?>
                        <div class="shadow-sm border border-secondary text-white rounded" style="flex: 0 0 135px; background-color: #1a1a1a;">
                            <div class="aluno-calendar-day-header text-white fw-bold text-center p-1" style="font-size: 0.8rem; border-radius: 5px 5px 0 0;">
                                <?= $dia['nome_dia']; ?><br>
                                <span class="fw-normal" style="font-size: 0.7rem;"><?= $dia['data_exibicao']; ?></span>
                            </div>
                            <div class="p-2">
                                <?php if (empty($dia['aulas'])): ?>
                                    <p class="text-muted text-center small fst-italic my-3" style="font-size: 0.75rem;">Sem aulas</p>
                                <?php else: ?>
                                    <?php foreach ($dia['aulas'] as $aula): ?>
                                        <div class="bg-black p-2 rounded mb-2 border border-secondary text-center">
                                            <span class="text-warning d-block fw-bold" style="font-size: 0.75rem;"><?= htmlspecialchars($aula['nome_turma']); ?></span>
                                            <span class="text-white d-block mb-2" style="font-size: 0.7rem;">⏰ <?= date('H:i', strtotime($aula['hora_inicio'])); ?></span>
                                            <?php
                                            $horaAulaExibicao = trim((string)($aula['hora_inicio'] ?? ''));
                                            if (preg_match('/^\d{2}:\d{2}$/', $horaAulaExibicao)) {
                                                $horaAulaExibicao .= ':00';
                                            }
                                            $dataHoraAula = DateTime::createFromFormat(
                                                '!Y-m-d H:i:s',
                                                $dia['data_iso'] . ' ' . $horaAulaExibicao
                                            );
                                            $errosDataHoraAula = DateTime::getLastErrors();
                                            $horarioValido = $dataHoraAula !== false
                                                && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $horaAulaExibicao)
                                                && ($errosDataHoraAula === false || ($errosDataHoraAula['warning_count'] === 0 && $errosDataHoraAula['error_count'] === 0));
                                            ?>
                                            <?php if (!$horarioValido): ?>
                                                <span class="badge bg-secondary w-100">Horário indisponível</span>
                                            <?php elseif ($dataHoraAula <= new DateTime()): ?>
                                                <span class="badge bg-secondary w-100">Horário encerrado</span>
                                            <?php elseif ($dia['limite_atingido']): ?>
                                                <span class="badge bg-secondary w-100" title="O limite semanal do seu plano foi atingido.">
                                                    Limite semanal atingido
                                                </span>
                                            <?php else: ?>
                                                <form method="POST" action="../../controller/AgendamentoController.php?acao=agendar_semana" class="d-block m-0 p-0 bg-transparent border-0 shadow-none">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="id_turma" value="<?= (int)$aula['id_turma']; ?>">
                                                    <input type="hidden" name="data_escolhida" value="<?= htmlspecialchars($dia['data_iso'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="hora_aula" value="<?= htmlspecialchars($aula['hora_inicio'], ENT_QUOTES, 'UTF-8'); ?>">

                                                    <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold border-0" style="font-size: 0.75rem; padding: 4px 0;">Agendar</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- PRÓXIMOS TREINOS AGENDADOS -->
            <div class="card shadow-sm border p-3 mb-3">
                <h6 class="fw-bold text-muted text-uppercase small mb-2">📌 Os Seus Próximos Treinos Marcados</h6>
                <div class="table-responsive">
                    <?php if (empty($meus_agendamentos)): ?>
                        <p class="text-muted small fst-italic mb-0">Ainda não tem nenhum treino agendado para os próximos dias.</p>
                    <?php else: ?>
                        <table class="table table-sm table-hover align-middle mb-0 small">
                            <thead>
                                <tr class="text-muted">
                                    <th scope="col">Turma</th>
                                    <th scope="col">Data e hora</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col" class="text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($meus_agendamentos as $ag): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($ag->getNomeTurma(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($ag->getDataAgendamento())); ?></td>
                                        <td><span class="badge bg-success"><?= htmlspecialchars($ag->getStatus(), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="text-end">
                                            <form method="POST" action="../../controller/AgendamentoController.php?acao=cancelar" class="d-inline agendamento-cancel-form">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="id_agendamento" value="<?= $ag->getIdAgendamento(); ?>">
                                                <button type="submit"
                                                        class="agendamento-cancel-button"
                                                        onclick="return confirm('Deseja cancelar este agendamento?')">
                                                    Cancelar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acesso aos históricos do aluno -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                <a href="historico_frequencia.php" class="btn btn-outline-dark btn-sm fw-bold px-4 py-2">
                    📜 Ver Histórico Completo de Frequência
                </a>
            </div>

        <?php endif; ?>

    </main>

    <?php if ($mensagem_modal_agendamento !== ''): ?>
    <div class="modal fade" id="modalFeedbackAgendamento" tabindex="-1" aria-labelledby="modalFeedbackAgendamentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-<?= htmlspecialchars($tipo_modal_agendamento, ENT_QUOTES, 'UTF-8'); ?> text-white">
                    <h5 class="modal-title fw-bold" id="modalFeedbackAgendamentoLabel">
                        <?= $tipo_modal_agendamento === 'success' ? 'Tudo certo!' : 'Atenção'; ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <?= htmlspecialchars($mensagem_modal_agendamento, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- MODAL DE DEMONSTRAÇÃO DO PIX E ENVIO DO COMPROVANTE -->
    <?php if ($id_pagamento_atual !== null && strtoupper((string)$status_pagamento) !== 'PAGO' && !$comprovante_pagamento_enviado): ?>
    <div class="modal fade" id="modalPagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalPagamentoLabel">Pagamento da mensalidade</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <ul class="nav nav-tabs nav-fill mb-4" id="pagamento-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="pagamento-pix-tab" data-bs-toggle="tab" data-bs-target="#pagamento-pix" type="button" role="tab" aria-controls="pagamento-pix" aria-selected="true">1. PIX</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="pagamento-comprovante-tab" data-bs-toggle="tab" data-bs-target="#pagamento-comprovante" type="button" role="tab" aria-controls="pagamento-comprovante" aria-selected="false">2. Comprovante</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active text-center" id="pagamento-pix" role="tabpanel" aria-labelledby="pagamento-pix-tab" tabindex="0">
                            <p class="text-muted mb-1">Valor da mensalidade</p>
                            <p class="fs-4 fw-bold text-dark mb-3"><?= htmlspecialchars($valor_pagamento, ENT_QUOTES, 'UTF-8'); ?></p>
                            <canvas id="qrCodeDemonstracao" class="img-fluid border rounded p-2 mb-2" width="232" height="232" role="img" aria-label="QR code visual fictício, não utilizável para pagamento"></canvas>
                            <p class="small fw-bold text-warning-emphasis mb-1">QR code fictício para demonstração</p>
                            <p class="small text-muted mb-3">Este código não processa pagamentos. Realize o PIX pelos canais da academia e depois envie o comprovante.</p>
                            <button class="btn btn-danger w-100 fw-bold" type="button" data-bs-toggle="tab" data-bs-target="#pagamento-comprovante" role="tab">
                                Já realizei o PIX — enviar comprovante
                            </button>
                        </div>

                        <div class="tab-pane fade" id="pagamento-comprovante" role="tabpanel" aria-labelledby="pagamento-comprovante-tab" tabindex="0">
                            <div class="alert alert-info small" role="alert">
                                O pagamento só será confirmado após a conferência do comprovante pelo gerente.
                            </div>
                            <form method="POST" action="../../controller/ComprovantePagamentoController.php" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="id_pagamento" value="<?= (int)$id_pagamento_atual; ?>">
                                <div class="mb-3">
                                    <label for="comprovantePagamento" class="form-label fw-bold">Selecione o comprovante</label>
                                    <input type="file" class="form-control" id="comprovantePagamento" name="comprovante" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <div class="form-text">Formatos permitidos: JPG, PNG ou PDF. Tamanho máximo: 5 MB.</div>
                                </div>
                                <button type="submit" class="btn btn-success w-100 fw-bold">Enviar para o gerente</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-print-none">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
    <?php if ($mensagem_modal_agendamento !== ''): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('modalFeedbackAgendamento');
            if (modal) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });
    </script>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('qrCodeDemonstracao');
            if (!canvas) return;

            const context = canvas.getContext('2d');
            const modules = 29;
            const cell = canvas.width / modules;
            let seed = 20261001;

            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, canvas.width, canvas.height);

            function isFinderArea(x, y) {
                return (x < 8 && y < 8) ||
                    (x >= modules - 8 && y < 8) ||
                    (x < 8 && y >= modules - 8);
            }

            function drawFinder(startX, startY) {
                for (let y = 0; y < 7; y++) {
                    for (let x = 0; x < 7; x++) {
                        const edge = x === 0 || x === 6 || y === 0 || y === 6;
                        const center = x >= 2 && x <= 4 && y >= 2 && y <= 4;
                        if (edge || center) {
                            context.fillStyle = '#111827';
                            context.fillRect((startX + x) * cell, (startY + y) * cell, cell, cell);
                        }
                    }
                }
            }

            context.fillStyle = '#111827';
            for (let y = 0; y < modules; y++) {
                for (let x = 0; x < modules; x++) {
                    if (isFinderArea(x, y)) continue;
                    seed = (seed * 9301 + 49297) % 233280;
                    if (seed / 233280 > 0.5) {
                        context.fillRect(x * cell, y * cell, cell, cell);
                    }
                }
            }

            drawFinder(0, 0);
            drawFinder(modules - 7, 0);
            drawFinder(0, modules - 7);
        });
    </script>
</body>
</html>