<?php
session_start();
$conn = require_once __DIR__ . '/../../Database/conn.php';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$usuario_id = $_SESSION['usuario_id'];

// --- API Integration ---
$limit = isset($_GET['all']) && $_GET['all'] == 1 ? 1000 : 3;
$isFullPage = $limit > 3;

// Buscar notificacoes via API
$response = file_get_contents("http://localhost/src/backend/api/notificacoes/buscar.php?usuario_id={$usuario_id}&limit={$limit}");
$notificacoes = json_decode($response, true);

$total_nao_lidas = array_reduce($notificacoes, function ($carry, $item) {
  return $carry + ($item['lida'] == 0 ? 1 : 0);
}, 0);

// Dados adicionais (nome, saldo)
$stmt = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$primeiro_nome = explode(' ', $usuario['nome'])[0];

$stmt = $conn->prepare("SELECT SUM(valor) as saldo FROM transacoes WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$saldo = $stmt->fetch(PDO::FETCH_ASSOC)['saldo'] ?? 0;
$saldo_formatado = number_format($saldo, 2, ',', '.');
$data_atual = date("d-m-y H:i");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notificações - FinPlan</title>
  <link rel="stylesheet" href="../css/notificacoes.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="sidebar">
    <!-- Sidebar fixa -->
  </div>

  <div class="main-content">
    <div class="header">
      <div class="date">Hoje: <?= $data_atual ?></div>
      <div class="balance">Saldo geral: R$ <?= $saldo_formatado ?></div>
      <div class="user-profile">Olá, <?= htmlspecialchars($primeiro_nome) ?></div>
    </div>

    <div class="notifications">
      <div class="header-row">
        <h2>Notificações <?= $isFullPage ? 'Completas' : 'Recentes' ?></h2>
        <?php if ($total_nao_lidas > 0): ?>
        <button onclick="marcarTodasComoLidas()" class="btn-small">Marcar todas como lidas</button>
        <?php endif; ?>
      </div>

      <div class="notificacao-list">
        <?php if (count($notificacoes) > 0): ?>
          <?php foreach ($notificacoes as $n): ?>
            <div class="notificacao-item <?= $n['lida'] ? 'read' : 'unread' ?>">
              <p><?= htmlspecialchars($n['mensagem']) ?></p>
              <small><?= date('d/m/Y H:i', strtotime($n['data_criacao'])) ?></small>
              <?php if (!$n['lida']): ?>
              <button onclick="marcarComoLida(<?= $n['id'] ?>)"><i class="fas fa-check"></i></button>
              <?php endif; ?>
              <button onclick="excluirNotificacao(<?= $n['id'] ?>)"><i class="fas fa-trash"></i></button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="sem-notificacoes">
            <i class="fas fa-bell-slash"></i> Nenhuma notificação encontrada.
          </div>
        <?php endif; ?>
      </div>

      <?php if (!$isFullPage): ?>
      <a href="notificacao.php?all=1" class="ver-todas">Ver todas <i class="fas fa-arrow-right"></i></a>
      <?php endif; ?>
    </div>
  </div>

<script>
  async function marcarComoLida(id) {
    const res = await fetch(`../../src/backend/api/notificacoes/marcar_lida.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ notificacao_id: id })
    });
    location.reload();
  }

  async function excluirNotificacao(id) {
    if (!confirm('Deseja excluir esta notificação?')) return;
    const res = await fetch(`../../src/backend/api/notificacoes/excluir.php`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ notificacao_id: id })
    });
    location.reload();
  }

  async function marcarTodasComoLidas() {
    const res = await fetch(`../../src/backend/api/notificacoes/marcar_todas.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ usuario_id: <?= $usuario_id ?> })
    });
    location.reload();
  }
</script>
</body>
</html>
