<?php
header("Content-Type: application/json");
session_start();

$conn = require_once __DIR__ . '/../../../Database/conn.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID da notificação inválido"]);
    exit();
}

$idNotificacao = (int) $_POST['id'];
$usuarioId = $_SESSION['usuario_id'];

try {
    $stmt = $conn->prepare("UPDATE notificacoes SET lida = 1 WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$idNotificacao, $usuarioId]);

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao marcar notificação como lida"]);
}
