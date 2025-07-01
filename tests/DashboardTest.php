<?php
use PHPUnit\Framework\TestCase;

class DashboardTest extends TestCase
{
    protected function setUp(): void {
        // Limpar dados antes de cada teste
        db_exec("DELETE FROM transacoes");
        db_exec("DELETE FROM usuarios");
    }

    public function testCalculoSaldoDashboard()
    {
        db_exec("INSERT INTO usuarios (nome, email, senha) VALUES ('Maria', 'maria@finplan.com', 'abc123')");
        $usuario_id = db_last_id();

        db_exec("INSERT INTO transacoes (usuario_id, tipo, valor, data) VALUES ($usuario_id, 'Entrada', 1000, '2025-06-01')");
        db_exec("INSERT INTO transacoes (usuario_id, tipo, valor, data) VALUES ($usuario_id, 'Saida', 200, '2025-06-02')");

        $res = db_query("SELECT SUM(CASE WHEN tipo = 'Entrada' THEN valor ELSE -valor END) as saldo FROM transacoes WHERE usuario_id = $usuario_id");
        $row = $res->fetchArray(SQLITE3_ASSOC);

        $this->assertEquals(800.00, (float)$row['saldo']);
    }

    public function testTotalEntradas()
    {
        db_exec("INSERT INTO usuarios (nome, email, senha) VALUES ('Maria', 'maria@finplan.com', 'abc123')");
        $usuario_id = db_last_id();
        db_exec("INSERT INTO transacoes (usuario_id, tipo, valor, data) VALUES ($usuario_id, 'Entrada', 1000, '2025-06-01')");

        $res = db_query("SELECT SUM(valor) as entradas FROM transacoes WHERE tipo = 'Entrada'");
        $row = $res->fetchArray(SQLITE3_ASSOC);

        $this->assertEquals(1000.00, (float)$row['entradas']);
    }

    public function testTotalSaidas()
    {
        db_exec("INSERT INTO usuarios (nome, email, senha) VALUES ('Maria', 'maria@finplan.com', 'abc123')");
        $usuario_id = db_last_id();
        db_exec("INSERT INTO transacoes (usuario_id, tipo, valor, data) VALUES ($usuario_id, 'Saida', 200, '2025-06-02')");

        $res = db_query("SELECT SUM(valor) as saidas FROM transacoes WHERE tipo = 'Saida'");
        $row = $res->fetchArray(SQLITE3_ASSOC);

        $this->assertEquals(200.00, (float)$row['saidas']);
    }
}