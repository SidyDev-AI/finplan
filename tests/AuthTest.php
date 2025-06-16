<?php
// tests/AuthTest.php
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testUsuarioAutenticadoComSucesso()
    {
        $db = $GLOBALS['db_test'];
        $db->exec("INSERT INTO usuarios (nome, email, senha) VALUES ('Joao', 'joao@teste.com', 'senha123')");

        $res = $db->query("SELECT * FROM usuarios WHERE email = 'joao@teste.com' AND senha = 'senha123'");
        $user = $res->fetchArray(SQLITE3_ASSOC);

        $this->assertNotEmpty($user);
        $this->assertEquals('Joao', $user['nome']);
    }

    public function testAutenticacaoFalha()
    {
        $db = $GLOBALS['db_test'];
        $res = $db->query("SELECT * FROM usuarios WHERE email = 'naoexiste@teste.com' AND senha = 'errada'");
        $user = $res->fetchArray(SQLITE3_ASSOC);

        $this->assertFalse($user);
    }
}