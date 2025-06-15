<?php
use PHPUnit\Framework\TestCase;

class PerfilTest extends TestCase
{
    public function testBuscaPerfilUsuario()
    {
        $db = $GLOBALS['db_test'];
        $db->exec("INSERT INTO usuarios (nome, email, senha, cpf, tipo_perfil) VALUES ('Carlos', 'carlos@email.com', '123456', '333.333.333-33', 'investidor')");
        $usuario_id = db_last_id();

        $result = $db->query("SELECT * FROM usuarios WHERE id = $usuario_id");
        $usuario = $result->fetchArray(SQLITE3_ASSOC);

        $this->assertEquals('Carlos', $usuario['nome']);
        $this->assertEquals('investidor', $usuario['tipo_perfil']);
    }

    public function testAtualizaPerfilUsuario()
    {
        $db = $GLOBALS['db_test'];
        $db->exec("INSERT INTO usuarios (nome, email, senha, cpf, tipo_perfil) VALUES ('Lucia', 'lucia@email.com', 'senha123', '444.444.444-44', 'basico')");
        $usuario_id = db_last_id();

        $db->exec("UPDATE usuarios SET tipo_perfil = 'avancado' WHERE id = $usuario_id");
        
        $result = $db->query("SELECT tipo_perfil FROM usuarios WHERE id = $usuario_id");
        $usuario = $result->fetchArray(SQLITE3_ASSOC);

        $this->assertEquals('avancado', $usuario['tipo_perfil']);
    }
}