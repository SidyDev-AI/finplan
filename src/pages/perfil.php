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

// Atualização do perfil
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['delete'])) {
    // Exclusão de conta
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    session_destroy();
    header("Location: ../../index.php");
    exit();
  } else {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = !empty($_POST['senha']) && $_POST['senha'] !== '********' ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : null;
    $cpf = $_POST['cpf'];

    if ($senha) {
      $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ?, cpf = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->execute([$nome, $email, $senha, $cpf, $id]);
    } else {
      $sql = "UPDATE usuarios SET nome = ?, email = ?, cpf = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->execute([$nome, $email, $cpf, $id]);
    }

    $mensagem = "Perfil atualizado com sucesso!";
  }
}

// Buscar dados do usuário
$sql = "SELECT nome, email, cpf FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não existir tipo_perfil, definir um valor padrão
if (!isset($usuario['tipo_perfil'])) {
  $usuario['tipo_perfil'] = 'Investor profile';
}

// Extrair o primeiro nome para exibição no cabeçalho
$primeiro_nome = explode(' ', $usuario['nome'])[0];

// Buscar saldo total
$sql_saldo = "SELECT SUM(valor) as saldo FROM transacoes WHERE usuario_id = ?";
$stmt_saldo = $conn->prepare($sql_saldo);
$stmt_saldo->execute([$id]);
$saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC)['saldo'] ?? 0;
$saldo_formatado = number_format($saldo, 2, ',', '.');

// Data atual
$data_atual = "31-03-25 20:00";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil do Usuário - FinPlan</title>
  <link rel="stylesheet" href="../css/perfil.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <div class="logo-circle">R$</div>
      <div class="logo-text">
        <span class="fin">Fin</span><span class="plan">Plan</span>
      </div>
    </div>

    <ul class="menu">
      <li><a href="#"><i class="fas fa-th-large"></i> Dashboard</a></li>
      <li><a href="#"><i class="fas fa-wallet"></i> Budget</a></li>
      <li><a href="#"><i class="fas fa-chart-pie"></i> Categories</a></li>
      <li><a href="#"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
      <li><a href="#"><i class="fas fa-chart-bar"></i> Analytics</a></li>
      <li><a href="#"><i class="fas fa-credit-card"></i> Accounts</a></li>
      <li><a href="#" class="active"><i class="fas fa-cog"></i> Settings</a></li>
      <li><a href="#"><i class="fas fa-question-circle"></i> Help</a></li>
      <li><a href="#"><i class="fas fa-sign-out-alt"></i> Log out</a></li>
    </ul>

    <div class="theme-toggle">
      <div class="toggle-track">
        <div class="toggle-thumb"></div>
      </div>
      <i class="fas fa-sun sun"></i>
      <i class="fas fa-moon moon"></i>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <div class="header-left">
        <div class="date">Today: <?= $data_atual ?></div>
        <div class="balance">General balance: R$ 15.543,00 (<span>↑ R$ 124,32</span>)</div>
      </div>
      <div class="header-right">
        <div class="notification">
          <i class="fas fa-bell"></i>
        </div>
        <div class="user-profile">
          <div class="user-avatar"></div>
          <div class="user-name"><?= htmlspecialchars($primeiro_nome) ?></div>
        </div>
        <div class="menu-dots">
          <i class="fas fa-ellipsis-v"></i>
        </div>
      </div>
    </div>

    <!-- Content Panel -->
    <div class="content-panel">
      <!-- Tabs -->
      <div class="tabs">
        <button class="tab active">Dados da conta</button>
        <button class="tab">Cartões</button>
      </div>

      <form method="POST" id="profileForm">
        <!-- Profile Content -->
        <div class="profile-content">
          <div class="profile-header">
            <div class="profile-avatar"></div>
            <div class="profile-info">
              <div class="profile-name"><?= htmlspecialchars($usuario['nome']) ?></div>
              <div class="profile-type">
                <?= htmlspecialchars($usuario['tipo_perfil']) ?> <i class="fas fa-arrow-right"></i>
              </div>
              
              <!-- Botão Editar (visível inicialmente) -->
              <button type="button" class="edit-button" id="editButton">
                <i class="fas fa-pen-square"></i> Editar
              </button>
              
              <!-- Botões Salvar e Cancelar (ocultos inicialmente) -->
              <div class="action-buttons" id="actionButtons">
                <button type="button" class="save-button" id="saveButton">
                  <i class="fas fa-save"></i> Salvar
                </button>
                <button type="button" class="cancel-button" id="cancelButton">
                  <i class="fas fa-times"></i> Cancelar
                </button>
              </div>
            </div>
          </div>

          <!-- Form Fields -->
          <div class="form-fields">
            <div class="form-field">
              <i class="fas fa-user"></i>
              <input type="text" name="nome" placeholder="Full name" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" readonly id="nomeInput">
            </div>
            
            <div class="form-field">
              <i class="fas fa-envelope"></i>
              <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" readonly id="emailInput">
            </div>
            
            <div class="form-field">
              <i class="fas fa-lock"></i>
              <input type="password" name="senha" placeholder="Password" value="********" readonly id="senhaInput">
              <i class="fas fa-eye eye-icon" id="togglePassword"></i>
            </div>
            
            <div class="form-field">
              <i class="fas fa-credit-card"></i>
              <input type="text" name="cpf" placeholder="CPF" value="<?= htmlspecialchars($usuario['cpf'] ?? '') ?>" readonly id="cpfInput">
            </div>

            <!-- Delete Button -->
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

  <script>
    // Armazenar valores originais dos campos
    const originalValues = {
      nome: '<?= htmlspecialchars($usuario['nome'] ?? '') ?>',
      email: '<?= htmlspecialchars($usuario['email'] ?? '') ?>',
      senha: '********',
      cpf: '<?= htmlspecialchars($usuario['cpf'] ?? '') ?>'
    };

    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      const senhaInput = document.getElementById('senhaInput');
      if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        this.classList.remove('fa-eye');
        this.classList.add('fa-eye-slash');
      } else {
        senhaInput.type = 'password';
        this.classList.remove('fa-eye-slash');
        this.classList.add('fa-eye');
      }
    });

    // Elementos DOM
    const editButton = document.getElementById('editButton');
    const actionButtons = document.getElementById('actionButtons');
    const saveButton = document.getElementById('saveButton');
    const cancelButton = document.getElementById('cancelButton');
    const inputs = ['nomeInput', 'emailInput', 'senhaInput', 'cpfInput'];
    const form = document.getElementById('profileForm');

    // Função para entrar no modo de edição
    function enableEditMode() {
      // Ocultar botão editar e mostrar botões de ação
      editButton.style.display = 'none';
      actionButtons.style.display = 'flex';
      
      // Habilitar campos para edição
      inputs.forEach(id => {
        document.getElementById(id).readOnly = false;
      });
      
      // Focar no primeiro campo
      document.getElementById('nomeInput').focus();
    }

    // Função para sair do modo de edição
    function disableEditMode() {
      // Mostrar botão editar e ocultar botões de ação
      editButton.style.display = 'flex';
      actionButtons.style.display = 'none';
      
      // Desabilitar campos para edição
      inputs.forEach(id => {
        document.getElementById(id).readOnly = true;
      });
    }

    // Função para restaurar valores originais
    function restoreOriginalValues() {
      document.getElementById('nomeInput').value = originalValues.nome;
      document.getElementById('emailInput').value = originalValues.email;
      document.getElementById('senhaInput').value = originalValues.senha;
      document.getElementById('cpfInput').value = originalValues.cpf;
    }

    // Event listeners
    editButton.addEventListener('click', enableEditMode);
    
    saveButton.addEventListener('click', function() {
      // Submeter o formulário
      form.submit();
    });
    
    cancelButton.addEventListener('click', function() {
      // Restaurar valores originais e sair do modo de edição
      restoreOriginalValues();
      disableEditMode();
    });

    // Tab switching
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
      tab.addEventListener('click', function() {
        tabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
      });
    });

    // Theme toggle
    document.querySelector('.toggle-track').addEventListener('click', function() {
      const thumb = document.querySelector('.toggle-thumb');
      if (thumb.style.right === '3px') {
        thumb.style.right = '33px';
        document.body.classList.add('light-theme');
      } else {
        thumb.style.right = '3px';
        document.body.classList.remove('light-theme');
      }
    });

    <?php if (!empty($mensagem)): ?>
    // Exibir mensagem de sucesso
    alert('<?= $mensagem ?>');
    <?php endif; ?>
  </script>
</body>
</html>