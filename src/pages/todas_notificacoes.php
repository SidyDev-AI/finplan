<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$usuario_id = $_SESSION['usuario_id'];
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
      <div class="logo-circle">R$</div>
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
        <div class="date">Today: <span id="dataAtual"></span></div>
        <div class="balance">General balance: <span id="saldoGeral"></span></div>
      </div>
      <div class="header-right">
        <div class="notification"><i class="fas fa-bell"></i><div class="notification-badge" id="badgeQtd"></div></div>
        <div class="user-profile"><div class="user-avatar"></div><div class="user-name">Você</div></div>
        <div class="menu-dots"><i class="fas fa-ellipsis-v"></i></div>
      </div>
    </div>

    <div class="notifications-page">
      <div class="notifications-container">
        <div class="notifications-header">
          <h2>Todas as Notificações</h2>
          <button id="btnMarcarTodas" class="mark-read-btn"><i class="fas fa-check-double"></i> Marcar todas como lidas</button>
        </div>
        <div id="listaNotificacoes"></div>
      </div>
    </div>
  </div>

  <script>
    const lista = document.getElementById('listaNotificacoes');
    const badgeQtd = document.getElementById('badgeQtd');
    const btnMarcarTodas = document.getElementById('btnMarcarTodas');
    const dataAtual = document.getElementById('dataAtual');
    const saldoGeral = document.getElementById('saldoGeral');

    function formatarDataAgora() {
      const agora = new Date();
      return agora.toLocaleString('pt-BR');
    }

    async function carregarNotificacoes() {
      const res = await fetch('../backend/api/notificacoes/buscar.php');
      const data = await res.json();

      lista.innerHTML = '';
      badgeQtd.textContent = data.total_nao_lidas || '';
      saldoGeral.textContent = `R$ ${data.saldo_formatado}`;
      dataAtual.textContent = formatarDataAgora();

      if (data.notificacoes.length === 0) {
        lista.innerHTML = '<p>Você não tem notificações.</p>';
        return;
      }

      data.notificacoes.forEach(notif => {
        const div = document.createElement('div');
        div.className = `notification-card ${notif.lida ? '' : 'unread'}`;
        div.innerHTML = `
          <div class="notification-avatar"></div>
          <div class="notification-content">
            <div>${notif.mensagem}</div>
            <div class="notification-date">${notif.data}</div>
            <div class="notification-actions">
              ${!notif.lida ? `<button onclick="marcarComoLida(${notif.id})" class="mark-read-btn"><i class="fas fa-check"></i> Marcar como lida</button>` : ''}
              <button onclick="excluir(${notif.id})" class="delete-notification-btn"><i class="fas fa-trash"></i> Excluir</button>
            </div>
          </div>
        `;
        lista.appendChild(div);
      });
    }

    async function marcarComoLida(id) {
      await fetch('../backend/api/notificacoes/marcar_como_lida.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      carregarNotificacoes();
    }

    async function excluir(id) {
      const confirma = confirm('Tem certeza que deseja excluir esta notificação?');
      if (!confirma) return;
      await fetch('../backend/api/notificacoes/excluir.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      carregarNotificacoes();
    }

    btnMarcarTodas.addEventListener('click', async () => {
      await fetch('../backend/api/notificacoes/marcar_todas.php', {
        method: 'POST'
      });
      carregarNotificacoes();
    });

    carregarNotificacoes();
  </script>
</body>
</html>
