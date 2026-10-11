<?php
// view/admin/cadastrar_academia.php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil_id'] != 1) { 
    header('Location: ../login.php'); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Academia - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css?v=<?= filemtime(__DIR__ . '/../../assets/css/estilo.css'); ?>">
</head>
<body>
    <header class="navbar">
        <div class="navbar-brand">
            <a href="home_admin.php" class="logo-link">
                <img src="../../assets/img/Dojify_original2.png" alt="Dojify Logo" class="navbar-logo">
                <div>
                    <h1>Dojify</h1>
                </div>
            </a>
        </div>
        
        <div class="navbar-user">
            <span class="user-greeting">Olá, <strong><strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></strong></span>
            <a href="home_admin.php" class="btn btn-sm btn-voltar">Voltar</a>
            <a href="../../controller/UsuarioController.php?acao=logout" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </header>

    <div class="container">

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-erro">Erro ao cadastrar. Verifique os dados fornecidos ou se o documento já está cadastrado.</div>
        <?php endif; ?>

        <form action="../../controller/AcademiaController.php" method="POST" data-cep-form autocomplete="off">
            <div class="text-center" style="margin-bottom: 20px;">
                <img src="../../assets/img/dojify_logo1.png" alt="Dojify Logo" style="width: 100px; height: auto; margin-bottom: 12px; filter: grayscale(100%);">
                <h2>Cadastrar Academia</h2>
            </div>
            <input type="hidden" name="acao" value="cadastrar">
            
            <label>Nome da Academia:</label>
            <input type="text" name="nome" required>

            <label>Documento (CNPJ ou CPF do responsável):</label>
            <input type="text" name="documento" placeholder="Apenas números" required>

            <label for="cep">CEP:</label>
            <div class="cep-lookup">
                <input type="text" id="cep" name="cep" inputmode="numeric" autocomplete="postal-code" maxlength="9" pattern="[0-9]{5}-?[0-9]{3}" placeholder="00000-000" required>
                <button type="button" class="btn btn-sm btn-info" data-cep-lookup>Buscar CEP</button>
            </div>
            <p id="cep-status" class="text-muted" aria-live="polite"></p>

            <label for="endereco">Endereço:</label>
            <input type="text" id="endereco" name="endereco" autocomplete="street-address" maxlength="100" required>

            <div class="form-row-inline">
                <div>
                    <label for="cidade">Cidade:</label>
                    <input type="text" id="cidade" name="cidade" autocomplete="address-level2" maxlength="100" required>
                </div>
                <div>
                    <label for="estado">Estado (UF):</label>
                    <select id="estado" name="estado" autocomplete="off" required>
                <option value="" selected>Selecione o estado...</option>
                <option value="AC">AC</option><option value="AL">AL</option>
                <option value="AP">AP</option><option value="AM">AM</option>
                <option value="BA">BA</option><option value="CE">CE</option>
                <option value="DF">DF</option><option value="ES">ES</option>
                <option value="GO">GO</option><option value="MA">MA</option>
                <option value="MT">MT</option><option value="MS">MS</option>
                <option value="MG">MG</option><option value="PA">PA</option>
                <option value="PB">PB</option><option value="PR">PR</option>
                <option value="PE">PE</option><option value="PI">PI</option>
                <option value="RJ">RJ</option><option value="RN">RN</option>
                <option value="RS">RS</option><option value="RO">RO</option>
                <option value="RR">RR</option><option value="SC">SC</option>
                <option value="SP">SP</option><option value="SE">SE</option>
                <option value="TO">TO</option>
                    </select>
                </div>
            </div>

            <label>Telefone:</label>
            <input type="tel" name="telefone" pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}" required>

            <label>E-mail de Contato:</label>
            <input type="email" name="email" required>

            <button type="submit">Cadastrar Academia</button>
             <div class="navbar-user"></div>
        </form>
    </div>
    <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>
    <script src="../../assets/js/cep.js" defer></script>
</body>
</html>