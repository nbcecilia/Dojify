<?php
// view/aluno/historico_graduacao.php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['usuario']) || (int) ($_SESSION['usuario']['perfil_id'] ?? 0) !== 4) {
	header('Location: ../login.php?erro=acesso_negado');
	exit;
}

require_once __DIR__ . '/../../controller/GraduacaoController.php';

$historico = [];
$mensagemErro = null;

try {
	$controller = new GraduacaoController();
	$historico = $controller->listarHistoricoAluno((int) $_SESSION['usuario']['id_usuario']);
} catch (Throwable $e) {
	error_log('Erro ao carregar histórico de graduação do aluno: ' . $e->getMessage());
	$mensagemErro = 'Não foi possível carregar o histórico de graduação.';
}

function formatarDataGraduacao(string $data): string
{
	return date('d/m/Y', strtotime($data));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Histórico de Graduação - Dojify</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../assets/css/estilo.css">
	<link rel="stylesheet" href="../../assets/css/historico-graduacao.css">
</head>
<body class="aluno-page">
	<?php include '../includes/header.php'; ?>

	<main class="container">
		<div class="historico-header">
			<div>
				<h2>Histórico de graduação</h2>
				<p>Acompanhe sua evolução nas modalidades da academia.</p>
			</div>
			<a href="home_aluno.php" class="btn btn-sm">Voltar ao início</a>
		</div>

		<?php if ($mensagemErro): ?>
			<div class="alert-erro alerta-historico" role="alert"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?></div>
		<?php endif; ?>

		<div class="historico-tabela historico-tabela--graduacao">
			<table>
				<thead>
					<tr>
						<th>Data</th>
						<th>Modalidade</th>
						<th>Faixa</th>
						<th>Grau</th>
						<th>Professor</th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($historico)): ?>
						<tr>
							<td colspan="5" class="text-center text-muted">Nenhuma graduação registrada.</td>
						</tr>
					<?php else: ?>
						<?php foreach ($historico as $graduacao): ?>
							<tr>
								<td><?= formatarDataGraduacao($graduacao->getDataGraduacao()) ?></td>
								<td><?= htmlspecialchars($graduacao->getNomeModalidade(), ENT_QUOTES, 'UTF-8') ?></td>
								<td class="graduacao-faixa"><?= htmlspecialchars($graduacao->getFaixa(), ENT_QUOTES, 'UTF-8') ?></td>
								<td><?= htmlspecialchars($graduacao->getGrau() ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
								<td><?= htmlspecialchars($graduacao->getNomeProfessor(), ENT_QUOTES, 'UTF-8') ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</main>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>