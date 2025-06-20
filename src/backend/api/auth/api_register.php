<?php
session_start();
header('Content-Type: application/json');

$conn = require_once __DIR__ . '/../../../../Database/conn.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validação básica
if (empty($data['email']) || empty($data['senha']) || empty($data['nome'])) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
    exit;
}

$email = trim($data['email']);
$senha = trim($data['senha']);
$nome  = trim($data['nome']);

// Verifica se email já existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Este e-mail já está em uso.']);
    exit;
}

// Criptografa a senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Insere no banco
try {
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $email, $senha_hash]);

    // Login automático
    $usuario_id = $conn->lastInsertId();
    $_SESSION['usuario_id'] = $usuario_id;

    echo json_encode(['success' => true]);
    exit;
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $e->getMessage()]);
    exit;
}
// Se tudo deu certo, retorna sucesso