<?php
session_start();
$db_path = '../../Database/database.sqlite';

try {
  $db = new PDO('sqlite:' . $db_path);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erro ao conectar: " . $e->getMessage());
}

$email = $_POST['email'] ?? '';

$stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->execute();

if ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $_SESSION['usuario_recuperacao'] = $usuario['id'];
  header('Location: ../pages/alterarSenha.php');
  exit();
} else {
  header('Location: ../pages/confirmarEmail.php?erro=1');
  exit();
}
?>