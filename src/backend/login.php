<?php

session_start();


$db_path = 'database.sqlite'; 


try {
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}


$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';


$stmt = $db->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email");
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->execute();


if ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
    if (password_verify($senha, $usuario['senha'])) {
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['logado'] = true;

        
        header('Location: ../pages/home.php');
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