<?php
session_start();
$pdo = require_once('../../Database/conn.php');

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

$stmt = $pdo->prepare("SELECT id, nome, senha, role FROM usuarios WHERE email = :email");
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->execute();

if ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
  if (password_verify($senha, $usuario['senha'])) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_role'] = $usuario['role'];
    $_SESSION['logado'] = true;

    if ($usuario['role'] === 'admin') {
      header("Location: /src/pages/painel_admin.php");
    } else {
      header("Location: /src/pages/dashboard.php");
    }
    exit();
  } else {
    header('Location: ../../index.php?erro=senha_incorreta');
    exit();
  }
} else {
  header('Location: ../../index.php?erro=usuario_nao_encontrado');
  exit();
}