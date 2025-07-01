<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';
use Google\Client;
use Dotenv\Dotenv;

// Carregar variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../../');
$dotenv->load();

// Configurar cliente do Google
$client = new Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']); // Deve apontar para o callback
$client->addScope('email');
$client->addScope('profile');

// Iniciar login
$auth_url = $client->createAuthUrl();
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit;
