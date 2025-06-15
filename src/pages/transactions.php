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
$usuario['tipo_perfil'] = $usuario['tipo_perfil'] ?? 'Investor profile';
$primeiro_nome = explode(' ', $usuario['nome'])[0];

// Buscar saldo
$stmt = $conn->prepare("SELECT SUM(valor) as saldo FROM transacoes WHERE usuario_id = ?");
$stmt->execute([$id]);
$saldo = $stmt->fetch(PDO::FETCH_ASSOC)['saldo'] ?? 0;
$saldo_formatado = number_format(is_numeric($saldo) ? (float)$saldo : 0.0, 2, ',', '.');

// Buscar notificações
$sql_notificacoes = "SELECT * FROM notificacoes WHERE usuario_id = ? AND lida = 0 ORDER BY data_criacao DESC LIMIT 3";
$stmt_notificacoes = $conn->prepare($sql_notificacoes);
$stmt_notificacoes->execute([$id]);
$notificacoes = $stmt_notificacoes->fetchAll(PDO::FETCH_ASSOC);

$sql_count = "SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = ? AND lida = 0";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute([$id]);
$total_nao_lidas = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

$data_atual = date("d-m-y H:i");

// Buscar apenas transações do usuário
$stmt = $conn->prepare("SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY data DESC");
$stmt->execute([$id]);
$result = $stmt;

// Totais
$stmt = $conn->prepare("SELECT SUM(valor) as total_entradas FROM transacoes WHERE usuario_id = ? AND tipo = 'Entrada'");
$stmt->execute([$id]);
$totalEntradas = $stmt->fetch(PDO::FETCH_ASSOC)['total_entradas'] ?? 0;

$stmt = $conn->prepare("SELECT SUM(valor) as total_saidas FROM transacoes WHERE usuario_id = ? AND tipo = 'Saida'");
$stmt->execute([$id]);
$totalSaidas = $stmt->fetch(PDO::FETCH_ASSOC)['total_saidas'] ?? 0;

$totalMes = $totalEntradas - $totalSaidas;

$entradasFormatado = number_format(is_numeric($totalEntradas) ? (float)$totalEntradas : 0.0, 2, ',', '.');
$saidasFormatado   = number_format(is_numeric($totalSaidas)   ? (float)$totalSaidas   : 0.0, 2, ',', '.');
$totalMesFormatado = number_format(is_numeric($totalMes)      ? (float)$totalMes      : 0.0, 2, ',', '.');

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
  <style>
    .transaction-popup {
      z-index: 1000;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: #3a5273;
      border-radius: 8px;
      padding: 25px;
      width: 450px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }
    
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
      display: none;
    }
    
    .popup-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .popup-title {
      font-size: 22px;
      font-weight: bold;
      color: white;
    }
    
    .close-popup {
      background-color: transparent;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>
</head>
<body>
<div class="overlay" id="overlay"></div>
<div class="sidebar">
  <div class="logo">
    <?php 
    $logoWidth = 50;
    $logoHeight = 50;
    $logoPath = __DIR__ . "/../img/logo.png";
    
    if (file_exists($logoPath)): 
      $logoStyle = "width: {$logoWidth}px; height: {$logoHeight}px; object-fit: cover; border-radius: 50%;";
    ?>
      <img 
        src="../img/logo.png?v=<?= time() ?>" 
        alt="FinPlan Logo" 
        class="logo-image" 
        style="<?= $logoStyle ?>"
      >
    <?php else: ?>
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
      <li><a href="transactions.php" class="active"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
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
        <div class="notification" id="notificationIcon">
          <i class="fas fa-bell"></i>
          <?php if ($total_nao_lidas > 0): ?>
            <div class="notification-badge"><?= $total_nao_lidas > 9 ? '9+' : $total_nao_lidas ?></div>
          <?php endif; ?>
          
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
          <button id="btnNovaTransacao"><i class="fa-solid fa-plus"></i>Nova Transação</button>
        </div>

        <div class="transaction-popup" style="display: none;">
          <div class="popup-header">
            <div class="popup-title">Gerenciar Transação</div>
            <button type="button" class="close-popup">&times;</button>
          </div>
          <form id="transactionForm" action="../backend/processar_transacao.php" method="post">
            <!-- Campos ocultos para ação e ID da transação -->
            <input type="hidden" name="action" id="transactionFormAction" value="insert">
            <input type="hidden" name="transacao_id" id="transactionFormId" value="">
            
            <div class="transaction-type-toggle">
                <div class="toggle-option active" id="incomeOption">Entrada</div>
                <div class="toggle-option expense" id="expenseOption">Saída</div>
                <input type="hidden" name="transaction_type" id="transactionType" value="income" required>
            </div>
            
            <div class="form-group">
                <label for="amount" class="form-label">Valor</label>
                <input type="text" id="amount" name="amount" class="form-input" placeholder="0,00" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Data</label>
                <div class="date-group">
                    <select class="date-select" name="day" id="day" required></select>
                    <select class="date-select" name="month" id="month" required></select>
                    <select class="date-select" name="year" id="year" required></select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="category" class="form-label">Categoria</label>
                <div class="dropdown">
                    <select id="category" name="category" class="dropdown-select all" required>
                        <option value="" disabled selected>Selecione uma categoria</option>
                        <option value="alimentacao">Alimentação</option>
                        <option value="divida">Dívida</option>
                        <option value="emprestimo">Empréstimo</option>
                        <option value="consorcio">Consórcio</option>
                        <option value="aluguel">Aluguel</option>
                        <option value="energia">Energia</option>
                        <option value="internet">Internet</option>
                        <option value="agua">Água</option>
                        <option value="lazer">Lazer</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Descrição</label>
                <input type="text" id="description" name="description" class="form-input" placeholder="Digite uma descrição" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Forma de Pagamento</label>
                <div class="payment-row">
                    <div class="payment-column">
                        <select class="dropdown-select all" name="payment_method" id="paymentMethod" required>
                            <option value="nubank" selected>Nubank</option>
                            <option value="bradesco">Bradesco</option>
                            <option value="caixa">Caixa Federal</option>
                            <option value="inter">Inter</option>
                            <option value="brasil">Banco do Brasil</option>
                        </select>
                    </div>
                    <div class="payment-column">
                        <select class="dropdown-select all" name="payment_type" id="paymentType" required>
                            <option value="credit" selected>Crédito</option>
                            <option value="debit">Débito</option>
                            <option value="cash">Dinheiro</option>
                            <option value="pix">PIX</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Parcelamento</label>
                <div class="installment-group">
                    <div class="installment-toggle">
                        <div class="installment-option active" id="installmentYes">Sim</div>
                        <div class="installment-option" id="installmentNo">Não</div>
                    </div>
                    <input type="hidden" name="installment" id="installmentValue" value="yes" required>
                    
                    <div id="installmentCountWrapper">
                        <!-- Correção: removido o atributo required -->
                        <select class="dropdown-select" name="installment_count" id="installmentCount">
                            <option value="2" selected>2x</option>
                            <option value="3">3x</option>
                            <option value="4">4x</option>
                            <option value="5">5x</option>
                            <option value="6">6x</option>
                            <option value="7">7x</option>
                            <option value="8">8x</option>
                            <option value="9">9x</option>
                            <option value="10">10x</option>
                            <option value="11">11x</option>
                            <option value="12">12x</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="save-button">Salvar</button>
          </form>
        </div>

        <div class="transacoes">
          <div class="content-transactions-wrapper">
            <div class="content-transactions">
              <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)):
                  $id        = $row['id'];
                  $ts        = strtotime($row['data']);
              ?>
                <div class="list-transactions"
                  data-id="<?= $id ?>"
                  data-valor="<?= htmlspecialchars($row['valor']) ?>"
                  data-dia="<?= date('d', $ts) ?>"
                  data-mes="<?= date('m', $ts) ?>"
                  data-ano="<?= date('Y', $ts) ?>"
                  data-categoria="<?= htmlspecialchars($row['categoria']) ?>"
                  data-descricao="<?= htmlspecialchars($row['descricao']) ?>"
                  data-tipo="<?= htmlspecialchars($row['tipo']) ?>"
                  data-metodo="<?= htmlspecialchars($row['metodo_pagamento']) ?>"
                  data-tipopag="<?= htmlspecialchars($row['tipo_pagamento']) ?>"
                  data-parcelamento="<?= htmlspecialchars($row['parcelamento']) ?>"
                  data-qtdparcelas="<?= htmlspecialchars($row['qtd_parcelas']) ?>"
                  >
                  
                  <span><i class="fa-solid fa-circle-dollar-to-slot"></i></span>
                  <p><?= date('d/m', $ts) ?></p>
                  <p><?= htmlspecialchars($row['categoria']) ?></p>
                  <p><?= htmlspecialchars($row['tipo']) ?></p>
                  <p class="<?= ($row['tipo']==='Entrada'?'entrada':'saida') ?>">
                    <?= ($row['tipo']==='Entrada'?'+':'-') ?>R$ 
                    <?= number_format((float)$row['valor'],2,',','.') ?>
                  </p>
                  <button class="btn toggle-menu" data-id="<?= $id ?>">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                  </button>
                  <div class="menu-transacao" id="menu-<?= $id ?>" style="display: none;">
                      <ul>
                        <li>
                          <button type="button" id="opt" onclick="editarTransacao(<?= $id ?>)">
                            Editar transação
                          </button>
                        </li>
                        <li>
                          <button type="button" id="opt" onclick="excluirTransacao(<?= $id ?>)">
                            Excluir transação
                          </button>
                        </li>
                        <li>
                          <button type="button" id="opt" onclick="visualizarTransacao(<?= $id ?>)">
                            Visualizar transação
                          </button>
                        </li>
                        <li>
                          <button type="button" id="opt" onclick="fecharMenu(<?= $id ?>)">
                            Fechar
                          </button>
                        </li>
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

  <!-- Form para excluir transação -->
<form id="deleteTransactionForm" action="../backend/processar_transacao.php" method="post" style="display: none;">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="transacao_id" id="deleteTransactionId">
</form>

  <script>
    // Configuração do tema
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
    const overlay = document.getElementById('overlay');
    
    notificationIcon.addEventListener('click', function(e) {
      e.stopPropagation();
      notificationPanel.style.display = notificationPanel.style.display === 'block' ? 'none' : 'block';
    });
    
    document.addEventListener('click', function(e) {
      if (!notificationPanel.contains(e.target) && e.target !== notificationIcon) {
        notificationPanel.style.display = 'none';
      }
    });
    
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

    // Menu de transação
    document.querySelectorAll('.toggle-menu').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
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
        console.log(`Editando transação ID: ${id}`);
        const popup = document.querySelector('.transaction-popup');
        const row = document.querySelector(`.list-transactions[data-id="${id}"]`);
        
        if (!row) {
            console.error(`Transação com ID ${id} não encontrada`);
            return;
        }
        
        // Configura o formulário para modo de atualização
        document.getElementById('transactionFormAction').value = 'update';
        document.getElementById('transactionFormId').value = id;

        // Preenche os campos
        document.getElementById('amount').value = parseFloat(row.dataset.valor).toFixed(2).replace('.', ',');

        // Preenche a data
        document.getElementById('day').value = row.dataset.dia;
        document.getElementById('month').value = row.dataset.mes;
        document.getElementById('year').value = row.dataset.ano;

        // Atualiza os dias
        updateDays();

        document.getElementById('category').value = row.dataset.categoria;
        document.getElementById('description').value = row.dataset.descricao;

        // Atualiza o tipo (Entrada/Saída)
        const isEntrada = row.dataset.tipo === 'Entrada';
        document.getElementById('transactionType').value = isEntrada ? 'income' : 'expense';
        document.getElementById('incomeOption').classList.toggle('active', isEntrada);
        document.getElementById('expenseOption').classList.toggle('active', !isEntrada);

        // Atualiza os métodos de pagamento
        document.getElementById('paymentMethod').value = row.dataset.metodo;
        document.getElementById('paymentType').value = row.dataset.tipopag;

        // Atualiza o parcelamento
        const parcelamento = row.dataset.parcelamento === 'yes';
        document.getElementById('installmentValue').value = parcelamento ? 'yes' : 'no';
        document.getElementById('installmentYes').classList.toggle('active', parcelamento);
        document.getElementById('installmentNo').classList.toggle('active', !parcelamento);
        document.getElementById('installmentCountWrapper').style.display = parcelamento ? 'block' : 'none';
        
        // Define o valor das parcelas apenas se o parcelamento estiver ativado
        if (parcelamento) {
            document.getElementById('installmentCount').value = row.dataset.qtdparcelas || 1;
        }

        // Exibe o popup
        popup.style.display = 'block';
        overlay.style.display = 'block';
        popup.scrollIntoView({ behavior: 'smooth' });
    }

    function excluirTransacao(id) {
      if (confirm('Deseja excluir esta transação?')) {
        document.getElementById('deleteTransactionId').value = id;
        document.getElementById('deleteTransactionForm').submit();
      }
    }

    function visualizarTransacao(id) {
      window.location.href = `visualizar.php?id=${id}`;
    }

    // Nova transação
    const botaoNovaTransacao = document.getElementById('btnNovaTransacao');
    const popupTransacao = document.querySelector('.transaction-popup');
    const closePopupButton = document.querySelector('.close-popup');

    botaoNovaTransacao.addEventListener('click', () => {
        // Configura para modo de inserção
        document.getElementById('transactionFormAction').value = 'insert';
        document.getElementById('transactionFormId').value = '';
        document.getElementById('transactionForm').reset();
        
        document.getElementById('transactionType').value = 'income';
        document.getElementById('incomeOption').classList.add('active');
        document.getElementById('expenseOption').classList.remove('active');
        
        document.getElementById('installmentValue').value = 'no';
        document.getElementById('installmentYes').classList.remove('active');
        document.getElementById('installmentNo').classList.add('active');
        document.getElementById('installmentCountWrapper').style.display = 'none';
        
        // Preenche com data atual
        const today = new Date();
        document.getElementById('day').value = today.getDate();
        document.getElementById('month').value = today.getMonth() + 1;
        document.getElementById('year').value = today.getFullYear();
        updateDays();
        
        popupTransacao.style.display = 'block';
        overlay.style.display = 'block';
        popupTransacao.scrollIntoView({ behavior: 'smooth' });
    });

    // Fechar popup
    closePopupButton.addEventListener('click', () => {
      popupTransacao.style.display = 'none';
      overlay.style.display = 'none';
    });

    overlay.addEventListener('click', () => {
      popupTransacao.style.display = 'none';
      overlay.style.display = 'none';
    });

    // Toggle Entrada/Saída
    const incomeOption = document.getElementById('incomeOption');
    const expenseOption = document.getElementById('expenseOption');
    const transactionType = document.getElementById('transactionType');

    incomeOption.addEventListener('click', () => {
        incomeOption.classList.add('active');
        expenseOption.classList.remove('active');
        transactionType.value = 'income';
    });

    expenseOption.addEventListener('click', () => {
        expenseOption.classList.add('active');
        incomeOption.classList.remove('active');
        transactionType.value = 'expense';
    });

    // Toggle Parcelamento
    const installmentYes = document.getElementById('installmentYes');
    const installmentNo = document.getElementById('installmentNo');
    const installmentValue = document.getElementById('installmentValue');
    const installmentCountWrapper = document.getElementById('installmentCountWrapper');

    installmentYes.addEventListener('click', () => {
        installmentYes.classList.add('active');
        installmentNo.classList.remove('active');
        installmentValue.value = 'yes';
        installmentCountWrapper.style.display = 'block';
    });

    installmentNo.addEventListener('click', () => {
        installmentNo.classList.add('active');
        installmentYes.classList.remove('active');
        installmentValue.value = 'no';
        installmentCountWrapper.style.display = 'none';
    });

    // Elementos de data
    const daySelect = document.getElementById('day');
    const monthSelect = document.getElementById('month');
    const yearSelect = document.getElementById('year');

    // Preencher datas
    function populateDateSelectors() {
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1;
        const currentDay = currentDate.getDate();

        // Limpa os selects
        yearSelect.innerHTML = '';
        monthSelect.innerHTML = '';
        daySelect.innerHTML = '';

        // Preenche anos (2020 até atual)
        for (let year = currentYear; year >= 2020; year--) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === currentYear) option.selected = true;
            yearSelect.appendChild(option);
        }

        // Preenche meses
        const meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", 
                      "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
        for (let i = 0; i < 12; i++) {
            const option = document.createElement('option');
            option.value = i+1;
            option.textContent = meses[i];
            if ((i+1) === currentMonth) option.selected = true;
            monthSelect.appendChild(option);
        }

        // Atualiza dias
        updateDays();
    }

    function updateDays() {
        const selectedYear = parseInt(yearSelect.value);
        const selectedMonth = parseInt(monthSelect.value);
        const lastDay = new Date(selectedYear, selectedMonth, 0).getDate();
        
        const currentDay = daySelect.value ? parseInt(daySelect.value) : new Date().getDate();
        const selectedDay = Math.min(currentDay, lastDay);
        
        daySelect.innerHTML = '';
        
        for (let day = 1; day <= lastDay; day++) {
            const option = document.createElement('option');
            option.value = day;
            option.textContent = day;
            if (day === selectedDay) {
                option.selected = true;
            }
            daySelect.appendChild(option);
        }
    }

    // Inicialização
    populateDateSelectors();
    monthSelect.addEventListener('change', updateDays);
    yearSelect.addEventListener('change', updateDays);

    // Configuração inicial do parcelamento
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('installmentValue').value === 'no') {
            document.getElementById('installmentCountWrapper').style.display = 'none';
        }
    });

  </script>

</body>
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

// Segurança: garante Float antes de formatar
$saldo_formatado = number_format(is_numeric($saldo) ? (float)$saldo : 0.0, 2, ',', '.');

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

// Segurança: garante Float antes de formatar
$entradasFormatado = number_format(is_numeric($totalEntradas) ? (float)$totalEntradas : 0.0, 2, ',', '.');
$saidasFormatado   = number_format(is_numeric($totalSaidas)   ? (float)$totalSaidas   : 0.0, 2, ',', '.');
$totalMesFormatado = number_format(is_numeric($totalMes)      ? (float)$totalMes      : 0.0, 2, ',', '.');

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
  <style>
    .transaction-popup {
      z-index: 1000;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: #3a5273;
      border-radius: 8px;
      padding: 25px;
      width: 450px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }
    
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
      display: none;
    }
    
    .popup-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .popup-title {
      font-size: 22px;
      font-weight: bold;
      color: white;
    }
    
    .close-popup {
      background-color: transparent;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>
</head>
<body>
<div class="overlay" id="overlay"></div>
<div class="sidebar">
  <div class="logo">
    <?php 
    $logoWidth = 50;
    $logoHeight = 50;
    $logoPath = __DIR__ . "/../img/logo.png";
    
    if (file_exists($logoPath)): 
      $logoStyle = "width: {$logoWidth}px; height: {$logoHeight}px; object-fit: cover; border-radius: 50%;";
    ?>
      <img 
        src="../img/logo.png?v=<?= time() ?>" 
        alt="FinPlan Logo" 
        class="logo-image" 
        style="<?= $logoStyle ?>"
      >
    <?php else: ?>
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
      <li><a href="transactions.php" class="active"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
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
        <div class="notification" id="notificationIcon">
          <i class="fas fa-bell"></i>
          <?php if ($total_nao_lidas > 0): ?>
            <div class="notification-badge"><?= $total_nao_lidas > 9 ? '9+' : $total_nao_lidas ?></div>
          <?php endif; ?>
          
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
          <button id="btnNovaTransacao"><i class="fa-solid fa-plus"></i>Nova Transação</button>
        </div>

        <div class="transaction-popup" style="display: none;">
          <div class="popup-header">
            <div class="popup-title">Gerenciar Transação</div>
            <button type="button" class="close-popup">&times;</button>
          </div>
          <form id="transactionForm" action="../backend/processar_transacao.php" method="post">
            <!-- Campos ocultos para ação e ID da transação -->
            <input type="hidden" name="action" id="transactionFormAction" value="insert">
            <input type="hidden" name="transacao_id" id="transactionFormId" value="">
            
            <div class="transaction-type-toggle">
                <div class="toggle-option active" id="incomeOption">Entrada</div>
                <div class="toggle-option expense" id="expenseOption">Saída</div>
                <input type="hidden" name="transaction_type" id="transactionType" value="income" required>
            </div>
            
            <div class="form-group">
                <label for="amount" class="form-label">Valor</label>
                <input type="text" id="amount" name="amount" class="form-input" placeholder="0,00" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Data</label>
                <div class="date-group">
                    <select class="date-select" name="day" id="day" required></select>
                    <select class="date-select" name="month" id="month" required></select>
                    <select class="date-select" name="year" id="year" required></select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="category" class="form-label">Categoria</label>
                <div class="dropdown">
                    <select id="category" name="category" class="dropdown-select all" required>
                        <option value="" disabled selected>Selecione uma categoria</option>
                        <option value="alimentacao">Alimentação</option>
                        <option value="divida">Dívida</option>
                        <option value="emprestimo">Empréstimo</option>
                        <option value="consorcio">Consórcio</option>
                        <option value="aluguel">Aluguel</option>
                        <option value="energia">Energia</option>
                        <option value="internet">Internet</option>
                        <option value="agua">Água</option>
                        <option value="lazer">Lazer</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Descrição</label>
                <input type="text" id="description" name="description" class="form-input" placeholder="Digite uma descrição" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Forma de Pagamento</label>
                <div class="payment-row">
                    <div class="payment-column">
                        <select class="dropdown-select all" name="payment_method" id="paymentMethod" required>
                            <option value="nubank" selected>Nubank</option>
                            <option value="bradesco">Bradesco</option>
                            <option value="caixa">Caixa Federal</option>
                            <option value="inter">Inter</option>
                            <option value="brasil">Banco do Brasil</option>
                        </select>
                    </div>
                    <div class="payment-column">
                        <select class="dropdown-select all" name="payment_type" id="paymentType" required>
                            <option value="credit" selected>Crédito</option>
                            <option value="debit">Débito</option>
                            <option value="cash">Dinheiro</option>
                            <option value="pix">PIX</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Parcelamento</label>
                <div class="installment-group">
                    <div class="installment-toggle">
                        <div class="installment-option active" id="installmentYes">Sim</div>
                        <div class="installment-option" id="installmentNo">Não</div>
                    </div>
                    <input type="hidden" name="installment" id="installmentValue" value="yes" required>
                    
                    <div id="installmentCountWrapper">
                        <!-- Correção: removido o atributo required -->
                        <select class="dropdown-select" name="installment_count" id="installmentCount">
                            <option value="2" selected>2x</option>
                            <option value="3">3x</option>
                            <option value="4">4x</option>
                            <option value="5">5x</option>
                            <option value="6">6x</option>
                            <option value="7">7x</option>
                            <option value="8">8x</option>
                            <option value="9">9x</option>
                            <option value="10">10x</option>
                            <option value="11">11x</option>
                            <option value="12">12x</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="save-button">Salvar</button>
          </form>
        </div>

        <div class="transacoes">
          <div class="content-transactions-wrapper">
            <div class="content-transactions">
              <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)):
                  $id        = $row['id'];
                  $ts        = strtotime($row['data']);
              ?>
                <div class="list-transactions"
                  data-id="<?= $id ?>"
                  data-valor="<?= htmlspecialchars($row['valor']) ?>"
                  data-dia="<?= date('d', $ts) ?>"
                  data-mes="<?= date('m', $ts) ?>"
                  data-ano="<?= date('Y', $ts) ?>"
                  data-categoria="<?= htmlspecialchars($row['categoria']) ?>"
                  data-descricao="<?= htmlspecialchars($row['descricao']) ?>"
                  data-tipo="<?= htmlspecialchars($row['tipo']) ?>"
                  data-metodo="<?= htmlspecialchars($row['metodo_pagamento']) ?>"
                  data-tipopag="<?= htmlspecialchars($row['tipo_pagamento']) ?>"
                  data-parcelamento="<?= htmlspecialchars($row['parcelamento']) ?>"
                  data-qtdparcelas="<?= htmlspecialchars($row['qtd_parcelas']) ?>"
                  >
                  
                  <span><i class="fa-solid fa-circle-dollar-to-slot"></i></span>
                  <p><?= date('d/m', $ts) ?></p>
                  <p><?= htmlspecialchars($row['categoria']) ?></p>
                  <p><?= htmlspecialchars($row['tipo']) ?></p>
                  <p class="<?= ($row['tipo']==='Entrada'?'entrada':'saida') ?>">
                    <?= ($row['tipo']==='Entrada'?'+':'-') ?>R$ 
                    <?= number_format((float)$row['valor'],2,',','.') ?>
                  </p>
                  <button class="btn toggle-menu" data-id="<?= $id ?>">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                  </button>
                  <div class="menu-transacao" id="menu-<?= $id ?>" style="display: none;">
                      <ul>
                        <li>
                          <button type="button" id="opt" onclick="editarTransacao(<?= $id ?>)">
                            Editar transação
                          </button>
                        </li>
                        <li>
                          <button type="button" id="opt" onclick="excluirTransacao(<?= $id ?>)">
                            Excluir transação
                          </button>
                        </li>
                        <li>
                          <button type="button" id="opt" onclick="visualizarTransacao(<?= $id ?>)">
                            Visualizar transação
                          </button>
                        </li>
                        <li>
                          <button type="button" id="opt" onclick="fecharMenu(<?= $id ?>)">
                            Fechar
                          </button>
                        </li>
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

  <!-- Form para excluir transação -->
<form id="deleteTransactionForm" action="../backend/processar_transacao.php" method="post" style="display: none;">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="transacao_id" id="deleteTransactionId">
</form>

  <script>
    // Configuração do tema
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
    const overlay = document.getElementById('overlay');
    
    notificationIcon.addEventListener('click', function(e) {
      e.stopPropagation();
      notificationPanel.style.display = notificationPanel.style.display === 'block' ? 'none' : 'block';
    });
    
    document.addEventListener('click', function(e) {
      if (!notificationPanel.contains(e.target) && e.target !== notificationIcon) {
        notificationPanel.style.display = 'none';
      }
    });
    
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

    // Menu de transação
    document.querySelectorAll('.toggle-menu').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
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
        console.log(`Editando transação ID: ${id}`);
        const popup = document.querySelector('.transaction-popup');
        const row = document.querySelector(`.list-transactions[data-id="${id}"]`);
        
        if (!row) {
            console.error(`Transação com ID ${id} não encontrada`);
            return;
        }
        
        // Configura o formulário para modo de atualização
        document.getElementById('transactionFormAction').value = 'update';
        document.getElementById('transactionFormId').value = id;

        // Preenche os campos
        document.getElementById('amount').value = parseFloat(row.dataset.valor).toFixed(2).replace('.', ',');

        // Preenche a data
        document.getElementById('day').value = row.dataset.dia;
        document.getElementById('month').value = row.dataset.mes;
        document.getElementById('year').value = row.dataset.ano;

        // Atualiza os dias
        updateDays();

        document.getElementById('category').value = row.dataset.categoria;
        document.getElementById('description').value = row.dataset.descricao;

        // Atualiza o tipo (Entrada/Saída)
        const isEntrada = row.dataset.tipo === 'Entrada';
        document.getElementById('transactionType').value = isEntrada ? 'income' : 'expense';
        document.getElementById('incomeOption').classList.toggle('active', isEntrada);
        document.getElementById('expenseOption').classList.toggle('active', !isEntrada);

        // Atualiza os métodos de pagamento
        document.getElementById('paymentMethod').value = row.dataset.metodo;
        document.getElementById('paymentType').value = row.dataset.tipopag;

        // Atualiza o parcelamento
        const parcelamento = row.dataset.parcelamento === 'yes';
        document.getElementById('installmentValue').value = parcelamento ? 'yes' : 'no';
        document.getElementById('installmentYes').classList.toggle('active', parcelamento);
        document.getElementById('installmentNo').classList.toggle('active', !parcelamento);
        document.getElementById('installmentCountWrapper').style.display = parcelamento ? 'block' : 'none';
        
        // Define o valor das parcelas apenas se o parcelamento estiver ativado
        if (parcelamento) {
            document.getElementById('installmentCount').value = row.dataset.qtdparcelas || 1;
        }

        // Exibe o popup
        popup.style.display = 'block';
        overlay.style.display = 'block';
        popup.scrollIntoView({ behavior: 'smooth' });
    }

    function excluirTransacao(id) {
      if (confirm('Deseja excluir esta transação?')) {
        document.getElementById('deleteTransactionId').value = id;
        document.getElementById('deleteTransactionForm').submit();
      }
    }

    function visualizarTransacao(id) {
      window.location.href = `visualizar.php?id=${id}`;
    }

    // Nova transação
    const botaoNovaTransacao = document.getElementById('btnNovaTransacao');
    const popupTransacao = document.querySelector('.transaction-popup');
    const closePopupButton = document.querySelector('.close-popup');

    botaoNovaTransacao.addEventListener('click', () => {
        // Configura para modo de inserção
        document.getElementById('transactionFormAction').value = 'insert';
        document.getElementById('transactionFormId').value = '';
        document.getElementById('transactionForm').reset();
        
        document.getElementById('transactionType').value = 'income';
        document.getElementById('incomeOption').classList.add('active');
        document.getElementById('expenseOption').classList.remove('active');
        
        document.getElementById('installmentValue').value = 'no';
        document.getElementById('installmentYes').classList.remove('active');
        document.getElementById('installmentNo').classList.add('active');
        document.getElementById('installmentCountWrapper').style.display = 'none';
        
        // Preenche com data atual
        const today = new Date();
        document.getElementById('day').value = today.getDate();
        document.getElementById('month').value = today.getMonth() + 1;
        document.getElementById('year').value = today.getFullYear();
        updateDays();
        
        popupTransacao.style.display = 'block';
        overlay.style.display = 'block';
        popupTransacao.scrollIntoView({ behavior: 'smooth' });
    });

    // Fechar popup
    closePopupButton.addEventListener('click', () => {
      popupTransacao.style.display = 'none';
      overlay.style.display = 'none';
    });

    overlay.addEventListener('click', () => {
      popupTransacao.style.display = 'none';
      overlay.style.display = 'none';
    });

    // Toggle Entrada/Saída
    const incomeOption = document.getElementById('incomeOption');
    const expenseOption = document.getElementById('expenseOption');
    const transactionType = document.getElementById('transactionType');

    incomeOption.addEventListener('click', () => {
        incomeOption.classList.add('active');
        expenseOption.classList.remove('active');
        transactionType.value = 'income';
    });

    expenseOption.addEventListener('click', () => {
        expenseOption.classList.add('active');
        incomeOption.classList.remove('active');
        transactionType.value = 'expense';
    });

    // Toggle Parcelamento
    const installmentYes = document.getElementById('installmentYes');
    const installmentNo = document.getElementById('installmentNo');
    const installmentValue = document.getElementById('installmentValue');
    const installmentCountWrapper = document.getElementById('installmentCountWrapper');

    installmentYes.addEventListener('click', () => {
        installmentYes.classList.add('active');
        installmentNo.classList.remove('active');
        installmentValue.value = 'yes';
        installmentCountWrapper.style.display = 'block';
    });

    installmentNo.addEventListener('click', () => {
        installmentNo.classList.add('active');
        installmentYes.classList.remove('active');
        installmentValue.value = 'no';
        installmentCountWrapper.style.display = 'none';
    });

    // Elementos de data
    const daySelect = document.getElementById('day');
    const monthSelect = document.getElementById('month');
    const yearSelect = document.getElementById('year');

    // Preencher datas
    function populateDateSelectors() {
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1;
        const currentDay = currentDate.getDate();

        // Limpa os selects
        yearSelect.innerHTML = '';
        monthSelect.innerHTML = '';
        daySelect.innerHTML = '';

        // Preenche anos (2020 até atual)
        for (let year = currentYear; year >= 2020; year--) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === currentYear) option.selected = true;
            yearSelect.appendChild(option);
        }

        // Preenche meses
        const meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", 
                      "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
        for (let i = 0; i < 12; i++) {
            const option = document.createElement('option');
            option.value = i+1;
            option.textContent = meses[i];
            if ((i+1) === currentMonth) option.selected = true;
            monthSelect.appendChild(option);
        }

        // Atualiza dias
        updateDays();
    }

    function updateDays() {
        const selectedYear = parseInt(yearSelect.value);
        const selectedMonth = parseInt(monthSelect.value);
        const lastDay = new Date(selectedYear, selectedMonth, 0).getDate();
        
        const currentDay = daySelect.value ? parseInt(daySelect.value) : new Date().getDate();
        const selectedDay = Math.min(currentDay, lastDay);
        
        daySelect.innerHTML = '';
        
        for (let day = 1; day <= lastDay; day++) {
            const option = document.createElement('option');
            option.value = day;
            option.textContent = day;
            if (day === selectedDay) {
                option.selected = true;
            }
            daySelect.appendChild(option);
        }
    }

    // Inicialização
    populateDateSelectors();
    monthSelect.addEventListener('change', updateDays);
    yearSelect.addEventListener('change', updateDays);

    // Configuração inicial do parcelamento
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('installmentValue').value === 'no') {
            document.getElementById('installmentCountWrapper').style.display = 'none';
        }
    });

  </script>

</body>
</html>