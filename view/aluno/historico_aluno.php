<?php
// view/aluno/historico_aluno.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['perfil_id'] !== 4) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/conexao.php';

$historico_treinos = [];
$mensagem_erro = "";
$id_aluno = $_SESSION['usuario']['id_usuario'];

try {
    $pdo_agenda = \Conexao::getConexao();

    // Buscar todo o histórico de treinos anteriores
    $stmt_hist = $pdo_agenda->prepare("
        SELECT a.data_agendamento, t.nome as nome_turma, p.status as status_presenca 
        FROM agendamento a 
        JOIN turma t ON a.id_turma = t.id_turma 
        LEFT JOIN presenca p ON a.id_agendamento = p.id_agendamento 
        WHERE a.id_usuario_aluno = ? AND a.data_agendamento < NOW()
        ORDER BY a.data_agendamento DESC
    ");
    $stmt_hist->execute([$id_aluno]);
    $historico_treinos = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $mensagem_erro = "Erro ao carregar o histórico: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Presenças - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>

<body style="background-color: var(--bg-body, #f8f9fa);">

    <div class="d-print-none">
        <?php include '../includes/header.php'; ?>
    </div>

    <main class="container py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>📜 Histórico Completo de Treinos</h2>
                <p class="text-muted mb-0">Consulte abaixo todas as suas aulas passadas e respetivos registos de presença.</p>
            </div>
            <div>
                <a href="home_aluno.php" class="btn btn-outline-secondary btn-sm">⬅️ Voltar ao Painel</a>
            </div>
        </div>

        <?php if (!empty($mensagem_erro)): ?>
            <div class="alert alert-danger text-center py-2"><?= $mensagem_erro; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border p-3">
            <div class="table-responsive">
                <?php if (empty($historico_treinos)): ?>
                    <p class="text-muted small fst-italic mb-0 text-center py-4">Ainda não há registos de treinos anteriores no seu histórico.</p>
                <?php else: ?>
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted">
                                <th>TURMA</th>
                                <th>DATA DA AULA</th>
                                <th>ESTADO DA PRESENÇA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historico_treinos as $hist): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($hist['nome_turma']); ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($hist['data_agendamento'])); ?></td>
                                    <td>
                                        <?php if ($hist['status_presenca'] === null): ?>
                                            <span class="badge bg-secondary">Pendente / Não lançado</span>
                                        <?php elseif ((int)$hist['status_presenca'] === 1): ?>
                                            <span class="badge bg-success">🟢 Presente (+15 XP)</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">🔴 Ausente / Falta</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <div class="d-print-none">
        <?php include '../includes/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>