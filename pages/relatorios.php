<?php
// Página de Relatórios - Sistema GERE TECH
// Relatórios simples de vendas, produtos e clientes

session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../php/conexao.php';

// Função para gerar relatório de vendas por período
function getRelatorioVendas($conexao, $data_inicio = null, $data_fim = null) {
    $user_id = $_SESSION['usuario_id'];
    $sql = "SELECT v.id, c.nome as cliente, p.nome as produto, v.quantidade, 
                   p.valor, (v.quantidade * p.valor) as total, v.data_venda
            FROM vendas v 
            JOIN clientes c ON v.id_cliente = c.id 
            JOIN produtos p ON v.id_produto = p.id
            WHERE v.user_id = ?";
    
    if ($data_inicio && $data_fim) {
        $sql .= " AND DATE(v.data_venda) BETWEEN ? AND ?";
        $sql .= " ORDER BY v.data_venda DESC";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("iss", $user_id, $data_inicio, $data_fim);
    } else {
        $sql .= " ORDER BY v.data_venda DESC";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $user_id);
    }
    
    $stmt->execute();
    return $stmt->get_result();
}

// Função para produtos mais vendidos
function getProdutosMaisVendidos($conexao, $limite = 10) {
    $user_id = $_SESSION['usuario_id'];
    $sql = "SELECT p.nome, SUM(v.quantidade) as total_vendido, 
                   SUM(v.quantidade * p.valor) as faturamento
            FROM vendas v 
            JOIN produtos p ON v.id_produto = p.id 
            WHERE v.user_id = ?
            GROUP BY p.id, p.nome 
            ORDER BY total_vendido DESC 
            LIMIT ?";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limite);
    $stmt->execute();
    return $stmt->get_result();
}

// Função para relatório de clientes
function getRelatorioClientes($conexao) {
    $user_id = $_SESSION['usuario_id'];
    $sql = "SELECT c.nome, c.cpf, c.telefone, c.email,
                   COUNT(v.id) as total_compras,
                   COALESCE(SUM(v.quantidade * p.valor), 0) as total_gasto
            FROM clientes c 
            LEFT JOIN vendas v ON c.id = v.id_cliente AND v.user_id = ?
            LEFT JOIN produtos p ON v.id_produto = p.id 
            WHERE c.user_id = ?
            GROUP BY c.id 
            ORDER BY total_gasto DESC";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Processar filtros
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$tipo_relatorio = $_GET['tipo'] ?? 'vendas';

// Buscar dados baseado no tipo de relatório
switch ($tipo_relatorio) {
    case 'produtos':
        $dados = getProdutosMaisVendidos($conexao);
        break;
    case 'clientes':
        $dados = getRelatorioClientes($conexao);
        break;
    default:
        $dados = getRelatorioVendas($conexao, $data_inicio, $data_fim);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - GERE TECH</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .relatorios-container {
            padding: 2rem;
        }
        
        .filtros-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filtros-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }
        
        .relatorio-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .tab-button {
            padding: 1rem 2rem;
            border: none;
            background: #f8f9fa;
            color: #666;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .tab-button.active {
            background: #667eea;
            color: white;
        }
        
        .relatorio-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .relatorio-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-export {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-export:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .relatorio-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .relatorio-table th,
        .relatorio-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .relatorio-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .relatorio-table tr:hover {
            background: #f8f9fa;
        }
        
        .total-row {
            background: #e3f2fd !important;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .relatorio-tabs {
                flex-direction: column;
            }
            
            .filtros-form {
                grid-template-columns: 1fr;
            }
            
            .relatorio-table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>
    
    <!-- Conteúdo principal -->
    <div class="main-content">
        <!-- Header -->
        <?php include '../includes/header.php'; ?>
        
        <div class="relatorios-container">
            <h1><i class="fas fa-chart-bar"></i> Relatórios</h1>
            
            <!-- Abas de relatórios -->
            <div class="relatorio-tabs">
                <a href="?tipo=vendas" class="tab-button <?php echo $tipo_relatorio === 'vendas' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Vendas
                </a>
                <a href="?tipo=produtos" class="tab-button <?php echo $tipo_relatorio === 'produtos' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Produtos Mais Vendidos
                </a>
                <a href="?tipo=clientes" class="tab-button <?php echo $tipo_relatorio === 'clientes' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Clientes
                </a>
            </div>
            
            <!-- Filtros (apenas para vendas) -->
            <?php if ($tipo_relatorio === 'vendas'): ?>
            <div class="filtros-container">
                <h3><i class="fas fa-filter"></i> Filtros</h3>
                <form method="GET" class="filtros-form">
                    <input type="hidden" name="tipo" value="vendas">
                    
                    <div class="form-group">
                        <label>Data Início:</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?php echo $data_inicio; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Data Fim:</label>
                        <input type="date" name="data_fim" class="form-control" value="<?php echo $data_fim; ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Relatório -->
            <div class="relatorio-card">
                <div class="relatorio-header">
                    <h2>
                        <?php 
                        switch ($tipo_relatorio) {
                            case 'produtos':
                                echo '<i class="fas fa-box"></i> Produtos Mais Vendidos';
                                break;
                            case 'clientes':
                                echo '<i class="fas fa-users"></i> Relatório de Clientes';
                                break;
                            default:
                                echo '<i class="fas fa-shopping-cart"></i> Relatório de Vendas';
                        }
                        ?>
                    </h2>
                    <button onclick="exportarPDF()" class="btn-export">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </button>
                </div>
                
                <div class="relatorio-content">
                    <?php if ($tipo_relatorio === 'vendas'): ?>
                        <!-- Relatório de Vendas -->
                        <table class="relatorio-table" id="tabelaRelatorio">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Valor Unit.</th>
                                    <th>Total</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_geral = 0;
                                while ($venda = $dados->fetch_assoc()): 
                                    $total_geral += $venda['total'];
                                ?>
                                <tr>
                                    <td><?php echo $venda['id']; ?></td>
                                    <td><?php echo htmlspecialchars($venda['cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($venda['produto']); ?></td>
                                    <td><?php echo $venda['quantidade']; ?></td>
                                    <td>R$ <?php echo number_format($venda['valor'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($venda['total'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <tr class="total-row">
                                    <td colspan="5"><strong>TOTAL GERAL:</strong></td>
                                    <td><strong>R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></strong></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    
                    <?php elseif ($tipo_relatorio === 'produtos'): ?>
                        <!-- Produtos Mais Vendidos -->
                        <table class="relatorio-table" id="tabelaRelatorio">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade Vendida</th>
                                    <th>Faturamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($produto = $dados->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                    <td><?php echo $produto['total_vendido']; ?> unidades</td>
                                    <td>R$ <?php echo number_format($produto['faturamento'], 2, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    
                    <?php else: ?>
                        <!-- Relatório de Clientes -->
                        <table class="relatorio-table" id="tabelaRelatorio">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>Telefone</th>
                                    <th>Email</th>
                                    <th>Total de Compras</th>
                                    <th>Total Gasto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($cliente = $dados->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['cpf']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                    <td><?php echo $cliente['total_compras']; ?> compras</td>
                                    <td>R$ <?php echo number_format($cliente['total_gasto'], 2, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="../js/main.js"></script>
    
    <script>
        // Função para exportar PDF (simulação)
        function exportarPDF() {
            // Em uma implementação real, você usaria uma biblioteca como jsPDF
            // Por enquanto, vamos simular com window.print()
            const printWindow = window.open('', '_blank');
            const tabela = document.getElementById('tabelaRelatorio').outerHTML;
            
            printWindow.document.write(`
                <html>
                <head>
                    <title>Relatório - GERE TECH</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .total-row { background-color: #e3f2fd; font-weight: bold; }
                        h1 { color: #667eea; text-align: center; }
                    </style>
                </head>
                <body>
                    <h1>GERE TECH - Relatório</h1>
                    <p>Data de geração: ${new Date().toLocaleDateString('pt-BR')}</p>
                    ${tabela}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }
        
        // Adicionar funcionalidade de busca na tabela
        function adicionarBusca() {
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Buscar na tabela...';
            searchInput.className = 'form-control';
            searchInput.style.marginBottom = '1rem';
            
            const tabela = document.getElementById('tabelaRelatorio');
            tabela.parentNode.insertBefore(searchInput, tabela);
            
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const rows = tabela.getElementsByTagName('tr');
                
                for (let i = 1; i < rows.length; i++) {
                    const row = rows[i];
                    const cells = row.getElementsByTagName('td');
                    let found = false;
                    
                    for (let j = 0; j < cells.length; j++) {
                        if (cells[j].textContent.toLowerCase().includes(filter)) {
                            found = true;
                            break;
                        }
                    }
                    
                    row.style.display = found ? '' : 'none';
                }
            });
        }
        
        // Inicializar busca quando a página carregar
        document.addEventListener('DOMContentLoaded', adicionarBusca);
    </script>
</body>
</html>