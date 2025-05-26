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
}

if (isset($_POST['excluir_notificacao'])) {
  $notificacao_id = $_POST['notificacao_id'];
  $sql = "DELETE FROM notificacoes WHERE id = ? AND usuario_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$notificacao_id, $id]);
}

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT nome, email, cpf FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Definir tipo de perfil padrão
$usuario['tipo_perfil'] = $usuario['tipo_perfil'] ?? 'Investor profile';
$primeiro_nome = explode(' ', $usuario['nome'])[0];

// Buscar saldo
$stmt = $conn->prepare("SELECT SUM(valor) as saldo FROM transacoes WHERE usuario_id = ?");
$stmt->execute([$id]);
$saldo = $stmt->fetch(PDO::FETCH_ASSOC)['saldo'] ?? 0;
$saldo_formatado = number_format($saldo, 2, ',', '.');

// Buscar notificações não lidas
$sql_notificacoes = "SELECT * FROM notificacoes WHERE usuario_id = ? AND lida = 0 ORDER BY data_criacao DESC LIMIT 3";
$stmt_notificacoes = $conn->prepare($sql_notificacoes);
$stmt_notificacoes->execute([$id]);
$notificacoes = $stmt_notificacoes->fetchAll(PDO::FETCH_ASSOC);

// Contar total de notificações não lidas
$sql_count = "SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = ? AND lida = 0";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute([$id]);
$total_nao_lidas = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

$data_atual = date("d-m-y H:i");

// Consulta todas as transações
$result = $conn->query("SELECT * FROM transacoes ORDER BY data DESC");

// Total de entradas
$stmt = $conn->prepare("SELECT SUM(valor) as total_entradas FROM transacoes WHERE usuario_id = ? AND tipo = 'Entrada'");
$stmt->execute([$id]);
$totalEntradas = $stmt->fetch(PDO::FETCH_ASSOC)['total_entradas'] ?? 0;

// Total de saídas
$stmt = $conn->prepare("SELECT SUM(valor) as total_saidas FROM transacoes WHERE usuario_id = ? AND tipo = 'Saida'");
$stmt->execute([$id]);
$totalSaidas = $stmt->fetch(PDO::FETCH_ASSOC)['total_saidas'] ?? 0;

// Cálculo do total do mês
$totalMes = $totalEntradas + $totalSaidas;

// Formatação dos valores para moeda brasileira
$entradasFormatado = number_format($totalEntradas, 2, ',', '.');
$saidasFormatado = number_format($totalSaidas, 2, ',', '.');
$totalMesFormatado = number_format($totalMes, 2, ',', '.');


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transações | FinPlan</title>
  <link rel="stylesheet" href="../css/perfil.css">
  <link rel="stylesheet" href="../css/notificacoes.css">
  <link rel="stylesheet" href="../css/transactions.css">
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
      <li><a href="transactions.php" class="active"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
      <li><a href="#"><i class="fas fa-chart-bar"></i> Analytics</a></li>
      <li><a href="#"><i class="fas fa-credit-card"></i> Accounts</a></li>
      <li><a href="perfil.php"><i class="fas fa-cog"></i> Settings</a></li>
      <li><a href="#"><i class="fas fa-question-circle"></i> Help</a></li>
      <li><a href="#"><i class="fas fa-sign-out-alt"></i> Log out</a></li>
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
        <div class="notification" id="notificationIcon">
          <i class="fas fa-bell"></i>
          <?php if ($total_nao_lidas > 0): ?>
            <div class="notification-badge"><?= $total_nao_lidas > 9 ? '9+' : $total_nao_lidas ?></div>
          <?php endif; ?>
          
          <!-- Painel de Notificações -->
          <div class="notification-panel" id="notificationPanel">
            <div class="notification-header">
              <div class="notification-title">
                Notificações <i class="fas fa-check-circle notification-check"></i>
              </div>
            </div>
            <div class="notification-list">
              <?php if (count($notificacoes) > 0): ?>
                <?php foreach ($notificacoes as $notificacao): ?>
                  <div class="notification-item unread">
                    <div class="notification-avatar"></div>
                    <div class="notification-content">
                      <?= htmlspecialchars($notificacao['mensagem']) ?>
                    </div>
                    <div class="notification-close" data-id="<?= $notificacao['id'] ?>">
                      <i class="fas fa-times"></i>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="notification-item">
                  <div class="notification-content">
                    Você não tem novas notificações.
                  </div>
                </div>
              <?php endif; ?>
            </div>
            <div class="notification-footer">
              <a href="todas_notificacoes.php" class="show-all-btn">
                Mostrar todas <i class="fas fa-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="user-profile">
          <div class="user-avatar"></div>
          <div class="user-name"><?= htmlspecialchars($primeiro_nome) ?></div>
        </div>
        <div class="menu-dots"><i class="fas fa-ellipsis-v"></i></div>
      </div>
    </div>

    <div class="panel">
      <div class="resumoSaldo">
        <h1>Resumo do mês</h1>
        <div class="cards">
          <div class="card entrada">
            <p>Total de entradas</p>
            <h2>R$ <?= $entradasFormatado ?></h2>
          </div>
          <div class="card saida">
            <p>Total de saídas</p>
            <h2>R$ <?= $saidasFormatado ?></h2>
          </div>
          <div class="card total">
            <p>Total do mês</p>
            <h2>R$ <?= $totalMesFormatado ?></h2>
          </div>
        </div>
      </div>

      <div>
        <div class="title">
          <h1>Últimas Transações</h1>
          <button><i class="fa-solid fa-plus"></i>Nova Transação</button>
        </div>

        <div class="transacoes">
          <div class="content-transactions-wrapper">
            <div class="content-transactions">
              <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)):
                $id = $row['id'];
                $data = date('d/m', strtotime($row['data']));
                $categoria = htmlspecialchars($row['categoria']);
                $tipo = htmlspecialchars($row['tipo']);
                $valor = number_format($row['valor'], 2, ',', '.');
                $sinal = $tipo === 'Entrada' ? '+' : '-';
                $classe = $tipo === 'Entrada' ? 'entrada' : 'saida';
              ?>
                <div class="list-transactions" data-id="<?= $id ?>">
                  <span><i class="fa-solid fa-circle-dollar-to-slot"></i></span>
                  <p><?= $data ?></p>
                  <p><?= $categoria ?></p>
                  <p><?= $tipo ?></p>
                  <p class="<?= $classe ?>"><?= $sinal ?>R$ <?= $valor ?></p>
                  <button class="btn toggle-menu" data-id="<?= $id ?>">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                  </button>
                  <div class="menu-transacao" id="menu-<?= $id ?>" style="display: none;">
                    <ul>
                      <li><button id="opt" onclick="editarTransacao(<?= $id ?>)">Editar transação</button></li>
                      <li><button id="opt" onclick="excluirTransacao(<?= $id ?>)">Excluir transação</button></li>
                      <li><button id="opt" onclick="visualizarTransacao(<?= $id ?>)">Visualizar transação</button></li>
                      <li><button id="opt" onclick="fecharMenu(<?= $id ?>)">Fechar</button></li>
                    </ul>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
            <button class="btn-extrato">Visualizar extrato <i class="fa-solid fa-chevron-right"></i></button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Form para marcar notificação como lida -->
  <form id="markReadForm" method="POST" style="display: none;">
    <input type="hidden" name="marcar_lida" value="1">
    <input type="hidden" name="notificacao_id" id="markReadId">
  </form>

  <!-- Form para excluir notificação -->
  <form id="deleteNotificationForm" method="POST" style="display: none;">
    <input type="hidden" name="excluir_notificacao" value="1">
    <input type="hidden" name="notificacao_id" id="deleteNotificationId">
  </form>

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

    // Notificações
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationPanel = document.getElementById('notificationPanel');
    
    // Abrir/fechar painel de notificações
    notificationIcon.addEventListener('click', function(e) {
      e.stopPropagation();
      notificationPanel.style.display = notificationPanel.style.display === 'block' ? 'none' : 'block';
    });
    
    // Fechar painel ao clicar fora
    document.addEventListener('click', function(e) {
      if (!notificationPanel.contains(e.target) && e.target !== notificationIcon) {
        notificationPanel.style.display = 'none';
      }
    });
    
    // Fechar notificação individual
    const closeButtons = document.querySelectorAll('.notification-close');
    closeButtons.forEach(button => {
      button.addEventListener('click', function() {
        const notificationId = this.getAttribute('data-id');
        document.getElementById('deleteNotificationId').value = notificationId;
        document.getElementById('deleteNotificationForm').submit();
      });
    });

    <?php if (!empty($mensagem)): ?>
    alert('<?= $mensagem ?>');
    <?php endif; ?>

    document.querySelectorAll('.toggle-menu').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        document.querySelectorAll('.menu-transacao').forEach(menu => {
          if (menu.id !== `menu-${id}`) {
            menu.style.display = 'none';
          }
        });
        const menu = document.getElementById(`menu-${id}`);
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
      });
    });

    function fecharMenu(id) {
      const menu = document.getElementById(`menu-${id}`);
      menu.style.display = 'none';
    }

    function editarTransacao(id) {
      // redirecionar ou abrir modal
      window.location.href = `editar.php?id=${id}`;
    }

    function excluirTransacao(id) {
      if (confirm('Deseja excluir esta transação?')) {
        window.location.href = `excluir.php?id=${id}`;
      }
    }

    function visualizarTransacao(id) {
      // Abrir modal ou nova página
      window.location.href = `visualizar.php?id=${id}`;
    }
  </script>
</body>
</html>