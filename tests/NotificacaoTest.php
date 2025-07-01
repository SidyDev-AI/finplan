<?php
use PHPUnit\Framework\TestCase;

class NotificacaoTest extends TestCase
{
    protected function setUp(): void {
        // Limpar dados antes de cada teste
        db_exec("DELETE FROM notificacoes");
        db_exec("DELETE FROM usuarios");
    }

    public function testCriarNotificacao()
    {
        db_exec("INSERT INTO usuarios (nome, email, senha, cpf) VALUES ('Ana', 'ana@email.com', 'senha', '222.222.222-22')");
        $usuario_id = db_last_id();

        $stmt = $GLOBALS['db_test']->prepare("INSERT INTO notificacoes (usuario_id, mensagem, lida, data_criacao) VALUES (?, ?, ?, ?)");
        $stmt->bindValue(1, $usuario_id);
        $stmt->bindValue(2, 'Transação aprovada.');
        $stmt->bindValue(3, 0);
        $stmt->bindValue(4, date('Y-m-d H:i:s'));

        $result = $stmt->execute();
        $this->assertNotFalse($result); // Corrigido para assertNotFalse

        $res = db_query("SELECT COUNT(*) as total FROM notificacoes");
        $row = $res->fetchArray(SQLITE3_ASSOC);

        $this->assertEquals(1, $row['total']);
    }
}