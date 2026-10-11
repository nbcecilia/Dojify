<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$perfilId = (int)($_SESSION['usuario']['perfil_id'] ?? 0);
if (!isset($_SESSION['usuario']) || !in_array($perfilId, [2, 3], true)) {
    header('Location: ../login.php?erro=acesso_negado');
    exit;
}

header('Location: listar_avaliacao.php');
exit;
