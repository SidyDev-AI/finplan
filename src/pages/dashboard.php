<?php
// Iniciar sessão para gerenciar login do usuário
session_start();

// Obter data atual formatada
$currentDate = date("d-m-y H:i");

// Dados fictícios para o dashboard
$generalBalance = 15543.00;
$balanceChange = 124.32;
$isBalancePositive = true;

// Nome do usuário
$userName = "User profile";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinPlan - Dashboard</title>
    <style>
        /* Reset e estilos gerais */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #041518;
            color: #ffffff;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Estilos da Sidebar */
        .sidebar {
            width: 250px;
            background-color: #163546;
            padding: 20px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Logo */
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .logo-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #ffffff;
            color: #3f51b5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: bold;
        }

        .fin {
            color: #ffffff;
        }

        .plan {
            color: #ff5252;
        }

        /* Menu */
        .menu {
            list-style: none;
            margin-bottom: 20px;
        }

        .menu li {
            margin-bottom: 15px;
        }

        .menu li a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
            color: #b0b0b0;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .menu li a:hover {
            background-color: #041518;
            color: #ffffff;
        }

        .menu li.active a {
            background-color: #041518;
            color: #ffffff;
        }

        .menu li a svg {
            min-width: 24px;
        }

        /* Theme Toggle */
        .theme-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            position: absolute;
            bottom: 30px;
            left: 20px;
        }

        .theme-toggle .sun-icon {
            color: #b0b0b0;
        }

        .theme-toggle .moon-icon {
            color: #279e8e;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #041518;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: #279e8e;
            transition: .4s;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #163546;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #0d2631;
        }

        .date {
            color: #b0b0b0;
        }

        .balance {
            color: #ffffff;
        }

        .balance .positive {
            color: #279e8e;
        }

        .balance .negative {
            color: #ff5252;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification {
            cursor: pointer;
        }

        .user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ffffff;
        }

        .user svg {
            cursor: pointer;
        }

        /* Dashboard */
        .dashboard {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .card {
            background-color: #163546;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #0d2631;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
        }

        /* Balance Accounts */
        .accounts-container {
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }

        .account-card {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #0d2631;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #0a1e28;
        }

        .account-icon {
            width: 40px;
            height: 40px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .account-icon.dollar {
            background-color: #279e8e;
        }

        .account-icon.credit-card {
            background-color: #9c27b0;
        }

        .account-icon.piggy-bank {
            background-color: #3f51b5;
        }

        .account-balance {
            font-weight: 600;
        }

        /* Statistics */
        .statistics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .period-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .date-input {
            position: relative;
            background-color: #0d2631;
            border-radius: 5px;
            padding: 5px 10px;
            border: 1px solid #0a1e28;
        }

        .date-input input {
            background: transparent;
            border: none;
            color: #ffffff;
            width: 80px;
            outline: none;
        }

        .date-input svg {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .statistics-content {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .chart-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto; /* Centralizar horizontalmente */
        }

        .bar-chart-container {
            width: 100%;
            height: 250px;
        }

        .chart-center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: 600;
            text-align: center;
        }

        .progress-bars {
            flex: 0.6;  /* Reduzido de 1 para 0.5 para diminuir o tamanho pela metade */
            display: flex;
            flex-direction: column;
            gap: 20px;
            justify-content: center;
            max-width: 800px; /* Limitar a largura máxima */
            margin: 0 auto; /* Centralizar horizontalmente */
        }

        .progress-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .progress-icon {
            width: 30px;
            text-align: center;
            font-size: 20px;
        }

        .progress-icon.dollar {
            color: #279e8e;
        }

        .progress-icon.investment {
            color: #9c27b0;
        }

        .progress-icon.goals {
            color: #3f51b5;
        }

        .progress-info {
            flex: 1;
        }

        .progress-label {
            margin-bottom: 5px;
        }

        .progress-bar-container {
            height: 10px;
            background-color: #ffffff;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 5px;
        }

        .progress-bar.balance {
            background-color: #279e8e;
        }

        .progress-bar.investment {
            background-color: #9c27b0;
        }

        .progress-bar.goals {
            background-color: #3f51b5;
        }

        .progress-percentage {
            width: 40px;
            text-align: right;
        }

        /* Bottom Section */
        .bottom-section {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        /* Expense Categories */
        .category-list {
            list-style: none;
        }

        .category-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .category-item:last-child {
            border-bottom: none;
        }

        .category-icon {
            width: 30px;
            text-align: center;
            font-size: 18px;
            margin-right: 15px;
        }

        .category-icon.supermarket {
            color: #03a9f4;
        }

        .category-icon.shopping {
            color: #9c27b0;
        }

        .category-icon.transport {
            color: #cddc39;
        }

        .category-icon.entertainment {
            color: #f44336;
        }

        .category-icon.games {
            color: #3f51b5;
        }

        .category-name {
            flex: 1;
        }

        .category-amount {
            font-weight: 600;
        }

        /* Goals */
        .goals-list {
            list-style: none;
        }

        .goal-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
        }

        .goal-icon {
            width: 30px;
            text-align: center;
            font-size: 18px;
            margin-right: 15px;
        }

        .goal-icon.university {
            color: #9c27b0;
        }

        .goal-icon.house {
            color: #ff5722;
        }

        .goal-icon.car {
            color: #4caf50;
        }

        .goal-icon.vacation {
            color: #2196f3;
        }

        .goal-icon.marriage {
            color: #f44336;
        }

        .goal-info {
            flex: 1;
        }

        .goal-name {
            margin-bottom: 5px;
        }

        .goal-progress-container {
            height: 10px;
            background-color: #ffffff;
            border-radius: 5px;
            overflow: hidden;
        }

        .goal-progress-bar {
            height: 100%;
            border-radius: 5px;
        }

        .goal-progress-bar.university {
            background-color: #9c27b0;
        }

        .goal-progress-bar.house {
            background-color: #ff5722;
        }

        .goal-progress-bar.car {
            background-color: #4caf50;
        }

        .goal-progress-bar.vacation {
            background-color: #2196f3;
        }

        .goal-progress-bar.marriage {
            background-color: #f44336;
        }

        .goal-percentage {
            width: 40px;
            text-align: right;
        }

        /* Comparative */
        .chart-legend {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .legend-color.balances {
            background-color: #4caf50;
        }

        .legend-color.expenses {
            background-color: #f44336;
        }

        /* Responsividade */
        @media (max-width: 1200px) {
            .bottom-section {
                grid-template-columns: 1fr 1fr;
            }

            .comparative {
                grid-column: span 2;
            }
        }

        @media (max-width: 992px) {
            .statistics-content {
                flex-direction: column;
                align-items: center;
            }

            .chart-container {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 15px;
            }

            .menu {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .menu li {
                margin-bottom: 0;
            }

            .theme-toggle {
                position: static;
                margin-top: 20px;
            }

            .header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .accounts-container {
                flex-direction: column;
            }

            .bottom-section {
                grid-template-columns: 1fr;
            }

            .comparative {
                grid-column: span 1;
            }
        }
        .statistics-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
    </style>
    <!-- Importação do Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Logo -->
            <div class="logo">
                <div class="logo-circle">R$</div>
                <div class="logo-text"><span class="fin">Fin</span><span class="plan">Plan</span></div>
            </div>
            
            <!-- Menu -->
            <ul class="menu">
                <li class="active">
                    <a href="dashboard.php">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                            <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                            <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                            <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <path d="M20.91 8.84 8.56 2.23a1.93 1.93 0 0 0-1.81 0L3.1 4.13a2.12 2.12 0 0 0-.05 3.69l12.22 6.93a2 2 0 0 0 1.94 0L21 12.51a2.12 2.12 0 0 0-.09-3.67Z"></path>
                            <path d="m3.09 8.84 12.35-6.61a1.93 1.93 0 0 1 1.81 0l3.65 1.9a2.12 2.12 0 0 1 .1 3.69L8.73 14.75a2 2 0 0 1-1.94 0L3 12.51a2.12 2.12 0 0 1 .09-3.67Z"></path>
                            <line x1="12" y1="22" x2="12" y2="13"></line>
                            <path d="M20 13.5v3.37a2.06 2.06 0 0 1-1.11 1.83l-6 3.08a1.93 1.93 0 0 1-1.78 0l-6-3.08A2.06 2.06 0 0 1 4 16.87V13.5"></path>
                        </svg>
                        <span>Budget</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <path d="M9 5H2v7h7V5Z"></path>
                            <path d="M22 5h-7v7h7V5Z"></path>
                            <path d="M9 19H2v-7h7v7Z"></path>
                            <path d="M22 19h-7v-7h7v7Z"></path>
                        </svg>
                        <span>Categories</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <path d="m17 7-7 7"></path>
                            <path d="m7 7 7 7"></path>
                        </svg>
                        <span>Transactions</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                        <span>Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                            <line x1="2" x2="22" y1="10" y2="10"></line>
                        </svg>
                        <span>Accounts</span>
                    </a>
                </li>
                <li>
                    <a href="perfil.php">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span>Help</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Log out</span>
                    </a>
                </li>
            </ul>
            
            <!-- Theme Toggle -->
            <div class="theme-toggle">
                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" class="sun-icon">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
                <label class="switch">
                    <input type="checkbox" id="theme-switch" checked>
                    <span class="slider round"></span>
                </label>
                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" class="moon-icon">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="date">Today: 31-03-25 20:00</div>
                <div class="balance">
                    General balance: R$ 15.543,00
                    <span class="positive">(↑ + R$ 124,32 )</span>
                </div>
                <div class="user-info">
                    <div class="notification">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                        </svg>
                    </div>
                    <div class="user">
                        <div class="avatar"></div>
                        <span>User profile</span>
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="19" cy="12" r="1"></circle>
                            <circle cx="5" cy="12" r="1"></circle>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <div class="dashboard">
                <!-- Balance Accounts Section -->
                <div class="card balance-accounts">
                    <h2>Balance Accounts</h2>
                    <div class="accounts-container">
                        <div class="account-card">
                            <div class="account-icon dollar">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                            <div class="account-balance">R$ 15.543,00</div>
                        </div>
                        <div class="account-card">
                            <div class="account-icon credit-card">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                    <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                    <line x1="2" x2="22" y1="10" y2="10"></line>
                                </svg>
                            </div>
                            <div class="account-balance">R$ 15.543,00</div>
                        </div>
                        <div class="account-card">
                            <div class="account-icon piggy-bank">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                    <path d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2h0V5z"></path>
                                    <path d="M2 9v1c0 1.1.9 2 2 2h1"></path>
                                    <path d="M16 11h0"></path>
                                </svg>
                            </div>
                            <div class="account-balance">R$ 15.543,00</div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Section -->
                <div class="card statistics">
                    <div class="statistics-header">
                        <h2>Statistics</h2>
                        <div class="period-selector">
                            <span>Period: from</span>
                            <div class="date-input">
                                <input type="text" value="01.01.2025" readonly>
                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
                                    <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                                    <path d="M3 9h18"></path>
                                </svg>
                            </div>
                            <span>on</span>
                            <div class="date-input">
                                <input type="text" value="01.04.2025" readonly>
                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
                                    <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                                    <path d="M3 9h18"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="statistics-content">
                        <div class="chart-container">
                            <canvas id="doughnutChart"></canvas>
                            <div class="chart-center-text">Total balance</div>
                        </div>
                        
                        <div class="progress-bars">
                            <div class="progress-item">
                                <div class="progress-icon dollar">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                                <div class="progress-info">
                                    <div class="progress-label">Balance</div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar balance" style="width: 55%"></div>
                                    </div>
                                </div>
                                <div class="progress-percentage">55%</div>
                            </div>
                            
                            <div class="progress-item">
                                <div class="progress-icon investment">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                                    </svg>
                                </div>
                                <div class="progress-info">
                                    <div class="progress-label">Investment</div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar investment" style="width: 30%"></div>
                                    </div>
                                </div>
                                <div class="progress-percentage">30%</div>
                            </div>
                            
                            <div class="progress-item">
                                <div class="progress-icon goals">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <circle cx="12" cy="12" r="6"></circle>
                                        <circle cx="12" cy="12" r="2"></circle>
                                    </svg>
                                </div>
                                <div class="progress-info">
                                    <div class="progress-label">Goals</div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar goals" style="width: 15%"></div>
                                    </div>
                                </div>
                                <div class="progress-percentage">15%</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bottom Section -->
                <div class="bottom-section">
                    <!-- Popular Expense Categories -->
                    <div class="card expense-categories">
                        <h2>Popular Expense Categories</h2>
                        <ul class="category-list">
                            <li class="category-item">
                                <div class="category-icon supermarket">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                                        <circle cx="8" cy="21" r="1"></circle>
                                        <circle cx="19" cy="21" r="1"></circle>
                                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                                    </svg>
                                </div>
                                <div class="category-name">Supermarket</div>
                                <div class="category-amount">R$ 1324,99</div>
                            </li>
                            <li class="category-item">
                                <div class="category-icon shopping">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                        <path d="M3 6h18"></path>
                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                    </svg>
                                </div>
                                <div class="category-name">Shopping</div>
                                <div class="category-amount">R$ 5004,99</div>
                            </li>
                            <li class="category-item">
                                <div class="category-icon transport">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                                        <path d="M18 8a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v8a3 3 0 0 0 3 3h1a3 3 0 0 0 3-3v-1h4v1a3 3 0 0 0 3 3h1a3 3 0 0 0 3-3V8Z"></path>
                                        <circle cx="6.5" cy="12.5" r="1.5"></circle>
                                        <circle cx="16.5" cy="12.5" r="1.5"></circle>
                                    </svg>
                                </div>
                                <div class="category-name">Transport</div>
                                <div class="category-amount">R$ 127,00</div>
                            </li>
                            <li class="category-item">
                                <div class="category-icon entertainment">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                                        <rect width="20" height="20" x="2" y="2" rx="2.18" ry="2.18"></rect>
                                        <line x1="7" y1="2" x2="7" y2="22"></line>
                                        <line x1="17" y1="2" x2="17" y2="22"></line>
                                        <line x1="2" y1="12" x2="22" y2="12"></line>
                                        <line x1="2" y1="7" x2="7" y2="7"></line>
                                        <line x1="2" y1="17" x2="7" y2="17"></line>
                                        <line x1="17" y1="17" x2="22" y2="17"></line>
                                        <line x1="17" y1="7" x2="22" y2="7"></line>
                                    </svg>
                                </div>
                                <div class="category-name">Entertainment</div>
                                <div class="category-amount">R$ 230,69</div>
                            </li>
                            <li class="category-item">
                                <div class="category-icon games">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                                        <line x1="6" y1="12" x2="10" y2="12"></line>
                                        <line x1="8" y1="10" x2="8" y2="14"></line>
                                        <line x1="15" y1="13" x2="15.01" y2="13"></line>
                                        <line x1="18" y1="11" x2="18.01" y2="11"></line>
                                        <rect width="20" height="12" x="2" y="6" rx="2"></rect>
                                    </svg>
                                </div>
                                <div class="category-name">Games</div>
                                <div class="category-amount">R$ 75,99</div>
                            </li>
                        </ul>
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
                                        <div class="goal-progress-bar university" style="width: 70%"></div>
                                    </div>
                                </div>
                                <div class="goal-percentage">70%</div>
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
                                        <div class="goal-progress-bar house" style="width: 30%"></div>
                                    </div>
                                </div>
                                <div class="goal-percentage">30%</div>
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
                                        <div class="goal-progress-bar car" style="width: 46%"></div>
                                    </div>
                                </div>
                                <div class="goal-percentage">46%</div>
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
                                        <div class="goal-progress-bar vacation" style="width: 88%"></div>
                                    </div>
                                </div>
                                <div class="goal-percentage">88%</div>
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
                                        <div class="goal-progress-bar marriage" style="width: 96%"></div>
                                    </div>
                                </div>
                                <div class="goal-percentage">96%</div>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Comparative Indicator -->
                    <div class="card comparative">
                        <h2>Comparative Indicator</h2>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-color balances"></div>
                                <div class="legend-label">Balances</div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color expenses"></div>
                                <div class="legend-label">Expenses</div>
                            </div>
                        </div>
                        <div class="chart-container bar-chart-container">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Configuração do tema
            const themeSwitch = document.getElementById("theme-switch");

            themeSwitch.addEventListener("change", function () {
                if (this.checked) {
                    // Tema escuro (padrão)
                    document.body.classList.remove("light-theme");
                } else {
                    // Tema claro
                    document.body.classList.add("light-theme");
                }
            });

            // Gráfico de rosca (doughnut) para estatísticas
            const doughnutCtx = document.getElementById("doughnutChart").getContext("2d");
            const doughnutChart = new Chart(doughnutCtx, {
                type: "doughnut",
                data: {
                    labels: ["Balance", "Investment", "Goals"],
                    datasets: [
                        {
                            data: [55, 30, 15],
                            backgroundColor: ["#279E8E", "#9C27B0", "#3F51B5"],
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

            // Gráfico de barras para indicador comparativo
            const barCtx = document.getElementById("barChart").getContext("2d");
            const barChart = new Chart(barCtx, {
                type: "bar",
                data: {
                    labels: ["JAN", "FEV", "MAR"],
                    datasets: [
                        {
                            label: "Balances",
                            data: [1500, 1200, 2200],
                            backgroundColor: "#4CAF50",
                            borderWidth: 0,
                            borderRadius: 4,
                        },
                        {
                            label: "Expenses",
                            data: [600, 1300, 900],
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
                            },
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: "#B0B0B0",
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
                                label: (context) => context.dataset.label + ": R$ " + context.raw.toFixed(2),
                            },
                        },
                    },
                    barPercentage: 0.6,
                },
            });

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
