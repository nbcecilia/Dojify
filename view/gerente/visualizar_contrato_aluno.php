<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || !in_array((int)($_SESSION['usuario']['perfil_id'] ?? 0), [2, 4], true)) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

require_once __DIR__ . '/../../model/dao/Conexao.php';

$perfilLogado = (int)$_SESSION['usuario']['perfil_id'];
$idAluno = $perfilLogado === 4
    ? (int)($_SESSION['usuario']['id_usuario'] ?? 0)
    : filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$idAcademia = $perfilLogado === 2 ? (int)($_SESSION['usuario']['id_academia'] ?? 0) : 0;

if (!$idAluno || ($perfilLogado === 2 && $idAcademia < 1)) {
    http_response_code(404);
    exit('Contrato não encontrado.');
}

$conexao = Conexao::getConexao();
$stmt = $conexao->prepare("
    SELECT
        aluno.nome,
        aluno.cpf,
        aluno.data_nascimento,
        aluno.telefone,
        aluno.email,
        aluno.responsavel,
        aluno.observacao,
        aluno.cep,
        aluno.endereco,
        aluno.cidade,
        aluno.estado,
        aluno.primeiro_acesso,
        academia.nome AS academia_nome,
        plano.nome_plano,
        plano.valor AS valor_plano,
        modalidade.nome AS modalidade_plano
    FROM usuario aluno
    LEFT JOIN academia ON academia.id_academia = aluno.id_academia
    LEFT JOIN plano ON plano.id_plano = (
        SELECT plano_atual.id_plano
        FROM plano plano_atual
        WHERE plano_atual.id_usuario_aluno = aluno.id_usuario
          AND plano_atual.status = 'ATIVO'
        ORDER BY plano_atual.data_inicio DESC, plano_atual.id_plano DESC
        LIMIT 1
    )
    LEFT JOIN modalidade ON modalidade.id_modalidade = plano.id_modalidade
    WHERE aluno.id_usuario = :id_aluno
      AND aluno.perfil_id = 4
      " . ($perfilLogado === 2 ? "AND aluno.id_academia = :id_academia" : "") . "
    LIMIT 1
");
$parametrosContrato = ['id_aluno' => $idAluno];
if ($perfilLogado === 2) {
    $parametrosContrato['id_academia'] = $idAcademia;
}
$stmt->execute($parametrosContrato);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno || (int)$aluno['primeiro_acesso'] !== 0) {
    http_response_code(404);
    exit('Contrato não encontrado ou ainda não aceito.');
}

$stmtModalidades = $conexao->prepare("
    SELECT DISTINCT modalidade.nome
    FROM plano
    INNER JOIN modalidade ON modalidade.id_modalidade = plano.id_modalidade
    WHERE plano.id_usuario_aluno = :id_aluno
      AND plano.status = 'ATIVO'
    ORDER BY modalidade.nome
");
$stmtModalidades->execute(['id_aluno' => $idAluno]);
$modalidades = $stmtModalidades->fetchAll(PDO::FETCH_COLUMN);

$escapar = static fn($valor): string => htmlspecialchars((string)($valor ?: 'Não informado'), ENT_QUOTES, 'UTF-8');
$formatarData = static function ($data): string {
    if (!$data) {
        return 'Não informado';
    }
    $timestamp = strtotime((string)$data);
    return $timestamp === false ? 'Não informado' : date('d/m/Y', $timestamp);
};
$cpf = preg_replace('/\D+/', '', (string)$aluno['cpf']);
$cpfFormatado = strlen($cpf) === 11
    ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf)
    : 'Não informado';
$nomeAcademia = $aluno['academia_nome'] ?: 'A TOKKA - Escola de Lutas';
$nomePlano = $aluno['nome_plano'] ?: 'Não especificado';
if ($aluno['valor_plano'] !== null) {
    $nomePlano .= ' - R$ ' . number_format((float)$aluno['valor_plano'], 2, ',', '.');
}
if ($modalidades === [] && !empty($aluno['modalidade_plano'])) {
    $modalidades[] = $aluno['modalidade_plano'];
}
$nomeModalidades = $modalidades === [] ? 'Artes Marciais / Ver agenda' : implode(', ', $modalidades);
$observacoes = $aluno['observacao'] ?: 'Nenhuma observação registrada';
$endereco = trim(implode(', ', array_filter([
    $aluno['endereco'],
    $aluno['cep'] ? 'CEP ' . $aluno['cep'] : '',
])));
$cidadeEstado = trim(implode(' / ', array_filter([$aluno['cidade'], $aluno['estado']])));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato do aluno - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
    <style>
        .contrato-documento { max-width: 900px; margin: 24px auto; padding: 32px; background: #fff; border: 1px solid #ddd; border-radius: 8px; line-height: 1.6; }
        .contrato-documento h1 { font-size: 1.5rem; text-align: center; }
        .contrato-documento h2 { margin-top: 1.5rem; font-size: 1.1rem; }
        .contrato-nota { max-width: 900px; margin: 24px auto 0; }
        @media print { .d-print-none { display: none !important; } .contrato-documento { margin: 0 auto; border: 0; } }
    </style>
</head>
<body>
    <div class="d-print-none">
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </div>
    <main class="container">
        <div class="contrato-nota alert alert-info d-print-none" role="status">
            <strong>Aceite registrado.</strong> O sistema registra que o aluno concluiu o aceite no primeiro acesso,
            mas não armazena a data/hora nem um arquivo original assinado. Esta cópia é reconstruída com os dados
            atualmente cadastrados.
        </div>
        <div class="d-flex justify-content-between gap-2 flex-wrap d-print-none">
            <a class="btn btn-info" href="<?= $perfilLogado === 4 ? '../aluno/perfil.php' : 'listar_usuarios.php'; ?>">
                <?= $perfilLogado === 4 ? 'Voltar ao perfil' : 'Voltar para alunos'; ?>
            </a>
            <button class="btn btn-outline" type="button" onclick="window.print()">Imprimir cópia</button>
        </div>
        <article class="contrato-documento">
            <header class="text-center">
                <strong><?= $escapar($nomeAcademia); ?></strong>
                <h1>Contrato de Prestação de Serviços de Aulas de Artes Marciais</h1>
            </header>

            <h2>Dados do aluno / contratante</h2>
            <p>
                <strong>Nome:</strong> <?= $escapar($aluno['nome']); ?><br>
                <strong>CPF:</strong> <?= $escapar($cpfFormatado); ?><br>
                <strong>Data de nascimento:</strong> <?= $escapar($formatarData($aluno['data_nascimento'])); ?><br>
                <strong>Responsável:</strong> <?= $escapar($aluno['responsavel'] ?: 'O próprio'); ?><br>
                <strong>E-mail:</strong> <?= $escapar($aluno['email']); ?><br>
                <strong>Telefone:</strong> <?= $escapar($aluno['telefone']); ?><br>
                <strong>Endereço:</strong> <?= $escapar($endereco); ?><br>
                <strong>Cidade / Estado:</strong> <?= $escapar($cidadeEstado); ?>
            </p>
            <h2>Modalidade e plano</h2>
            <p><strong>Modalidade:</strong> <?= $escapar($nomeModalidades); ?><br>
                <strong>Plano:</strong> <?= $escapar($nomePlano); ?></p>
            <h2>Informações adicionais</h2>
            <p><strong>Informações médicas / observações:</strong> <?= $escapar($observacoes); ?></p>
            <h2>Dados do contratado</h2>
            <p>
                <strong>Contratado:</strong> <?= $escapar($nomeAcademia); ?><br>
                <strong>CNPJ:</strong> 66.790.246/0001-12<br>
                <strong>Endereço:</strong> QNM 08 Conjunto B Lote 34<br>
                <strong>Telefone:</strong> (61) 99869-3504<br>
                <strong>Instrutor responsável:</strong> Klevisson Araújo
            </p>
            <h2>Termos e cláusulas</h2>
            <p>As partes acima identificadas têm, entre si, justo e acertado o presente contrato de prestação de serviços de aulas de artes marciais, que se regerá pelas cláusulas e condições a seguir:</p>
            <p><strong>CLÁUSULA 1 — DO OBJETO</strong><br>1.1 O presente contrato tem como objeto a prestação de serviços de aulas de artes marciais na modalidade contratada, observada a frequência escolhida pelo(a) CONTRATANTE, em dias e horários previamente disponibilizados pelo CONTRATADO.</p>
            <p><strong>CLÁUSULA 2 — DO VALOR E FORMA DE PAGAMENTO</strong><br>2.1 O CONTRATANTE pagará ao CONTRATADO o valor correspondente ao plano selecionado, conforme indicado neste instrumento, a serem pagos até o dia 10 (dez) de cada mês, via dinheiro, pix ou transferência bancária.<br>2.2 O inadimplemento poderá acarretar a suspensão da participação do CONTRATANTE nas aulas até a regularização dos valores pendentes.</p>
            <p><strong>CLÁUSULA 3 — DAS RESPONSABILIDADES</strong><br>3.1 O CONTRATADO compromete-se a ministrar as aulas de acordo com as técnicas próprias da modalidade, observando as normas de segurança e zelando pelo adequado estado de conservação dos equipamentos e instalações.<br>3.2 O CONTRATANTE compromete-se a respeitar as normas internas, regras de vestimenta, higiene, segurança e conduta estabelecidas pela academia.<br>3.3 O CONTRATANTE declara estar apto à prática de atividades físicas e ciente de que a prática de artes marciais envolve esforço físico e riscos inerentes, responsabilizando-se pelas informações de saúde prestadas.<br>3.4 O CONTRATANTE assume responsabilidade por eventuais problemas de saúde ou lesões decorrentes da prática das atividades, isentando o CONTRATADO de responsabilidade nos casos de imprudência, descumprimento das orientações recebidas ou existência de condições médicas não informadas previamente.<br>3.5 Caso o CONTRATANTE seja o responsável legal por menor de idade, declara, para todos os fins de direito, que assume, em nome do menor, integral responsabilidade pelos riscos decorrentes da prática das atividades contratadas, estendendo-se a isenção de responsabilidade aqui prevista ao menor sob sua guarda.</p>
            <p><strong>CLÁUSULA 4 — DAS FALTAS E REPOSIÇÕES</strong><br>4.1 O não comparecimento do CONTRATANTE às aulas não gera direito à reposição, desconto, compensação ou reembolso de valores.</p>
            <p><strong>CLÁUSULA 5 — DA DURAÇÃO DO CONTRATO</strong><br>5.1 O presente contrato terá duração mínima de 3 (três) meses, contados a partir da data de sua assinatura.</p>
            <p><strong>CLÁUSULA 6 — DA RESCISÃO</strong><br>O presente contrato poderá ser rescindido:<br>6.1 Por qualquer das partes, mediante aviso prévio de 30 (trinta) dias.<br>6.2 Em caso de inadimplemento ou descumprimento de cláusulas contratuais.<br>6.3 O CONTRATADO poderá rescindir imediatamente o presente contrato em caso de conduta agressiva, desrespeito às normas internas, comportamento inadequado ou atitudes que coloquem em risco os demais alunos, professores ou colaboradores.<br>6.4 O CONTRATADO não está obrigado à devolução dos valores pagos.</p>
            <p><strong>CLÁUSULA 7 — DO USO DE IMAGEM</strong><br>7.1 A utilização da imagem do CONTRATANTE ou do RESPONSÁVEL LEGAL (no caso de aluno menor de 18 anos) em fotos ou vídeos para divulgação da academia depende de autorização específica, separada e facultativa, que pode ser concedida ou retirada sem afetar a prestação dos serviços contratados.</p>
            <p><strong>CLÁUSULA 8 — DAS DISPOSIÇÕES FINAIS</strong><br>8.1 Este contrato é firmado em duas vias de igual teor e forma, assinadas pelas partes para que produza seus efeitos legais.</p>
            <p class="text-center mt-4"><strong><?= htmlspecialchars('Brasília - DF', ENT_QUOTES, 'UTF-8'); ?></strong></p>
            <div class="d-flex justify-content-between gap-4 text-center mt-5">
                <div class="w-50"><hr>CONTRATANTE — ACEITE ELETRÔNICO REGISTRADO</div>
                <div class="w-50"><hr>CONTRATADO</div>
            </div>
        </article>
    </main>
</body>
</html>
