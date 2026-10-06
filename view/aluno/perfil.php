<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['perfil_id'] ?? 0) !== 4) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/Conexao.php';

$idAluno = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
$dadosAluno = null;
$planoAluno = null;
$erroPerfil = null;

try {
    $conexao = Conexao::getConexao();
    $stmtAluno = $conexao->prepare("
        SELECT
            aluno.nome,
            aluno.cpf,
            aluno.data_nascimento,
            aluno.telefone,
            aluno.email,
            aluno.responsavel,
            aluno.data_matricula,
            aluno.status AS status_matricula,
            academia.nome AS nome_academia
        FROM usuario aluno
        LEFT JOIN academia ON academia.id_academia = aluno.id_academia
        WHERE aluno.id_usuario = :id_aluno
          AND aluno.perfil_id = 4
        LIMIT 1
    ");
    $stmtAluno->execute(['id_aluno' => $idAluno]);
    $dadosAluno = $stmtAluno->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($dadosAluno === null) {
        throw new RuntimeException('Não foi possível localizar os dados do aluno.');
    }

    $stmtPlano = $conexao->prepare("
        SELECT
            plano.nome_plano,
            plano.valor,
            plano.data_inicio,
            plano.data_fim,
            plano.status,
            modalidade.nome AS nome_modalidade
        FROM plano
        LEFT JOIN modalidade ON modalidade.id_modalidade = plano.id_modalidade
        WHERE plano.id_usuario_aluno = :id_aluno
        ORDER BY plano.data_inicio DESC, plano.id_plano DESC
        LIMIT 1
    ");
    $stmtPlano->execute(['id_aluno' => $idAluno]);
    $planoAluno = $stmtPlano->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    error_log('Erro ao carregar perfil do aluno: ' . $e->getMessage());
    $erroPerfil = 'Não foi possível carregar os dados do perfil. Tente novamente mais tarde.';
}

$escapar = static fn($valor): string => htmlspecialchars((string)($valor ?: 'Não informado'), ENT_QUOTES, 'UTF-8');
$formatarData = static function ($data): string {
    if (!$data) {
        return 'Não informado';
    }
    $timestamp = strtotime((string)$data);
    return $timestamp === false ? 'Não informado' : date('d/m/Y', $timestamp);
};
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu perfil - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css?v=<?= filemtime(__DIR__ . '/../../assets/css/estilo.css'); ?>">
    <link rel="stylesheet" href="../../assets/css/aluno.css?v=<?= filemtime(__DIR__ . '/../../assets/css/aluno.css'); ?>">
</head>
<body class="aluno-theme aluno-profile-page">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container aluno-profile-dashboard">
        <header class="aluno-dashboard-welcome">
            <div>
                <h2>Meu perfil</h2>
                <p>Consulte seus dados pessoais, plano e matrícula.</p>
            </div>
            <span class="aluno-dashboard-role">
                <i class="bi bi-person"></i>
                Aluno
            </span>
        </header>

        <?php if ($erroPerfil !== null): ?>
            <div class="alert alert-danger" role="alert"><?= $escapar($erroPerfil); ?></div>
        <?php elseif ($dadosAluno !== null): ?>
            <section class="aluno-profile-section" aria-labelledby="dadosPessoaisTitulo">
                <div class="aluno-profile-section-heading">
                    <h3 id="dadosPessoaisTitulo">Dados pessoais</h3>
                    <p>Informações cadastradas na academia.</p>
                </div>
                <div class="aluno-profile-grid">
                    <article class="aluno-profile-item">
                        <span>Nome completo</span>
                        <strong><?= $escapar($dadosAluno['nome']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>E-mail</span>
                        <strong><?= $escapar($dadosAluno['email']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>CPF</span>
                        <strong><?= $escapar($dadosAluno['cpf']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Data de nascimento</span>
                        <strong><?= $escapar($formatarData($dadosAluno['data_nascimento'])); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Telefone</span>
                        <strong><?= $escapar($dadosAluno['telefone']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Responsável</span>
                        <strong><?= $escapar($dadosAluno['responsavel']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Academia</span>
                        <strong><?= $escapar($dadosAluno['nome_academia']); ?></strong>
                    </article>
                </div>
            </section>

            <section class="aluno-profile-section" aria-labelledby="matriculaTitulo">
                <div class="aluno-profile-section-heading">
                    <h3 id="matriculaTitulo">Matrícula</h3>
                    <p>Situação e período de vínculo com a academia.</p>
                </div>
                <div class="aluno-profile-grid">
                    <article class="aluno-profile-item">
                        <span>Status</span>
                        <strong><?= $escapar($dadosAluno['status_matricula']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Data de matrícula</span>
                        <strong><?= $escapar($formatarData($dadosAluno['data_matricula'])); ?></strong>
                    </article>
                </div>
            </section>

            <section class="aluno-profile-section" aria-labelledby="planoTitulo">
                <div class="aluno-profile-section-heading">
                    <h3 id="planoTitulo">Plano</h3>
                    <p>Detalhes do plano mais recente cadastrado.</p>
                </div>
                <?php if ($planoAluno !== null): ?>
                    <div class="aluno-profile-grid">
                        <article class="aluno-profile-item">
                            <span>Plano</span>
                            <strong><?= $escapar($planoAluno['nome_plano']); ?></strong>
                        </article>
                        <article class="aluno-profile-item">
                            <span>Modalidade</span>
                            <strong><?= $escapar($planoAluno['nome_modalidade']); ?></strong>
                        </article>
                        <article class="aluno-profile-item">
                            <span>Valor mensal</span>
                            <strong>R$ <?= number_format((float)$planoAluno['valor'], 2, ',', '.'); ?></strong>
                        </article>
                        <article class="aluno-profile-item">
                            <span>Status do plano</span>
                            <strong><?= $escapar($planoAluno['status']); ?></strong>
                        </article>
                        <article class="aluno-profile-item">
                            <span>Início</span>
                            <strong><?= $escapar($formatarData($planoAluno['data_inicio'])); ?></strong>
                        </article>
                        <article class="aluno-profile-item">
                            <span>Fim</span>
                            <strong><?= $escapar($formatarData($planoAluno['data_fim'])); ?></strong>
                        </article>
                    </div>
                <?php else: ?>
                    <p class="aluno-profile-empty">Nenhum plano cadastrado no momento.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="../../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
