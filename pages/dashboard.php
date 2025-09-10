<?php
/**
 * Dashboard Principal - Sistema GERE TECH
 * Painel de controle com visão geral do sistema
 * Inclui estatísticas, alertas e funcionalidades principais
 */

// Configurar codificação UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

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
    SELECT v.*, c.nome as cliente_nome, p.nome as produto_nome, (v.quantidade * p.valor) as valor_total
    FROM vendas v
    JOIN clientes c ON v.id_cliente = c.id
    JOIN produtos p ON v.id_produto = p.id
    WHERE v.user_id = " . $_SESSION['usuario_id'] . "
    ORDER BY v.data_venda DESC
    LIMIT 5
");

// Produtos com baixo estoque
$estoque_minimo = getConfiguracao('estoque_minimo_alerta') ?: 5;
$produtos_baixo_estoque = $conexao->query("
    SELECT nome, estoque 
    FROM produtos 
    WHERE estoque <= $estoque_minimo AND user_id = " . $_SESSION['usuario_id'] . "
    ORDER BY estoque ASC
    LIMIT 5
");

// Dados para gráficos
$vendas_por_mes = $conexao->query("
    SELECT 
        DATE_FORMAT(v.data_venda, '%Y-%m') as mes_ano,
        MONTHNAME(v.data_venda) as nome_mes,
        MONTH(v.data_venda) as mes, 
        COUNT(*) as total, 
        SUM(v.valor_total) as faturamento
    FROM vendas v
    WHERE v.data_venda >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH) 
        AND v.user_id = " . $_SESSION['usuario_id'] . "
    GROUP BY DATE_FORMAT(v.data_venda, '%Y-%m'), MONTH(v.data_venda), MONTHNAME(v.data_venda)
    ORDER BY v.data_venda
");

$formas_pagamento = $conexao->query("
    SELECT 
        CASE 
            WHEN v.forma_pagamento IS NULL OR v.forma_pagamento = '' THEN 'dinheiro'
            ELSE v.forma_pagamento
        END as forma_pagamento, 
        COUNT(*) as total
    FROM vendas v
    WHERE v.user_id = " . $_SESSION['usuario_id'] . "
    GROUP BY 
        CASE 
            WHEN v.forma_pagamento IS NULL OR v.forma_pagamento = '' THEN 'dinheiro'
            ELSE v.forma_pagamento
        END
    ORDER BY total DESC
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
                        <div id="alerta-<?php echo $alerta['id']; ?>" class="alert alert-<?php echo $alerta['tipo'] == 'estoque_baixo' ? 'warning' : 'info'; ?>">
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
                    <div class="stat-card clientes-cadastrados">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_clientes']; ?></h3>
                            <p>Clientes Cadastrados</p>
                        </div>
                    </div>
                    
                    <div class="stat-card produtos-cadastrados">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_produtos']; ?></h3>
                            <p>Produtos Cadastrados</p>
                        </div>
                    </div>
                    
                    <div class="stat-card vendas-semanais">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['vendas_mes']; ?></h3>
                            <p>Vendas Este Mês</p>
                        </div>
                    </div>
                    
                    <div class="stat-card total-bruto">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo formatarMoeda($stats['faturamento_mes']); ?></h3>
                            <p>Faturamento do Mês</p>
                        </div>
                    </div>
                    
                    <!-- Cards adicionais para completar o visual -->
                    <div class="stat-card vendas-diarias">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['vendas_hoje'] ?? '0'; ?></h3>
                            <p>Vendas Hoje</p>
                        </div>
                    </div>
                    
                    <div class="stat-card produtos-vendidos">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['produtos_vendidos_mes'] ?? '0'; ?></h3>
                            <p>Produtos Vendidos</p>
                        </div>
                    </div>
                    
                    <div class="stat-card novos-clientes">
                        <div class="stat-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['novos_clientes_mes'] ?? '0'; ?></h3>
                            <p>Novos Clientes</p>
                        </div>
                    </div>
                    
                    <div class="stat-card total-liquido">
                        <div class="stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo formatarMoeda($stats['lucro_mes'] ?? 0); ?></h3>
                            <p>Lucro Líquido</p>
                        </div>
                    </div>
                </div>
            
            <!-- Gráficos e Tabelas -->
            <section class="dashboard-content">
                <div class="content-grid">
                    <!-- Gráfico de Vendas -->
                    <div class="card chart-card vendas-chart">
                        <div class="card-header">
                            <h3>Vendas dos Últimos 6 Meses</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gráfico de Formas de Pagamento -->
                    <div class="card chart-card pagamentos-chart">
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
                        <div class="table-responsive">
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
                                            <span>
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
                    // Buscar o elemento alerta pelo ID específico
                    const alertElement = document.getElementById(`alerta-${alertaId}`);
                    if (alertElement) {
                        alertElement.style.opacity = '0';
                        alertElement.style.transition = 'opacity 0.3s ease';
                        setTimeout(() => {
                            alertElement.remove();
                            // Se não há mais alertas, esconder a seção
                            const alertsContainer = document.querySelector('.alert-list');
                            if (alertsContainer && alertsContainer.children.length === 0) {
                                const alertCard = alertsContainer.closest('.card');
                                if (alertCard) alertCard.style.display = 'none';
                            }
                        }, 300);
                    }
                } else {
                    console.error('Erro ao marcar alerta como lido:', data.message);
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
            });
        }
        
        // Dados para os gráficos
        const salesData = {
            labels: [
                <?php 
                $vendas_labels = [];
                $vendas_data = [];
                // Reset do ponteiro do resultado
                $vendas_por_mes->data_seek(0);
                while ($row = $vendas_por_mes->fetch_assoc()) {
                    $vendas_labels[] = '"' . $row['nome_mes'] . '"';
                    $vendas_data[] = $row['total'];
                }
                echo implode(', ', $vendas_labels);
                ?>
            ],
            datasets: [{
                label: 'Vendas',
                data: [<?php echo implode(', ', $vendas_data); ?>],
                backgroundColor: '#4FC3F7',
                borderColor: '#29B6F6',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false
            }]
        };
        
        const paymentData = {
            labels: [
                <?php 
                $payment_labels = [];
                $payment_data = [];
                $payment_colors = [
                    'dinheiro' => '#4CAF50',
                    'cartao_debito' => '#2196F3',
                    'cartao_credito' => '#FF9800',
                    'pix' => '#9C27B0'
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
                borderWidth: 2,
                borderColor: '#fff',
                hoverBorderWidth: 3,
                hoverBorderColor: '#fff'
            }]
        };
        
        // Criar gráfico de vendas
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'bar',
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
                            color: 'rgba(0,0,0,0.05)',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: '#666'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#666'
                        }
                    }
                },
                elements: {
                    bar: {
                        borderRadius: 4
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
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 12
                            },
                            color: '#666'
                        }
                    }
                },
                elements: {
                    arc: {
                        borderRadius: 4
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
        
        .vendas-chart {
            grid-column: span 2;
            grid-row: 1;
        }
        
        .pagamentos-chart {
            grid-column: span 2;
            grid-row: 2;
        }
        
        .table-card {
            grid-column: span 2;
            grid-row: 3;
        }
        
        .alert-card {
            grid-column: span 2;
            grid-row: 4;
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
            background: white !important;
        }
        
        .alert-info {
            color: #333 !important;
            background: white !important;
        }
        
        .alert-info strong {
            color: #333 !important;
            background: transparent !important;
        }
        
        .alert-info span {
            color: #333 !important;
            background: transparent !important;
        }
        
        /* Garantir que produtos com baixo estoque tenham texto preto */
        .alert-card .alert-item .alert-info strong,
        .alert-card .alert-item .alert-info span {
            color: #333 !important;
            background: white !important;
        }
        
        /* Classes removidas para evitar formatação colorida nos nomes dos produtos */
        
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
            
            .vendas-chart,
            .pagamentos-chart,
            .table-card,
            .alert-card {
                grid-column: span 1;
                grid-row: auto;
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