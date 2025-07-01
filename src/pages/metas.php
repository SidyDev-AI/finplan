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
$totalMes = $totalEntradas - $totalSaidas;

// Consulta metas do usuário
$sql = "SELECT * FROM metas WHERE usuario_id = :usuario_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute([$id]);
$metas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Metas | FinPlan</title>
  <link rel="stylesheet" href="../css/perfil.css">
  <link rel="stylesheet" href="../css/notificacoes.css">
  <link rel="stylesheet" href="../css/metas.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
      <li><a href="metas.php" class="active"><i class="fas fa-wallet"></i> Goals</a></li>
      <li><a href="#"><i class="fas fa-chart-pie"></i> Categories</a></li>
      <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
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
        <div class="balance">General balance: R$ <?= $totalMes ?> (<span>↑ R$ 124,32</span>)</div>
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
    </div> <!-- Final do header -->
    
    <div class="content-panel">
      <div class="container-metas">
        <div class="period-selector">
          <span>Period: from</span>
          <div class="date-input">
            <input type="text" value="01.<?php echo date('m.Y'); ?>" readonly>
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
              <rect width="18" height="18" x="3" y="3" rx="2"></rect>
              <path d="M3 9h18"></path>
            </svg>
          </div>
          <span>on</span>
          <div class="date-input">
            <input type="text" value="<?php echo date('t.m.Y'); ?>" readonly>
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
              <rect width="18" height="18" x="3" y="3" rx="2"></rect>
              <path d="M3 9h18"></path>
            </svg>
          </div>
        </div>

        <section>
          <div class="title">
            <h1>Metas e Progressos</h1>
            <button id="btnNovaMeta"><i class="fa-solid fa-plus"></i>Criar Meta</button>
          </div>
          <div class="metas-popup" style="display: none;">
            <div class="cabecalho">
              <h2>Cadastrar Nova Meta</h2>
              <button id="fecharPopup" class="fechar-btn">X</button>
            </div>
            <form id="metaForm" action="../backend/processar_metas.php" method="post">
              <!-- Titulo -->
              <div class="form-group">
                <label for="titleMeta" class="form-label">Titulo da Meta</label>
                <input type="text" id="titleMeta" name="titleMeta" class="form-input" placeholder="Digite o titulo da meta" required>
              </div>
              <!-- Valor -->
              <div class="form-group">
                <label for="amount" class="form-label">Valor da Meta</label>
                <input type="text" id="amount" name="amount" class="form-input" placeholder="R$ 0,00" required>
              </div>
              <!-- Data Inicial (automática e bloqueada) -->
              <div class="form-group">
                <label class="form-label">Data Inicial</label>
                <div class="date-group">
                  <select class="date-select" name="day" id="day" disabled required></select>
                  <select class="date-select" name="month" id="month" disabled required></select>
                  <select class="date-select" name="year" id="year" disabled required></select>
                </div>
              </div>
              <!-- Data Final (usuário escolhe) -->
              <div class="form-group">
                <label class="form-label">Data Final</label>
                <div class="date-group">
                  <select class="date-select" name="day_final" id="day_final" required></select>
                  <select class="date-select" name="month_final" id="month_final" required></select>
                  <select class="date-select" name="year_final" id="year_final" required></select>
                </div>
              </div>
              <!-- Descrição -->
              <div class="form-group">
                <label for="amount" class="form-label">Descrição</label>
                <textarea name="descricaoMeta" id="descricaoMeta" class="form-text" placeholder="Descreva sua meta" required></textarea>
              </div>
              <!-- Botão de Adicionar -->
              <button type="submit" class="adicionar-button">Adicionar</button>
            </form>
          </div>
        </section>

        <section class="list-metas">
          <?php foreach ($metas as $meta): 
            $id = $meta['id'];
            $valor = (float)$meta['valor'];
            $valor_atual = (float)$meta['valor_atual'];
            $porcentagem = $valor > 0 ? min(100, ($valor_atual / $valor) * 100) : 0;

            // Cálculo do stroke-dashoffset para barra circular
            $dashArray = 157.08; // Circunferência semicircular do SVG
            $dashOffset = $dashArray - ($dashArray * $porcentagem / 100);

            $dataFinal = $meta['data_final'];
            list($ano, $mes, $dia) = explode('-', $dataFinal);
          ?>
          <div class="card">
            <div class="title-meta">
              <h3 class="card-title"><?= htmlspecialchars($meta['titulo']) ?></h3>
              <button id="openViewMeta" class="btn toggle-menu" data-id="<?= $meta['id'] ?>">
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>

            <div class="view-metas-popup" style="display: none;">
              <div class="title-view-metas">
                <h2><?= htmlspecialchars($meta['titulo']) ?></h2>
                <button id="closeViewMeta" class="fechar-btn">X</button>
              </div>
              <div class="info-view-metas">
                <div class="info-nome-meta">
                  <h3>Nome da Meta</h3>
                  <input type="text" name="nomeMeta" class="nomeMetaInput" placeholder="Nome da Meta" value="<?= htmlspecialchars($meta['titulo']) ?>" readonly>
                </div>
                <div class="info-valor-meta">
                  <h3>Valor da Meta</h3>
                  <input type="text" name="valorMeta" class="valorMetaInput" placeholder="Digite o valor da meta" value="R$<?= number_format($valor, 2, ',', '.') ?>" readonly>
                </div>
                <div class="info-valorAtual-meta">
                  <h3>Valor em caixa</h3>
                  <input type="text" name="valorAtualMeta" class="valorAtualMetaInput" placeholder="Digite o valor da meta" value="R$<?= number_format($valor_atual, 2, ',', '.') ?>" readonly>
                </div>
                <!-- Data Final (usuário escolhe) -->
                <div class="info-view-date">
                  <h3>Data Final</h3>
                  <div class="view-date">
                    <!-- Dia -->
                    <select class="info-date day view_day_final" name="view_day_final" disabled></select>
                    <!-- Mês -->
                    <select class="info-date mes view_month_final" name="view_month_final" disabled></select>
                    <!-- Ano -->
                    <select class="info-date ano view_year_final" name="view_year_final" disabled></select>
                    <input type="hidden" class="data-final-salva" value="<?= $meta['data_final'] ?>">
                  </div>
                </div>
                <div class="info-descricao-meta">
                  <h3>Descrição</h3>
                  <textarea name="descricaoViewMeta" class="descricaoViewMeta" placeholder="Descreva sua meta" readonly><?= htmlspecialchars($meta['descricao']) ?></textarea>
                </div>
                <div class="view-metas-buttons">
                  <button type="button" class="view-metas-btn editar-meta">Editar</button>
                  <button type="button" class="view-metas-btn excluir-meta" data-id="<?= $meta['id'] ?>">Excluir</button>
                </div>
              </div>
            </div>

            <div class="progress-circle">
              <svg class="progress-svg" viewBox="0 0 140 80">
                <path class="progress-bg" d="M 20 60 A 50 50 0 0 1 120 60" stroke-dasharray="<?= $dashArray ?>" stroke-dashoffset="0"/>
                <path class="progress-bar" d="M 20 60 A 50 50 0 0 1 120 60" stroke-dasharray="<?= $dashArray ?>" stroke-dashoffset="<?= $dashOffset ?>"/>
              </svg>
              <div class="progress-text">
                <span class="progress-amount">R$<?= number_format($valor_atual, 2, ',', '.') ?></span>
                <div class="progress-percentage"><?= round($porcentagem) ?>%</div>
              </div>
            </div>
            <div class="buttons">
              <button class="action-btn reserve-btn">
                <i class="fa-solid fa-money-bill-transfer"></i>Reservar
              </button>
              <button class="action-btn withdraw-btn">
                <i class="fa-solid fa-money-bill-transfer"></i>Retirar
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </section>
      </div> <!-- Final do Metas -->
    </div>

  </div> <!-- Final do main -->

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
    <?php if (!empty($mensagem)): ?>
    alert('<?= $mensagem ?>');
    <?php endif; ?>
  </script>
  <script src="../js/notificacoes.js"></script>
  <script src="../js/metas.js"></script>
</body>
</html>