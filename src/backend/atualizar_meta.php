<?php
$pdo = require_once("../../Database/conn.php");

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'];
$titulo = $data['titulo'];
$descricao = $data['descricao'];
$data_final = $data['data_final'];
$valor = $data['valor'];

$stmt = $pdo->prepare("UPDATE metas SET titulo = ?, descricao = ?, data_final = ?, valor = ? WHERE id = ?");
$sucesso = $stmt->execute([$titulo, $descricao, $data_final, $valor, $id]);

header('Content-Type: application/json');
echo json_encode(['sucesso' => $sucesso]);