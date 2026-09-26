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

$acao = filter_input(INPUT_GET, 'acao', FILTER_SANITIZE_SPECIAL_CHARS);

if ($acao === 'atualizar') {
    $idPlano = filter_input(INPUT_POST, 'id_plano', FILTER_VALIDATE_INT);
    $idUsuarioAluno = filter_input(INPUT_POST, 'id_usuario_aluno', FILTER_VALIDATE_INT);
    $nomePlano = filter_input(INPUT_POST, 'nome_plano', FILTER_SANITIZE_SPECIAL_CHARS);
    $valorStr = filter_input(INPUT_POST, 'valor', FILTER_SANITIZE_SPECIAL_CHARS);
    $dataInicio = filter_input(INPUT_POST, 'data_inicio', FILTER_SANITIZE_SPECIAL_CHARS);
    $dataFim = filter_input(INPUT_POST, 'data_fim', FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$idPlano || !$idUsuarioAluno) {
        header('Location: ../view/gerente/listar_plano.php?erro=dados_invalidos');
        exit;
    }

    // Tratar o valor monetário (substitui vírgula por ponto para aceitar no banco)
    $valorTratado = str_replace(['.', ','], ['', '.'], $valorStr);
    $valorFloat = floatval($valorTratado);

    $dto = new PlanoDTO();
    $dto->setIdPlano($idPlano);
    $dto->setIdUsuarioAluno($idUsuarioAluno);
    $dto->setNomePlano($nomePlano);
    $dto->setValor($valorFloat);
    $dto->setDataInicio($dataInicio);
    $dto->setDataFim(!empty($dataFim) ? $dataFim : null);
    $dto->setStatus($status);

    $dao = new PlanoDAO();
    $sucesso = $dao->atualizar($dto);

    if ($sucesso) {
        header('Location: ../view/gerente/listar_plano.php?sucesso=atualizado');
        exit;
    } else {
        header('Location: ../view/gerente/editar_plano.php?id=' . $idPlano . '&erro=falha_atualizar');
        exit;
    }
}