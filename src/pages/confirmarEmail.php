<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/confirmarEmail.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <title>FinPlan | Confirmar Email</title>
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
      <p>Please enter your email to send a password </br> recovery message</p>
      <form id="formLogin" action="../backend/confirmarEmail.php" method="POST">
        <div class="email">
          <i class="fa-solid fa-envelope"></i>
          <input type="email" id="email" name="email" placeholder="E-mail" required>
        </div>
        <button type="submit" class="form-btn">Enviar Email</button>
      </form>
      <?php if(isset($_GET['erro'])): ?>
        <p style="color:red;">Email não encontrado!</p>
      <?php endif; ?>
    </div>
  </section>
</body>
</html>