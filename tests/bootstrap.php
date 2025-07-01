<?php
// tests/bootstrap.php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Carregar variáveis de ambiente
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Conectar banco de dados em memória usando SQLite3 nativo
try {
    $GLOBALS['db_test'] = new SQLite3(':memory:');
    $GLOBALS['db_test']->enableExceptions(true); // Ativar exceções para melhor tratamento de erros
} catch (Exception $e) {
    die("Erro ao conectar ao SQLite: " . $e->getMessage());
}

// Script de criação de tabelas
$schema = <<<SQL
CREATE TABLE usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT,
    email TEXT,
    senha TEXT,
    cpf TEXT,
    tipo_perfil TEXT
);

CREATE TABLE transacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER,
    tipo TEXT,
    valor REAL,
    data TEXT,
    categoria TEXT,
    descricao TEXT,
    metodo_pagamento TEXT,
    tipo_pagamento TEXT,
    parcelamento TEXT,
    qtd_parcelas INTEGER
);

CREATE TABLE notificacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER,
    mensagem TEXT,
    lida INTEGER DEFAULT 0,
    data_criacao TEXT
);
SQL;

// Executar script de criação
try {
    $GLOBALS['db_test']->exec($schema);
} catch (Exception $e) {
    die("Erro ao criar tabelas: " . $e->getMessage());
}

// Funções úteis para uso nos testes
function db_exec(string $sql): void {
    $GLOBALS['db_test']->exec($sql);
}

function db_query(string $sql): SQLite3Result {
    return $GLOBALS['db_test']->query($sql);
}

function db_last_id(): int {
    return $GLOBALS['db_test']->lastInsertRowID();
}