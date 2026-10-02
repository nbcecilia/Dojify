<?php
// controller/ComprovantePagamentoController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/dao/PagamentoDAO.php';

class ComprovantePagamentoController {
    private const TAMANHO_MAXIMO = 5242880;
    private const TIPOS_PERMITIDOS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf'
    ];

    public function processar(): void {
        $acao = $_GET['acao'] ?? 'enviar';

        if ($acao === 'visualizar') {
            $this->visualizar();
            return;
        }

        $this->enviar();
    }

    private function enviar(): void {
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !isset($_SESSION['usuario']) ||
            (int)($_SESSION['usuario']['perfil_id'] ?? 0) !== 4
        ) {
            $this->redirecionarAluno('erro=acesso');
        }

        $tokenSessao = $_SESSION['csrf_token'] ?? '';
        $tokenFormulario = $_POST['csrf_token'] ?? '';
        if (!is_string($tokenFormulario) || $tokenSessao === '' || !hash_equals($tokenSessao, $tokenFormulario)) {
            $this->redirecionarAluno('erro=token');
        }

        $idPagamento = filter_input(INPUT_POST, 'id_pagamento', FILTER_VALIDATE_INT);
        $idAluno = (int)$_SESSION['usuario']['id_usuario'];
        $arquivo = $_FILES['comprovante'] ?? null;

        if (
            !$idPagamento ||
            $idAluno <= 0 ||
            !is_array($arquivo) ||
            ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
            !isset($arquivo['tmp_name'], $arquivo['size']) ||
            !is_string($arquivo['tmp_name']) ||
            !is_numeric($arquivo['size']) ||
            !is_uploaded_file($arquivo['tmp_name'])
        ) {
            $this->redirecionarAluno('erro=comprovante');
        }

        if ((int)$arquivo['size'] <= 0 || (int)$arquivo['size'] > self::TAMANHO_MAXIMO) {
            $this->redirecionarAluno('erro=tamanho');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipoMime = $finfo->file($arquivo['tmp_name']);
        if (!isset(self::TIPOS_PERMITIDOS[$tipoMime])) {
            $this->redirecionarAluno('erro=formato');
        }

        $diretorio = dirname(__DIR__) . '/storage/comprovantes';
        if (!is_dir($diretorio) && !mkdir($diretorio, 0750, true) && !is_dir($diretorio)) {
            error_log('Não foi possível criar o diretório privado para comprovantes.');
            $this->redirecionarAluno('erro=servidor');
        }

        $nomeArquivo = bin2hex(random_bytes(24)) . '.' . self::TIPOS_PERMITIDOS[$tipoMime];
        $caminhoArquivo = $diretorio . '/' . $nomeArquivo;
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
            error_log('Não foi possível armazenar o comprovante enviado.');
            $this->redirecionarAluno('erro=servidor');
        }

        try {
            $dao = new PagamentoDAO();
            $registrado = $dao->registrarEnvioComprovante($idPagamento, $idAluno, $nomeArquivo);
        } catch (PDOException $e) {
            error_log('Erro ao associar comprovante ao pagamento: ' . $e->getMessage());
            $registrado = false;
        }

        if (!$registrado) {
            unlink($caminhoArquivo);
            $this->redirecionarAluno('erro=pagamento');
        }

        $this->redirecionarAluno('sucesso=comprovante');
    }

    private function visualizar(): void {
        if (
            !isset($_SESSION['usuario']) ||
            (int)($_SESSION['usuario']['perfil_id'] ?? 0) !== 2 ||
            empty($_SESSION['id_academia'])
        ) {
            http_response_code(403);
            exit('Acesso negado.');
        }

        $idPagamento = filter_input(INPUT_GET, 'id_pagamento', FILTER_VALIDATE_INT);
        if (!$idPagamento) {
            http_response_code(404);
            exit('Comprovante não encontrado.');
        }

        try {
            $dao = new PagamentoDAO();
            $nomeArquivo = $dao->buscarComprovanteDaAcademia(
                $idPagamento,
                (int)$_SESSION['id_academia']
            );
        } catch (PDOException $e) {
            error_log('Erro ao localizar comprovante para o gerente: ' . $e->getMessage());
            http_response_code(500);
            exit('Não foi possível abrir o comprovante.');
        }

        if ($nomeArquivo === null || basename($nomeArquivo) !== $nomeArquivo) {
            http_response_code(404);
            exit('Comprovante não encontrado.');
        }

        $caminhoArquivo = dirname(__DIR__) . '/storage/comprovantes/' . $nomeArquivo;
        if (!is_file($caminhoArquivo)) {
            http_response_code(404);
            exit('Arquivo de comprovante não encontrado.');
        }

        $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
        $tiposPorExtensao = array_flip(self::TIPOS_PERMITIDOS);
        if (!isset($tiposPorExtensao[$extensao])) {
            http_response_code(415);
            exit('Formato de comprovante não suportado.');
        }

        header('Content-Type: ' . $tiposPorExtensao[$extensao]);
        header('Content-Disposition: inline; filename="comprovante-pagamento.' . $extensao . '"');
        header('Content-Length: ' . filesize($caminhoArquivo));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($caminhoArquivo);
    }

    private function redirecionarAluno(string $resultado): void {
        header('Location: ../view/aluno/home_aluno.php?' . $resultado);
        exit;
    }
}

(new ComprovantePagamentoController())->processar();
