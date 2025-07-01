<?php
header("Content-Type: application/json");
session_start();

$conn = require_once __DIR__ . '/../../../Database/conn.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

$id = $_SESSION['usuario_id'];
$full = isset($_GET['all']) && $_GET['all'] == 1;
$limit = $full ? 1000 : 3;

try {
    $stmt = $conn->prepare("
        SELECT id, mensagem, data_criacao, lida 
        FROM notificacoes 
        WHERE usuario_id = ? 
        ORDER BY data_criacao DESC 
        LIMIT ?
    ");
    $stmt->execute([$id, $limit]);
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = ? AND lida = 0");
    $stmt->execute([$id]);
    $total_nao_lidas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        "notificacoes" => $notificacoes,
        "total_nao_lidas" => $total_nao_lidas
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao buscar notificações"]);
}
