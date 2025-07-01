<?php
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase
{
    protected function setUp(): void {
        // Limpar dados antes de cada teste
        db_exec("DELETE FROM usuarios");
    }

    public function testInserirUsuario()
    {
        $stmt = $GLOBALS['db_test']->prepare("INSERT INTO usuarios (nome, email, senha, cpf, tipo_perfil) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindValue(1, 'João Silva');
        $stmt->bindValue(2, 'joao@email.com');
        $stmt->bindValue(3, password_hash('123456', PASSWORD_DEFAULT));
        $stmt->bindValue(4, '123.456.789-00');
        $stmt->bindValue(5, 'investidor');
        
        $result = $stmt->execute();
        $this->assertNotFalse($result); // Corrigido para assertNotFalse

        $res = db_query("SELECT COUNT(*) as total FROM usuarios");
        $row = $res->fetchArray(SQLITE3_ASSOC);

        $this->assertEquals(1, $row['total']);
    }
}