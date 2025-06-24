<?php
session_start();
$conn = require_once __DIR__ . '/../../Database/conn.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] !== 'usuario') {
  header('Location: ../../index.php');
  exit();
}

$id = $_SESSION['usuario_id'];

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
$stmt = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$primeiro_nome = $usuario && isset($usuario['nome']) ? explode(' ', $usuario['nome'])[0] : 'Usuário';

// ✅ Usar função centralizada do conn.php
$resumoFinanceiro = calcularResumoFinanceiro($conn, $id);

// Garantir que os índices existam
$saldo_total    = $resumoFinanceiro['saldo_total'] ?? 0;
$entradas_mes   = $resumoFinanceiro['entradas_mes'] ?? 0;
$saidas_mes     = $resumoFinanceiro['saidas_mes'] ?? 0;
$saldo_mes      = $resumoFinanceiro['saldo_mes'] ?? 0;
$saldo_mes_anterior = $resumoFinanceiro['saldo_mes_anterior'] ?? 0;

$saldo_formatado = number_format($saldo_total, 2, ',', '.');
$variacao = $saldo_mes - $saldo_mes_anterior;
$variacao_positiva = $variacao >= 0;

// Gráfico de rosca (percentuais fixos por enquanto)
$total_absoluto = abs($entradas_mes) + abs($saidas_mes);
$perc_balance = 100;
$perc_investment = 0;
$perc_goals = 0;

// Gráfico comparativo últimos 3 meses
$mes_atual = date('Y-m');
$stmt = $conn->prepare("
    SELECT 
        strftime('%Y-%m', data) as mes,
        CASE strftime('%m', data)
            WHEN '01' THEN 'JAN'
            WHEN '02' THEN 'FEV'
            WHEN '03' THEN 'MAR'
            WHEN '04' THEN 'ABR'
            WHEN '05' THEN 'MAI'
            WHEN '06' THEN 'JUN'
            WHEN '07' THEN 'JUL'
            WHEN '08' THEN 'AGO'
            WHEN '09' THEN 'SET'
            WHEN '10' THEN 'OUT'
            WHEN '11' THEN 'NOV'
            WHEN '12' THEN 'DEZ'
        END as nome_mes,
        SUM(CASE WHEN tipo = 'Entrada' THEN valor ELSE 0 END) AS entradas,
        SUM(CASE WHEN tipo = 'Saida' THEN valor ELSE 0 END) AS saidas
    FROM transacoes 
    WHERE usuario_id = ? 
    AND data >= date('now', '-3 months')
    GROUP BY strftime('%Y-%m', data)
    ORDER BY mes ASC
    LIMIT 3
");
$stmt->execute([$id]);
$dados_grafico = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar dados para JS
$meses_grafico = [];
$entradas_grafico = [];
$saidas_grafico = [];

foreach ($dados_grafico as $dado) {
    $meses_grafico[] = $dado['nome_mes'];
    $entradas_grafico[] = floatval($dado['entradas']);
    $saidas_grafico[] = floatval($dado['saidas']);
}

while (count($meses_grafico) < 3) {
    array_unshift($meses_grafico, 'MÊS');
    array_unshift($entradas_grafico, 0);
    array_unshift($saidas_grafico, 0);
}

// Buscar categorias de gastos do mês atual
$stmt = $conn->prepare("
    SELECT 
        categoria,
        SUM(valor) as total,
        COUNT(*) as quantidade_transacoes,
        tipo
    FROM transacoes 
    WHERE usuario_id = ? 
    AND tipo = 'Saida' 
    AND strftime('%Y-%m', data) = ?
    AND categoria IS NOT NULL AND categoria != '' AND TRIM(categoria) != ''
    GROUP BY categoria
    ORDER BY total DESC
    LIMIT 8
");
$stmt->execute([$id, $mes_atual]);
$categorias_gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Notificações
$sql_notificacoes = "SELECT * FROM notificacoes WHERE usuario_id = ? AND lida = 0 ORDER BY data_criacao DESC LIMIT 3";
$stmt_notificacoes = $conn->prepare($sql_notificacoes);
$stmt_notificacoes->execute([$id]);
$notificacoes = $stmt_notificacoes->fetchAll(PDO::FETCH_ASSOC);

$sql_count = "SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = ? AND lida = 0";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute([$id]);
$total_nao_lidas = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

$data_atual = date("d-m-y H:i");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinPlan - Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/perfil.css">
    <link rel="stylesheet" href="../css/notificacoes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Importação do Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <!-- CSS inline para garantir que as alterações sejam aplicadas -->
    <style>
        /* Layout que aproveita toda a tela */
        .statistics {
            background-color: #163546 !important;
            border: 1px solid #0d2631 !important;
            min-height: 400px !important;
            width: 100% !important;
        }

        .statistics-content {
            display: flex !important;
            width: 100% !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 30px 50px !important;
            gap: 80px !important;
        }

        .chart-container {
            position: relative !important;
            width: 280px !important;
            height: 280px !important;
            flex-shrink: 0 !important;
        }

        .chart-center-text {
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            font-weight: 600 !important;
            text-align: center !important;
            font-size: 16px !important;
            color: white !important;
            z-index: 10 !important;
            line-height: 1.2 !important;
        }

        .progress-bars {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 35px !important;
            width: 100% !important;
        }

        .progress-item {
            display: flex !important;
            align-items: center !important;
            gap: 20px !important;
            width: 100% !important;
        }

        .progress-info {
            flex: 1 !important;
            margin-right: 15px !important;
            width: 100% !important;
        }

        .progress-label {
            margin-bottom: 12px !important;
            font-size: 14px !important;
            color: white !important;
            text-align: left !important;
        }

        .progress-bar-container {
            height: 10px !important;
            background-color: rgba(255, 255, 255, 0.3) !important;
            border-radius: 5px !important;
            overflow: hidden !important;
            width: 100% !important;
            position: relative !important;
        }

        .progress-bar-fill.balance {
            background-color: #00ff00 !important;
            width: <?php echo $perc_balance; ?>% !important;
        }
        
        .progress-bar-fill.investment {
            background-color: #ff00ff !important;
            width: <?php echo $perc_investment; ?>% !important;
        }
        
        .progress-bar-fill.goals {
            background-color: #0000ff !important;
            width: <?php echo $perc_goals; ?>% !important;
        }

        .progress-percentage {
            width: 60px !important;
            text-align: right !important;
            font-weight: 600 !important;
            color: white !important;
            font-size: 16px !important;
        }

        .progress-icon {
            width: 30px !important;
            height: 30px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 20px !important;
        }

        .progress-icon.dollar {
            color: #00ff00 !important;
            font-size: 24px !important;
            width: 30px !important;
            text-align: center !important;
        }

        .progress-icon.investment {
            color: #ff00ff !important;
            width: 30px !important;
            text-align: center !important;
        }

        .progress-icon.goals {
            color: #0000ff !important;
            width: 30px !important;
            text-align: center !important;
        }

        /* Bottom section que aproveita toda a largura */
        .bottom-section {
            display: grid !important;
            grid-template-columns: 1fr 1fr 1fr !important;
            gap: 25px !important;
            width: 100% !important;
        }

        .comparative {
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
        }

        .bar-chart-container {
            flex: 1 !important;
            min-height: 300px !important;
            width: 100% !important;
            margin-top: 10px !important;
        }

        /* Responsividade */
        @media (max-width: 1200px) {
            .bottom-section {
                grid-template-columns: 1fr 1fr !important;
            }

            .comparative {
                grid-column: span 2 !important;
            }
        }

        @media (max-width: 992px) {
            .statistics-content {
                flex-direction: column !important;
                align-items: center !important;
                gap: 40px !important;
                padding: 25px 30px !important;
            }

            .progress-bars {
                width: 100% !important;
            }
        }

        @media (max-width: 768px) {
            .bottom-section {
                grid-template-columns: 1fr !important;
            }

            .comparative {
                grid-column: span 1 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Logo -->
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
            
            <!-- Menu -->
            <ul class="menu">
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="metas.php">
                        <i class="fas fa-wallet"></i> Goals
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-chart-pie"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="transactions.php">
                        <i class="fas fa-exchange-alt"></i> Transactions
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-credit-card"></i> Accounts
                    </a>
                </li>
                <li>
                    <a href="perfil.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-question-circle"></i> Help
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Log out
                    </a>
                </li>
            </ul>
            
            <!-- Theme Toggle -->
            <div class="theme-toggle">
                <div class="toggle-track"><div class="toggle-thumb"></div></div>
                <i class="fas fa-sun sun"></i>
                <i class="fas fa-moon moon"></i>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    <div class="date">Today: <?php echo $data_atual; ?></div>
                    <div class="balance">
                        General balance: R$ <?php echo $saldo_formatado; ?>
                        <span class="<?php echo $variacao_positiva ? 'positive' : 'negative'; ?>">
                            (<?php echo $variacao_positiva ? '↑' : '↓'; ?> R$ <?php echo number_format(abs($variacao), 2, ',', '.'); ?> )
                        </span>
                    </div>
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
            
            <!-- Dashboard Content -->
            <div class="dashboard">
                <!-- Balance Accounts Section -->
<div class="card balance-accounts">
    <h2>Balance Accounts</h2>
    <div class="accounts-container">
        <!-- Conta principal (saldo real) -->
        <div class="account-card">
            <div class="account-icon dollar">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="account-balance">
                R$ <?php echo number_format($saldo_mes, 2, ',', '.'); ?>
            </div>
        </div>

        <!-- Conta crédito (zerada até implementar Investment) -->
        <div class="account-card">
            <div class="account-icon credit-card">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                    <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                    <line x1="2" x2="22" y1="10" y2="10"></line>
                </svg>
            </div>
            <div class="account-balance">R$ 0,00</div>
        </div>

        <!-- Poupança/cofrinho (zerado até implementar Goals) -->
        <div class="account-card">
            <div class="account-icon piggy-bank">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                    <path d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2h0V5z"></path>
                    <path d="M2 9v1c0 1.1.9 2 2 2h1"></path>
                    <path d="M16 11h0"></path>
                </svg>
            </div>
            <div class="account-balance">R$ 0,00</div>
        </div>
    </div>
</div>

                
                <!-- Statistics Section -->
                <div class="statistics">
                    <div class="statistics-header">
                        <h2>Statistics</h2>
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
                    </div>
                    
                    <div class="statistics-content">
    <!-- Gráfico de rosca -->
    <div class="chart-container">
    <canvas id="doughnutChart"></canvas>
    <div class="chart-center-text">
        Total balance<br>R$ <?php echo number_format($saldo_mes, 0, ',', '.'); ?>
    </div>
</div>

<!-- Barras de progresso -->
<div class="progress-bars">
    <!-- Barra de Balance -->
    <div class="progress-item">
        <div class="progress-icon dollar">$</div>
        <div class="progress-info">
            <div class="progress-label">Balance (R$ <?php echo number_format($saldo_mes, 2, ',', '.'); ?>)</div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill balance" style="width: <?php echo $perc_balance; ?>%;"></div>
            </div>
        </div>
        <div class="progress-percentage"><?php echo $perc_balance; ?>%</div>
    </div>
        
        <!-- Barra de Investment -->
    <div class="progress-item">
        <div class="progress-icon investment">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
        </div>
        <div class="progress-info">
            <div class="progress-label">Investment (R$ 0,00)</div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill investment" style="width: 0%;"></div>
            </div>
        </div>
        <div class="progress-percentage">0%</div>
    </div>
        
        <!-- Barra de Goals -->
        <div class="progress-item">
            <div class="progress-icon goals">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
                    <circle cx="12" cy="12" r="10"></circle>
                    <circle cx="12" cy="12" r="6"></circle>
                    <circle cx="12" cy="12" r="2"></circle>
                </svg>
            </div>
            <div class="progress-info">
                <div class="progress-label">Goals (R$ 0,00)</div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill goals" style="width: 0%;"></div>
                </div>
            </div>
            <div class="progress-percentage">0%</div>
        </div>
    </div>
</div>
                </div>
                
                <!-- Bottom Section -->
                <div class="bottom-section">
                    <!-- Popular Expense Categories -->
                    
<!-- Popular Expense Categories -->
<div class="card expense-categories">
    <h2>Popular Expense Categories</h2>
    <ul class="category-list">
        <?php if (count($categorias_gastos) > 0): ?>
            <?php foreach ($categorias_gastos as $categoria): ?>
                <?php 
                $icone_categoria = buscarIconeCategoria($categoria['categoria'], $icons);
                $porcentagem_uso = round(($categoria['quantidade_transacoes'] / array_sum(array_column($categorias_gastos, 'quantidade_transacoes'))) * 100, 1);
                ?>
                <li class="category-item">
                    <div class="category-icon" title="<?php echo htmlspecialchars($categoria['categoria']); ?>">
                        <i class="<?php echo $icone_categoria; ?>"></i>
                    </div>
                    <div class="category-info">
                        <div class="category-name"><?php echo htmlspecialchars($categoria['categoria']); ?></div>
                        <div class="category-usage"><?php echo $categoria['quantidade_transacoes']; ?> transações (<?php echo $porcentagem_uso; ?>%)</div>
                    </div>
                    <div class="category-amount">R$ <?php echo number_format($categoria['total'], 2, ',', '.'); ?></div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="category-item no-data">
                <div class="category-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="category-info">
                    <div class="category-name">Nenhuma categoria encontrada</div>
                    <div class="category-usage">Cadastre transações para ver suas categorias populares</div>
                </div>
                <div class="category-amount">R$ 0,00</div>
            </li>
        <?php endif; ?>
    </ul>
    
    <?php if (count($categorias_gastos) > 0): ?>
        <div class="category-summary">
            <div class="summary-item">
                <span class="summary-label">Total de categorias:</span>
                <span class="summary-value"><?php echo count($categorias_gastos); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Gasto total:</span>
                <span class="summary-value">R$ <?php echo number_format(array_sum(array_column($categorias_gastos, 'total')), 2, ',', '.'); ?></span>
            </div>
        </div>
    <?php endif; ?>
</div>
                    
                    <!-- Goals and Progress -->
                    <div class="card goals-progress">
                        <h2>Goals and Progress</h2>
                        <ul class="goals-list">
                            <li class="goal-item">
                                <div class="goal-icon university">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                    </svg>
                                </div>
                                <div class="goal-info">
                                    <div class="goal-name">University</div>
                                    <div class="goal-progress-container">
                                        <div class="goal-progress-bar university" style="width: <?php echo min(100, ($entradas_mes / 10000) * 100); ?>%"></div>
                                    </div>
                                </div>
                                <div class="goal-percentage"><?php echo min(100, round(($entradas_mes / 10000) * 100)); ?>%</div>
                            </li>
                            <li class="goal-item">
                                <div class="goal-icon house">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                    </svg>
                                </div>
                                <div class="goal-info">
                                    <div class="goal-name">House</div>
                                    <div class="goal-progress-container">
                                        <div class="goal-progress-bar house" style="width: <?php echo min(100, ($saldo_total / 50000) * 100); ?>%"></div>
                                    </div>
                                </div>
                                <div class="goal-percentage"><?php echo min(100, round(($saldo_total / 50000) * 100)); ?>%</div>
                            </li>
                            <li class="goal-item">
                                <div class="goal-icon car">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                                        <path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"></path>
                                        <circle cx="6.5" cy="16.5" r="2.5"></circle>
                                        <circle cx="16.5" cy="16.5" r="2.5"></circle>
                                    </svg>
                                </div>
                                <div class="goal-info">
                                    <div class="goal-name">Car</div>
                                    <div class="goal-progress-container">
                                        <div class="goal-progress-bar car" style="width: <?php echo min(100, ($saldo_total / 30000) * 100); ?>%"></div>
                                    </div>
                                </div>
                                <div class="goal-percentage"><?php echo min(100, round(($saldo_total / 30000) * 100)); ?>%</div>
                            </li>
                            <li class="goal-item">
                                <div class="goal-icon vacation">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                                        <path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path>
                                        <polygon points="12 15 17 21 7 21 12 15"></polygon>
                                    </svg>
                                </div>
                                <div class="goal-info">
                                    <div class="goal-name">Vacation</div>
                                    <div class="goal-progress-container">
                                        <div class="goal-progress-bar vacation" style="width: <?php echo min(100, ($entradas_mes / 5000) * 100); ?>%"></div>
                                    </div>
                                </div>
                                <div class="goal-percentage"><?php echo min(100, round(($entradas_mes / 5000) * 100)); ?>%</div>
                            </li>
                            <li class="goal-item">
                                <div class="goal-icon marriage">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <circle cx="12" cy="10" r="3"></circle>
                                        <path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path>
                                    </svg>
                                </div>
                                <div class="goal-info">
                                    <div class="goal-name">Marriage</div>
                                    <div class="goal-progress-container">
                                        <div class="goal-progress-bar marriage" style="width: <?php echo min(100, ($saldo_total / 20000) * 100); ?>%"></div>
                                    </div>
                                </div>
                                <div class="goal-percentage"><?php echo min(100, round(($saldo_total / 20000) * 100)); ?>%</div>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Comparative Indicator -->
                    <div class="card comparative">
                        <h2>Comparative Indicator</h2>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-color balances"></div>
                                <div class="legend-label">Entradas</div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color expenses"></div>
                                <div class="legend-label">Saídas</div>
                            </div>
                        </div>
                        <div class="bar-chart-container">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Forms para notificações -->
    <form id="markReadForm" method="POST" style="display: none;">
        <input type="hidden" name="marcar_lida" value="1">
        <input type="hidden" name="notificacao_id" id="markReadId">
    </form>

    <form id="deleteNotificationForm" method="POST" style="display: none;">
        <input type="hidden" name="excluir_notificacao" value="1">
        <input type="hidden" name="notificacao_id" id="deleteNotificationId">
    </form>
    
    <!-- Script para os gráficos -->
    <script>
document.addEventListener("DOMContentLoaded", () => {
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

    // Dados dinâmicos do PHP
    const dadosGrafico = {
        meses: <?php echo json_encode($meses_grafico); ?>,
        entradas: <?php echo json_encode($entradas_grafico); ?>,
        saidas: <?php echo json_encode($saidas_grafico); ?>
    };

    const percentuais = {
        balance: <?php echo $perc_balance; ?>,
        investment: <?php echo $perc_investment; ?>,
        goals: <?php echo $perc_goals; ?>
    };

    // Gráfico de rosca (doughnut) para estatísticas
    const doughnutCtx = document.getElementById("doughnutChart");
    if (doughnutCtx) {
        const doughnutChart = new Chart(doughnutCtx, {
            type: "doughnut",
            data: {
                labels: ["Balance", "Investment", "Goals"],
                datasets: [
                    {
                        data: [percentuais.balance, percentuais.investment, percentuais.goals],
                        backgroundColor: ["#00FF00", "#FF00FF", "#0000FF"],
                        borderWidth: 0,
                        cutout: "70%",
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: "#041518",
                        titleColor: "#FFFFFF",
                        bodyColor: "#FFFFFF",
                        bodyFont: {
                            size: 14,
                        },
                        displayColors: false,
                        callbacks: {
                            label: (context) => context.label + ": " + context.raw + "%",
                        },
                    },
                },
            },
        });
    }

    // Gráfico de barras para indicador comparativo
    const barCtx = document.getElementById("barChart");
    if (barCtx) {
        const barChart = new Chart(barCtx, {
            type: "bar",
            data: {
                labels: dadosGrafico.meses,
                datasets: [
                    {
                        label: "Entradas",
                        data: dadosGrafico.entradas,
                        backgroundColor: "#4CAF50",
                        borderWidth: 0,
                        borderRadius: 4,
                    },
                    {
                        label: "Saídas",
                        data: dadosGrafico.saidas,
                        backgroundColor: "#F44336",
                        borderWidth: 0,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(255, 255, 255, 0.1)",
                        },
                        ticks: {
                            color: "#B0B0B0",
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: "#B0B0B0",
                            font: {
                                size: 12
                            }
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: "#041518",
                        titleColor: "#FFFFFF",
                        bodyColor: "#FFFFFF",
                        bodyFont: {
                            size: 14,
                        },
                        callbacks: {
                            label: (context) => context.dataset.label + ": R$ " + context.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2}),
                        },
                    },
                },
                barPercentage: 0.8,
                categoryPercentage: 0.9,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10,
                        left: 10,
                        right: 10
                    }
                }
            },
        });
    }

    // Notificações
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationPanel = document.getElementById('notificationPanel');
    
    if (notificationIcon && notificationPanel) {
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
    }

    // Simulação de interatividade nos seletores de data
    const dateInputs = document.querySelectorAll(".date-input");
    dateInputs.forEach((input) => {
        input.addEventListener("click", () => {
            alert("O seletor de data seria aberto aqui em uma implementação completa.");
        });
    });
});
</script>
</body>
</html>
