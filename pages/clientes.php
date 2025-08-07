<?php
// Página de Clientes - Sistema GERE TECH
// Gerenciamento de clientes

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
        $cpf = trim($_POST['cpf']);
        $telefone = trim($_POST['telefone']);
        $email = trim($_POST['email']);
        
        if (empty($nome) || empty($cpf)) {
            $mensagem = 'Nome e CPF são obrigatórios.';
            $tipo_mensagem = 'error';
        } else {
            // Verificar se CPF já existe
            $stmt = $conexao->prepare("SELECT id FROM clientes WHERE cpf = ?");
            $stmt->bind_param("s", $cpf);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $mensagem = 'CPF já cadastrado no sistema.';
                $tipo_mensagem = 'error';
            } else {
                // Inserir cliente
                $stmt = $conexao->prepare("INSERT INTO clientes (nome, cpf, telefone, email) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nome, $cpf, $telefone, $email);
                
                if ($stmt->execute()) {
                    $mensagem = 'Cliente cadastrado com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao cadastrar cliente.';
                    $tipo_mensagem = 'error';
                }
            }
            $stmt->close();
        }
    }
    
    if ($acao === 'editar') {
        $id = $_POST['id'];
        $nome = trim($_POST['nome']);
        $cpf = trim($_POST['cpf']);
        $telefone = trim($_POST['telefone']);
        $email = trim($_POST['email']);
        
        if (empty($nome) || empty($cpf)) {
            $mensagem = 'Nome e CPF são obrigatórios.';
            $tipo_mensagem = 'error';
        } else {
            $stmt = $conexao->prepare("UPDATE clientes SET nome = ?, cpf = ?, telefone = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nome, $cpf, $telefone, $email, $id);
            
            if ($stmt->execute()) {
                $mensagem = 'Cliente atualizado com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao atualizar cliente.';
                $tipo_mensagem = 'error';
            }
            $stmt->close();
        }
    }
    
    if ($acao === 'excluir') {
        $id = $_POST['id'];
        
        // Verificar se cliente tem vendas
        $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM vendas WHERE id_cliente = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $vendas = $resultado->fetch_assoc()['total'];
        
        if ($vendas > 0) {
            $mensagem = 'Não é possível excluir cliente com vendas registradas.';
            $tipo_mensagem = 'error';
        } else {
            $stmt = $conexao->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $mensagem = 'Cliente excluído com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao excluir cliente.';
                $tipo_mensagem = 'error';
            }
        }
        $stmt->close();
    }
}

// Buscar clientes
$busca = $_GET['busca'] ?? '';
$where = '';
if ($busca) {
    $where = "WHERE nome LIKE '%$busca%' OR cpf LIKE '%$busca%' OR email LIKE '%$busca%'";
}

$clientes = $conexao->query("SELECT * FROM clientes $where ORDER BY nome");

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - GERE TECH</title>
    
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
                        <a href="clientes.php" class="active">
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
                    <h1>Gerenciar Clientes</h1>
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
                    <button class="btn btn-primary" data-modal="modalCliente">
                        <i class="fas fa-plus"></i>
                        Novo Cliente
                    </button>
                </div>
                
                <div class="actions-right">
                    <form method="GET" class="search-form">
                        <div class="search-group">
                            <input 
                                type="text" 
                                name="busca" 
                                placeholder="Buscar por nome, CPF ou email..."
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
            
            <!-- Tabela de Clientes -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($clientes->num_rows > 0): ?>
                            <?php while ($cliente = $clientes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $cliente['id']; ?></td>
                                    <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['cpf']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                    <td>
                                        <button 
                                            class="btn btn-sm btn-secondary"
                                            onclick="editarCliente(<?php echo htmlspecialchars(json_encode($cliente)); ?>)"
                                            title="Editar"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button 
                                            class="btn btn-sm btn-danger"
                                            onclick="excluirCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nome']); ?>')"
                                            title="Excluir"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <?php if ($busca): ?>
                                        Nenhum cliente encontrado para "<?php echo htmlspecialchars($busca); ?>"
                                    <?php else: ?>
                                        Nenhum cliente cadastrado
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Modal de Cliente -->
    <div id="modalCliente" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Novo Cliente</h2>
            
            <form id="formCliente" method="POST">
                <input type="hidden" name="acao" id="acao" value="cadastrar">
                <input type="hidden" name="id" id="clienteId">
                
                <div class="form-group">
                    <label for="nome" class="form-label">Nome *</label>
                    <input type="text" id="nome" name="nome" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="cpf" class="form-label">CPF *</label>
                    <input type="text" id="cpf" name="cpf" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" id="telefone" name="telefone" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control">
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
        let clienteParaExcluir = null;
        
        // Editar cliente
        function editarCliente(cliente) {
            document.getElementById('modalTitle').textContent = 'Editar Cliente';
            document.getElementById('acao').value = 'editar';
            document.getElementById('clienteId').value = cliente.id;
            document.getElementById('nome').value = cliente.nome;
            document.getElementById('cpf').value = cliente.cpf;
            document.getElementById('telefone').value = cliente.telefone || '';
            document.getElementById('email').value = cliente.email || '';
            
            document.getElementById('modalCliente').style.display = 'block';
        }
        
        // Excluir cliente
        function excluirCliente(id, nome) {
            clienteParaExcluir = id;
            document.getElementById('mensagemConfirmacao').textContent = 
                `Tem certeza que deseja excluir o cliente "${nome}"?`;
            document.getElementById('modalConfirmacao').style.display = 'block';
        }
        
        // Confirmar exclusão
        function confirmarExclusao() {
            if (clienteParaExcluir) {
                document.getElementById('idExcluir').value = clienteParaExcluir;
                document.getElementById('formExcluir').submit();
            }
        }
        
        // Fechar modais
        function fecharModal() {
            document.getElementById('modalCliente').style.display = 'none';
            limparFormulario();
        }
        
        function fecharModalConfirmacao() {
            document.getElementById('modalConfirmacao').style.display = 'none';
            clienteParaExcluir = null;
        }
        
        // Limpar formulário
        function limparFormulario() {
            document.getElementById('modalTitle').textContent = 'Novo Cliente';
            document.getElementById('acao').value = 'cadastrar';
            document.getElementById('clienteId').value = '';
            document.getElementById('formCliente').reset();
        }
        
        // Evento para abrir modal de novo cliente
        document.querySelector('[data-modal="modalCliente"]').addEventListener('click', function() {
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
        /* Estilos específicos da página de clientes */
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
        
        .search-group input {
            min-width: 300px;
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