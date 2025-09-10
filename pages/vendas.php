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
        $stmt = $conexao->prepare("SELECT nome, estoque FROM produtos WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id_produto, $_SESSION['usuario_id']);
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
                $stmt = $conexao->prepare("INSERT INTO vendas (user_id, id_cliente, id_produto, quantidade) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiii", $_SESSION['usuario_id'], $id_cliente, $id_produto, $quantidade);
                $stmt->execute();
                
                // Atualizar estoque
                $novo_estoque = $produto['estoque'] - $quantidade;
                $stmt = $conexao->prepare("UPDATE produtos SET estoque = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("iii", $novo_estoque, $id_produto, $_SESSION['usuario_id']);
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
$clientes = $conexao->query("SELECT id, nome FROM clientes WHERE user_id = " . $_SESSION['usuario_id'] . " ORDER BY nome");

// Buscar produtos com estoque para o select
$produtos = $conexao->query("SELECT id, nome, valor, estoque FROM produtos WHERE estoque > 0 AND user_id = " . $_SESSION['usuario_id'] . " ORDER BY nome");

// Buscar vendas com filtros
$filtro_periodo = $_GET['periodo'] ?? '';
$busca_cliente = $_GET['cliente'] ?? '';
$where_conditions = [];

// Sempre filtrar por user_id
$where_conditions[] = "v.user_id = " . $_SESSION['usuario_id'];

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

$where = 'WHERE ' . implode(' AND ', $where_conditions);

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
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Header -->
            <?php include '../includes/header.php'; ?>
            
            <!-- Mensagens -->
            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>
            
            <!-- Estatísticas -->
            <div class="stats-grid">
                <div class="stat-card vendas-totais">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_vendas'] ?? 0); ?></h3>
                        <p>Total de Vendas</p>
                    </div>
                </div>
                
                <div class="stat-card faturamento-total">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>R$ <?php echo number_format($stats['faturamento'] ?? 0, 2, ',', '.'); ?></h3>
                        <p>Faturamento</p>
                    </div>
                </div>
                
                <div class="stat-card produtos-vendidos">
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
                    <button type="button" class="btn btn-secondary modal-close">Cancelar</button>
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
        
        // Função de fechamento removida - agora gerenciada pelo ModalManager global
        
        // Evento para abrir modal
        document.querySelector('[data-modal="modalVenda"]').addEventListener('click', function() {
            const modal = document.getElementById('modalVenda');
            if (window.ModalManager) {
                window.ModalManager.open(modal);
            }
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
        .sales-management-section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
            margin-bottom: 1.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-card.vendas-totais {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        
        .stat-card.vendas-totais .stat-icon {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .stat-card.vendas-totais .stat-content h3,
        .stat-card.vendas-totais .stat-content p {
            color: white;
        }
        
        .stat-card.faturamento-total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        
        .stat-card.faturamento-total .stat-icon {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .stat-card.faturamento-total .stat-content h3,
        .stat-card.faturamento-total .stat-content p {
            color: white;
        }
        
        .stat-card.produtos-vendidos {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        
        .stat-card.produtos-vendidos .stat-icon {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .stat-card.produtos-vendidos .stat-content h3,
        .stat-card.produtos-vendidos .stat-content p {
            color: white;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }
        
        .stat-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
            color: #212529;
        }
        
        .stat-content p {
            margin: 0;
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .search-form {
            display: flex;
        }
        
        .search-group {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            background: rgba(255, 255, 255, 0.9);
            padding: 0.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .search-group::before {
            content: "🔍";
            font-size: 1.1rem;
            opacity: 0.7;
        }
        
        .search-group select {
            min-width: 180px;
            border: none;
            background: transparent;
            padding: 0.5rem;
        }
        
        .search-group input {
            min-width: 250px;
            border: none;
            background: transparent;
            padding: 0.5rem;
        }
        
        .search-group select:focus,
        .search-group input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.3);
        }
        
        .produto-info {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1rem 0;
            border: 1px solid rgba(102, 126, 234, 0.2);
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        }
        
        .info-item:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #5a67d8;
            font-size: 0.95rem;
        }
        
        .info-value {
            font-weight: 700;
            color: #2d3748;
            font-size: 1rem;
        }
        
        .total-venda-preview {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(25, 135, 84, 0.1));
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1rem 0;
            text-align: center;
            border: 2px solid rgba(40, 167, 69, 0.3);
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 20px rgba(40, 167, 69, 0.1);
            animation: slideInUp 0.3s ease;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .total-label {
            font-size: 1rem;
            color: #28a745;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .total-value {
            font-size: 2rem;
            font-weight: 700;
            color: #28a745;
            text-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
        }
        
        .total-venda {
            font-weight: 700;
            color: #28a745;
            font-size: 1.1rem;
        }
        
        .alert {
            padding: 1.2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: slideInDown 0.3s ease;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(212, 237, 218, 0.9), rgba(195, 230, 203, 0.9));
            color: #155724;
            border-color: rgba(195, 230, 203, 0.8);
        }
        
        .alert-error {
            background: linear-gradient(135deg, rgba(248, 215, 218, 0.9), rgba(245, 198, 203, 0.9));
            color: #721c24;
            border-color: rgba(245, 198, 203, 0.8);
        }
        
        /* Tabela */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table thead th {
            padding: 1.2rem;
            font-weight: 600;
            text-align: left;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            transform: scale(1.01);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .table tbody td {
            padding: 1rem 1.2rem;
            color: #2d3748;
            font-size: 0.95rem;
        }
        
        /* Botões */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid rgba(102, 126, 234, 0.1);
        }
        
        .text-center {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 2rem;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }
        
        .modal.show {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            animation: fadeIn 0.3s ease;
        }
        
        .modal.active {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            margin: 2rem;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInScale 0.3s ease;
        }
        
        @keyframes slideInScale {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(-50px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        .modal-content h2 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin: 0;
            padding: 1.5rem 2rem;
            border-radius: 16px 16px 0 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .modal-content .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
        }
        
        .modal-content .close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        .modal-content form {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .stat-card {
                padding: 1.5rem;
                gap: 1rem;
            }
            
            .stat-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .stat-content h3 {
                font-size: 1.8rem;
            }
            
            .page-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
                padding: 1rem;
            }
            
            .search-group {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .search-group select,
            .search-group input {
                min-width: auto;
                width: 100%;
            }
            
            .table-container {
                overflow-x: auto;
                border-radius: 12px;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }
            
            .btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .modal-content {
                margin: 1rem;
                width: calc(100% - 2rem);
            }
            
            .modal-content h2 {
                padding: 1rem 1.5rem;
                font-size: 1.3rem;
            }
            
            .modal-content form {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .stat-card {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.3rem;
            }
            
            .stat-content h3 {
                font-size: 1.5rem;
            }
            
            .page-actions {
                padding: 0.75rem;
            }
            
            .search-group {
                padding: 0.4rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.5rem 0.3rem;
                font-size: 0.8rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
            
            .modal-content {
                margin: 0.5rem;
                width: calc(100% - 1rem);
                max-height: 95vh;
            }
            
            .modal-content h2 {
                padding: 0.75rem 1rem;
                font-size: 1.2rem;
            }
            
            .modal-content form {
                padding: 1rem;
            }
            
            .produto-info,
            .total-venda-preview {
                padding: 1rem;
            }
        }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        SidebarManager.init();
    });
    </script>
</body>
</html>