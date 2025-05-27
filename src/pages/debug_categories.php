<?php
session_start();
$conn = require_once __DIR__ . '/../../Database/conn.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$id = $_SESSION['usuario_id'];
$mes_atual = date('Y-m');

echo "<h1>Debug - Categorias do Dashboard</h1>";
echo "<p><strong>Usuário ID:</strong> $id</p>";
echo "<p><strong>Mês atual:</strong> $mes_atual</p>";

// 1. Verificar estrutura da tabela
echo "<h2>1. Estrutura da tabela transacoes:</h2>";
try {
    $stmt = $conn->prepare("PRAGMA table_info(transacoes)");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Nome</th><th>Tipo</th><th>Não Nulo</th><th>Padrão</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($coluna['name']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['type']) . "</td>";
        echo "<td>" . ($coluna['notnull'] ? 'SIM' : 'NÃO') . "</td>";
        echo "<td>" . htmlspecialchars($coluna['dflt_value'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao verificar estrutura: " . $e->getMessage() . "</p>";
}

// 2. Todas as transações do usuário
echo "<h2>2. Todas as transações do usuário:</h2>";
try {
    $stmt = $conn->prepare("SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY data DESC LIMIT 20");
    $stmt->execute([$id]);
    $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($transacoes) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Descrição</th><th>Categoria</th><th>Valor</th><th>Tipo</th><th>Data</th></tr>";
        foreach ($transacoes as $trans) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($trans['id']) . "</td>";
            echo "<td>" . htmlspecialchars($trans['descricao']) . "</td>";
            echo "<td style='background: " . (empty($trans['categoria']) ? '#ffcccc' : '#ccffcc') . ";'>" . htmlspecialchars($trans['categoria'] ?? 'VAZIO') . "</td>";
            echo "<td>R$ " . number_format($trans['valor'], 2, ',', '.') . "</td>";
            echo "<td>" . htmlspecialchars($trans['tipo']) . "</td>";
            echo "<td>" . htmlspecialchars($trans['data']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>Nenhuma transação encontrada!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao buscar transações: " . $e->getMessage() . "</p>";
}

// 3. Transações de saída com categoria
echo "<h2>3. Transações de saída com categoria:</h2>";
try {
    $stmt = $conn->prepare("
        SELECT * FROM transacoes 
        WHERE usuario_id = ? 
        AND tipo = 'Saida' 
        AND categoria IS NOT NULL 
        AND categoria != ''
        ORDER BY data DESC
    ");
    $stmt->execute([$id]);
    $saidas_com_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($saidas_com_categoria) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Descrição</th><th>Categoria</th><th>Valor</th><th>Data</th></tr>";
        foreach ($saidas_com_categoria as $trans) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($trans['id']) . "</td>";
            echo "<td>" . htmlspecialchars($trans['descricao']) . "</td>";
            echo "<td style='background: #ccffcc;'>" . htmlspecialchars($trans['categoria']) . "</td>";
            echo "<td>R$ " . number_format($trans['valor'], 2, ',', '.') . "</td>";
            echo "<td>" . htmlspecialchars($trans['data']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>Nenhuma transação de saída com categoria encontrada!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao buscar saídas: " . $e->getMessage() . "</p>";
}

// 4. Teste da consulta de categorias
echo "<h2>4. Teste da consulta de categorias (sem filtro de mês):</h2>";
try {
    $stmt = $conn->prepare("
        SELECT 
            categoria,
            SUM(valor) as total,
            COUNT(*) as quantidade_transacoes,
            tipo
        FROM transacoes 
        WHERE usuario_id = ? 
        AND tipo = 'Saida' 
        AND categoria IS NOT NULL
        AND categoria != ''
        GROUP BY categoria
        ORDER BY total DESC
    ");
    $stmt->execute([$id]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($categorias) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Categoria</th><th>Total</th><th>Quantidade</th></tr>";
        foreach ($categorias as $cat) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($cat['categoria']) . "</td>";
            echo "<td>R$ " . number_format($cat['total'], 2, ',', '.') . "</td>";
            echo "<td>" . $cat['quantidade_transacoes'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>Nenhuma categoria encontrada!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro na consulta de categorias: " . $e->getMessage() . "</p>";
}

// 5. Teste da consulta de categorias COM filtro de mês
echo "<h2>5. Teste da consulta de categorias (COM filtro de mês atual: $mes_atual):</h2>";
try {
    $stmt = $conn->prepare("
        SELECT 
            categoria,
            SUM(valor) as total,
            COUNT(*) as quantidade_transacoes,
            strftime('%Y-%m', data) as mes_transacao
        FROM transacoes 
        WHERE usuario_id = ? 
        AND tipo = 'Saida' 
        AND strftime('%Y-%m', data) = ?
        AND categoria IS NOT NULL
        AND categoria != ''
        GROUP BY categoria
        ORDER BY total DESC
    ");
    $stmt->execute([$id, $mes_atual]);
    $categorias_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($categorias_mes) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Categoria</th><th>Total</th><th>Quantidade</th><th>Mês</th></tr>";
        foreach ($categorias_mes as $cat) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($cat['categoria']) . "</td>";
            echo "<td>R$ " . number_format($cat['total'], 2, ',', '.') . "</td>";
            echo "<td>" . $cat['quantidade_transacoes'] . "</td>";
            echo "<td>" . htmlspecialchars($cat['mes_transacao']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>Nenhuma categoria encontrada para o mês atual!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro na consulta de categorias do mês: " . $e->getMessage() . "</p>";
}

echo "<br><br><a href='dashboard.php'>← Voltar ao Dashboard</a>";
?>
