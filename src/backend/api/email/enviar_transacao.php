<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Carrega variáveis do .env
if (class_exists('Dotenv\Dotenv')) {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__, 4))->load();
} elseif (class_exists('Symfony\Component\Dotenv\Dotenv')) {
    (new Symfony\Component\Dotenv\Dotenv())->load(dirname(__DIR__, 4).'/.env');
} else {
    http_response_code(500);
    echo json_encode(['erro' => 'Biblioteca dotenv não encontrada.']);
    exit;
}

// Define cabeçalho JSON
header('Content-Type: application/json');

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

// Coleta e valida dados obrigatórios
$dadosObrigatorios = ['email', 'nome', 'valor', 'tipo', 'data'];
foreach ($dadosObrigatorios as $campo) {
    if (empty($_POST[$campo])) {
        http_response_code(400);
        echo json_encode(['erro' => "Campo obrigatório ausente: $campo"]);
        exit;
    }
}

// Atribui variáveis
$email         = $_POST['email'];
$nome          = $_POST['nome'];
$valor         = $_POST['valor'];
$tipo          = $_POST['tipo'];
$categoria     = $_POST['categoria'] ?? 'Não especificado';
$descricao     = $_POST['descricao'] ?? '';
$data          = $_POST['data'];
$metodo        = $_POST['metodo_pagamento'] ?? 'Não informado';
$parcelamento  = $_POST['parcelamento'] ?? 'Não';

// Instancia e configura PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USERNAME'];
    $mail->Password   = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['MAIL_PORT'];

    // Remetente e destinatário
    $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME'] ?? 'FinPlan');
    $mail->addAddress($email, $nome);

    // Conteúdo
    $mail->isHTML(true);
    $mail->Subject = '✅ Confirmação de Transação - FinPlan';
    $mail->Body = "
        <div style='font-family:Arial,sans-serif; color:#333;'>
            <h2 style='color:#24A646;'>Transação Registrada com Sucesso</h2>
            <p><strong>Tipo:</strong> {$tipo}</p>
            <p><strong>Valor:</strong> R$ {$valor}</p>
            <p><strong>Categoria:</strong> {$categoria}</p>
            <p><strong>Descrição:</strong> {$descricao}</p>
            <p><strong>Data:</strong> {$data}</p>
            <p><strong>Forma de Pagamento:</strong> {$metodo}</p>
            <p><strong>Parcelamento:</strong> {$parcelamento}</p>
            <br>
            <p>Obrigado por utilizar o <strong>FinPlan</strong>! 💚</p>
        </div>
    ";

    $mail->send();
    echo json_encode(['sucesso' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao enviar e-mail: ' . $mail->ErrorInfo]);
}
