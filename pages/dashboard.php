<?php
/**
 * Dashboard Principal - Sistema GERE TECH
 * Painel de controle com visão geral do sistema
 * Inclui estatísticas, alertas e funcionalidades principais
 */

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Incluir arquivos necessários
require_once '../includes/conexao.php';
require_once '../includes/config.php';

// Registrar acesso ao dashboard
registrarLog($_SESSION['usuario_id'], 'ACESSO_DASHBOARD', 'dashboard', null, 'Usuário acessou o dashboard');

// Obter estatísticas do sistema
$stats = getEstatisticasDashboard();

// Obter alertas não lidos
$alertas = getAlertasNaoLidos(5);

// Vendas recentes
$vendas_recentes = $conexao->query("
    SELECT v.*, c.nome as cliente_nome, p.nome as produto_nome, v.valor_total
    FROM vendas v
    JOIN clientes c ON v.id_cliente = c.id
    JOIN produtos p ON v.id_produto = p.id
    ORDER BY v.data_venda DESC
    LIMIT 5
");

// Produtos com baixo estoque
$estoque_minimo = getConfiguracao('estoque_minimo_alerta') ?: 5;
$produtos_baixo_estoque = $conexao->query("
    SELECT nome, estoque 
    FROM produtos 
    WHERE estoque <= $estoque_minimo 
    ORDER BY estoque ASC
    LIMIT 5
");

// Dados para gráficos
$vendas_por_mes = $conexao->query("
    SELECT MONTH(data_venda) as mes, COUNT(*) as total, SUM(valor_total) as faturamento
    FROM vendas 
    WHERE YEAR(data_venda) = YEAR(CURRENT_DATE())
    GROUP BY MONTH(data_venda)
    ORDER BY mes
");

$formas_pagamento = $conexao->query("
    SELECT forma_pagamento, COUNT(*) as total
    FROM vendas
    GROUP BY forma_pagamento
");

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GERE TECH</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard">
        <!-- Incluir Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Incluir Header -->
            <?php 
            $page_title = 'Dashboard';
            $page_subtitle = 'Visão geral do sistema';
            include '../includes/header.php'; 
            ?>
            
            <!-- Container da Página -->
            <div class="page-container">
            
                <!-- Alertas -->
                <?php if (!empty($alertas)): ?>
                <div class="alerts-container">
                    <?php foreach ($alertas as $alerta): ?>
                        <div class="alert alert-<?php echo $alerta['tipo'] == 'estoque_baixo' ? 'warning' : 'info'; ?>">
                            <i class="fas fa-<?php echo $alerta['tipo'] == 'estoque_baixo' ? 'exclamation-triangle' : 'info-circle'; ?>"></i>
                            <div class="alert-content">
                                <strong><?php echo htmlspecialchars($alerta['titulo']); ?></strong>
                                <p><?php echo htmlspecialchars($alerta['mensagem']); ?></p>
                            </div>
                            <button class="alert-close" onclick="marcarAlertaLido(<?php echo $alerta['id']; ?>)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Cards de Estatísticas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_clientes']; ?></h3>
                            <p>Clientes Cadastrados</p>
                            <span class="text-success">Total no sistema</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_produtos']; ?></h3>
                            <p>Produtos</p>
                            <?php if ($stats['produtos_estoque_baixo'] > 0): ?>
                                <span class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?php echo $stats['produtos_estoque_baixo']; ?> com estoque baixo
                                </span>
                            <?php else: ?>
                                <span class="text-success">Estoque adequado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['vendas_mes']; ?></h3>
                            <p>Vendas Este Mês</p>
                            <span class="text-info">Período atual</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo formatarMoeda($stats['faturamento_mes']); ?></h3>
                            <p>Faturamento do Mês</p>
                            <span class="text-success">Receita atual</span>
                        </div>
                    </div>
                </div>
            
            <!-- Gráficos e Tabelas -->
            <section class="dashboard-content">
                <div class="content-grid">
                    <!-- Gráfico de Vendas -->
                    <div class="card chart-card">
                        <div class="card-header">
                            <h3>Vendas dos Últimos 6 Meses</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gráfico de Formas de Pagamento -->
                    <div class="card chart-card">
                        <div class="card-header">
                            <h3>Formas de Pagamento</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="paymentChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Vendas Recentes -->
                    <div class="card table-card">
                        <div class="card-header">
                            <h3>Vendas Recentes</h3>
                            <a href="vendas.php" class="btn btn-primary btn-sm">Ver todas</a>
                        </div>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($vendas_recentes->num_rows > 0): ?>
                                        <?php while ($venda = $vendas_recentes->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($venda['cliente_nome']); ?></td>
                                                <td><?php echo htmlspecialchars($venda['produto_nome']); ?></td>
                                                <td><?php echo $venda['quantidade']; ?></td>
                                                <td><?php echo formatarMoeda($venda['valor_total']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhuma venda encontrada</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Produtos com Baixo Estoque -->
                    <div class="card alert-card">
                        <div class="card-header">
                            <h3>Produtos com Baixo Estoque</h3>
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        </div>
                        <div class="alert-list">
                            <?php if ($produtos_baixo_estoque->num_rows > 0): ?>
                                <?php while ($produto = $produtos_baixo_estoque->fetch_assoc()): ?>
                                    <div class="alert-item">
                                        <div class="alert-info">
                                            <strong><?php echo htmlspecialchars($produto['nome']); ?></strong>
                                            <span class="stock-level <?php echo $produto['estoque'] <= 2 ? 'critical' : 'warning'; ?>">
                                                <?php echo $produto['estoque']; ?> unidades
                                            </span>
                                        </div>
                                        <a href="produtos.php" class="btn btn-sm btn-outline">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="alert-item">
                                    <div class="alert-info">
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i>
                                            Todos os produtos têm estoque adequado
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    
    <!-- JavaScript -->
    <script src="../js/main.js"></script>
    
    <script>
        // Função para marcar alerta como lido
        function marcarAlertaLido(alertaId) {
            fetch('../includes/marcar_alerta_lido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: alertaId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover o alerta da tela
                    const alertElement = document.querySelector(`[onclick="marcarAlertaLido(${alertaId})"]`).closest('.alert');
                    alertElement.style.opacity = '0';
                    setTimeout(() => alertElement.remove(), 300);
                }
            })
            .catch(error => console.error('Erro:', error));
        }
        
        // Dados para os gráficos
        const salesData = {
            labels: [
                <?php 
                $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                $vendas_labels = [];
                $vendas_data = [];
                while ($row = $vendas_por_mes->fetch_assoc()) {
                    $vendas_labels[] = '"' . $meses[$row['mes']] . '"';
                    $vendas_data[] = $row['total'];
                }
                echo implode(', ', $vendas_labels);
                ?>
            ],
            datasets: [{
                label: 'Vendas',
                data: [<?php echo implode(', ', $vendas_data); ?>],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };
        
        const paymentData = {
            labels: [
                <?php 
                $payment_labels = [];
                $payment_data = [];
                $payment_colors = [
                    'dinheiro' => '#28a745',
                    'cartao_debito' => '#17a2b8',
                    'cartao_credito' => '#ffc107',
                    'pix' => '#6f42c1'
                ];
                $colors = [];
                while ($row = $formas_pagamento->fetch_assoc()) {
                    $forma = ucfirst(str_replace('_', ' ', $row['forma_pagamento']));
                    $payment_labels[] = '"' . $forma . '"';
                    $payment_data[] = $row['total'];
                    $colors[] = '"' . $payment_colors[$row['forma_pagamento']] . '"';
                }
                echo implode(', ', $payment_labels);
                ?>
            ],
            datasets: [{
                data: [<?php echo implode(', ', $payment_data); ?>],
                backgroundColor: [<?php echo implode(', ', $colors); ?>],
                borderWidth: 0
            }]
        };
        
        // Criar gráfico de vendas
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: salesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Criar gráfico de formas de pagamento
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: paymentData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
        
        // Atualizar dados a cada 5 minutos
        setInterval(() => {
            location.reload();
        }, 300000);
    </script>
    
    <style>
        /* Estilos específicos do dashboard */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-left h1 {
            margin: 0;
            color: #333;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .theme-toggle {
            background: none;
            border: 1px solid #ddd;
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .theme-toggle:hover {
            background: #f8f9fa;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar-header {
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid #34495e;
        }
        
        .sidebar-header .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
        }
        
        .sidebar-header h3 {
            color: white;
            margin: 0;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .chart-card {
            grid-column: span 1;
        }
        
        .table-card {
            grid-column: span 2;
        }
        
        .alert-card {
            grid-column: span 2;
        }
        
        .chart-container {
            height: 300px;
            padding: 1rem;
        }
        
        .card-footer {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .card-footer a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .alert-list {
            padding: 1rem;
        }
        
        .alert-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }
        
        .stock-level.critical {
            color: #dc3545;
            font-weight: bold;
        }
        
        .stock-level.warning {
            color: #ffc107;
            font-weight: bold;
        }
        
        .text-success {
            color: #28a745;
        }
        
        .text-warning {
            color: #ffc107;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #667eea;
            color: #667eea;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-card,
            .table-card,
            .alert-card {
                grid-column: span 1;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .header-right {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</body>
</html>