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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilo Global Dojify -->
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
            <span class="user-greeting">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></strong></span>
                <a
                href="../../controller/LoginController.php?acao=logout"
                class="btn btn-sm btn-danger header-logout-button"
                aria-label="Sair"
                title="Sair"
                >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10 17l5-5-5-5M15 12H3"/>
                    <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>
                </svg>
                </a>
        </div>
    </header>

    <main class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <header class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="listar_academias_gerentes.php" class="btn btn-dark btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Gestão de Academias & Gerentes
                        </a>
                    </div>
                </header>

                <section class="card shadow-sm border-0 p-4">
                    <?php if (isset($_GET['erro'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Erro ao cadastrar. Verifique os dados fornecidos ou se o documento já está cadastrado.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h2 class="fs-4 fw-bold">Cadastrar Nova Academia</h2>
                        <p class="text-muted">Cadastrar uma nova academia no sistema.</p>
                    </div>

                    <form action="../../controller/AcademiaController.php" method="POST" data-cep-form autocomplete="off" class="w-100 mw-100 p-0 m-0 border-0 shadow-none bg-transparent">
                        <input type="hidden" name="acao" value="cadastrar">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="nome" class="form-label">Nome da Academia: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>

                            <div class="col-12">
                                <label for="documento" class="form-label">Documento (CNPJ ou CPF do responsável): <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="documento" name="documento" placeholder="000.000.000-00 ou 00.000.000/0000-00" maxlength="18" required>
                            </div>

                            <div class="col-md-4">
                                <label for="cep" class="form-label">CEP: <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="cep" name="cep" inputmode="numeric" autocomplete="postal-code" maxlength="9" pattern="[0-9]{5}-?[0-9]{3}" placeholder="00000-000" required>
                                    <button type="button" class="btn btn-outline-secondary" data-cep-lookup>Buscar CEP</button>
                                </div>
                                <p id="cep-status" class="form-text text-muted mb-0" aria-live="polite"></p>
                            </div>

                            <div class="col-md-8">
                                <label for="endereco" class="form-label">Endereço: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="endereco" name="endereco" autocomplete="street-address" maxlength="100" required>
                            </div>

                            <div class="col-md-8">
                                <label for="cidade" class="form-label">Cidade: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cidade" name="cidade" autocomplete="address-level2" maxlength="100" required>
                            </div>

                            <div class="col-md-4">
                                <label for="estado" class="form-label">Estado (UF): <span class="text-danger">*</span></label>
                                <select class="form-select" id="estado" name="estado" autocomplete="off" required>
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

                            <div class="col-md-6">
                                <label for="telefone" class="form-label">Telefone: <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" inputmode="numeric" maxlength="15" pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}" placeholder="(61) 98888-7777" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail de Contato: <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="col-12 mt-4 d-flex gap-3 justify-content-center align-items-center">
                                <a href="home_admin.php" class="btn btn-secondary px-4 m-0 w-auto">Cancelar</a>
                                <button type="submit" class="btn btn-success px-4 m-0 w-auto">
                                    <i class="bi bi-check-circle me-1"></i> Cadastrar Academia
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Dojify. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/cep.js" defer></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const telefone = document.getElementById('telefone');
            const documento = document.getElementById('documento');

            if (telefone) {
                telefone.addEventListener('input', function () {
                    const posicaoOriginal = telefone.selectionStart ?? telefone.value.length;
                    const digitosAntesDoCursor = telefone.value.slice(0, posicaoOriginal).replace(/\D/g, '').length;
                    const digitos = telefone.value.replace(/\D/g, '').slice(0, 11);
                    const tamanhoNumero = digitos.length > 10 ? 5 : 4;
                    let telefoneFormatado = digitos ? '(' + digitos.slice(0, 2) : '';

                    if (digitos.length > 2) {
                        telefoneFormatado += ') ' + digitos.slice(2, 2 + tamanhoNumero);
                    }
                    if (digitos.length > 2 + tamanhoNumero) {
                        telefoneFormatado += '-' + digitos.slice(2 + tamanhoNumero);
                    }

                    telefone.value = telefoneFormatado;
                    let novaPosicao = 0;
                    let digitosContados = 0;
                    while (novaPosicao < telefoneFormatado.length && digitosContados < digitosAntesDoCursor) {
                        if (/\d/.test(telefoneFormatado[novaPosicao])) {
                            digitosContados++;
                        }
                        novaPosicao++;
                    }
                    telefone.setSelectionRange(novaPosicao, novaPosicao);
                });
            }

            if (documento) {
                documento.addEventListener('input', function () {
                    const posicaoOriginal = documento.selectionStart ?? documento.value.length;
                    const digitosAntesDoCursor = documento.value.slice(0, posicaoOriginal).replace(/\D/g, '').length;
                    let digitos = documento.value.replace(/\D/g, '').slice(0, 14);
                    let documentoFormatado = '';

                    if (digitos.length <= 11) {
                        if (digitos.length > 0) documentoFormatado = digitos.slice(0, 3);
                        if (digitos.length >= 4) documentoFormatado += '.' + digitos.slice(3, 6);
                        if (digitos.length >= 7) documentoFormatado += '.' + digitos.slice(6, 9);
                        if (digitos.length >= 10) documentoFormatado += '-' + digitos.slice(9, 11);
                    } else {
                        documentoFormatado = digitos.slice(0, 2);
                        if (digitos.length >= 3) documentoFormatado += '.' + digitos.slice(2, 5);
                        if (digitos.length >= 6) documentoFormatado += '.' + digitos.slice(5, 8);
                        if (digitos.length >= 9) documentoFormatado += '/' + digitos.slice(8, 12);
                        if (digitos.length >= 13) documentoFormatado += '-' + digitos.slice(12, 14);
                    }

                    documento.value = documentoFormatado;
                    let novaPosicao = 0;
                    let digitosContados = 0;
                    while (novaPosicao < documentoFormatado.length && digitosContados < digitosAntesDoCursor) {
                        if (/\d/.test(documentoFormatado[novaPosicao])) {
                            digitosContados++;
                        }
                        novaPosicao++;
                    }
                    documento.setSelectionRange(novaPosicao, novaPosicao);
                });
            }
        });
    </script>
</body>
</html>