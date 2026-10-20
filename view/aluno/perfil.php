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
$planosAluno = [];
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
            aluno.cep,
            aluno.endereco,
            aluno.cidade,
            aluno.estado,
            aluno.responsavel,
            aluno.observacao,
            aluno.data_matricula,
            aluno.status AS status_matricula,
            aluno.primeiro_acesso,
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
            plano.status,
            GROUP_CONCAT(DISTINCT modalidade.nome ORDER BY modalidade.nome SEPARATOR ', ') AS modalidades
        FROM plano
        LEFT JOIN modalidade ON modalidade.id_modalidade = plano.id_modalidade
        WHERE plano.id_usuario_aluno = :id_aluno
        GROUP BY plano.nome_plano, plano.valor, plano.data_inicio, plano.data_fim, plano.status
        ORDER BY plano.data_inicio DESC, plano.nome_plano ASC
    ");
    $stmtPlano->execute(['id_aluno' => $idAluno]);
    $planosAluno = $stmtPlano->fetchAll(PDO::FETCH_ASSOC);
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
            <div class="aluno-profile-welcome-copy">
                <h2>Meu perfil</h2>
                <p>Consulte seus dados pessoais, plano e matrícula.</p>
            </div>
            <a href="home_aluno.php" class="btn aluno-profile-back-button">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Voltar ao painel
            </a>
        </header>

        <?php if ($erroPerfil !== null): ?>
            <div class="alert alert-danger" role="alert"><?= $escapar($erroPerfil); ?></div>
        <?php elseif ($dadosAluno !== null): ?>

            <!-- DADOS PESSOAIS -->
            <section class="aluno-profile-section" aria-labelledby="dadosPessoaisTitulo">
                <div class="aluno-profile-section-heading">
                    <h3 id="dadosPessoaisTitulo">Dados pessoais</h3>
                    <p>Informações cadastradas na academia.</p>
                </div>

                <div class="aluno-profile-grid">
                    <article class="aluno-profile-item">
                        <span>Foto de perfil</span>
                        <div class="aluno-profile-photo-row">
                            <div class="aluno-profile-avatar-preview">
                                <?php
                                    $avatarUrlAluno = null;
                                    foreach (['webp','png','jpg','jpeg'] as $extA) {
                                        $arqA = __DIR__ . '/../../assets/uploads/avatars/user-' . $idAluno . '.' . $extA;
                                        if (is_file($arqA)) {
                                            $avatarUrlAluno = '../../assets/uploads/avatars/user-' . $idAluno . '.' . $extA . '?v=' . filemtime($arqA);
                                            break;
                                        }
                                    }
                                ?>
                                <?php if ($avatarUrlAluno): ?>
                                    <img src="<?= htmlspecialchars($avatarUrlAluno, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de perfil">
                                <?php else: ?>
                                    <?= mb_strtoupper(mb_substr((string)$dadosAluno['nome'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                                <?php endif; ?>
                            </div>
                            <div class="aluno-profile-photo-actions">
                                <form action="../../controller/ProfileAvatarController.php" method="POST" enctype="multipart/form-data" class="aluno-profile-photo-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$_SESSION['avatar_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="return_to" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required class="aluno-profile-photo-input">
                                    <div class="aluno-profile-photo-btns">
                                        <button type="submit" class="aluno-profile-photo-btn">Salvar</button>
                                        <?php if ($avatarUrlAluno): ?>
                                            <button type="submit"
                                                onclick="this.form.querySelector('[name=acao]').value='remover'; this.form.removeAttribute('enctype');"
                                                class="aluno-profile-photo-btn aluno-profile-photo-btn--outline">Remover</button>
                                            <input type="hidden" name="acao" value="">
                                        <?php endif; ?>
                                    </div>
                                </form>
                                <small class="aluno-profile-photo-hint">JPG, PNG ou WebP · até 2 MB</small>
                            </div>
                        </div>
                    </article>
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
                        <span>CEP</span>
                        <strong><?= $escapar($dadosAluno['cep']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Endereço</span>
                        <strong><?= $escapar($dadosAluno['endereco']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Cidade</span>
                        <strong><?= $escapar($dadosAluno['cidade']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Estado</span>
                        <strong><?= $escapar($dadosAluno['estado']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Responsável</span>
                        <strong><?= $escapar($dadosAluno['responsavel']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Observações</span>
                        <strong><?= $escapar($dadosAluno['observacao']); ?></strong>
                    </article>
                    <article class="aluno-profile-item">
                        <span>Academia</span>
                        <strong><?= $escapar($dadosAluno['nome_academia']); ?></strong>
                    </article>
                </div>
            </section>

            <section class="aluno-profile-section" aria-labelledby="matriculaTitulo">
                <div class="aluno-profile-section-heading aluno-profile-matricula-heading">
                    <div>
                        <h3 id="matriculaTitulo">Matrícula</h3>
                        <p>Situação e período de vínculo com a academia.</p>
                    </div>
                    <?php if ((int)$dadosAluno['primeiro_acesso'] === 0): ?>
                        <a href="../gerente/visualizar_contrato_aluno.php" class="btn aluno-profile-contract-button">
                            <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                            Visualizar contrato
                        </a>
                    <?php endif; ?>
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
                <?php if ((int)$dadosAluno['primeiro_acesso'] !== 0): ?>
                    <p class="aluno-profile-empty mt-3">O contrato ficará disponível após o aceite no primeiro acesso.</p>
                <?php endif; ?>
            </section>

            <section class="aluno-profile-section" aria-labelledby="planoTitulo">
                <div class="aluno-profile-section-heading">
                    <h3 id="planoTitulo">Plano</h3>
                    <p>Todos os planos vinculados à sua matrícula.</p>
                </div>
                <?php if ($planosAluno !== []): ?>
                    <?php foreach ($planosAluno as $planoAluno): ?>
                        <div class="aluno-profile-grid">
                            <article class="aluno-profile-item">
                                <span>Plano</span>
                                <strong><?= $escapar($planoAluno['nome_plano']); ?></strong>
                            </article>
                            <article class="aluno-profile-item">
                                <span>Modalidades</span>
                                <strong><?= $escapar($planoAluno['modalidades']); ?></strong>
                            </article>
                            <article class="aluno-profile-item">
                                <span>Valor total</span>
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
                        </div>
                    <?php endforeach; ?>
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
