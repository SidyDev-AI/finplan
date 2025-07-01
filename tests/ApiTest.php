<?php
// tests/ApiTest.php
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    public function testConstrucaoRequisicaoPost()
    {
        $dados = [
            'email'            => 'usuario@finplan.com',
            'nome'             => 'Usuário FinPlan',
            'valor'            => '99,90',
            'tipo'             => 'Entrada',
            'categoria'        => 'alimentacao',
            'descricao'        => 'Compra no mercado',
            'data'             => '07/06/2025',
            'metodo_pagamento' => 'PIX',
            'parcelamento'     => 'Não'
        ];

        $conteudo = http_build_query($dados);

        $opcoes = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $conteudo
            ]
        ];

        $this->assertIsArray($opcoes['http']);
        $this->assertStringContainsString('email=usuario%40finplan.com', $conteudo);
        $this->assertStringContainsString('valor=99%2C90', $conteudo);
        $this->assertEquals('POST', $opcoes['http']['method']);
    }
}
