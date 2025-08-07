<?php
// Página de Produtos - Sistema GERE TECH
// Gerenciamento de produtos

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../php/conexao.php';

$mensagem = '';
$tipo_mensagem = '';

// Processar ações
if ($_POST) {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'cadastrar') {
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);
        $estoque = (int)$_POST['estoque'];
        
        if (empty($nome) || empty($valor) || $estoque < 0) {
            $mensagem = 'Nome, valor e estoque são obrigatórios.';
            $tipo_mensagem = 'error';
        } else {
            // Inserir produto
            $stmt = $conexao->prepare("INSERT INTO produtos (nome, descricao, valor, estoque) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssdi", $nome, $descricao, $valor, $estoque);
            
            if ($stmt->execute()) {
                $mensagem = 'Produto cadastrado com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao cadastrar produto.';
                $tipo_mensagem = 'error';
            }
            $stmt->close();
        }
    }
    
    if ($acao === 'editar') {
        $id = $_POST['id'];
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);
        $estoque = (int)$_POST['estoque'];
        
        if (empty($nome) || empty($valor) || $estoque < 0) {
            $mensagem = 'Nome, valor e estoque são obrigatórios.';
            $tipo_mensagem = 'error';
        } else {
            $stmt = $conexao->prepare("UPDATE produtos SET nome = ?, descricao = ?, valor = ?, estoque = ? WHERE id = ?");
            $stmt->bind_param("ssdii", $nome, $descricao, $valor, $estoque, $id);
            
            if ($stmt->execute()) {
                $mensagem = 'Produto atualizado com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao atualizar produto.';
                $tipo_mensagem = 'error';
            }
            $stmt->close();
        }
    }
    
    if ($acao === 'excluir') {
        $id = $_POST['id'];
        
        // Verificar se produto tem vendas
        $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM vendas WHERE id_produto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $vendas = $resultado->fetch_assoc()['total'];
        
        if ($vendas > 0) {
            $mensagem = 'Não é possível excluir produto com vendas registradas.';
            $tipo_mensagem = 'error';
        } else {
            $stmt = $conexao->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $mensagem = 'Produto excluído com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao excluir produto.';
                $tipo_mensagem = 'error';
            }
        }
        $stmt->close();
    }
}

// Buscar produtos
$busca = $_GET['busca'] ?? '';
$filtro_estoque = $_GET['filtro_estoque'] ?? '';
$where_conditions = [];

if ($busca) {
    $where_conditions[] = "(nome LIKE '%$busca%' OR descricao LIKE '%$busca%')";
}

if ($filtro_estoque === 'baixo') {
    $where_conditions[] = "estoque <= 5";
} elseif ($filtro_estoque === 'zero') {
    $where_conditions[] = "estoque = 0";
}

$where = '';
if (!empty($where_conditions)) {
    $where = 'WHERE ' . implode(' AND ', $where_conditions);
}

$produtos = $conexao->query("SELECT * FROM produtos $where ORDER BY nome");

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - GERE TECH</title>
    
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
                        <a href="produtos.php" class="active">
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
                        <a href="vendas.php">
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
                    <h1>Gerenciar Produtos</h1>
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
            
            <!-- Ações -->
            <div class="page-actions">
                <div class="actions-left">
                    <button class="btn btn-primary" data-modal="modalProduto">
                        <i class="fas fa-plus"></i>
                        Novo Produto
                    </button>
                </div>
                
                <div class="actions-right">
                    <form method="GET" class="search-form">
                        <div class="search-group">
                            <select name="filtro_estoque" class="form-control">
                                <option value="">Todos os produtos</option>
                                <option value="baixo" <?php echo $filtro_estoque === 'baixo' ? 'selected' : ''; ?>>Estoque baixo (≤5)</option>
                                <option value="zero" <?php echo $filtro_estoque === 'zero' ? 'selected' : ''; ?>>Sem estoque</option>
                            </select>
                            
                            <input 
                                type="text" 
                                name="busca" 
                                placeholder="Buscar por nome ou descrição..."
                                value="<?php echo htmlspecialchars($busca); ?>"
                                class="form-control"
                            >
                            
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tabela de Produtos -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Estoque</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($produtos->num_rows > 0): ?>
                            <?php while ($produto = $produtos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $produto['id']; ?></td>
                                    <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                    <td>
                                        <?php 
                                        $descricao = htmlspecialchars($produto['descricao']);
                                        echo strlen($descricao) > 50 ? substr($descricao, 0, 50) . '...' : $descricao;
                                        ?>
                                    </td>
                                    <td>R$ <?php echo number_format($produto['valor'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="estoque-badge <?php echo $produto['estoque'] <= 0 ? 'sem-estoque' : ($produto['estoque'] <= 5 ? 'baixo-estoque' : 'estoque-ok'); ?>">
                                            <?php echo $produto['estoque']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($produto['estoque'] <= 0): ?>
                                            <span class="status-badge status-danger">Sem estoque</span>
                                        <?php elseif ($produto['estoque'] <= 5): ?>
                                            <span class="status-badge status-warning">Estoque baixo</span>
                                        <?php else: ?>
                                            <span class="status-badge status-success">Disponível</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button 
                                            class="btn btn-sm btn-secondary"
                                            onclick="editarProduto(<?php echo htmlspecialchars(json_encode($produto)); ?>)"
                                            title="Editar"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button 
                                            class="btn btn-sm btn-danger"
                                            onclick="excluirProduto(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>')"
                                            title="Excluir"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <?php if ($busca || $filtro_estoque): ?>
                                        Nenhum produto encontrado com os filtros aplicados
                                    <?php else: ?>
                                        Nenhum produto cadastrado
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Modal de Produto -->
    <div id="modalProduto" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Novo Produto</h2>
            
            <form id="formProduto" method="POST">
                <input type="hidden" name="acao" id="acao" value="cadastrar">
                <input type="hidden" name="id" id="produtoId">
                
                <div class="form-group">
                    <label for="nome" class="form-label">Nome *</label>
                    <input type="text" id="nome" name="nome" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="valor" class="form-label">Valor (R$) *</label>
                        <input type="text" id="valor" name="valor" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="estoque" class="form-label">Estoque *</label>
                        <input type="number" id="estoque" name="estoque" class="form-control" min="0" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal de Confirmação -->
    <div id="modalConfirmacao" class="modal">
        <div class="modal-content">
            <h3>Confirmar Exclusão</h3>
            <p id="mensagemConfirmacao"></p>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="fecharModalConfirmacao()">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
    
    <!-- Formulário oculto para exclusão -->
    <form id="formExcluir" method="POST" style="display: none;">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id" id="idExcluir">
    </form>
    
    <!-- JavaScript -->
    <script src="../js/main.js"></script>
    
    <script>
        let produtoParaExcluir = null;
        
        // Editar produto
        function editarProduto(produto) {
            document.getElementById('modalTitle').textContent = 'Editar Produto';
            document.getElementById('acao').value = 'editar';
            document.getElementById('produtoId').value = produto.id;
            document.getElementById('nome').value = produto.nome;
            document.getElementById('descricao').value = produto.descricao || '';
            document.getElementById('valor').value = formatCurrency(produto.valor);
            document.getElementById('estoque').value = produto.estoque;
            
            document.getElementById('modalProduto').style.display = 'block';
        }
        
        // Excluir produto
        function excluirProduto(id, nome) {
            produtoParaExcluir = id;
            document.getElementById('mensagemConfirmacao').textContent = 
                `Tem certeza que deseja excluir o produto "${nome}"?`;
            document.getElementById('modalConfirmacao').style.display = 'block';
        }
        
        // Confirmar exclusão
        function confirmarExclusao() {
            if (produtoParaExcluir) {
                document.getElementById('idExcluir').value = produtoParaExcluir;
                document.getElementById('formExcluir').submit();
            }
        }
        
        // Fechar modais
        function fecharModal() {
            document.getElementById('modalProduto').style.display = 'none';
            limparFormulario();
        }
        
        function fecharModalConfirmacao() {
            document.getElementById('modalConfirmacao').style.display = 'none';
            produtoParaExcluir = null;
        }
        
        // Limpar formulário
        function limparFormulario() {
            document.getElementById('modalTitle').textContent = 'Novo Produto';
            document.getElementById('acao').value = 'cadastrar';
            document.getElementById('produtoId').value = '';
            document.getElementById('formProduto').reset();
        }
        
        // Formatar valor monetário
        function formatCurrency(value) {
            return parseFloat(value).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        // Evento para abrir modal de novo produto
        document.querySelector('[data-modal="modalProduto"]').addEventListener('click', function() {
            limparFormulario();
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
        /* Estilos específicos da página de produtos */
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .estoque-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .estoque-ok {
            background: #d4edda;
            color: #155724;
        }
        
        .baixo-estoque {
            background: #fff3cd;
            color: #856404;
        }
        
        .sem-estoque {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-danger {
            background: #f8d7da;
            color: #721c24;
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
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin-right: 0.25rem;
        }
        
        @media (max-width: 768px) {
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
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</body>
</html>