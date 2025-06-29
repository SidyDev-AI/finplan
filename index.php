<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="/src/css/index.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <title>FinPlan | Login</title>
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
      <h1>Acesse sua conta</h1>

      <form id="formLogin">
        <div class="email">
          <i class="fa-solid fa-envelope"></i>
          <input type="email" id="email" name="email" placeholder="E-mail" required>
        </div>
        <div class="password">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="senha" name="senha" placeholder="Senha" required minlength="6">
        </div>
        <div class="conectado">
          <input type="checkbox" id="keepLogged">
          <p>Permanecer conectado</p>
        </div>
        <button type="submit" class="form-btn">Login</button>
        <p id="mensagemErro" style="color: red; margin-top: 10px;"></p>
      </form>

      <p><a class="esqueci" href="/src/pages/confirmarEmail.php">Esqueci minha senha!</a></p>
      <a href="/src/pages/register.html">Criar Nova Conta</a>
      <p>--------------- OU ---------------</p>
      <div class="social-media">
        <a href="src/backend/api/auth/google-login.php"><img src="/src/img/icons/google.png" alt="Login com conta Google"></a>
        <a href="#"><img src="/src/img/icons/apple.png" alt="Login com conta Apple (desativado)"></a>
      </div>
    </div>
  </section>

  <script>
    document.getElementById('formLogin').addEventListener('submit', async function (e) {
      e.preventDefault();

      const email = document.getElementById('email').value;
      const senha = document.getElementById('senha').value;

      const resposta = await fetch('src/backend/api/auth/api_auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, senha })
      });

      const resultado = await resposta.json();

     if (resultado.success) {
        if (resultado.role === 'admin') {
          window.location.href = "src/pages/painel_admin.php";
        } else {
          window.location.href = "src/pages/dashboard.php";
        }
      } 
    });
  </script>
</body>
</html>
