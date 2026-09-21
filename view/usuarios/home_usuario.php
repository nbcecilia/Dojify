<?php
//view/usuario/home_usuario.php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Área do Usuário - Dojify</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
    <h1>Área Interna</h1>
    <p>Olá, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>!</p>
    <a href="../../controller/UsuarioController.php?acao=logout">Sair</a>
</body>
</html>