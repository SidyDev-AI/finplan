<?php
session_start();
header('Content-Type: application/json');

$conn = require_once __DIR__ . '/../../../../Database/conn.php';

// Apenas administradores podem acessar
if (!isset($_SESSION['logado']) || $_SESSION['usuario_role'] !== 'admin') {
  http_response_code(403); // Proibido
  echo json_encode(['error' => 'Acesso não autorizado.']);
  exit();
}

try {
    // 1. Buscar todos os usuários (exceto o próprio admin)
    $stmt_users = $conn->prepare("SELECT id, nome, email, data_cadastro FROM usuarios WHERE role = 'usuario' ORDER BY data_cadastro DESC");
    $stmt_users->execute();
    $usuarios = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

    // 2. Buscar as últimas transações de todos os usuários
    $stmt_trans = $conn->prepare("
        SELECT t.id, t.descricao, t.categoria, t.valor, t.data, t.tipo, u.nome as usuario_nome
        FROM transacoes t
        JOIN usuarios u ON t.usuario_id = u.id
        WHERE u.role = 'usuario'
        ORDER BY t.criado_em DESC
        LIMIT 20
    ");
    $stmt_trans->execute();
    $transacoes = $stmt_trans->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Contar total de usuários e transações
    $total_usuarios = $conn->query("SELECT COUNT(*) FROM usuarios WHERE role = 'usuario'")->fetchColumn();
    $total_transacoes = $conn->query("SELECT COUNT(*) FROM transacoes")->fetchColumn();


    echo json_encode([
        'sucesso' => true,
        'dados' => [
            'total_usuarios' => (int)$total_usuarios,
            'total_transacoes' => (int)$total_transacoes,
            'usuarios' => $usuarios,
            'transacoes_recentes' => $transacoes
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500); // Erro do Servidor
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
}
?>