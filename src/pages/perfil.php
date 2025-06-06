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

// Atualização de dados
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['marcar_lida']) && !isset($_POST['excluir_notificacao'])) {
  if (isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    session_destroy();
    header("Location: ../../index.php");
    exit();
  } else {
    $nome  = $_POST['nome'];
    $email = $_POST['email'];
    $senha = (!empty($_POST['senha']) && $_POST['senha'] !== '********') ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : null;
    $cpf = !empty($_POST['cpf']) ? $_POST['cpf'] : null;


    if ($senha) {
      $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ?, cpf = ? WHERE id = ?");
      $stmt->execute([$nome, $email, $senha, $cpf, $id]);
    } else {
      $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, cpf = ? WHERE id = ?");
      $stmt->execute([$nome, $email, $cpf, $id]);
    }

    $mensagem = "Perfil atualizado com sucesso!";
  }
}

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT nome, email, cpf FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  // Usuário não encontrado
  $_SESSION = [];
  session_destroy();
  header("Location: ../../index.php");
  exit();
}

// Definir tipo de perfil padrão
$usuario['tipo_perfil'] = $usuario['tipo_perfil'] ?? 'Investor profile';
$primeiro_nome = !empty($usuario['nome']) ? explode(' ', $usuario['nome'])[0] : 'Usuário';

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
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil do Usuário - FinPlan</title>
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
      <li><a href="metas.php"><i class="fas fa-wallet"></i> Goals</a></li>
      <li><a href="#"><i class="fas fa-chart-pie"></i> Categories</a></li>
      <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
      <li><a href="#"><i class="fas fa-chart-bar"></i> Analytics</a></li>
      <li><a href="#"><i class="fas fa-credit-card"></i> Accounts</a></li>
      <li><a href="#" class="active"><i class="fas fa-cog"></i> Settings</a></li>
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

    <div class="content-panel">
      <div class="tabs">
        <button class="tab active">Dados da conta</button>
        <button class="tab">Cartões</button>
      </div>

      <form method="POST" id="profileForm">
        <div class="profile-content">
          <div class="profile-header">
            <div class="profile-avatar"></div>
            <div class="profile-info">
              <div class="profile-name"><?= htmlspecialchars($usuario['nome']) ?></div>
              <div class="profile-type"><?= htmlspecialchars($usuario['tipo_perfil']) ?> <i class="fas fa-arrow-right"></i></div>

              <button type="button" class="edit-button" id="editButton"><i class="fas fa-pen-square"></i> Editar</button>

              <div class="action-buttons" id="actionButtons">
                <button type="button" class="save-button" id="saveButton"><i class="fas fa-save"></i> Salvar</button>
                <button type="button" class="cancel-button" id="cancelButton"><i class="fas fa-times"></i> Cancelar</button>
              </div>
            </div>
          </div>

          <div class="form-fields">
            <div class="form-field">
              <i class="fas fa-user"></i>
              <input type="text" name="nome" id="nomeInput" placeholder="Full name" value="<?= htmlspecialchars($usuario['nome']) ?>" readonly>
            </div>
            <div class="form-field">
              <i class="fas fa-envelope"></i>
              <input type="email" name="email" id="emailInput" placeholder="Email" value="<?= htmlspecialchars($usuario['email']) ?>" readonly>
            </div>
            <div class="form-field">
              <i class="fas fa-lock"></i>
              <input type="password" name="senha" id="senhaInput" placeholder="Password" value="********" readonly>
              <i class="fas fa-eye eye-icon" id="togglePassword"></i>
            </div>
            <div class="form-field">
              <i class="fas fa-credit-card"></i>
              <?php $cpf_formatado = isset($usuario['cpf']) && !empty($usuario['cpf']) ? htmlspecialchars($usuario['cpf']) : ''; ?>
              <input type="text" name="cpf" id="cpfInput" placeholder="CPF" value="<?= $cpf_formatado ?>" readonly>
            </div>

            <div class="delete-button-container">
              <button type="submit" name="delete" class="delete-button" onclick="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.')">
                <i class="fas fa-trash"></i> Excluir
              </button>
            </div>
          </div>
        </div>
      </form>
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
    const originalValues = {
      nome: '<?= htmlspecialchars($usuario['nome']) ?>',
      email: '<?= htmlspecialchars($usuario['email']) ?>',
      senha: '********',
      cpf: '<?= isset($usuario['cpf']) && !empty($usuario['cpf']) ? htmlspecialchars($usuario['cpf']) : '' ?>'
    };

    document.getElementById('togglePassword').addEventListener('click', function() {
      const senhaInput = document.getElementById('senhaInput');
      senhaInput.type = senhaInput.type === 'password' ? 'text' : 'password';
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });

    const editButton = document.getElementById('editButton');
    const actionButtons = document.getElementById('actionButtons');
    const saveButton = document.getElementById('saveButton');
    const cancelButton = document.getElementById('cancelButton');
    const inputs = ['nomeInput', 'emailInput', 'senhaInput', 'cpfInput'];

    editButton.addEventListener('click', () => {
      editButton.style.display = 'none';
      actionButtons.style.display = 'flex';
      inputs.forEach(id => document.getElementById(id).readOnly = false);
      document.getElementById('nomeInput').focus();
    });

    cancelButton.addEventListener('click', () => {
      document.getElementById('nomeInput').value = originalValues.nome;
      document.getElementById('emailInput').value = originalValues.email;
      document.getElementById('senhaInput').value = originalValues.senha;
      document.getElementById('cpfInput').value = originalValues.cpf;
      editButton.style.display = 'flex';
      actionButtons.style.display = 'none';
      inputs.forEach(id => document.getElementById(id).readOnly = true);
    });

    saveButton.addEventListener('click', () => document.getElementById('profileForm').submit());

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
  </script>
</body>
</html>