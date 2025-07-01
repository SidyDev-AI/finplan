<?php
session_start();
$pdo = require_once("../../Database/conn.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$cpf = $_POST['cpf'] ?? '';
$senha = $_POST['senha'] ?? '';

// Validação básica (você pode adicionar mais conforme quiser)
if (empty($nome) || empty($email) || empty($cpf)) {
    echo "Por favor, preencha todos os campos obrigatórios.";
    exit();
}

try {
    // Atualiza com ou sem a senha
    if (!empty($senha)) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nome = :nome, email = :email, cpf = :cpf, senha = :senha WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':cpf' => $cpf,
            ':senha' => $senha_hash,
            ':id' => $usuario_id
        ]);
    } else {
        $sql = "UPDATE usuarios SET nome = :nome, email = :email, cpf = :cpf WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':cpf' => $cpf,
            ':id' => $usuario_id
        ]);
    }

    // Atualiza o nome da sessão
    $_SESSION['usuario_nome'] = $nome;

    header("Location: ../pages/perfil.php?sucesso=perfil_atualizado");
    exit();

} catch (PDOException $e) {
    echo "Erro ao atualizar perfil: " . $e->getMessage();
    exit();
}
