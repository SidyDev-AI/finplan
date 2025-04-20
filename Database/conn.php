<?php
// Caminho absoluto ao invés de relativo
$databasePath = __DIR__ . '/database.sqlite';

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

$sqlTransacoes = "CREATE TABLE IF NOT EXISTS transacoes (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  usuario_id INTEGER NOT NULL,
  valor REAL NOT NULL,
  descricao TEXT,
  data DATE DEFAULT CURRENT_DATE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
)";

    $pdo->exec($sql);
    $pdo->exec($sqlTransacoes);
    return $pdo;
  } catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
  }
}

return conectarBanco();
?>
