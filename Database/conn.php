<?php
// Caminho absoluto ao invés de relativo
$databasePath = __DIR__ . '/database.sqlite';

function conectarBanco() {
  global $databasePath;

  try {
    $pdo = new PDO("sqlite:$databasePath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');

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
      tipo TEXT NOT NULL CHECK (tipo IN ('Entrada', 'Saida')),
      valor REAL NOT NULL,
      data DATE NOT NULL,
      categoria TEXT NOT NULL,
      descricao TEXT,
      metodo_pagamento TEXT NOT NULL,
      tipo_pagamento TEXT NOT NULL,
      parcelamento TEXT CHECK (parcelamento IN ('yes', 'no')) NOT NULL,
      qtd_parcelas INTEGER DEFAULT 1,
      criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
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
