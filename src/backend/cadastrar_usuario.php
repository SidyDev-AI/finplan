<?php
$pdo = require_once '../../Database/conn.php';

function cadastrarUsuario($nome, $email, $senha, $confirmacaoSenha) {
  global $pdo;
  
  if ($senha !== $confirmacaoSenha) {
    throw new Exception("As senhas não coincidem!");
  }
  
  if (strlen($senha) < 6) {
    throw new Exception("A senha deve ter pelo menos 6 caracteres!");
  }
  
  $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
  
  try {
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senhaHash);
      
    $stmt->execute();
    return $pdo->lastInsertId();
  } catch (PDOException $e) {
    if ($e->getCode() == 23000) {
      throw new Exception("Este e-mail já está cadastrado!");
    }
    throw new Exception("Erro ao cadastrar usuário: " . $e->getMessage());
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmacao = $_POST['confirm_senha'] ?? '';
        
    cadastrarUsuario($nome, $email, $senha, $confirmacao);

  } catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
  }
}
?>