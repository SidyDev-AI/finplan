<?php
session_start();

if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: ../../index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h1>
    <p>Esta é a página inicial do seu aplicativo.</p>
    <a href="logout.php">Sair</a>
</body>
</html>