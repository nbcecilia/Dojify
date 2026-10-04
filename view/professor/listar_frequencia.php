<?php
// view/aluno/listar_frequencia.php
session_start();

// Validação de acesso básica
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/PresencaDAO.php';
require_once __DIR__ . '/../../model/dto/Historico_FrequenciaDTO.php';

$dao = new PresencaDAO();

// ID do aluno passado por GET (ou o próprio aluno logado)
$idAluno = filter_input(INPUT_GET, 'id_aluno', FILTER_VALIDATE_INT) ?? $_SESSION['usuario']['id_usuario'];

$historico = $dao->listarPorAluno($idAluno);

// Cálculo do resumo de frequência
$presencas = 0;
$ausencias = 0;
foreach ($historico as $reg) {
    if ($reg->getStatusPresenca() === 1) $presencas++;
    if ($reg->getStatusPresenca() === 0) $ausencias++;
}
$totalAulas = $presencas + $ausencias;
$percentual = $totalAulas > 0 ? ($presencas / $totalAulas) * 100 : 0.0;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dojify - Histórico de Frequência</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Histórico de Frequência</h2>
            <a href="javascript:history.back()" class="btn btn-secondary btn-sm">Voltar</a>
        </div>

        <!-- Cards de Resumo -->
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-white p-3">
                    <h6 class="text-muted">Total de Aulas Registadas</h6>
                    <h3><?= $totalAulas ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-white p-3">
                    <h6 class="text-muted">Presenças</h6>
                    <h3 class="text-success"><?= $presencas ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-white p-3">
                    <h6 class="text-muted">Taxa de Frequência</h6>
                    <h3 class="text-primary"><?= number_format($percentual, 1, ',', '.') ?>%</h3>
                </div>
            </div>
        </div>

        <!-- Tabela de Histórico -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Turma</th>
                            <th>Data do Agendamento</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historico)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Nenhum registo de frequência encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historico as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item->getNomeTurma()) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($item->getDataAgendamento())) ?></td>
                                    <td class="text-center">
                                        <?php if ($item->getStatusPresenca() === 1): ?>
                                            <span class="badge bg-success">Presente</span>
                                        <?php elseif ($item->getStatusPresenca() === 0): ?>
                                            <span class="badge bg-danger">Ausente</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pendente / Sem Registo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>