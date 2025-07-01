<?php
session_start();
$conn = require_once __DIR__ . '/../../../../Database/conn.php';

header('Content-Type: application/json');

$entrada = json_decode(file_get_contents('php://input'), true);

$email = trim($entrada['email'] ?? '');
$senha = $entrada['senha'] ?? '';

if (!$email || !$senha) {
  echo json_encode(['success' => false, 'message' => 'Preencha e-mail e senha.']);
  exit;
}

$stmt = $conn->prepare("SELECT id, senha, role, nome FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
  echo json_encode(['success' => false, 'message' => 'E-mail ou senha inválidos.']);
  exit;
}

$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_role'] = $usuario['role'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['logado'] = true;

echo json_encode([
  'success' => true,
  'role' => $usuario['role'],
  'redirect' => $usuario['role'] === 'admin' ? '/src/pages/painel_admin.php' : '/src/pages/dashboard.php'
]);
exit;