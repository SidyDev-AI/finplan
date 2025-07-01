<?php
session_start();
$conn = require_once __DIR__ . '/../../Database/conn.php';

// Apenas administradores podem acessar esta página
if (!isset($_SESSION['logado']) || $_SESSION['usuario_role'] !== 'admin') {
  header('Location: ../../index.php');
  exit();
}

// Buscar todos os usuários com a role 'usuario'
$stmt = $conn->prepare("SELECT id, nome, email, data_cadastro FROM usuarios WHERE role = 'usuario' ORDER BY nome ASC");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Usuários | FinPlan Admin</title>
  <link rel="stylesheet" href="../css/painel_admin.css">
  <link rel="stylesheet" href="../css/listar_usuarios.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <header>
    <nav>
      <div class="logo">
        <img src="../img/logo.png" alt="Logo FinPlan">
        <h2>Fin<span>Plan</span></h2>
      </div>
      <div class="options">
        <a href="painel_admin.php"><i class="fas fa-arrow-left"></i></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
      </div>
    </nav>
  </header>
  <main>
    <div class="container-usuarios">
      <h1>Lista de Usuários Cadastrados</h1>
      
      <table class="tabela-usuarios">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Data de Cadastro</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($usuarios) > 0): ?>
            <?php foreach ($usuarios as $usuario): ?>
              <tr>
                <td><?= htmlspecialchars($usuario['id']) ?></td>
                <td><?= htmlspecialchars($usuario['nome']) ?></td>
                <td><?= htmlspecialchars($usuario['email']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($usuario['data_cadastro'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4">Nenhum usuário encontrado.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <a href="painel_admin.php" class="btn-voltar">Voltar ao Painel</a>
    </div>
  </main>
</body>
</html>