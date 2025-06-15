<?php
use PHPUnit\Framework\TestCase;

class TransacaoTest extends TestCase
{
    protected function setUp(): void {
        // Limpar dados antes de cada teste
        db_exec("DELETE FROM transacoes");
        db_exec("DELETE FROM usuarios");
    }

    public function testInserirTransacao()
    {
        // Inserir usuário antes da transação
        db_exec("INSERT INTO usuarios (nome, email, senha, cpf, tipo_perfil) VALUES ('Maria', 'maria@email.com', '123', '111.111.111-11', 'investidor')");
        $usuario_id = db_last_id();

        $stmt = $GLOBALS['db_test']->prepare("INSERT INTO transacoes (usuario_id, tipo, valor, data, categoria, descricao, metodo_pagamento, tipo_pagamento, parcelamento, qtd_parcelas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bindValue(1, $usuario_id);
        $stmt->bindValue(2, 'Entrada');
        $stmt->bindValue(3, 300.00);
        $stmt->bindValue(4, '2025-06-01');
        $stmt->bindValue(5, 'salario');
        $stmt->bindValue(6, 'Pagamento mensal');
        $stmt->bindValue(7, 'pix');
        $stmt->bindValue(8, 'credito');
        $stmt->bindValue(9, 'no');
        $stmt->bindValue(10, 1);

        $result = $stmt->execute();
        $this->assertNotFalse($result); // Corrigido para assertNotFalse

        $res = db_query("SELECT COUNT(*) as total FROM transacoes");
        $row = $res->fetchArray(SQLITE3_ASSOC);

        $this->assertEquals(1, $row['total']);
    }
}