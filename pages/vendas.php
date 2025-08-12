<?php
// Página de Vendas - Sistema GERE TECH
// Gerenciamento de vendas

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../php/conexao.php';

$mensagem = '';
$tipo_mensagem = '';

// Processar nova venda
if ($_POST && isset($_POST['acao']) && $_POST['acao'] === 'nova_venda') {
    $id_cliente = (int)$_POST['id_cliente'];
    $id_produto = (int)$_POST['id_produto'];
    $quantidade = (int)$_POST['quantidade'];
    
    if ($id_cliente <= 0 || $id_produto <= 0 || $quantidade <= 0) {
        $mensagem = 'Todos os campos são obrigatórios e devem ser válidos.';
        $tipo_mensagem = 'error';
    } else {
        // Verificar estoque disponível
        $stmt = $conexao->prepare("SELECT nome, estoque FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id_produto);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $produto = $resultado->fetch_assoc();
        
        if (!$produto) {
            $mensagem = 'Produto não encontrado.';
            $tipo_mensagem = 'error';
        } elseif ($produto['estoque'] < $quantidade) {
            $mensagem = "Estoque insuficiente. Disponível: {$produto['estoque']} unidades.";
            $tipo_mensagem = 'error';
        } else {
            // Iniciar transação
            $conexao->begin_transaction();
            
            try {
                // Inserir venda
                $stmt = $conexao->prepare("INSERT INTO vendas (id_cliente, id_produto, quantidade) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $id_cliente, $id_produto, $quantidade);
                $stmt->execute();
                
                // Atualizar estoque
                $novo_estoque = $produto['estoque'] - $quantidade;
                $stmt = $conexao->prepare("UPDATE produtos SET estoque = ? WHERE id = ?");
                $stmt->bind_param("ii", $novo_estoque, $id_produto);
                $stmt->execute();
                
                $conexao->commit();
                $mensagem = 'Venda registrada com sucesso!';
                $tipo_mensagem = 'success';
                
            } catch (Exception $e) {
                $conexao->rollback();
                $mensagem = 'Erro ao registrar venda.';
                $tipo_mensagem = 'error';
            }
        }
        $stmt->close();
    }
}

// Buscar clientes para o select
$clientes = $conexao->query("SELECT id, nome FROM clientes ORDER BY nome");

// Buscar produtos com estoque para o select
$produtos = $conexao->query("SELECT id, nome, valor, estoque FROM produtos WHERE estoque > 0 ORDER BY nome");

// Buscar vendas com filtros
$filtro_periodo = $_GET['periodo'] ?? '';
$busca_cliente = $_GET['cliente'] ?? '';
$where_conditions = [];

if ($filtro_periodo) {
    switch ($filtro_periodo) {
        case 'hoje':
            $where_conditions[] = "DATE(v.data_venda) = CURDATE()";
            break;
        case 'semana':
            $where_conditions[] = "v.data_venda >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'mes':
            $where_conditions[] = "v.data_venda >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
}

if ($busca_cliente) {
    $where_conditions[] = "c.nome LIKE '%$busca_cliente%'";
}

$where = '';
if (!empty($where_conditions)) {
    $where = 'WHERE ' . implode(' AND ', $where_conditions);
}

$vendas = $conexao->query("
    SELECT 
        v.id,
        v.quantidade,
        v.data_venda,
        c.nome as cliente_nome,
        p.nome as produto_nome,
        p.valor as produto_valor,
        (v.quantidade * p.valor) as total
    FROM vendas v
    JOIN clientes c ON v.id_cliente = c.id
    JOIN produtos p ON v.id_produto = p.id
    $where
    ORDER BY v.data_venda DESC
    LIMIT 100
");

// Estatísticas do período
$stats = $conexao->query("
    SELECT 
        COUNT(*) as total_vendas,
        SUM(v.quantidade * p.valor) as faturamento,
        SUM(v.quantidade) as produtos_vendidos
    FROM vendas v
    JOIN produtos p ON v.id_produto = p.id
    $where
")->fetch_assoc();

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas - GERE TECH</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">GT</div>
                <h3>GERE TECH</h3>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Visão Geral</span>
                        </a>
                    </li>
                    <li>
                        <a href="produtos.php">
                            <i class="fas fa-box"></i>
                            <span>Produtos</span>
                        </a>
                    </li>
                    <li>
                        <a href="clientes.php">
                            <i class="fas fa-users"></i>
                            <span>Clientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="vendas.php" class="active">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Vendas</span>
                        </a>
                    </li>
                    <li>
                        <a href="configuracoes.php">
                            <i class="fas fa-cog"></i>
                            <span>Configurações</span>
                        </a>
                    </li>
                    <li>
                        <a href="../php/logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Sair</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="header-left">
                    <button id="menuToggle" class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Gerenciar Vendas</h1>
                </div>
                
                <div class="header-right">
                    <div class="user-info">
                        <span><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Mensagens -->
            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>
            
            <!-- Estatísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_vendas'] ?? 0); ?></h3>
                        <p>Total de Vendas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>R$ <?php echo number_format($stats['faturamento'] ?? 0, 2, ',', '.'); ?></h3>
                        <p>Faturamento</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['produtos_vendidos'] ?? 0); ?></h3>
                        <p>Produtos Vendidos</p>
                    </div>
                </div>
            </div>
            
            <!-- Ações -->
            <div class="page-actions">
                <div class="actions-left">
                    <button class="btn btn-primary" data-modal="modalVenda">
                        <i class="fas fa-plus"></i>
                        Nova Venda
                    </button>
                </div>
                
                <div class="actions-right">
                    <form method="GET" class="search-form">
                        <div class="search-group">
                            <select name="periodo" class="form-control">
                                <option value="">Todos os períodos</option>
                                <option value="hoje" <?php echo $filtro_periodo === 'hoje' ? 'selected' : ''; ?>>Hoje</option>
                                <option value="semana" <?php echo $filtro_periodo === 'semana' ? 'selected' : ''; ?>>Última semana</option>
                                <option value="mes" <?php echo $filtro_periodo === 'mes' ? 'selected' : ''; ?>>Último mês</option>
                            </select>
                            
                            <input 
                                type="text" 
                                name="cliente" 
                                placeholder="Buscar por cliente..."
                                value="<?php echo htmlspecialchars($busca_cliente); ?>"
                                class="form-control"
                            >
                            
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tabela de Vendas -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data/Hora</th>
                            <th>Cliente</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Valor Unit.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($vendas->num_rows > 0): ?>
                            <?php while ($venda = $vendas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $venda['id']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></td>
                                    <td><?php echo htmlspecialchars($venda['cliente_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($venda['produto_nome']); ?></td>
                                    <td><?php echo $venda['quantidade']; ?></td>
                                    <td>R$ <?php echo number_format($venda['produto_valor'], 2, ',', '.'); ?></td>
                                    <td class="total-venda">R$ <?php echo number_format($venda['total'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <?php if ($filtro_periodo || $busca_cliente): ?>
                                        Nenhuma venda encontrada com os filtros aplicados
                                    <?php else: ?>
                                        Nenhuma venda registrada
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Modal de Nova Venda -->
    <div id="modalVenda" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Nova Venda</h2>
            
            <form id="formVenda" method="POST">
                <input type="hidden" name="acao" value="nova_venda">
                
                <div class="form-group">
                    <label for="id_cliente" class="form-label">Cliente *</label>
                    <select id="id_cliente" name="id_cliente" class="form-control" required>
                        <option value="">Selecione um cliente</option>
                        <?php 
                        $clientes->data_seek(0); // Reset pointer
                        while ($cliente = $clientes->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $cliente['id']; ?>">
                                <?php echo htmlspecialchars($cliente['nome']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="id_produto" class="form-label">Produto *</label>
                    <select id="id_produto" name="id_produto" class="form-control" required onchange="atualizarInfoProduto()">
                        <option value="">Selecione um produto</option>
                        <?php 
                        $produtos->data_seek(0); // Reset pointer
                        while ($produto = $produtos->fetch_assoc()): 
                        ?>
                            <option 
                                value="<?php echo $produto['id']; ?>"
                                data-valor="<?php echo $produto['valor']; ?>"
                                data-estoque="<?php echo $produto['estoque']; ?>"
                            >
                                <?php echo htmlspecialchars($produto['nome']); ?> 
                                (Estoque: <?php echo $produto['estoque']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="produto-info" id="produtoInfo" style="display: none;">
                    <div class="info-item">
                        <span class="info-label">Valor unitário:</span>
                        <span class="info-value" id="valorUnitario">R$ 0,00</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estoque disponível:</span>
                        <span class="info-value" id="estoqueDisponivel">0</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="quantidade" class="form-label">Quantidade *</label>
                    <input 
                        type="number" 
                        id="quantidade" 
                        name="quantidade" 
                        class="form-control" 
                        min="1" 
                        required 
                        onchange="calcularTotal()"
                    >
                </div>
                
                <div class="total-venda-preview" id="totalPreview" style="display: none;">
                    <div class="total-label">Total da venda:</div>
                    <div class="total-value" id="totalVenda">R$ 0,00</div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Venda</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="../js/main.js"></script>
    
    <script>
        let produtoSelecionado = null;
        
        // Atualizar informações do produto
        function atualizarInfoProduto() {
            const select = document.getElementById('id_produto');
            const option = select.options[select.selectedIndex];
            const produtoInfo = document.getElementById('produtoInfo');
            
            if (option.value) {
                produtoSelecionado = {
                    valor: parseFloat(option.dataset.valor),
                    estoque: parseInt(option.dataset.estoque)
                };
                
                document.getElementById('valorUnitario').textContent = 
                    'R$ ' + produtoSelecionado.valor.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    
                document.getElementById('estoqueDisponivel').textContent = produtoSelecionado.estoque;
                
                // Atualizar limite da quantidade
                const quantidadeInput = document.getElementById('quantidade');
                quantidadeInput.max = produtoSelecionado.estoque;
                quantidadeInput.value = '';
                
                produtoInfo.style.display = 'block';
                document.getElementById('totalPreview').style.display = 'none';
            } else {
                produtoSelecionado = null;
                produtoInfo.style.display = 'none';
                document.getElementById('totalPreview').style.display = 'none';
                document.getElementById('quantidade').value = '';
            }
        }
        
        // Calcular total da venda
        function calcularTotal() {
            const quantidade = parseInt(document.getElementById('quantidade').value);
            const totalPreview = document.getElementById('totalPreview');
            
            if (produtoSelecionado && quantidade > 0) {
                if (quantidade > produtoSelecionado.estoque) {
                    alert(`Quantidade não pode ser maior que o estoque disponível (${produtoSelecionado.estoque})`);
                    document.getElementById('quantidade').value = produtoSelecionado.estoque;
                    return;
                }
                
                const total = produtoSelecionado.valor * quantidade;
                document.getElementById('totalVenda').textContent = 
                    'R$ ' + total.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    
                totalPreview.style.display = 'block';
            } else {
                totalPreview.style.display = 'none';
            }
        }
        
        // Fechar modal
        function fecharModal() {
            document.getElementById('modalVenda').style.display = 'none';
            document.getElementById('formVenda').reset();
            document.getElementById('produtoInfo').style.display = 'none';
            document.getElementById('totalPreview').style.display = 'none';
            produtoSelecionado = null;
        }
        
        // Evento para abrir modal
        document.querySelector('[data-modal="modalVenda"]').addEventListener('click', function() {
            document.getElementById('modalVenda').style.display = 'block';
        });
        
        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
    
    <style>
        /* Estilos específicos da página de vendas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .stat-content h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }
        
        .stat-content p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }
        
        .search-form {
            display: flex;
        }
        
        .search-group {
            display: flex;
            gap: 0.5rem;
        }
        
        .search-group select {
            min-width: 180px;
        }
        
        .search-group input {
            min-width: 250px;
        }
        
        .produto-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            font-weight: 700;
            color: #333;
        }
        
        .total-venda-preview {
            background: #e8f5e8;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            text-align: center;
        }
        
        .total-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .total-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #28a745;
        }
        
        .total-venda {
            font-weight: 700;
            color: #28a745;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: opacity 0.3s;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .text-center {
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .page-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-group {
                flex-direction: column;
            }
            
            .search-group select,
            .search-group input {
                min-width: auto;
            }
            
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</body>
</html>