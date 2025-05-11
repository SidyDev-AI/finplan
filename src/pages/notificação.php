<?php
session_start();
$conn = require_once __DIR__ . '/../../Database/conn.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$id = $_SESSION['usuario_id'];

// Determinar se estamos na página de todas as notificações
$isFullPage = isset($_GET['all']) && $_GET['all'] == 1;

// Marcar notificação como lida
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
  $notificacao_id = $_GET['mark_read'];
  $stmt = $conn->prepare("UPDATE notificacoes SET lida = 1 WHERE id = ? AND usuario_id = ?");
  $stmt->execute([$notificacao_id, $id]);
  
  // Redirecionar para evitar reenvio do formulário
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit();
}

// Marcar todas como lidas
if (isset($_GET['mark_all_read'])) {
  $stmt = $conn->prepare("UPDATE notificacoes SET lida = 1 WHERE usuario_id = ?");
  $stmt->execute([$id]);
  
  // Redirecionar para evitar reenvio do formulário
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit();
}

// Buscar notificações
$limit = $isFullPage ? 1000 : 3; // Limitar a 3 na visualização compacta
$stmt = $conn->prepare("
  SELECT id, mensagem, data_criacao, lida 
  FROM notificacoes 
  WHERE usuario_id = ? 
  ORDER BY data_criacao DESC
  LIMIT ?
");
$stmt->execute([$id, $limit]);
$notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contagem total de notificações não lidas
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = ? AND lida = 0");
$stmt->execute([$id]);
$total_nao_lidas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Se for a página completa de notificações
if ($isFullPage) {
  // Buscar dados para o cabeçalho
  $stmt = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
  $stmt->execute([$id]);
  $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
  $primeiro_nome = explode(' ', $usuario['nome'])[0];
  
  // Buscar saldo
  $stmt = $conn->prepare("SELECT SUM(valor) as saldo FROM transacoes WHERE usuario_id = ?");
  $stmt->execute([$id]);
  $saldo = $stmt->fetch(PDO::FETCH_ASSOC)['saldo'] ?? 0;
  $saldo_formatado = number_format($saldo, 2, ',', '.');
  
  $data_atual = date("d-m-y H:i");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Todas as Notificações - FinPlan</title>
  <link rel="stylesheet" href="../css/notificacao.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="sidebar">
    <div class="logo">
      <div class="logo-circle">R$</div>
      <div class="logo-text"><span class="fin">Fin</span><span class="plan">Plan</span></div>
    </div>

    <ul class="menu">
      <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
      <li><a href="budget.php"><i class="fas fa-wallet"></i> Budget</a></li>
      <li><a href="categories.php"><i class="fas fa-chart-pie"></i> Categories</a></li>
      <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
      <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
      <li><a href="accounts.php"><i class="fas fa-credit-card"></i> Accounts</a></li>
      <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
      <li><a href="help.php"><i class="fas fa-question-circle"></i> Help</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log out</a></li>
    </ul>

    <div class="theme-toggle">
      <div class="toggle-track"><div class="toggle-thumb"></div></div>
      <i class="fas fa-sun sun"></i>
      <i class="fas fa-moon moon"></i>
    </div>
  </div>

  <div class="main-content">
    <div class="header">
      <div class="header-left">
        <div class="date">Today: <?= $data_atual ?></div>
        <div class="balance">General balance: R$ <?= $saldo_formatado ?> (<span>↑ R$ 124,32</span>)</div>
      </div>
      <div class="header-right">
        <div class="notification active"><i class="fas fa-bell"></i>
          <?php if ($total_nao_lidas > 0): ?>
            <span class="notification-badge"><?= $total_nao_lidas ?></span>
          <?php endif; ?>
        </div>
        <div class="user-profile">
          <div class="user-avatar"></div>
          <div class="user-name"><?= htmlspecialchars($primeiro_nome) ?></div>
        </div>
        <div class="menu-dots"><i class="fas fa-ellipsis-v"></i></div>
      </div>
    </div>

    <div class="notifications-full-page">
      <div class="notifications-header">
        <h1>Todas as Notificações</h1>
        <div class="action-buttons">
          <?php if ($total_nao_lidas > 0): ?>
            <a href="?all=1&mark_all_read=1" class="mark-all-btn">
              <i class="fas fa-check-double"></i> Marcar todas como lidas
            </a>
          <?php endif; ?>
          <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
          </a>
        </div>
      </div>
<?php 
} // Fim do if isFullPage header
?>

<div class="notification-container <?= $isFullPage ? 'full-page' : '' ?>">
  <?php if (!$isFullPage): ?>
    <div class="notification-header">
      <h2>Notificações</h2>
      <?php if ($total_nao_lidas > 0): ?>
        <a href="?mark_all_read=1" class="mark-all-btn-small">
          <i class="fas fa-check-double"></i>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="notification-list">
    <?php if (count($notificacoes) > 0): ?>
      <?php foreach ($notificacoes as $notificacao): ?>
        <div class="notification-item <?= $notificacao['lida'] ? 'read' : 'unread' ?>">
          <div class="notification-avatar">
            <div class="avatar-circle"></div>
          </div>
          <div class="notification-content">
            <div class="notification-message"><?= htmlspecialchars($notificacao['mensagem']) ?></div>
            <div class="notification-date">
              <?= date("d/m/Y H:i", strtotime($notificacao['data_criacao'])) ?>
            </div>
          </div>
          <?php if (!$notificacao['lida']): ?>
            <a href="?<?= $isFullPage ? 'all=1&' : '' ?>mark_read=<?= $notificacao['id'] ?>" class="notification-action">
              <i class="fas fa-times"></i>
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-notifications">
        <i class="fas fa-bell-slash"></i>
        <p>Você não tem notificações</p>
      </div>
    <?php endif; ?>
  </div>

  <?php if (!$isFullPage && count($notificacoes) > 0): ?>
    <div class="notification-footer">
      <a href="notificacao.php?all=1" class="view-all-btn">
        Mostrar todas <i class="fas fa-arrow-right"></i>
      </a>
    </div>
  <?php endif; ?>
</div>

<?php if ($isFullPage): ?>
    </div> <!-- Fecha notifications-full-page -->
  </div> <!-- Fecha main-content -->

  <script>
    document.querySelector('.toggle-track').addEventListener('click', function() {
      const thumb = document.querySelector('.toggle-thumb');
      const body = document.body;
      if (thumb.style.right === '3px') {
        thumb.style.right = '33px';
        body.classList.add('light-theme');
      } else {
        thumb.style.right = '3px';
        body.classList.remove('light-theme');
      }
    });
  </script>
</body>
</html>
<?php endif; ?>