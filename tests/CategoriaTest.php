<?php
// tests/CategoriaTest.php
use PHPUnit\Framework\TestCase;

class CategoriaTest extends TestCase
{
    public function testCategoriaRegistrada()
    {
        $db = $GLOBALS['db_test'];
        $db->exec("INSERT INTO usuarios (nome, email, senha) VALUES ('Lucas', 'lucas@teste.com', '123456')");
        $usuario_id = $db->lastInsertRowID();

        $categoria = 'alimentacao';
        $sql = "INSERT INTO transacoes (usuario_id, tipo, valor, data, categoria) VALUES (?, 'Entrada', 100.00, '2025-06-07', ?)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $usuario_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $categoria, SQLITE3_TEXT);
        $stmt->execute();

        $res = $db->query("SELECT categoria FROM transacoes WHERE usuario_id = $usuario_id");
        $row = $res->fetchArray(SQLITE3_ASSOC);

        $this->assertEquals($categoria, $row['categoria']);
    }
}