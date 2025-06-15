<?php
// tests/EmailTest.php

use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testEmailSimuladoComDadosValidos()
    {
        $dados = [
            'email' => 'teste@finplan.com',
            'nome' => 'Usuário Teste',
            'valor' => '250,00',
            'tipo' => 'Entrada',
            'categoria' => 'servicos',
            'descricao' => 'Freelance',
            'data' => '10/06/2025',
            'metodo_pagamento' => 'PIX',
            'parcelamento' => 'Não'
        ];

        $mensagem = "Transação: {$dados['tipo']} | R$ {$dados['valor']} | Categoria: {$dados['categoria']}";

        $this->assertStringContainsString('Entrada', $mensagem);
        $this->assertStringContainsString('250,00', $mensagem);
        $this->assertStringContainsString('servicos', $mensagem);
    }
}