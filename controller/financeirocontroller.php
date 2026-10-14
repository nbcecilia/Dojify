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
            default:
                header('Location: ../view/gerente/home_gerente.php');
                exit;
        }
    }

    private function baixarPagamento(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $idsPagamentosStr = trim($_POST['ids_pagamentos'] ?? '');
        $idPagamentoUnico = filter_input(INPUT_POST, 'id_pagamento', FILTER_VALIDATE_INT);
        $formaPagamento = trim($_POST['forma_pagamento'] ?? 'DINHEIRO');
        $dataPagamento = trim($_POST['data_pagamento'] ?? date('Y-m-d'));

        $ids = [];
        if (!empty($idsPagamentosStr)) {
            $ids = array_map('intval', explode(',', $idsPagamentosStr));
        } elseif ($idPagamentoUnico) {
            $ids = [$idPagamentoUnico];
        }

        $sucesso = true;
        foreach ($ids as $id) {
            if (!$this->dao->registarRecebimento($id, $formaPagamento, $dataPagamento)) {
                $sucesso = false;
            }
        }

        if ($sucesso && !empty($ids)) {
            header('Location: ../view/gerente/listar_pagamentos.php?sucesso=1');
        } else {
            header('Location: ../view/gerente/listar_pagamentos.php?erro=1');
        }
        exit;
    }
} // <--- A chaveta de fecho da classe que estava em falta e causava o erro!

if (isset($_REQUEST['acao'])) {
    $controller = new FinanceiroController();
    $controller->processarRequisicao();
}