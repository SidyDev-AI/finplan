<?php
// Caminho absoluto ao invés de relativo
$databasePath = __DIR__ . '/database.sqlite';

function conectarBanco() {
  global $databasePath;

  try {
    $pdo = new PDO("sqlite:$databasePath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tabela de usuários
    $sqlUsuarios = "CREATE TABLE IF NOT EXISTS usuarios (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      nome TEXT NOT NULL,
      email TEXT NOT NULL UNIQUE,
      senha TEXT NOT NULL,
      cpf TEXT NULL,
      data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    // Tabela de transações com categoria e tipo
    $sqlTransacoes = "CREATE TABLE IF NOT EXISTS transacoes (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      usuario_id INTEGER NOT NULL,
      valor REAL NOT NULL,
      descricao TEXT,
      categoria TEXT,
      tipo TEXT CHECK(tipo IN ('Entrada', 'Saida')) NOT NULL,
      data DATE DEFAULT CURRENT_DATE,
      FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )";

    // Tabela de notificações
    $sqlNotificacoes = "CREATE TABLE IF NOT EXISTS notificacoes (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      usuario_id INTEGER NOT NULL,
      titulo TEXT NOT NULL,
      mensagem TEXT NOT NULL,
      remetente TEXT DEFAULT 'Sistema',
      data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      lida INTEGER DEFAULT 0,
      tipo TEXT DEFAULT 'info',
      FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )";

    // Criação das tabelas
    $pdo->exec($sqlUsuarios);
    $pdo->exec($sqlTransacoes);
    $pdo->exec($sqlNotificacoes);

    return $pdo;

  } catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
  }
}

return conectarBanco();
?>