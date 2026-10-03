<?php
// controller/PlanoController.php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 2) {
    header('Location: ../view/login.php');
    exit;
}

require_once __DIR__ . '/../model/dao/Conexao.php';
require_once __DIR__ . '/../model/dto/PlanoDTO.php';
require_once __DIR__ . '/../model/dao/PlanoDAO.php';
require_once __DIR__ . '/../model/dao/ModalidadeDAO.php';

$acao = filter_input(INPUT_GET, 'acao', FILTER_SANITIZE_SPECIAL_CHARS);

if ($acao === 'salvar' || $acao === 'atualizar') {
    $idPlano = $acao === 'atualizar'
        ? filter_input(INPUT_POST, 'id_plano', FILTER_VALIDATE_INT)
        : null;
    $idUsuarioAluno = filter_input(INPUT_POST, 'id_usuario_aluno', FILTER_VALIDATE_INT);
    $idModalidade = filter_input(INPUT_POST, 'id_modalidade', FILTER_VALIDATE_INT);
    $nomePlano = filter_input(INPUT_POST, 'nome_plano', FILTER_SANITIZE_SPECIAL_CHARS);
    $valorStr = filter_input(INPUT_POST, 'valor', FILTER_SANITIZE_SPECIAL_CHARS);
    $dataInicio = filter_input(INPUT_POST, 'data_inicio', FILTER_SANITIZE_SPECIAL_CHARS);
    $dataFim = filter_input(INPUT_POST, 'data_fim', FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$idUsuarioAluno || !$idModalidade || ($acao === 'atualizar' && !$idPlano)) {
        $destino = $acao === 'atualizar'
            ? '../view/gerente/editar_plano.php?id=' . (int)$idPlano . '&erro=dados_invalidos'
            : '../view/gerente/cadastrar_plano.php?erro=dados_invalidos';
        header('Location: ' . $destino);
        exit;
    }

    $idAcademia = (int)($_SESSION['usuario']['id_academia'] ?? 0);
    $modalidadeDAO = new ModalidadeDAO();
    $modalidade = $modalidadeDAO->buscarPorId($idModalidade, $idAcademia);
    $pdo = Conexao::getConexao();
    $stmtAluno = $pdo->prepare("
        SELECT 1 FROM usuario
        WHERE id_usuario = ? AND id_academia = ? AND perfil_id = 4
    ");
    $stmtAluno->execute([$idUsuarioAluno, $idAcademia]);
    if (!$modalidade || !$stmtAluno->fetchColumn()) {
        $destino = $acao === 'atualizar'
            ? '../view/gerente/editar_plano.php?id=' . $idPlano . '&erro=dados_invalidos'
            : '../view/gerente/cadastrar_plano.php?erro=dados_invalidos';
        header('Location: ' . $destino);
        exit;
    }

    // Tratar o valor monetário (substitui vírgula por ponto para aceitar no banco)
    $valorTratado = str_replace(['.', ','], ['', '.'], $valorStr);
    $valorFloat = floatval($valorTratado);

    $dto = new PlanoDTO();
    if ($idPlano) {
        $dto->setIdPlano($idPlano);
    }
    $dto->setIdUsuarioAluno($idUsuarioAluno);
    $dto->setIdModalidade($idModalidade);
    $dto->setNomePlano($nomePlano);
    $dto->setValor($valorFloat);
    $dto->setDataInicio($dataInicio);
    $dto->setDataFim(!empty($dataFim) ? $dataFim : null);
    $dto->setStatus($status);

    $dao = new PlanoDAO();
    $sucesso = $acao === 'atualizar'
        ? $dao->atualizar($dto)
        : $dao->inserir($dto);

    if ($sucesso) {
        header('Location: ../view/gerente/listar_plano.php?sucesso=' . ($acao === 'atualizar' ? 'atualizado' : 'cadastrado'));
        exit;
    } else {
        $destino = $acao === 'atualizar'
            ? '../view/gerente/editar_plano.php?id=' . $idPlano . '&erro=falha_atualizar'
            : '../view/gerente/cadastrar_plano.php?erro=falha_cadastro';
        header('Location: ' . $destino);
        exit;
    }
}