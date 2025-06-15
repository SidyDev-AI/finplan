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
        $qtd_parcelas     = $parcelamento === 'yes' ? intval($_POST['installment_count']) : 1;

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

    // -------------------- INSERT --------------------
    $tipo = $_POST['transaction_type'] === 'income' ? 'Entrada' : 'Saida';
    $valor_bruto = $_POST['amount'];
    $valor = str_replace(',', '.', str_replace('.', '', $valor_bruto));
    $dia  = $_POST['day'];
    $mes  = $_POST['month'];
    $ano  = $_POST['year'];
    $data = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);

    $categoria        = $_POST['category'] ?? 'Não especificado';
    $descricao        = $_POST['description'] ?? '';
    $metodo_pagamento = $_POST['payment_method'];
    $tipo_pagamento   = $_POST['payment_type'];
    $parcelamento     = $_POST['installment'] === 'yes' ? 'yes' : 'no';
    $qtd_parcelas     = $parcelamento === 'yes' ? intval($_POST['installment_count']) : 1;

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

        // Buscar email e nome do usuário
        $stmtUser = $conn->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
        $stmtUser->execute([$usuario_id]);
        $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($usuario && !empty($usuario['email'])) {
            $emailData = [
                'email'            => $usuario['email'],
                'nome'             => $usuario['nome'],
                'valor'            => number_format((float)$valor, 2, ',', '.'),
                'tipo'             => $tipo,
                'categoria'        => $categoria,
                'descricao'        => $descricao,
                'data'             => date("d/m/Y", strtotime($data)),
                'metodo_pagamento' => $metodo_pagamento,
                'parcelamento'     => $parcelamento === 'yes' ? "{$qtd_parcelas}x" : 'Não'
            ];

            $url = "http://localhost/src/backend/api/email/enviar_transacao.php";
            $context = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query($emailData),
                    'timeout' => 2
                ]
            ]);
            @file_get_contents($url, false, $context);
        }

        header("Location: ../pages/transactions.php?success=1");
        exit();

    } catch (PDOException $e) {
        error_log("Erro ao inserir transação: " . $e->getMessage());
        echo "Erro ao inserir transação. Tente novamente mais tarde.";
    }

} else {
    header("Location: ../pages/transactions.php");
    exit();
}
