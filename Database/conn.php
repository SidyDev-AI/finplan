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

    // Tabela de Metas
    $sqlMetas = "CREATE TABLE IF NOT EXISTS metas (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      usuario_id INTEGER NOT NULL,
      titulo TEXT NOT NULL,
      valor REAL NOT NULL,
      valor_atual REAL DEFAULT 0,
      data_inicial TEXT NOT NULL,
      data_final TEXT NOT NULL,
      descricao TEXT NOT NULL,
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
    $pdo->exec($sqlMetas);
    $pdo->exec($sqlNotificacoes);

    return $pdo;

  } catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
  }
}

// ✅ Função de resumo financeiro: Entradas, Saídas e Saldo do mês atual
function calcularResumoFinanceiro($pdo, $usuario_id) {
    $resumo = [
        'saldo_total' => 0,
        'entradas_mes' => 0,
        'saidas_mes' => 0,
        'saldo_mes' => 0
    ];

    $mes_atual = date('Y-m');
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN tipo = 'Entrada' THEN valor ELSE -valor END) AS saldo_total
        FROM transacoes 
        WHERE usuario_id = ?
    ");
    $stmt->execute([$usuario_id]);
    $resumo['saldo_total'] = $stmt->fetchColumn() ?? 0;

    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN tipo = 'Entrada' THEN valor ELSE 0 END) AS entradas_mes,
            SUM(CASE WHEN tipo = 'Saida' THEN valor ELSE 0 END) AS saidas_mes,
            SUM(CASE WHEN tipo = 'Entrada' THEN valor ELSE -valor END) AS saldo_mes
        FROM transacoes 
        WHERE usuario_id = ? AND strftime('%Y-%m', data) = ?
    ");
    $stmt->execute([$usuario_id, $mes_atual]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $resumo['entradas_mes'] = $result['entradas_mes'] ?? 0;
    $resumo['saidas_mes'] = $result['saidas_mes'] ?? 0;
    $resumo['saldo_mes'] = $result['saldo_mes'] ?? 0;

    return $resumo;
}


return conectarBanco();
?>
