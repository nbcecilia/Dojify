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
require_once dirname(__DIR__, 2) . '/model/dao/AvaliacaoDAO.php';

$calendarioSemanal = [];
$agendaExibeProximaSemana = false;
$meus_agendamentos = [];
$graduacoesPrevistasPainel = [];
$avaliacoesAlunoPainel = [];
$planoAtivoAgenda = null;
$modalidadesAluno = [];
$idsModalidadesAluno = [];
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
$c_academia_nome = "A TOKKA - Escola de Lutas";
$c_infoMedica = $c_especial = $c_obs = "---";
$status_pagamento = 'PENDENTE';
$data_vencimento = '---';
$data_vencimento_iso = null;
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
$faixaImagem = null;
$faixaCadastrada = false;
$diasSemanaPt = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
$mesesPt = [
    1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
    5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
    9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
];
$dataAtualAluno = $diasSemanaPt[(int)date('w')] . ', ' . date('d') . ' de '
    . $mesesPt[(int)date('n')] . ' de ' . date('Y');

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
        'data_passada' => 'O agendamento deve ser feito com pelo menos 30 minutos de antecedência.',
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
                SELECT u.nome, u.email, u.cpf, u.data_nascimento, u.sexo, u.telefone,
                       u.endereco, u.cidade, u.estado,
                       u.responsavel, u.observacao, a.nome AS academia_nome,
                       (
                           SELECT GROUP_CONCAT(p2.nome_plano ORDER BY p2.id_plano ASC SEPARATOR ', ')
                           FROM plano p2
                           WHERE p2.id_usuario_aluno = u.id_usuario
                             AND p2.status = 'ATIVO'
                       ) AS nome_plano,
                       (
                           SELECT SUM(p2.valor)
                           FROM plano p2
                           WHERE p2.id_usuario_aluno = u.id_usuario
                             AND p2.status = 'ATIVO'
                       ) AS valor_plano,
                       (
                           SELECT m2.nome
                           FROM plano p2
                           INNER JOIN modalidade m2 ON m2.id_modalidade = p2.id_modalidade
                           WHERE p2.id_usuario_aluno = u.id_usuario
                             AND p2.status = 'ATIVO'
                           ORDER BY p2.data_inicio DESC, p2.id_plano DESC
                           LIMIT 1
                       ) AS modalidade_plano
                FROM usuario u
                LEFT JOIN academia a ON a.id_academia = u.id_academia
                WHERE u.id_usuario = ?
            ");
            $stmt_aluno->execute([$id_aluno]);
            $dados_contrato = $stmt_aluno->fetch(PDO::FETCH_ASSOC);

            if ($dados_contrato) {
                $c_nome = $dados_contrato['nome'] ?: "Não informado";
                $c_email = $dados_contrato['email'] ?: "Não informado";
                $cpf_limpo = $dados_contrato['cpf'];
                $c_cpf = (strlen($cpf_limpo) == 11) ? preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cpf_limpo) : "Não informado";
                $c_data_nasc = $dados_contrato['data_nascimento'] ? date('d/m/Y', strtotime($dados_contrato['data_nascimento'])) : "Não informado";
                $c_sexo = $dados_contrato['sexo'] ?: "Não informado";
                $c_tel = !empty($dados_contrato['telefone']) ? $dados_contrato['telefone'] : "Não informado";
                $c_endereco = $dados_contrato['endereco'] ?: "Não informado";
                $c_cidade = $dados_contrato['cidade'] ?: "Não informado";
                $c_estado = $dados_contrato['estado'] ?: "Não informado";
                $c_responsavel = $dados_contrato['responsavel'] ?: "O próprio";
                $c_obs = $dados_contrato['observacao'] ?: "Nenhuma observação registrada";
                $c_academia_nome = $dados_contrato['academia_nome'] ?: $c_academia_nome;
                $c_plano = $dados_contrato['nome_plano'] ?: "Plano Base";
                if ($dados_contrato['valor_plano'] !== null) {
                    $c_plano .= ' - R$ ' . number_format((float)$dados_contrato['valor_plano'], 2, ',', '.');
                }

                $stmt_modalidades_contrato = $pdo_agenda->prepare("
                    SELECT DISTINCT m.nome
                    FROM plano p
                    INNER JOIN modalidade m ON m.id_modalidade = p.id_modalidade
                    WHERE p.id_usuario_aluno = ?
                      AND p.status = 'ATIVO'
                      AND p.id_modalidade IS NOT NULL
                    ORDER BY m.nome
                ");
                $stmt_modalidades_contrato->execute([$id_aluno]);
                $nomesModalidadesContrato = $stmt_modalidades_contrato->fetchAll(PDO::FETCH_COLUMN);
                if ($nomesModalidadesContrato === [] && !empty($dados_contrato['modalidade_plano'])) {
                    $nomesModalidadesContrato[] = $dados_contrato['modalidade_plano'];
                }
                if ($nomesModalidadesContrato !== []) {
                    $c_luta = implode(', ', array_unique($nomesModalidadesContrato));
                }

                $c_infoMedica = "Vide observações gerais: " . $c_obs;
                $c_especial = "Não";
            }
        }
    } catch (Throwable $e) {
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
            try {
                $pdo_agenda->beginTransaction();
                $stmt_senha = $pdo_agenda->prepare("UPDATE login SET senha_hash = ? WHERE id_usuario = ?");
                $senhaAtualizada = $stmt_senha->execute([$senha_hash, $id_aluno]) && $stmt_senha->rowCount() > 0;

                $stmt_acesso = $pdo_agenda->prepare("
                    UPDATE usuario
                    SET primeiro_acesso = 0, data_aceite_contrato = ?
                    WHERE id_usuario = ? AND primeiro_acesso = 1
                ");
                $aceiteRegistrado = $stmt_acesso->execute([date('Y-m-d H:i:s'), $id_aluno])
                    && $stmt_acesso->rowCount() > 0;

                if ($senhaAtualizada && $aceiteRegistrado) {
                    $pdo_agenda->commit();
                    header("Location: home_aluno.php");
                    exit;
                }

                $pdo_agenda->rollBack();
                $mensagem_erro = "Erro ao guardar as alterações.";
            } catch (Throwable $e) {
                if ($pdo_agenda->inTransaction()) {
                    $pdo_agenda->rollBack();
                }
                error_log('Falha ao registrar o aceite do contrato do aluno ' . $id_aluno . ': ' . $e->getMessage());
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
        $faixaCadastrada = $dados_grad && trim((string)$dados_grad['faixa']) !== '';
        $faixa_aluno = $faixaCadastrada ? $dados_grad['faixa'] : 'Iniciante';
        $grau_aluno = $dados_grad && !empty($dados_grad['grau']) ? $dados_grad['grau'] : 'Iniciante';
        $avaliacaoDAOPainel = new \AvaliacaoDAO();
        $graduacoesPrevistasPainel = $avaliacaoDAOPainel->listarGraduacoesPrevistasAluno((int)$id_aluno);
        $avaliacoesAlunoPainel = $avaliacaoDAOPainel->listarEvolucaoAluno((int)$id_aluno);

        $faixaNormalizada = strtolower(trim((string)$faixa_aluno));
        $faixaNormalizada = strtr($faixaNormalizada, [
            'á' => 'a', 'ã' => 'a', 'â' => 'a',
            'é' => 'e', 'ê' => 'e',
            'í' => 'i',
            'ó' => 'o', 'ô' => 'o',
            'ú' => 'u',
            'Á' => 'a', 'Ã' => 'a', 'Â' => 'a',
            'É' => 'e', 'Ê' => 'e',
            'Í' => 'i',
            'Ó' => 'o', 'Ô' => 'o',
            'Ú' => 'u',
        ]);
        if (strpos($faixaNormalizada, 'preta') !== false) {
            if (strpos($faixaNormalizada, 'tres faixas vermelhas') !== false) {
                $faixaImagem = 'faixa_preta_tres_faixas_vermelhas.svg';
            } elseif (strpos($faixaNormalizada, 'duas faixas vermelhas') !== false) {
                $faixaImagem = 'faixa_preta_duas_faixas_vermelhas.svg';
            } elseif (strpos($faixaNormalizada, 'faixa vermelha') !== false) {
                $faixaImagem = 'faixa_preta_faixa_vermelha.svg';
            } elseif (strpos($faixaNormalizada, 'faixa branca') !== false) {
                $faixaImagem = 'faixa_preta_faixa_branca.svg';
            } else {
                $faixaImagem = 'faixa_preta.svg';
            }
        } else {
            $coresFaixa = [
                'branca' => 'faixa_branca.svg',
                'amarela' => 'faixa_amarela.svg',
                'azul' => 'faixa_azul.svg',
                'laranja' => 'faixa_laranja.svg',
                'verde' => 'faixa_verde.svg',
                'roxa' => 'faixa_roxa.svg',
                'marrom' => 'faixa_marrom.svg',
            ];
            foreach ($coresFaixa as $corFaixa => $arquivoFaixa) {
                if (strpos($faixaNormalizada, $corFaixa) !== false) {
                    $faixaImagem = $arquivoFaixa;
                    break;
                }
            }
        }

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

        // Busca a cobrança consolidada do aluno somando todos os planos ativos da mesma data de vencimento
        $stmt_pag = $pdo_agenda->prepare("
            SELECT 
                MIN(pag.id_pagamento) AS id_pagamento,
                pag.data_vencimento,
                SUM(pag.valor) AS valor_total,
                MIN(pag.status) AS status_geral
            FROM plano pl
            INNER JOIN pagamento pag ON pag.id_plano_matricula = pl.id_plano
            WHERE pl.id_usuario_aluno = ?
              AND pl.status = 'ATIVO'
              AND pag.status IN ('PENDENTE', 'ATRASADO', 'EM_ANALISE')
            GROUP BY pag.data_vencimento
            ORDER BY pag.data_vencimento ASC
            LIMIT 1
        ");
        $stmt_pag->execute([$id_aluno]);
        $pagamentoAtualConsolidado = $stmt_pag->fetch(PDO::FETCH_ASSOC);

        if ($pagamentoAtualConsolidado) {
            $id_pagamento_atual = (int)$pagamentoAtualConsolidado['id_pagamento'];
            $data_vencimento_iso = $pagamentoAtualConsolidado['data_vencimento'];
            $data_vencimento = $data_vencimento_iso
                ? date('d/m/Y', strtotime($data_vencimento_iso))
                : '---';
            $valorTotalPagamento = (float)$pagamentoAtualConsolidado['valor_total'];
            $valor_pagamento = $valorTotalPagamento > 0
                ? 'R$ ' . number_format($valorTotalPagamento, 2, ',', '.')
                : '---';

            $statusPagamentoAtual = 'PAGO';
            $stmt_status_lote = $pdo_agenda->prepare("
                SELECT pag.status, pag.data_vencimento
                FROM plano pl
                INNER JOIN pagamento pag ON pag.id_plano_matricula = pl.id_plano
                WHERE pl.id_usuario_aluno = ?
                  AND pl.status = 'ATIVO'
                  AND pag.data_vencimento = ?
            ");
            $stmt_status_lote->execute([$id_aluno, $data_vencimento_iso]);
            $pagamentosDoLote = $stmt_status_lote->fetchAll(PDO::FETCH_ASSOC);

            foreach ($pagamentosDoLote as $pagamentoAtual) {
                $statusAtual = strtoupper((string)($pagamentoAtual['status'] ?? 'PENDENTE'));
                $estaAtrasado = $statusAtual === 'ATRASADO'
                    || ($statusAtual === 'PENDENTE'
                        && $pagamentoAtual['data_vencimento'] < date('Y-m-d'));

                if ($estaAtrasado) {
                    $statusPagamentoAtual = 'ATRASADO';
                    break;
                }
                if ($statusAtual === 'PENDENTE') {
                    $statusPagamentoAtual = 'PENDENTE';
                } elseif ($statusAtual === 'EM_ANALISE' && $statusPagamentoAtual === 'PAGO') {
                    $statusPagamentoAtual = 'EM_ANALISE';
                } elseif (!in_array($statusAtual, ['PAGO', 'EM_ANALISE'], true)
                    && $statusPagamentoAtual === 'PAGO') {
                    $statusPagamentoAtual = $statusAtual;
                }
            }
            $status_pagamento = $statusPagamentoAtual;
            $comprovante_pagamento_enviado = $status_pagamento === 'EM_ANALISE';
        } else {
            $stmt_pago = $pdo_agenda->prepare("
                SELECT 
                    pag.data_vencimento,
                    SUM(pag.valor) AS valor_total,
                    'PAGO' AS status_geral
                FROM plano pl
                INNER JOIN pagamento pag ON pag.id_plano_matricula = pl.id_plano
                WHERE pl.id_usuario_aluno = ?
                  AND pl.status = 'ATIVO'
                GROUP BY pag.data_vencimento
                ORDER BY pag.data_vencimento DESC
                LIMIT 1
            ");
            $stmt_pago->execute([$id_aluno]);
            $pagamentoPagoRecente = $stmt_pago->fetch(PDO::FETCH_ASSOC);

            if ($pagamentoPagoRecente) {
                $data_vencimento = date('d/m/Y', strtotime($pagamentoPagoRecente['data_vencimento']));
                $valor_pagamento = 'R$ ' . number_format((float)$pagamentoPagoRecente['valor_total'], 2, ',', '.');
                $status_pagamento = 'PAGO';
            }
        }

        $stmt_historico_pagamentos = $pdo_agenda->prepare("
            SELECT 
                SUM(pag.valor) AS valor, 
                pag.data_vencimento, 
                MAX(pag.data_pagamento) AS data_pagamento, 
                MAX(pag.status) AS status
            FROM pagamento pag
            INNER JOIN plano pl ON pl.id_plano = pag.id_plano_matricula
            WHERE pl.id_usuario_aluno = ?
            GROUP BY pag.data_vencimento
            ORDER BY pag.data_vencimento DESC
            LIMIT 12
        ");
        $stmt_historico_pagamentos->execute([$id_aluno]);
        $historico_pagamentos = array_reverse($stmt_historico_pagamentos->fetchAll(PDO::FETCH_ASSOC));

        $agendamentoDAO = new \AgendamentoDAO();
        $meus_agendamentos = $agendamentoDAO->listarAgendamentosFuturosPorAluno((int)$id_aluno);
        $total_agendamentos_futuros = count($meus_agendamentos);

        // --- NOTIFICAÇÕES DINÂMICAS DO ALUNO ---
        if (
            $id_pagamento_atual !== null &&
            (strtoupper($status_pagamento) === 'PENDENTE' || strtoupper($status_pagamento) === 'ATRASADO')
        ) {
            $pagamentoAtrasado = strtoupper($status_pagamento) === 'ATRASADO'
                || ($data_vencimento_iso !== null && $data_vencimento_iso < date('Y-m-d'));
            $mensagemStatusPagamento = $pagamentoAtrasado
                ? 'venceu em '
                : 'vence em ';
            $notificacoes[] = [
                'id' => 'pagamento-' . (int)$id_pagamento_atual,
                'acao' => 'pagamento',
                'tipo' => 'warning',
                'icone' => '⚠️',
                'titulo' => $pagamentoAtrasado ? 'Mensalidade em Atraso' : 'Mensalidade Pendente',
                'mensagem' => 'A sua mensalidade de ' . $valor_pagamento . ' ' . $mensagemStatusPagamento . $data_vencimento . '. Consulte a academia para realizar o pagamento.'
            ];
        }

        if ($total_agendamentos_futuros > 0) {
            $notificacoes[] = [
                'id' => 'treinos-' . (int)$id_aluno,
                'acao' => 'agenda',
                'tipo' => 'success',
                'titulo' => 'Treinos Agendados',
                'mensagem' => 'Tem ' . $total_agendamentos_futuros . ' treino(s) confirmado(s).'
            ];
        }

        $total_notificacoes = count($notificacoes);

        $planoAtivoAgenda = $agendamentoDAO->obterDadosPlanoAtivo((int)$id_aluno);
        $modalidadesAluno = $agendamentoDAO->obterModalidadesAluno((int)$id_aluno);
        $idsModalidadesAluno = array_map(
            static fn (array $modalidade): int => (int)$modalidade['id_modalidade'],
            $modalidadesAluno
        );
        $turmas_brutas = $agendamentoDAO->listarTurmasAtivasComHorario(
            $idsModalidadesAluno
        );
        $limitesSemanaisPorModalidade = [];
        foreach ($idsModalidadesAluno as $idModalidadeAluno) {
            $limitesSemanaisPorModalidade[$idModalidadeAluno] = $agendamentoDAO->obterLimiteSemanalPlano(
                (int)$id_aluno,
                $idModalidadeAluno
            );
        }
        $agendamentosPorSemana = [];

        $hoje = new DateTime();
        $inicioCalendario = clone $hoje;
        $agendaExibeProximaSemana = (int)$hoje->format('N') >= 5;
        $inicioCalendario->modify($agendaExibeProximaSemana ? 'monday next week' : 'monday this week');
        $inicioCalendario->setTime(0, 0, 0);
        $fimCalendario = (clone $inicioCalendario)->modify('+6 days')->setTime(23, 59, 59);
        $reservasConfirmadas = $agendamentoDAO->listarReservasConfirmadasNoPeriodo(
            (int)$id_aluno,
            $inicioCalendario->format('Y-m-d H:i:s'),
            $fimCalendario->format('Y-m-d H:i:s')
        );
        $reservasPorAula = [];
        foreach ($reservasConfirmadas as $reservaConfirmada) {
            $chaveReserva = (int)$reservaConfirmada['id_turma'] . '|' . $reservaConfirmada['data_agendamento'];
            $reservasPorAula[$chaveReserva] = true;
        }

        for ($i = 1; $i <= 7; $i++) {
            $diaLoop = clone $inicioCalendario;
            $diaLoop->modify('+' . ($i - 1) . ' days');
            $inicioSemana = (clone $diaLoop)->modify('monday this week')->setTime(0, 0, 0);
            $fimSemana = (clone $inicioSemana)->modify('sunday this week')->setTime(23, 59, 59);
            $chaveSemana = $inicioSemana->format('Y-m-d');
            if (!array_key_exists($chaveSemana, $agendamentosPorSemana)) {
                $agendamentosPorSemana[$chaveSemana] = [];
                foreach ($idsModalidadesAluno as $idModalidadeAluno) {
                    $agendamentosPorSemana[$chaveSemana][$idModalidadeAluno] =
                        $agendamentoDAO->contarAgendamentosSemanaPorModalidade(
                            (int)$id_aluno,
                            $idModalidadeAluno,
                            $inicioSemana->format('Y-m-d H:i:s'),
                            $fimSemana->format('Y-m-d H:i:s')
                        );
                }
            }
            $nomeDiaPt = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'][(int)$diaLoop->format('w')];
            $calendarioSemanal[$i] = [
                'nome_dia' => $nomeDiaPt,
                'data_iso' => $diaLoop->format('Y-m-d'),
                'data_exibicao' => $diaLoop->format('d/m'),
                'aulas' => []
            ];
            foreach ($turmas_brutas as $turma) {
                $diaTurma = trim(ucfirst(strtolower($turma['dia_semana'] ?? '')));
                if (empty($diaTurma) || $diaTurma === 'Geral' || stripos($diaTurma, $nomeDiaPt) !== false) {
                    $turma['hora_inicio'] = $turma['hora_inicio'] ?? '19:00:00';
                    $idModalidadeTurma = (int)$turma['id_modalidade'];
                    $turma['limite_atingido'] =
                        ($agendamentosPorSemana[$chaveSemana][$idModalidadeTurma] ?? 0)
                        >= ($limitesSemanaisPorModalidade[$idModalidadeTurma] ?? 0);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css?v=<?= filemtime(__DIR__ . '/../../assets/css/estilo.css'); ?>">
    <link rel="stylesheet" href="../../assets/css/aluno.css?v=<?= filemtime(__DIR__ . '/../../assets/css/aluno.css'); ?>">
    <link rel="stylesheet" href="../../assets/css/gerente.css">
</head>

<body class="aluno-theme<?= $is_primeiro_acesso ? ' aluno-contract-page' : ''; ?>">

    <div class="d-print-none">
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </div>

    <main class="container py-4 aluno-dashboard">
        
        <?php if ($is_primeiro_acesso): ?>
            <!-- TELA DE PRIMEIRO ACESSO (CONTRATO) -->
            <div class="row justify-content-center d-print-block">
                <div class="col-lg-10">
                    <div class="card shadow border-0 rounded-3">
                        <div class="card-header bg-dark text-white text-center py-3 d-print-none">
                            <h4 class="mb-0 fw-bold"> Bem-vindo ao seu Acesso Inicial</h4>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-center text-muted mb-4 d-print-none">Para liberar o seu painel de agendamentos, confirme os seus dados no contrato abaixo, assinale a caixa de aceite e defina a sua nova senha pessoal.</p>
                            <?php if (!empty($mensagem_erro)): ?>
                                <div class="alert alert-secondary py-2 text-center fw-bold d-print-none"><?= $mensagem_erro; ?></div>
                            <?php endif; ?>

                            <div id="area-impressao" class="p-4 mb-4" style="border: 1px solid #dee2e6; border-radius: 0.375rem; background-color: #ffffff; max-height: 450px; overflow-y: auto; color: #333; font-size: 0.9rem; line-height: 1.6;">
                                <div class="text-center mb-4">
                                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center gap-3 mb-3">
                                        <div class="p-2 rounded bg-white border">
                                            <img
                                                src="../../assets/img/logo_tokka.png"
                                                alt="Logo da academia"
                                                style="width: 96px; height: 96px; object-fit: contain;"
                                            >
                                        </div>
                                        <div>
                                            <p class="fw-bold text-uppercase mb-1"><?= htmlspecialchars($c_academia_nome, ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="small text-muted mb-0">Contrato de prestação de serviços</p>
                                        </div>
                                    </div>
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
                
                                <p><strong>Observações médicas ou gerais:</strong> <?= htmlspecialchars($c_obs) ?></p>
                                <h3 class="fw-bold mt-4 mb-2" style="color: #212529; font-size: 1.1rem; border-bottom: 1px solid #eee;">Dados do Contratado</h3>
                                <p class="mb-1"><strong>Contratado:</strong> <?= htmlspecialchars($c_academia_nome, ENT_QUOTES, 'UTF-8'); ?></p>
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
                            <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3 d-print-none">
                                <button type="button" class="btn btn-outline-dark fw-bold" onclick="window.print()">
                                    Imprimir contrato
                                </button>
                                <span class="small text-muted align-self-sm-center">A impressão é opcional. Para continuar, aceite o contrato e defina sua senha.</span>
                            </div>
                            <form method="POST" action="" class="d-print-none">
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
                                <button type="submit" class="btn btn-success w-100 fw-bold py-3 fs-5 shadow-sm">Avançar para o painel</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <header class="aluno-dashboard-welcome">
                <div class="text-start">
                    <h2 class="text-start">Olá, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>!</h2>
                    <p class="text-start text-muted mb-0">Gerencie seus agendamentos, consulte sua evolução, acompanhe suas avaliações e realize pagamentos.</p>
                </div>
            </header>

            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alert alert-success text-center py-2"><?= $mensagem_sucesso; ?></div>
            <?php endif; ?>
            <?php if (!empty($mensagem_erro)): ?>
                <div class="alert alert-secondary text-center py-2"><?= $mensagem_erro; ?></div>
            <?php endif; ?>

<?php ob_start(); ?>
<section class="mb-4">
    <div class="row g-3 align-items-stretch">

        <!-- CARD 1: GRADUAÇÃO -->
        <div class="col-12 col-md-4">
            <div class="aluno-stat-card h-100">
                <div class="aluno-stat-main">
                    <?php
                        $corFaixaMap = [
                            'branca'  => '#f0f0f0',
                            'amarela' => '#f5c518',
                            'laranja' => '#f97316',
                            'azul'    => '#3b82f6',
                            'verde'   => '#22c55e',
                            'roxa'    => '#a855f7',
                            'marrom'  => '#92400e',
                            'preta'   => '#111111',
                        ];
                        $corFaixaAtual = '#9ca3af';
                        foreach ($corFaixaMap as $nomeCor => $hexCor) {
                            if (strpos($faixaNormalizada ?? '', $nomeCor) !== false) {
                                $corFaixaAtual = $hexCor;
                                break;
                            }
                        }
                    ?>
                    <div class="aluno-stat-icon aluno-stat-icon--grad" aria-hidden="true">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <div class="aluno-stat-body">
                        <span class="aluno-stat-label">Graduação</span>
                        <?php if ($faixaCadastrada): ?>
                            <strong class="aluno-stat-value"><?= htmlspecialchars($faixa_aluno, ENT_QUOTES, 'UTF-8') ?></strong>
                            <small class="aluno-stat-sub">
                                <?= htmlspecialchars($grau_aluno, ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($graduacoesPrevistasPainel)): ?>
                                    · Próxima: <?= date('d/m/Y', strtotime((string)$graduacoesPrevistasPainel[0]['data_prevista'])) ?>
                                <?php else: ?>
                                    · Sem próxima previsão
                                <?php endif; ?>
                            </small>
                        <?php else: ?>
                            <strong class="aluno-stat-value">Faixa não cadastrada</strong>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="aluno-stat-belt-bar" aria-hidden="true">
                    <span class="aluno-stat-belt-bar-fill" style="background:<?= $corFaixaAtual ?>;" title="Faixa: <?= htmlspecialchars($faixa_aluno ?? 'Não cadastrada', ENT_QUOTES, 'UTF-8') ?>"></span>
                </div>
                <div class="aluno-stat-footer">
                    <span><i class="bi bi-clipboard-check me-1"></i><?= !empty($avaliacoesAlunoPainel) ? count($avaliacoesAlunoPainel) . ' avaliação(ões)' : 'Nenhuma avaliação' ?></span>
                    <a href="historico_graduacao.php#graduacoes-previstas" class="aluno-stat-link" title="Ver detalhes da graduação">
                        Detalhes <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- CARD 2: EVOLUÇÃO (NÍVEL E XP) -->
        <div class="col-12 col-md-4">
            <div class="aluno-stat-card aluno-stat-card--gold h-100">
                <div class="aluno-stat-main">
                    <div class="aluno-stat-icon aluno-stat-icon--gold" aria-hidden="true"><i class="bi bi-trophy-fill"></i></div>
                    <div class="aluno-stat-body">
                        <span class="aluno-stat-label">Evolução</span>
                        <strong class="aluno-stat-value">Nível <?= (int)$num_nivel ?> · <?= htmlspecialchars($nome_nivel, ENT_QUOTES, 'UTF-8') ?></strong>
                        <div class="aluno-stat-progress" role="progressbar" aria-valuenow="<?= (int)round($progresso_xp) ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="aluno-stat-progress-bar" style="width: <?= (float)$progresso_xp ?>%;"></div>
                        </div>
                        <small class="aluno-stat-sub"><?= (int)$xp_atual ?> / <?= (int)$xp_max ?> XP</small>
                    </div>
                </div>
                <div class="aluno-stat-footer">
                    <span><i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars($dataAtualAluno, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </div>

        <!-- CARD 3: MENSALIDADE -->
        <div class="col-12 col-md-4">
            <?php
                $statusPagamentoUpper = strtoupper((string)$status_pagamento);
                $historicoMensalidadeCard = [];
                foreach ($historico_pagamentos as $pagHist) {
                    $stHist = strtoupper((string)($pagHist['status'] ?? 'PENDENTE'));
                    if ($stHist === 'PENDENTE' && $pagHist['data_vencimento'] < date('Y-m-d')) {
                        $stHist = 'ATRASADO';
                    }
                    $mapaHist = [
                        'PAGO'       => ['payment-bar--paid', 'Pago'],
                        'ATRASADO'   => ['payment-bar--late', 'Atrasado'],
                        'EM_ANALISE' => ['payment-bar--review', 'Em análise'],
                    ];
                    [$classeHist, $rotuloHist] = $mapaHist[$stHist] ?? ['payment-bar--pending', 'Pendente'];
                    $historicoMensalidadeCard[] = [
                        'valor'      => (float)$pagHist['valor'],
                        'vencimento' => $pagHist['data_vencimento'],
                        'pagamento'  => $pagHist['data_pagamento'],
                        'classe'     => $classeHist,
                        'rotulo'     => $rotuloHist,
                    ];
                }
                // $historico_pagamentos está em ordem cronológica (mais antigo -> mais recente)
                $ultimosSeisMeses = array_slice($historicoMensalidadeCard, -6);
                $maiorValorMini = 0.0;
                foreach ($ultimosSeisMeses as $itemMini) {
                    $maiorValorMini = max($maiorValorMini, $itemMini['valor']);
                }
            ?>
            <div class="aluno-stat-card h-100">
                <div class="aluno-stat-main">
                    <div class="aluno-stat-icon aluno-stat-icon--payment" aria-hidden="true"><i class="bi bi-cash-coin"></i></div>
                    <div class="aluno-stat-body">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="aluno-stat-label">Mensalidade</span>
                            <?php if ($statusPagamentoUpper === 'PAGO'): ?>
                                <span class="aluno-stat-pill aluno-stat-pill--ok">Confirmado</span>
                            <?php elseif ($statusPagamentoUpper === 'EM_ANALISE'): ?>
                                <span class="aluno-stat-pill aluno-stat-pill--info">Em análise</span>
                            <?php else: ?>
                                <span class="aluno-stat-pill aluno-stat-pill--danger">Pendente</span>
                            <?php endif; ?>
                        </div>
                        <strong class="aluno-stat-value"><?= htmlspecialchars($valor_pagamento, ENT_QUOTES, 'UTF-8') ?></strong>
                        <small class="aluno-stat-sub">Venc: <?= htmlspecialchars($data_vencimento, ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                </div>
                <!-- HISTÓRICO DOS ÚLTIMOS 6 MESES -->
                <div class="aluno-mini-history">
                    <div class="aluno-mini-history-title">Últimos 6 meses</div>
                    <?php if (empty($ultimosSeisMeses)): ?>
                        <p class="aluno-stat-sub mb-0">Ainda não há pagamentos registrados.</p>
                    <?php else: ?>
                        <ul class="aluno-mini-history-chart list-unstyled mb-0">
                            <?php foreach ($ultimosSeisMeses as $itemMini): ?>
                                <?php $alturaMini = $maiorValorMini > 0 ? max(12, ($itemMini['valor'] / $maiorValorMini) * 100) : 12; ?>
                                <li class="aluno-mini-history-item"
                                    title="<?= htmlspecialchars(date('m/Y', strtotime($itemMini['vencimento'])) . ' · ' . $itemMini['rotulo'] . ' · R$ ' . number_format($itemMini['valor'], 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?>">
                                    <span class="aluno-mini-history-bar <?= $itemMini['classe'] ?>" style="height: <?= number_format($alturaMini, 0, '.', '') ?>%;"></span>
                                    <span class="aluno-mini-history-date"><?= date('m/y', strtotime($itemMini['vencimento'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="aluno-stat-footer">
                    <?php if (!empty($historicoMensalidadeCard)): ?>
                        <button type="button" class="aluno-stat-textlink" data-bs-toggle="modal" data-bs-target="#modalHistoricoMensalidade">
                            <i class="bi bi-clock-history me-1"></i>Ver histórico
                        </button>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                    <?php if ($statusPagamentoUpper !== 'PAGO' && $statusPagamentoUpper !== 'EM_ANALISE' && $id_pagamento_atual !== null && !$comprovante_pagamento_enviado): ?>
                        <button type="button" class="aluno-stat-link" data-bs-toggle="modal" data-bs-target="#modalPagamento">
                            Escolher pagamento <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    <?php elseif ($statusPagamentoUpper === 'PAGO'): ?>
                        <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Mensalidade em dia</span>
                    <?php else: ?>
                        <span class="fw-semibold">Em conferência pela academia</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</section>
<?php $progressoAlunoHtml = ob_get_clean(); ?>
            <!-- SEÇÃO FINANCEIRA / STATUS DA MENSALIDADE -->
            <?php ob_start(); ?>
            <?php $statusPagamentoUpper = strtoupper((string)$status_pagamento); ?>
            <?php
                $maiorValorHistorico = 0.0;
                foreach ($historico_pagamentos as $pagamentoHistorico) {
                    $maiorValorHistorico = max($maiorValorHistorico, (float)$pagamentoHistorico['valor']);
                }
            ?>
            <section class="card card-pagamento aluno-dashboard-panel border-0 shadow-sm mb-4 overflow-hidden" aria-labelledby="estadoFinanceiroTitulo">
                <div class="card-header bg-dark text-white border-0 px-3 py-2">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div>
                            <h5 class="fw-bold mb-0" id="estadoFinanceiroTitulo"> Mensalidade</h5>
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
                                <div class="payment-current-value">
                                    <div>
                                        <span class="text-muted small text-uppercase fw-bold">Valor</span>
                                        <p class="fs-3 fw-bold text-dark mb-1"><?= htmlspecialchars($valor_pagamento, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                </div>
                                <?php if ($statusPagamentoUpper !== 'PAGO' && $statusPagamentoUpper !== 'EM_ANALISE' && $id_pagamento_atual !== null && !$comprovante_pagamento_enviado): ?>
                                    <button type="button" class="btn btn-danger btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalPagamento">
                                        Escolher forma de pagamento
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
                                <div class="payment-current-due">
                                    <span class="text-muted small text-uppercase fw-bold">Vencimento</span>
                                    <p class="fw-semibold text-dark mb-0"><?= htmlspecialchars($data_vencimento, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
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
            <?php $cardPagamentoHtml = ob_get_clean(); ?>
            <?= str_replace('<!-- ALUNO-PAYMENT-CARD -->', $cardPagamentoHtml, $progressoAlunoHtml); ?>

            <?php
                $modaisAgendamento = [];
                $limiteAgendamento = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->modify('+30 minutes');
            ?>
            <!-- CALENDÁRIO SEMANAL COMPACTO -->
            <section class="card shadow-sm border p-3 mb-4 aluno-dashboard-panel aluno-agenda-panel" aria-labelledby="agendaSemanalTitulo">
                <h2 id="agendaSemanalTitulo" class="aluno-dashboard-section-title">AGENDA SEMANAL DE TREINOS</h2>
                <p class="aluno-dashboard-section-subtitle">
                    <?php if (!$agendaExibeProximaSemana): ?>
                        Escolha uma aula e confirme seu agendamento.
                    <?php endif; ?>
                </p>
                <?php if ($idsModalidadesAluno === []): ?>
                    <div class="alert alert-warning small text-center py-2" role="alert">
                        O seu plano ainda não está vinculado a uma modalidade. Peça à academia para atualizar o cadastro para liberar as aulas.
                    </div>
                <?php else: ?>
                    <p class="text-muted small mb-3 aluno-agenda-modalidades">
                        Modalidades cadastradas:
                        <strong><?= htmlspecialchars(implode(', ', array_column($modalidadesAluno, 'modalidade_nome')), ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>
                <?php endif; ?>

                <div class="d-flex overflow-auto pb-2 mx-auto aluno-calendar">
                    <?php foreach ($calendarioSemanal as $dia): ?>
                        <div class="shadow-sm border rounded aluno-calendar-day">
                            <div class="aluno-calendar-day-header fw-bold text-center p-1">
                                <?= $dia['nome_dia']; ?><br>
                                <span class="fw-normal"><?= $dia['data_exibicao']; ?></span>
                            </div>
                            <div class="p-2">
                                <?php if (empty($dia['aulas'])): ?>
                                    <p class="text-muted text-center small fst-italic my-3" style="font-size: 0.75rem;">Sem aulas</p>
                                <?php else: ?>
                                    <?php foreach ($dia['aulas'] as $aula): ?>
                                        <div class="p-2 rounded mb-2 border text-center aluno-calendar-class">
                                            <span class="d-block fw-bold aluno-calendar-class-name"><?= htmlspecialchars($aula['nome_turma']); ?></span>
                                            <span class="d-block mb-2 aluno-calendar-class-time"> <?= date('H:i', strtotime($aula['hora_inicio'])); ?></span>
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
                                            <?php elseif ($dataHoraAula < $limiteAgendamento): ?>
                                                <span class="badge bg-secondary w-100">Agendamento encerrado (mín. 30 min antes)</span>
                                            <?php elseif (isset($reservasPorAula[(int)$aula['id_turma'] . '|' . $dia['data_iso']])): ?>
                                                <button type="button" class="btn btn-success btn-sm w-100 fw-bold border-0" style="font-size: 0.75rem; padding: 4px 0;" disabled aria-pressed="true">
                                                    Agendado
                                                </button>
                                            <?php elseif ($aula['limite_atingido']): ?>
                                                <span class="badge bg-secondary w-100" title="O limite semanal do seu plano foi atingido.">
                                                    Limite semanal atingido
                                                </span>
                                            <?php else: ?>
                                                <?php
                                                    $modalAgendamentoId = 'modalAgendar-' . $dia['data_iso'] . '-' . (int)$aula['id_turma'];
                                                    $modaisAgendamento[] = [
                                                        'id' => $modalAgendamentoId,
                                                        'id_turma' => (int)$aula['id_turma'],
                                                        'data_iso' => $dia['data_iso'],
                                                        'hora' => $horaAulaExibicao,
                                                        'turma' => (string)$aula['nome_turma']
                                                    ];
                                                ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-success btn-sm w-100 fw-bold border-0"
                                                    style="font-size: 0.75rem; padding: 4px 0;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#<?= htmlspecialchars($modalAgendamentoId, ENT_QUOTES, 'UTF-8'); ?>"
                                                >
                                                    Agendar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php foreach ($modaisAgendamento as $modalAgendamento): ?>
                <div
                    class="modal fade"
                    id="<?= htmlspecialchars($modalAgendamento['id'], ENT_QUOTES, 'UTF-8'); ?>"
                    tabindex="-1"
                    aria-labelledby="<?= htmlspecialchars($modalAgendamento['id'], ENT_QUOTES, 'UTF-8'); ?>Label"
                    aria-hidden="true"
                >
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0 aluno-agendamento-confirmation">
                            <div class="modal-header bg-dark text-white">
                                <h5 class="modal-title fw-bold" id="<?= htmlspecialchars($modalAgendamento['id'], ENT_QUOTES, 'UTF-8'); ?>Label">
                                    Confirmar agendamento
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2">Deseja agendar esta aula?</p>
                                <dl class="row mb-0">
                                    <dt class="col-4">Turma</dt>
                                    <dd class="col-8"><?= htmlspecialchars($modalAgendamento['turma'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-4">Data</dt>
                                    <dd class="col-8"><?= date('d/m/Y', strtotime($modalAgendamento['data_iso'])); ?></dd>
                                    <dt class="col-4">Horário</dt>
                                    <dd class="col-8"><?= date('H:i', strtotime($modalAgendamento['hora'])); ?></dd>
                                </dl>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                                <form method="POST" action="../../controller/AgendamentoController.php?acao=agendar_semana" class="m-0 p-0 bg-transparent border-0 shadow-none">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id_turma" value="<?= $modalAgendamento['id_turma']; ?>">
                                    <input type="hidden" name="data_escolhida" value="<?= htmlspecialchars($modalAgendamento['data_iso'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="hora_aula" value="<?= htmlspecialchars($modalAgendamento['hora'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-success fw-bold">Confirmar agendamento</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- PRÓXIMOS TREINOS AGENDADOS -->
            <section class="card shadow-sm border p-3 mb-3 aluno-dashboard-panel aluno-upcoming-panel">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3 aluno-dashboard-section-heading">
                    <div>
                        <h3 class="aluno-dashboard-section-title">Próximos treinos</h3>
                        <p class="aluno-dashboard-section-subtitle">Acompanhe as aulas que já estão agendadas.</p>
                    </div>
                    <a href="historico_frequencia.php" class="btn btn-outline-dark btn-sm fw-bold text-nowrap">
                           Visualizar Histórico de Frequência
                    </a>
                </div>
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
                                                        style="border: 0 !important; outline: 0 !important; box-shadow: none !important; appearance: none; -webkit-appearance: none;"
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
            </section>

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

    <!-- MODAL: HISTÓRICO DE MENSALIDADES -->
    <?php if (!empty($historicoMensalidadeCard)): ?>
    <div class="modal fade" id="modalHistoricoMensalidade" tabindex="-1" aria-labelledby="modalHistoricoMensalidadeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalHistoricoMensalidadeLabel">Histórico de mensalidades</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Vencimento</th>
                                <th>Pago em</th>
                                <th>Valor</th>
                                <th class="pe-3 text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($historicoMensalidadeCard) as $itemHist): ?>
                                <tr>
                                    <td class="ps-3"><?= date('d/m/Y', strtotime($itemHist['vencimento'])) ?></td>
                                    <td><?= !empty($itemHist['pagamento']) ? date('d/m/Y', strtotime($itemHist['pagamento'])) : '---' ?></td>
                                    <td>R$ <?= number_format($itemHist['valor'], 2, ',', '.') ?></td>
                                    <td class="pe-3 text-end">
                                        <span class="aluno-status-chip <?= $itemHist['classe'] ?>"><?= htmlspecialchars($itemHist['rotulo'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- MODAL DE OPÇÕES DE PAGAMENTO -->
    <?php if ($id_pagamento_atual !== null && strtoupper((string)$status_pagamento) !== 'PAGO' && !$comprovante_pagamento_enviado): ?>
    <div class="modal fade" id="modalPagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalPagamentoLabel">Pagamento da mensalidade</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <ul class="nav nav-tabs nav-fill mb-4 flex-wrap" id="pagamento-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="pagamento-pix-tab" data-bs-toggle="tab" data-bs-target="#pagamento-pix" type="button" role="tab" aria-controls="pagamento-pix" aria-selected="true">1. PIX</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="pagamento-debito-tab" data-bs-toggle="tab" data-bs-target="#pagamento-debito" type="button" role="tab" aria-controls="pagamento-debito" aria-selected="false">2. Débito</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="pagamento-credito-tab" data-bs-toggle="tab" data-bs-target="#pagamento-credito" type="button" role="tab" aria-controls="pagamento-credito" aria-selected="false">3. Crédito</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active text-center" id="pagamento-pix" role="tabpanel" aria-labelledby="pagamento-pix-tab" tabindex="0">
                            <p class="text-muted mb-1">Valor da mensalidade</p>
                            <p class="fs-4 fw-bold text-dark mb-3"><?= htmlspecialchars($valor_pagamento, ENT_QUOTES, 'UTF-8'); ?></p>
                            <canvas id="qrCodeDemonstracao" class="img-fluid border rounded p-2 mb-2" width="232" height="232" role="img" aria-label="QR code visual fictício, não utilizável para pagamento"></canvas>
                            <p class="small fw-bold text-warning-emphasis mb-1">QR code fictício para demonstração</p>
                            <p class="small text-muted mb-0">Este código não processa pagamentos. Para pagar por PIX, consulte a secretaria da academia.</p>
                        </div>

                        <div class="tab-pane fade text-center" id="pagamento-debito" role="tabpanel" aria-labelledby="pagamento-debito-tab" tabindex="0">
                            <p class="text-muted mb-1">Valor da mensalidade</p>
                            <p class="fs-4 fw-bold text-dark mb-3"><?= htmlspecialchars($valor_pagamento, ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="alert alert-info small text-start" role="alert">
                                O pagamento com cartão de débito é feito presencialmente na academia. Este portal não coleta dados do cartão nem processa transações.
                            </div>
                        </div>

                        <div class="tab-pane fade text-center" id="pagamento-credito" role="tabpanel" aria-labelledby="pagamento-credito-tab" tabindex="0">
                            <p class="text-muted mb-1">Valor da mensalidade</p>
                            <p class="fs-4 fw-bold text-dark mb-3"><?= htmlspecialchars($valor_pagamento, ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="alert alert-info small text-start" role="alert">
                                O pagamento com cartão de crédito é feito presencialmente na academia. Este portal não coleta dados do cartão nem processa transações.
                            </div>
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