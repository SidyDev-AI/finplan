<?php
session_start();
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Google\Client;
use Dotenv\Dotenv;

// Carrega o .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../../');
$dotenv->load();

// Inicializa o cliente Google
$client = new Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (isset($token['error'])) {
            throw new Exception("Erro ao obter token: " . $token['error_description']);
        }

        $client->setAccessToken($token['access_token']);

        $oauth2 = new \Google\Service\Oauth2($client);
        $userInfo = $oauth2->userinfo->get();

        // Conecta ao banco
        $conn = require_once __DIR__ . '/../../../../Database/conn.php';

        // Verifica se usuário já existe
        $stmt = $conn->prepare("SELECT id, nome, role FROM usuarios WHERE email = ?");
        $stmt->execute([$userInfo->email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            // Criar novo usuário com role padrão "usuario"
            $senhaFake = password_hash(uniqid(), PASSWORD_DEFAULT);
            $role = 'usuario';

            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userInfo->name, $userInfo->email, $senhaFake, $role]);
            $usuario_id = $conn->lastInsertId();

            $_SESSION['usuario_nome'] = $userInfo->name;
            $_SESSION['usuario_role'] = $role;
        } else {
            $usuario_id = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_role'] = $usuario['role'];
        }

        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['logado'] = true;

        // Redirecionar de acordo com a role
        if ($_SESSION['usuario_role'] === 'admin') {
            header('Location: /src/pages/painel_admin.php');
        } else {
            header('Location: /src/pages/dashboard.php');
        }
        exit;

    } catch (Exception $e) {
        echo "Erro no login com Google: " . htmlspecialchars($e->getMessage());
        exit;
    }
} else {
    echo "Código de autorização não encontrado.";
    exit;
}
