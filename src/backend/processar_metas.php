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
  // Recebe os dados do formulário
  $titulo = trim($_POST['titleMeta']);
  $valor = str_replace(',', '.', str_replace('.', '', $_POST['amount']));

  // Datas (inicial e final)
  $data_inicial = date('Y-m-d');

  $dia_fim = $_POST['day_final'];
  $mes_fim = $_POST['month_final'];
  $ano_fim = $_POST['year_final'];
  $data_final = sprintf('%04d-%02d-%02d', $ano_fim, $mes_fim, $dia_fim);

  $descricao = trim($_POST['descricaoMeta']);

  try {
    $sql = "INSERT INTO metas (usuario_id, titulo, valor, data_inicial, data_final, descricao)
      VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
      $usuario_id,
      $titulo,
      $valor,
      $data_inicial,
      $data_final,
      $descricao
    ]);

      header("Location: ../pages/metas.php?success=1");
      exit();
    } catch (PDOException $e) {
      echo "Erro ao salvar meta: " . $e->getMessage();
    }
} else {
  header("Location: ../pages/metas.php");
  exit();
}
?>