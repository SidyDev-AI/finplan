<?php
session_start();
if (!isset($_SESSION['usuario_recuperacao'])) {
    header('Location: ../pages/confirmarEmail.php');
    exit();
}

$db_path = '../../Database/database.sqlite';

try {
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// Verifica se as senhas coincidem
if ($senha !== $confirmar_senha) {
    header('Location: ../pages/alterarSenha.php?erro=1');
    exit();
}

// Atualiza a senha no banco (com hash de segurança)
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
$stmt->bindValue(':senha', $senha_hash, PDO::PARAM_STR);
$stmt->bindValue(':id', $_SESSION['usuario_recuperacao'], PDO::PARAM_INT);
$stmt->execute();

// Limpa a sessão e redireciona
unset($_SESSION['usuario_recuperacao']);
header('Location: ../../index.php');
exit();
?>