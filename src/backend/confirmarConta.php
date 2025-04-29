<?php
session_start();
$pdo = require_once '../../Database/conn.php';

function enviarCodigo($email) {
  global $pdo;

  $codigo = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

  $stmt = $pdo->prepare("UPDATE usuarios SET codigo_confirmacao = :codigo WHERE email = :email");
  $stmt->bindParam(':codigo', $codigo);
  $stmt->bindParam(':email', $email);
  $stmt->execute();

  $assunto = "Confirmação de Conta - FinPlan";
  $mensagem = "Seu código de confirmação é: $codigo";
  $headers = "From: no-reply@finplan.com.br\r\n";

  mail($email, $assunto, $mensagem, $headers);
}

$email = $_SESSION['email'] ?? null;

if (!$email) {
  echo "Sessão expirada. Faça login novamente.";
  exit();
}

// Reenviar código
if (isset($_POST['reenviar'])) {
  enviarCodigo($email);
  echo "<script>alert('Novo código enviado!'); window.location.href = '../pages/confirmarConta.php';</script>";
  exit();
}

// Confirmar código
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['reenviar'])) {
  $codigo = ($_POST['codigo1'] ?? '') . ($_POST['codigo2'] ?? '') . ($_POST['codigo3'] ?? '') . ($_POST['codigo4'] ?? '');

  $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$usuario) {
    echo "Usuário não encontrado.";
    exit();
  }

  if ($usuario['codigo_confirmacao'] === $codigo) {
    $update = $pdo->prepare("UPDATE usuarios SET conta_confirmada = TRUE WHERE email = :email");
    $update->bindParam(':email', $email);
    $update->execute();

    $_SESSION['usuario'] = $usuario;
    header("Location: ../pages/perfil.php");
    exit();
  } else {
    echo "<script>alert('Código inválido!'); window.location.href = '../pages/confirmarConta.php';</script>";
    exit();
  }
}
?>
