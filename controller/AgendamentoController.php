<?php
// controller/AgendamentoController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/dao/AgendamentoDAO.php';

class AgendamentoController
{
    private \AgendamentoDAO $dao;

    public function __construct()
    {
        $this->dao = new \AgendamentoDAO();
    }

    public function processar(): void
    {
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['perfil_id'] ?? 0) !== 4) {
            $this->redirecionarParaHome('erro=acesso_negado');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionarParaHome('erro=metodo_invalido');
        }

        $tokenSessao = $_SESSION['csrf_token'] ?? '';
        $tokenFormulario = $_POST['csrf_token'] ?? '';
        if (
            !is_string($tokenFormulario) ||
            !is_string($tokenSessao) ||
            $tokenSessao === '' ||
            !hash_equals($tokenSessao, $tokenFormulario)
        ) {
            $this->redirecionarParaHome('erro=token_agendamento');
        }

        $acao = $_GET['acao'] ?? '';

        if ($acao === 'agendar_semana') {
            $this->agendarSemana();
        } elseif ($acao === 'cancelar') {
            $this->cancelar();
        } else {
            $this->redirecionarParaHome('erro=acao_invalida');
        }
    }

    private function agendarSemana(): void
    {
        $id_aluno = (int)$_SESSION['usuario']['id_usuario'];
        $id_turma = filter_input(INPUT_POST, 'id_turma', FILTER_VALIDATE_INT);
        $data_escolhida = $_POST['data_escolhida'] ?? null; 
        $hora_aula = trim((string)($_POST['hora_aula'] ?? '19:00:00'));

        if (preg_match('/^\d{2}:\d{2}$/', $hora_aula)) {
            $hora_aula .= ':00';
        }

        $data_formatada = (string)$data_escolhida . ' ' . $hora_aula;
        $data_obj = DateTime::createFromFormat('!Y-m-d H:i:s', $data_formatada);
        $errosData = DateTime::getLastErrors();
        $dataValida = $data_obj !== false
            && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $hora_aula)
            && ($errosData === false || ($errosData['warning_count'] === 0 && $errosData['error_count'] === 0));

        if (!$id_turma || $id_turma <= 0 || !$dataValida) {
            $this->redirecionarParaHome('erro=dados_invalidos');
        }

        if ($data_obj <= new DateTime()) {
            $this->redirecionarParaHome('erro=data_passada');
        }

        $turmaInfo = $this->dao->obterInfoTurma($id_turma);
        $planoAtivo = $this->dao->obterDadosPlanoAtivo($id_aluno);
        $idModalidadePlano = (int)($planoAtivo['id_modalidade'] ?? 0);
        if ($idModalidadePlano <= 0) {
            $this->redirecionarParaHome('erro=plano_sem_modalidade');
        }
        if (
            $turmaInfo === null ||
            (int)$turmaInfo['id_modalidade'] !== $idModalidadePlano
        ) {
            $this->redirecionarParaHome('erro=modalidade_incompativel');
        }

        // 1. Verifica duplicidade
        if ($this->dao->verificarAgendamentoDuplicado($id_aluno, $id_turma, $data_formatada)) {
            $this->redirecionarParaHome('erro=agendamento_duplicado');
        }

        // 2. Verifica capacidade
        $capacidadeMax = (int)($turmaInfo['capacidade'] ?? 20);
        $vagasOcupadas = $this->dao->contarVagasOcupadas($id_turma, $data_formatada);

        if ($vagasOcupadas >= $capacidadeMax) {
            $this->redirecionarParaHome('erro=turma_lotada');
        }

        // 3. Verifica limite semanal do plano
        $limiteSemanal = $this->dao->obterLimiteSemanalPlano($id_aluno);
        
        $inicioSemana = clone $data_obj; 
        $inicioSemana->modify('monday this week')->setTime(0, 0, 0);
        $fimSemana = clone $data_obj; 
        $fimSemana->modify('sunday this week')->setTime(23, 59, 59);

        $totalSemana = $this->dao->contarAgendamentosSemana(
            $id_aluno, 
            $inicioSemana->format('Y-m-d H:i:s'), 
            $fimSemana->format('Y-m-d H:i:s')
        );

        if ($totalSemana >= $limiteSemanal) {
            $this->redirecionarParaHome('erro=limite_semanal');
        }

        // 4. Salva o agendamento
        if ($this->dao->registrarAgendamento($id_turma, $id_aluno, $data_formatada)) {
            $this->redirecionarParaHome('sucesso=agendado');
        } else {
            $this->redirecionarParaHome('erro=falha_sistema');
        }
    }

    private function cancelar(): void
    {
        $id_agendamento = filter_input(INPUT_POST, 'id_agendamento', FILTER_VALIDATE_INT);
        $id_aluno = (int)$_SESSION['usuario']['id_usuario'];

        if (!$id_agendamento) {
            $this->redirecionarParaHome('erro=dados_invalidos');
        }

        if ($this->dao->cancelarAgendamento($id_agendamento, $id_aluno)) {
            $this->redirecionarParaHome('sucesso=cancelado');
        } else {
            $this->redirecionarParaHome('erro=falha_sistema');
        }
    }

    private function redirecionarParaHome(string $params = ''): void
    {
        $url = '../view/aluno/home_aluno.php';
        if ($params !== '') {
            $url .= '?' . $params;
        }
        header("Location: $url");
        exit;
    }
}

// Inicia o processo
$controller = new AgendamentoController();
$controller->processar();