<?php

session_start();


$db_path = 'database.sqlite'; 


$pdo = require_once('../../Database/conn.php');



$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';


$stmt = $pdo->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email");
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->execute();


if ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
    if (password_verify($senha, $usuario['senha'])) {
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['logado'] = true;

        
        header("Location: /src/pages/perfil.php");
        exit();
        
    } else {
       
        header('Location: ../../index.php?erro=senha_incorreta');
        exit();
    }
} else {
   
    header('Location: ../../index.php?erro=usuario_nao_encontrado');
    exit();
}
?>