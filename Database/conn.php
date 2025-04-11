<?php
$databasePath = 'database.sqlite';

function conectarBanco() {
  global $databasePath;
    
  try {
    $pdo = new PDO("sqlite:$databasePath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
    $sql = "CREATE TABLE IF NOT EXISTS usuarios (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      nome TEXT NOT NULL,
      email TEXT NOT NULL UNIQUE,
      senha TEXT NOT NULL,
      cpf TEXT NULL,
      data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
        
    $pdo->exec($sql);   
    return $pdo;    
  } catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
  }
}

return conectarBanco();
?>