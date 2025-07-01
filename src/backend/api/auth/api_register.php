<?php
session_start();
header('Content-Type: application/json');

$conn = require_once __DIR__ . '/../../../../Database/conn.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email']) || empty($data['senha']) || empty($data['nome'])) {
  echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
  exit;
}

// Normalizar entrada
$email = strtolower(trim($data['email']));
$senha = trim($data['senha']);
$nome  = trim($data['nome']);
$role  = (isset($data['role']) && $data['role'] === 'admin') ? 'admin' : 'usuario';

try {
  // Verificar se email já existe
  $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
  $stmt->execute([$email]);

  if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Este e-mail já está em uso.']);
    exit;
  }

  $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

  // Inserir novo usuário
  $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, ?)");
  $stmt->execute([$nome, $email, $senha_hash, $role]);

  // Iniciar sessão
  $_SESSION['usuario_id'] = $conn->lastInsertId();
  $_SESSION['usuario_nome'] = $nome;
  $_SESSION['usuario_role'] = $role;
  $_SESSION['logado'] = true;

  echo json_encode([
    'success' => true,
    'role' => $role,
    'redirect' => $role === 'admin' ? '/src/pages/painel_admin.php' : '/src/pages/dashboard.php'
  ]);
  exit;

} catch (PDOException $e) {
  if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
    echo json_encode(['success' => false, 'message' => 'Este e-mail já está em uso.']);
  } else {
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar.']);
  }
  exit;
}
