<?php
session_start();
if (!isset($_SESSION['usuario_recuperacao'])) {
    header('Location: confirmarEmail.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/alterarSenha.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <title>FinPlan | Alterar Senha</title>
</head>
<body>
  <section id="container">
    <div class="theme">
      <div class="sun">
        <i class="fa-solid fa-sun"></i>
      </div>
      <div class="moon">
        <i class="fa-solid fa-moon"></i>
      </div>
    </div>
    <div id="content">
      <img class="logo" src="/src/img/logo.png" alt="Logo FinPlan">
      <p>Please enter your new password</p>
      <form id="formLogin" action="../backend/alterarSenha.php" method="POST">
        <div class="password">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="senha" name="senha" placeholder="Nova Senha" required minlength="6">
        </div>
        <div class="password">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirmar Senha" required minlength="6">
        </div>
        <button type="submit" class="form-btn">Confirmar</button>
      </form>
    </div>
    <?php if(isset($_GET['erro'])): ?>
        <p style="color:red;">As senhas não coincidem!</p>
    <?php endif; ?>
  </section>
</body>
</html>