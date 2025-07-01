<?php
session_start();
$pdo = require_once("../../Database/conn.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

try {
    // Exclui o usuário
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $usuario_id]);

    // Destroi a sessão
    session_unset();
    session_destroy();

    // Redireciona para página inicial
    header("Location: ../../index.php?msg=conta_excluida");
    exit();

} catch (PDOException $e) {
    echo "Erro ao excluir conta: " . $e->getMessage();
    exit();
}
