<?php
header("Content-Type: application/json");
session_start();

$conn = require_once __DIR__ . '/../../../Database/conn.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

if (empty($_POST['mensagem'])) {
    http_response_code(400);
    echo json_encode(["error" => "Mensagem da notificação é obrigatória"]);
    exit();
}

$usuarioId = $_SESSION['usuario_id'];
$mensagem = trim($_POST['mensagem']);

try {
    $stmt = $conn->prepare("INSERT INTO notificacoes (usuario_id, mensagem, lida, data_criacao) VALUES (?, ?, 0, NOW())");
    $stmt->execute([$usuarioId, $mensagem]);

    echo json_encode(["success" => true, "id" => $conn->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao adicionar notificação"]);
}
