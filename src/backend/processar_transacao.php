<?php
session_start();
$conn = require_once __DIR__ . '/../../Database/conn.php';

 //Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do formulário
    $tipo = $_POST['transaction_type'] === 'income' ? 'Entrada' : 'Saida';
    $valor = str_replace(',', '.', str_replace('.', '', $_POST['amount']));
    $dia = $_POST['day'];
    $mes = $_POST['month'];
    $ano = $_POST['year'];
    $data = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);


    $categoria = $_POST['category'] ?? 'Não especificado';
    $descricao = $_POST['description'] ?? '';
    $metodo_pagamento = $_POST['payment_method'];
    $tipo_pagamento = $_POST['payment_type'];
    $parcelamento = $_POST['installment'] === 'yes' ? 'yes' : 'no';
    $qtd_parcelas = $parcelamento === 'yes' ? intval($_POST['installment_count']) : 1;

    try {
        $sql = "INSERT INTO transacoes 
            (usuario_id, tipo, valor, data, categoria, descricao, metodo_pagamento, tipo_pagamento, parcelamento, qtd_parcelas) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $usuario_id,
            $tipo,
            $valor,
            $data,
            $categoria,
            $descricao,
            $metodo_pagamento,
            $tipo_pagamento,
            $parcelamento,
            $qtd_parcelas
        ]);

        header("Location: ../pages/transactions.php?success=1");
        exit();
    } catch (PDOException $e) {
        echo "Erro ao inserir transação: " . $e->getMessage();
    }
} else {
    header("Location: ../pages/transactions.php");
    exit();
}
?>
