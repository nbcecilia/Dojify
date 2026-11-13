<?php
// controller/FinanceiroController.php

session_start();

require_once __DIR__ . '/../model/dao/PagamentoDAO.php';

class FinanceiroController {
    private \PagamentoDAO $dao;

    public function __construct() {
        $this->dao = new \PagamentoDAO();
    }

    public function processarRequisicao(): void {
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 2) {
            header('Location: ../view/login.php?erro=acesso_negado');
            exit;
        }

        $acao = $_REQUEST['acao'] ?? '';

        switch ($acao) {
            case 'baixar_pagamento':
                $this->baixarPagamento();
                break;
            case 'editar_pagamento':
                $this->editarPagamento();
                break;
            case 'editar_vencimento':
                $this->editarVencimento();
                break;
            case 'cancelar_pagamento':
                $this->cancelarPagamento();
                break;
            default:
                header('Location: ../view/gerente/home_gerente.php');
                exit;
        }
    }

    private function baixarPagamento(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionarComResultado(false);
        }

        $idPagamento = $this->obterIdPagamento();
        $idAcademia = (int)($_SESSION['id_academia'] ?? 0);
        $formaRecebida = $_POST['forma_pagamento'] ?? '';
        $dataRecebida = $_POST['data_pagamento'] ?? date('Y-m-d');
        if (!is_string($formaRecebida) || !is_string($dataRecebida)) {
            $this->redirecionarComResultado(false);
        }
        $formaPagamento = strtoupper(trim($formaRecebida));
        $dataPagamento = trim($dataRecebida);
        $formasPermitidas = ['DINHEIRO', 'PIX', 'CARTAO', 'OUTRO'];

        $sucesso = $idPagamento !== null
            && $idAcademia > 0
            && in_array($formaPagamento, $formasPermitidas, true)
            && $this->dataValida($dataPagamento)
            && $this->dao->registarRecebimento($idPagamento, $idAcademia, $formaPagamento, $dataPagamento);

        $this->redirecionarComResultado($sucesso, 'recebido');
    }

    private function editarPagamento(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionarComResultado(false);
        }

        $idPagamento = $this->obterIdPagamento();
        $idAcademia = (int)($_SESSION['id_academia'] ?? 0);
        $sucesso = false;

        if ($idPagamento !== null && $idAcademia > 0) {
            try {
                $pagamento = \PagamentoDTO::fromArray($_POST);
                $sucesso = $this->dao->editarPagamentosDaAcademia($idPagamento, $idAcademia, $pagamento);
            } catch (InvalidArgumentException $e) {
                $sucesso = false;
            }
        }

        $this->redirecionarComResultado($sucesso, 'atualizado');
    }

    private function cancelarPagamento(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionarComResultado(false);
        }

        $idPagamento = $this->obterIdPagamento();
        $idAcademia = (int)($_SESSION['id_academia'] ?? 0);
        $sucesso = $idPagamento !== null
            && $idAcademia > 0
            && $this->dao->cancelarPagamentosDaAcademia($idPagamento, $idAcademia);

        $this->redirecionarComResultado($sucesso, 'cancelado');
    }

    private function editarVencimento(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionarComResultado(false);
        }

        $idPagamento = $this->obterIdPagamento();
        $idAcademia = (int)($_SESSION['id_academia'] ?? 0);
        $dataVencimento = $_POST['data_vencimento'] ?? '';
        $sucesso = $idPagamento !== null
            && $idAcademia > 0
            && is_string($dataVencimento)
            && $this->dataValida(trim($dataVencimento))
            && $this->dao->editarDataVencimento($idPagamento, $idAcademia, trim($dataVencimento));

        $this->redirecionarComResultado($sucesso, 'atualizado');
    }

    private function obterIdPagamento(): ?int {
        $id = filter_var($_POST['id_pagamento'] ?? null, FILTER_VALIDATE_INT);
        return $id !== false && $id !== null && $id > 0 ? $id : null;
    }

    private function dataValida(string $data): bool {
        $dataObj = \DateTimeImmutable::createFromFormat('!Y-m-d', $data);
        $erros = \DateTimeImmutable::getLastErrors();

        return $dataObj !== false
            && $dataObj->format('Y-m-d') === $data
            && ($erros === false || ($erros['warning_count'] === 0 && $erros['error_count'] === 0));
    }

    private function redirecionarComResultado(bool $sucesso, string $tipo = ''): void {
        $parametro = $sucesso ? 'sucesso=' . rawurlencode($tipo) : 'erro=1';
        header('Location: ../view/gerente/listar_pagamentos.php?' . $parametro);
        exit;
    }
} // <--- A chaveta de fecho da classe que estava em falta e causava o erro!

if (isset($_REQUEST['acao'])) {
    $controller = new FinanceiroController();
    $controller->processarRequisicao();
}