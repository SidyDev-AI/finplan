<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id'])) {
  echo json_encode(['sucesso' => false, 'erro' => 'ID não fornecido']);
  exit;
}

$id = intval($data['id']);
try {
  $db = new PDO('sqlite:../../Database/database.sqlite');
  $stmt = $db->prepare("DELETE FROM metas WHERE id = :id");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $sucesso = $stmt->execute();

  echo json_encode(['sucesso' => $sucesso]);
} catch (PDOException $e) {
  echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
