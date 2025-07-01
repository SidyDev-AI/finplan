<?php
session_start();
$conn = require_once __DIR__ . '/../../Database/conn.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$id = $_SESSION['usuario_id'];

// Verificar se a tabela notificacoes existe no SQLite
$stmt = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='notificacoes'");
if ($stmt->fetchColumn() === false) {
  // Criar tabela notificacoes
  $conn->exec("CREATE TABLE notificacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    titulo TEXT NOT NULL,
    mensagem TEXT NOT NULL,
    remetente TEXT DEFAULT 'Sistema',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lida INTEGER DEFAULT 0,
    tipo TEXT DEFAULT 'info',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  )");
  echo "Tabela de notificações criada com sucesso!<br>";
}

// Notificações de exemplo
$notificacoes = [
  [
    'titulo' => 'Dica Financeira',
    'mensagem' => 'Lidar com dinheiro não é uma tarefa fácil para as pessoas. Ganhar dinheiro é muito difícil, por outro lado, gastar é extremamente fácil. Ofertas, promoções, propagandas na internet, enfim, sonhos de consumo incentivam ao dispêndio desenfreado e sem responsabilidade.'
  ],
  [
    'titulo' => 'Alerta de Orçamento',
    'mensagem' => 'Você atingiu 80% do seu orçamento mensal para a categoria "Alimentação". Considere revisar seus gastos para evitar ultrapassar o limite.'
  ],
  [
    'titulo' => 'Nova Funcionalidade',
    'mensagem' => 'Agora você pode configurar metas de economia! Acesse a seção "Metas" para definir objetivos financeiros e acompanhar seu progresso.'
  ],
  [
    'titulo' => 'Transação Suspeita',
    'mensagem' => 'Detectamos uma transação incomum em sua conta. Verifique a seção "Transações" para confirmar se reconhece todas as atividades recentes.'
  ],
  [
    'titulo' => 'Dica de Investimento',
    'mensagem' => 'Com base no seu perfil de investidor, temos algumas recomendações de investimentos que podem ser interessantes para você. Confira na seção "Investimentos".'
  ]
];

// Adicionar notificações
$sql = "INSERT INTO notificacoes (usuario_id, titulo, mensagem, data_criacao) VALUES (?, ?, ?, datetime('now'))";
$stmt = $conn->prepare($sql);

foreach ($notificacoes as $notificacao) {
  $stmt->execute([$id, $notificacao['titulo'], $notificacao['mensagem']]);
}

echo "Notificações de teste adicionadas com sucesso!";
echo "<br><a href='perfil.php'>Voltar para o perfil</a>";
?>