<?php
session_start();
if (!isset($_SESSION['usuario']) || !in_array((int)$_SESSION['usuario']['perfil_id'], [2, 3])) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/Conexao.php';

$idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
$perfilId  = (int)$_SESSION['usuario']['perfil_id'];
$dados = null;
$erro  = null;

try {
    $pdo  = Conexao::getConexao();
    $stmt = $pdo->prepare("
        SELECT u.nome, u.cpf, u.data_nascimento, u.telefone, u.email,
               u.endereco, u.cidade, u.estado, u.especialidade, u.data_admissao, u.status,
               a.nome AS nome_academia
        FROM usuario u
        LEFT JOIN academia a ON a.id_academia = u.id_academia
        WHERE u.id_usuario = ? AND u.perfil_id IN (2, 3)
        LIMIT 1
    ");
    $stmt->execute([$idUsuario]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($dados === null) throw new RuntimeException('Dados não encontrados.');
} catch (Throwable $e) {
    $erro = 'Não foi possível carregar os dados do perfil.';
}

$avatarUrl = null;
foreach (['webp', 'png', 'jpg', 'jpeg'] as $ext) {
    $arq = __DIR__ . '/../../assets/uploads/avatars/user-' . $idUsuario . '.' . $ext;
    if (is_file($arq)) {
        $avatarUrl = '../../assets/uploads/avatars/user-' . $idUsuario . '.' . $ext . '?v=' . filemtime($arq);
        break;
    }
}

$esc     = static fn($v): string => htmlspecialchars((string)($v ?: 'Não informado'), ENT_QUOTES, 'UTF-8');
$data    = static fn($d): string => $d ? (date('d/m/Y', strtotime((string)$d)) ?: 'Não informado') : 'Não informado';
$homeUrl = $perfilId === 2 ? '../gerente/home_gerente.php' : 'home_professor.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu perfil - Dojify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <link rel="stylesheet" href="../../assets/css/gerente.css?v=12">
    <link rel="stylesheet" href="../../assets/css/aluno.css?v=<?= filemtime(__DIR__ . '/../../assets/css/aluno.css'); ?>">
</head>
<body class="aluno-theme aluno-profile-page">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container aluno-profile-dashboard">
        <header class="aluno-dashboard-welcome">
            <div class="aluno-profile-welcome-copy">
                <h2>Meu perfil</h2>
                <p>Consulte seus dados pessoais e personalize sua foto.</p>
            </div>
            <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn aluno-profile-back-button">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Voltar ao painel
            </a>
        </header>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        <?php elseif ($dados): ?>

            <section class="aluno-profile-section" aria-labelledby="dadosPessoaisTitulo">
                <div class="aluno-profile-section-heading">
                    <h3 id="dadosPessoaisTitulo">Dados pessoais</h3>
                    <p>Informações cadastradas no sistema.</p>
                </div>
                <div class="row g-3 align-items-stretch aluno-profile-content">
                    <aside class="col-12 col-lg-3">
                        <article class="aluno-profile-item aluno-profile-photo-item">
                            <span>Foto de perfil</span>
                            <div class="aluno-profile-photo-row">
                                <div class="aluno-profile-avatar-preview">
                                    <?php if ($avatarUrl): ?>
                                        <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de perfil">
                                    <?php else: ?>
                                        <?= mb_strtoupper(mb_substr($dados['nome'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                                    <?php endif; ?>
                                </div>
                                <div class="aluno-profile-photo-actions">
                                    <form action="../../controller/ProfileAvatarController.php" method="POST" enctype="multipart/form-data" class="aluno-profile-photo-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$_SESSION['avatar_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="return_to" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required class="aluno-profile-photo-input">
                                        <small class="aluno-profile-photo-hint">JPG, PNG ou WebP · até 2 MB</small>
                                        <div class="aluno-profile-photo-btns">
                                            <button type="submit" class="aluno-profile-photo-btn">Salvar</button>
                                            <?php if ($avatarUrl): ?>
                                                <button type="submit" formnovalidate
                                                    onclick="this.form.querySelector('[name=acao]').value='remover'; this.form.removeAttribute('enctype');"
                                                    class="aluno-profile-photo-btn aluno-profile-photo-btn--outline">Remover</button>
                                                <input type="hidden" name="acao" value="">
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </article>
                    </aside>
                    <div class="col-12 col-lg-9">
                        <div class="aluno-profile-grid">
                    <article class="aluno-profile-item"><span>Nome completo</span><strong><?= $esc($dados['nome']) ?></strong></article>
                    <article class="aluno-profile-item"><span>E-mail</span><strong><?= $esc($dados['email']) ?></strong></article>
                    <article class="aluno-profile-item"><span>CPF</span><strong><?= $esc($dados['cpf']) ?></strong></article>
                    <article class="aluno-profile-item"><span>Data de nascimento</span><strong><?= $esc($data($dados['data_nascimento'])) ?></strong></article>
                    <article class="aluno-profile-item"><span>Telefone</span><strong><?= $esc($dados['telefone']) ?></strong></article>
                    <article class="aluno-profile-item"><span>Endereço</span><strong><?= $esc($dados['endereco']) ?></strong></article>
                    <article class="aluno-profile-item"><span>Cidade</span><strong><?= $esc($dados['cidade']) ?></strong></article>
                    <article class="aluno-profile-item"><span>Estado</span><strong><?= $esc($dados['estado']) ?></strong></article>
                    <article class="aluno-profile-item"><span>Academia</span><strong><?= $esc($dados['nome_academia']) ?></strong></article>
                    <article class="aluno-profile-item"><span>Especialidade</span><strong><?= $esc($dados['especialidade']) ?></strong></article>
                    <article class="aluno-profile-item"><span>Data de admissão</span><strong><?= $esc($data($dados['data_admissao'])) ?></strong></article>
                    <article class="aluno-profile-item"><span>Status</span><strong><?= $esc($dados['status']) ?></strong></article>
                        </div>
                    </div>
                </div>
            </section>

        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
