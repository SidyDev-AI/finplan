<?php
session_start();
$conn = require_once __DIR__ . '/../../Database/conn.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // -------------------- DELETE --------------------
    if (!empty($_POST['action']) && $_POST['action'] === 'delete' && is_numeric($_POST['transacao_id'])) {
        $id = (int)$_POST['transacao_id'];
        $stmt = $conn->prepare("DELETE FROM transacoes WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $usuario_id]);
        header("Location: ../pages/transactions.php?success=deleted");
        exit();
    }

    // -------------------- UPDATE --------------------
    if (!empty($_POST['action']) && $_POST['action'] === 'update' && is_numeric($_POST['transacao_id'])) {
        $id   = (int)$_POST['transacao_id'];
        $tipo = $_POST['transaction_type'] === 'income' ? 'Entrada' : 'Saida';
        $valor = str_replace(',', '.', str_replace('.', '', $_POST['amount']));
        $data  = sprintf(
            '%04d-%02d-%02d',
            (int)$_POST['year'],
            (int)$_POST['month'],
            (int)$_POST['day']
        );
        $categoria        = $_POST['category']         ?? 'Não especificado';
        $descricao        = $_POST['description']      ?? '';
        $metodo_pagamento = $_POST['payment_method']   ?? '';
        $tipo_pagamento   = $_POST['payment_type']     ?? '';
        $parcelamento     = (($_POST['installment'] ?? 'no') === 'yes') ? 'yes' : 'no';
        $qtd_parcelas     = $parcelamento === 'yes'
                            ? intval($_POST['installment_count'])
                            : 1;

        try {
            $sql = "UPDATE transacoes SET
                        tipo = ?, valor = ?, data = ?, categoria = ?, descricao = ?,
                        metodo_pagamento = ?, tipo_pagamento = ?, parcelamento = ?, qtd_parcelas = ?
                    WHERE id = ? AND usuario_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $tipo, $valor, $data, $categoria, $descricao,
                $metodo_pagamento, $tipo_pagamento, $parcelamento, $qtd_parcelas,
                $id, $usuario_id
            ]);
            header("Location: ../pages/transactions.php?success=updated");
            exit();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar transação: " . $e->getMessage());
            header("Location: ../pages/transactions.php?error=update_failed");
            exit();
        }
    }

    // -------------------- CREATE (default) --------------------
    // Se não veio action=delete nem action=update, cai aqui:
    $tipo = $_POST['transaction_type'] === 'income' ? 'Entrada' : 'Saida';
    $valor = str_replace(',', '.', str_replace('.', '', $_POST['amount']));
    $data  = sprintf(
        '%04d-%02d-%02d',
        (int)$_POST['year'],
        (int)$_POST['month'],
        (int)$_POST['day']
    );
    $categoria        = $_POST['category']         ?? 'Não especificado';
    $descricao        = $_POST['description']      ?? '';
    $metodo_pagamento = $_POST['payment_method']   ?? '';
    $tipo_pagamento   = $_POST['payment_type']     ?? '';
    $parcelamento     = (($_POST['installment'] ?? 'no') === 'yes') ? 'yes' : 'no';
    $qtd_parcelas     = $parcelamento === 'yes'
                        ? intval($_POST['installment_count'])
                        : 1;

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
        error_log("Erro ao inserir transação: " . $e->getMessage());
        header("Location: ../pages/transactions.php?error=insert_failed");
        exit();
    }

} else {
    // Se não for POST, redireciona para a página de transações
    header("Location: ../pages/transactions.php");
    exit();
}