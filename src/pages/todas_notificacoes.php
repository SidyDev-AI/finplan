<?php
session_start();
$conn = require_once __DIR__ . '/../../Database/conn.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$id = $_SESSION['usuario_id'];
$mensagem = "";

// Processar ações de notificação
if (isset($_POST['marcar_lida'])) {
  $notificacao_id = $_POST['notificacao_id'];
  $sql = "UPDATE notificacoes SET lida = 1 WHERE id = ? AND usuario_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$notificacao_id, $id]);
  header("Location: todas_notificacoes.php");
  exit();
}

if (isset($_POST['excluir_notificacao'])) {
  $notificacao_id = $_POST['notificacao_id'];
  $sql = "DELETE FROM notificacoes WHERE id = ? AND usuario_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$notificacao_id, $id]);
  header("Location: todas_notificacoes.php");
  exit();
}

if (isset($_POST['marcar_todas_lidas'])) {
  $sql = "UPDATE notificacoes SET lida = 1 WHERE usuario_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$id]);
  header("Location: todas_notificacoes.php");
  exit();
}

// Buscar dados do usuário
$sql = "SELECT nome FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Extrair o primeiro nome para exibição no cabeçalho
$primeiro_nome = explode(' ', $usuario['nome'])[0];

// Buscar todas as notificações
$sql_notificacoes = "SELECT * FROM notificacoes WHERE usuario_id = ? ORDER BY lida ASC, data_criacao DESC";
$stmt_notificacoes = $conn->prepare($sql_notificacoes);
$stmt_notificacoes->execute([$id]);
$notificacoes = $stmt_notificacoes->fetchAll(PDO::FETCH_ASSOC);

// Contar total de notificações não lidas
$sql_count = "SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = ? AND lida = 0";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute([$id]);
$total_nao_lidas = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

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
  <title>Notificações - FinPlan</title>
  <link rel="stylesheet" href="../css/perfil.css">
  <link rel="stylesheet" href="../css/notificacoes.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="sidebar">
  <div class="logo">
    <?php 
    $logoWidth = 50; // Largura em pixels
    $logoHeight = 50; // Altura em pixels
    
    // Caminho para a imagem
    $logoPath = __DIR__ . "/../img/logo.png";
    
    if (file_exists($logoPath)): 
      // Estilo inline para controlar o tamanho
      $logoStyle = "width: {$logoWidth}px; height: {$logoHeight}px; object-fit: cover; border-radius: 50%;";
    ?>
      <img 
        src="../img/logo.png?v=<?= time() ?>" 
        alt="FinPlan Logo" 
        class="logo-image" 
        style="<?= $logoStyle ?>"
      >
    <?php else: ?>
      <!-- Ajuste o tamanho do círculo de fallback também -->
      <div class="logo-circle" style="width: <?= $logoWidth ?>px; height: <?= $logoHeight ?>px; line-height: <?= $logoHeight ?>px; font-size: <?= $logoWidth/2 ?>px;">
        R$
      </div>
    <?php endif; ?>
    <div class="logo-text"><span class="fin">Fin</span><span class="plan">Plan</span></div>
  </div>

    <ul class="menu">
      <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
      <li><a href="#"><i class="fas fa-wallet"></i> Budget</a></li>
      <li><a href="#"><i class="fas fa-chart-pie"></i> Categories</a></li>
      <li><a href="#"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
      <li><a href="#"><i class="fas fa-chart-bar"></i> Analytics</a></li>
      <li><a href="#"><i class="fas fa-credit-card"></i> Accounts</a></li>
      <li><a href="perfil.php"><i class="fas fa-cog"></i> Settings</a></li>
      <li><a href="#"><i class="fas fa-question-circle"></i> Help</a></li>
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
        <div class="notification">
          <i class="fas fa-bell"></i>
          <?php if ($total_nao_lidas > 0): ?>
            <div class="notification-badge"><?= $total_nao_lidas > 9 ? '9+' : $total_nao_lidas ?></div>
          <?php endif; ?>
        </div>
        <div class="user-profile">
          <div class="user-avatar"></div>
          <div class="user-name"><?= htmlspecialchars($primeiro_nome) ?></div>
        </div>
        <div class="menu-dots"><i class="fas fa-ellipsis-v"></i></div>
      </div>
    </div>

    <!-- Notifications Page Content -->
    <div class="notifications-page">
      <div class="notifications-container">
        <div class="notifications-header">
          <div class="notifications-title">
            <h2>Todas as Notificações</h2>
          </div>
          <?php if ($total_nao_lidas > 0): ?>
            <form method="POST">
              <button type="submit" name="marcar_todas_lidas" class="mark-read-btn">
                <i class="fas fa-check-double"></i> Marcar todas como lidas
              </button>
            </form>
          <?php endif; ?>
        </div>

        <?php if (count($notificacoes) > 0): ?>
          <?php foreach ($notificacoes as $notificacao): ?>
            <div class="notification-card <?= $notificacao['lida'] ? '' : 'unread' ?>">
              <div class="notification-avatar"></div>
              <div class="notification-content">
                <div><?= htmlspecialchars($notificacao['mensagem']) ?></div>
                <div class="notification-date">
                  <?= date('d/m/Y H:i', strtotime($notificacao['data_criacao'])) ?>
                </div>
                <div class="notification-actions">
                  <?php if (!$notificacao['lida']): ?>
                    <form method="POST" style="display: inline;">
                      <input type="hidden" name="marcar_lida" value="1">
                      <input type="hidden" name="notificacao_id" value="<?= $notificacao['id'] ?>">
                      <button type="submit" class="mark-read-btn">
                        <i class="fas fa-check"></i> Marcar como lida
                      </button>
                    </form>
                  <?php endif; ?>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="excluir_notificacao" value="1">
                    <input type="hidden" name="notificacao_id" value="<?= $notificacao['id'] ?>">
                    <button type="submit" class="delete-notification-btn" onclick="return confirm('Tem certeza que deseja excluir esta notificação?')">
                      <i class="fas fa-trash"></i> Excluir
                    </button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-notifications">
            <p>Você não tem notificações.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

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