<?php
session_start();
$conn = require_once __DIR__ . '/../../Database/conn.php';

if (!isset($_SESSION['logado']) || $_SESSION['usuario_role'] !== 'admin') {
  header('Location: ../../index.php');
  exit();
}

$id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT nome, email, cpf FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$primeiro_nome = !empty($usuario['nome']) ? explode(' ', $usuario['nome'])[0] : 'Admin';

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | FinPlan</title>
  <link rel="stylesheet" href="../css/painel_admin.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
  <header>
    <nav>
      <div class="logo">
        <img src="../img/logo.png" alt="Logo FinPlan">
        <h2>Fin<span>Plan</span></h2>
      </div>
      <div class="options">
        <div class="user-profile">
          <div class="user-avatar"></div>
          <div class="user-name"><?= htmlspecialchars($primeiro_nome)?></div>
        </div>
        <a href="#" class="active"><i class="fas fa-cog"></i></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
      </div>
    </nav>
  </header>
  <main>
    <div class="cards-details">
      <div class="card users-card">
        <div class="card-info users">
          <img src="../img/icons/person.png">
          <h1>7.500 K</h1>
          <h2>Users</h2>
        </div>
        <button class="btn">View Users</button>
      </div>
      <div class="card chat-card">
        <div class="card-info chat">
          <img src="../img/icons/chat.png">
          <h1>7.500 K</h1>
          <h2>Mensagens</h2>
        </div>
        <button class="btn">Chat</button>
      </div>
      <div class="card investments-card">
        <div class="card-info investments">
          <img src="../img/icons/graphic.png">
          <h1>7.500 K</h1>
          <h2>Investments</h2>
        </div>
        <button class="btn">Visualizar</button>
      </div>
    </div>
    <div class="content">
      <div class="list-users-transactions">
        <h1>All Recents Transactions</h1>
        <div class="list-transactions">
          <div class="item-list">
            <div class="circle"></div>
            <h3 class="item-user">User Name</h3>
            <p class="item-category">Category</p>
            <p class="item-pagamento">Forma Pagamento</p>
            <p class="item-data">Data</p>
            <p class="item-valor">Valor</p>
          </div>
          <div class="item-list">
            <div class="circle"></div>
            <h3 class="item-user">User Name</h3>
            <p class="item-category">Category</p>
            <p class="item-pagamento">Forma Pagamento</p>
            <p class="item-data">Data</p>
            <p class="item-valor">Valor</p>
          </div>
          <div class="item-list">
            <div class="circle"></div>
            <h3 class="item-user">User Name</h3>
            <p class="item-category">Category</p>
            <p class="item-pagamento">Forma Pagamento</p>
            <p class="item-data">Data</p>
            <p class="item-valor">Valor</p>
          </div>
        </div>
      </div>
      <div class="graphic-new-users"></div>
    </div>
  </main>
</body>
</html>