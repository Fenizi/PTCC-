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
        
        // Validações server-side
        if (empty($nome) || empty($cpf) || empty($telefone)) {
            $mensagem = 'Nome, CPF e telefone são obrigatórios.';
            $tipo_mensagem = 'error';
        } elseif (!preg_match('/^[A-Za-zÀ-ÿ\s]+$/', $nome)) {
            $mensagem = 'O nome deve conter apenas letras e espaços.';
            $tipo_mensagem = 'error';
        } elseif (count(array_filter(explode(' ', $nome), function($palavra) { return strlen(trim($palavra)) >= 2; })) < 2) {
            $mensagem = 'Digite o nome completo (nome e sobrenome).';
            $tipo_mensagem = 'error';
        } elseif (strlen(preg_replace('/[^0-9]/', '', $telefone)) < 10 || strlen(preg_replace('/[^0-9]/', '', $telefone)) > 11) {
            $mensagem = 'O telefone deve ter 10 ou 11 dígitos.';
            $tipo_mensagem = 'error';
        } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = 'E-mail inválido.';
            $tipo_mensagem = 'error';
        } else {
            // Verificar se CPF já existe para este usuário
            $stmt = $conexao->prepare("SELECT id FROM clientes WHERE cpf = ? AND user_id = ?");
            $stmt->bind_param("si", $cpf, $_SESSION['usuario_id']);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $mensagem = 'CPF já cadastrado no sistema.';
                $tipo_mensagem = 'error';
            } else {
                // Inserir cliente
                $stmt = $conexao->prepare("INSERT INTO clientes (user_id, nome, cpf, telefone, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $_SESSION['usuario_id'], $nome, $cpf, $telefone, $email);
                
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
        
        // Validações server-side
        if (empty($nome) || empty($cpf) || empty($telefone)) {
            $mensagem = 'Nome, CPF e telefone são obrigatórios.';
            $tipo_mensagem = 'error';
        } elseif (!preg_match('/^[A-Za-zÀ-ÿ\s]+$/', $nome)) {
            $mensagem = 'O nome deve conter apenas letras e espaços.';
            $tipo_mensagem = 'error';
        } elseif (count(array_filter(explode(' ', $nome), function($palavra) { return strlen(trim($palavra)) >= 2; })) < 2) {
            $mensagem = 'Digite o nome completo (nome e sobrenome).';
            $tipo_mensagem = 'error';
        } elseif (strlen(preg_replace('/[^0-9]/', '', $telefone)) < 10 || strlen(preg_replace('/[^0-9]/', '', $telefone)) > 11) {
            $mensagem = 'O telefone deve ter 10 ou 11 dígitos.';
            $tipo_mensagem = 'error';
        } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = 'E-mail inválido.';
            $tipo_mensagem = 'error';
        } else {
            $stmt = $conexao->prepare("UPDATE clientes SET nome = ?, cpf = ?, telefone = ?, email = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssssii", $nome, $cpf, $telefone, $email, $id, $_SESSION['usuario_id']);
            
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
        $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM vendas WHERE id_cliente = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $vendas = $resultado->fetch_assoc()['total'];
        
        if ($vendas > 0) {
            $mensagem = 'Não é possível excluir cliente com vendas registradas.';
            $tipo_mensagem = 'error';
        } else {
            $stmt = $conexao->prepare("DELETE FROM clientes WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
            
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
$where = "WHERE user_id = " . $_SESSION['usuario_id'];
if ($busca) {
    $where .= " AND (nome LIKE '%$busca%' OR cpf LIKE '%$busca%' OR email LIKE '%$busca%')";
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
                    <label for="nome" class="form-label">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" class="form-control" required pattern="[A-Za-zÀ-ÿ\s]+" title="Digite o nome completo (nome e sobrenome)" placeholder="Digite o nome completo">
                </div>
                
                <div class="form-group">
                    <label for="cpf" class="form-label">CPF *</label>
                    <input type="text" id="cpf" name="cpf" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="telefone" class="form-label">Telefone *</label>
                    <input type="text" id="telefone" name="telefone" class="form-control" placeholder="(11) 99999-9999" required maxlength="15">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary modal-close">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvar">Adicionar</button>
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
                <button type="button" class="btn btn-secondary modal-close">Cancelar</button>
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
            console.log('Função editarCliente chamada com:', cliente);
            
            try {
                // Preencher os campos do formulário
                document.getElementById('modalTitle').textContent = 'Editar Cliente';
                document.getElementById('acao').value = 'editar';
                document.getElementById('clienteId').value = cliente.id;
                document.getElementById('nome').value = cliente.nome;
                document.getElementById('cpf').value = cliente.cpf;
                document.getElementById('telefone').value = cliente.telefone || '';
                document.getElementById('email').value = cliente.email || '';
                document.getElementById('btnSalvar').textContent = 'Salvar';
                
                // Abrir modal
                const modal = document.getElementById('modalCliente');
                console.log('Modal encontrado:', !!modal);
                console.log('ModalManager disponível:', !!window.ModalManager);
                
                if (modal) {
                    if (window.ModalManager && typeof window.ModalManager.open === 'function') {
                        window.ModalManager.open(modal);
                        console.log('Modal de edição aberto via ModalManager');
                    } else {
                        // Fallback: abrir modal manualmente
                        modal.style.display = 'flex';
                        modal.classList.add('show', 'active');
                        document.body.classList.add('modal-open');
                        console.log('Modal de edição aberto via fallback');
                    }
                } else {
                    console.error('Modal modalCliente não encontrado');
                    alert('Erro: Modal não encontrado');
                }
            } catch (error) {
                console.error('Erro na função editarCliente:', error);
                alert('Erro ao editar cliente: ' + error.message);
            }
        }
        
        // Excluir cliente
        function excluirCliente(id, nome) {
            console.log('Função excluirCliente chamada com ID:', id, 'Nome:', nome);
            
            try {
                clienteParaExcluir = id;
                document.getElementById('mensagemConfirmacao').textContent = 
                    `Tem certeza que deseja excluir o cliente "${nome}"?`;
                    
                const modal = document.getElementById('modalConfirmacao');
                console.log('Modal de confirmação encontrado:', !!modal);
                console.log('ModalManager disponível:', !!window.ModalManager);
                
                if (modal) {
                    if (window.ModalManager && typeof window.ModalManager.open === 'function') {
                        window.ModalManager.open(modal);
                        console.log('Modal de confirmação aberto via ModalManager');
                    } else {
                        // Fallback: abrir modal manualmente
                        modal.style.display = 'flex';
                        modal.classList.add('show', 'active');
                        document.body.classList.add('modal-open');
                        console.log('Modal de confirmação aberto via fallback');
                    }
                } else {
                    console.error('Modal modalConfirmacao não encontrado');
                    alert('Erro: Modal de confirmação não encontrado');
                }
            } catch (error) {
                console.error('Erro na função excluirCliente:', error);
                alert('Erro ao excluir cliente: ' + error.message);
            }
        }
        
        // Confirmar exclusão
        function confirmarExclusao() {
            if (clienteParaExcluir) {
                document.getElementById('idExcluir').value = clienteParaExcluir;
                document.getElementById('formExcluir').submit();
            }
        }
        
        // Funções de fechamento removidas - agora gerenciadas pelo ModalManager global
        
        // Limpar formulário
        function limparFormulario() {
            document.getElementById('modalTitle').textContent = 'Novo Cliente';
            document.getElementById('acao').value = 'cadastrar';
            document.getElementById('clienteId').value = '';
            document.getElementById('btnSalvar').textContent = 'Adicionar';
            document.getElementById('formCliente').reset();
        }
        
        // Inicialização robusta dos modais
        function initializeClientModals() {
            console.log('Inicializando modais da página de clientes...');
            
            // Verificar se os elementos existem
            const novoClienteBtn = document.querySelector('[data-modal="modalCliente"]');
            const modalCliente = document.getElementById('modalCliente');
            const closeBtn = document.querySelector('#modalCliente .close');
            
            console.log('Botão Novo Cliente:', novoClienteBtn);
            console.log('Modal Cliente:', modalCliente);
            
            if (novoClienteBtn && modalCliente) {
                // Remover event listeners existentes
                novoClienteBtn.removeEventListener('click', handleNovoClienteClick);
                
                // Adicionar novo event listener
                novoClienteBtn.addEventListener('click', handleNovoClienteClick);
                console.log('Event listener adicionado ao botão Novo Cliente');
            } else {
                console.error('Elementos não encontrados:', {
                    botao: !!novoClienteBtn,
                    modal: !!modalCliente
                });
            }
            
            // Event listeners de fechamento são gerenciados pelo ModalManager global
            console.log('Event listeners de fechamento gerenciados pelo ModalManager');
        }
        
        function handleNovoClienteClick(e) {
            e.preventDefault();
            console.log('Botão Novo Cliente clicado!');
            
            // Limpar formulário
            limparFormulario();
            
            // Abrir modal usando ModalManager
            const modal = document.getElementById('modalCliente');
            if (modal && window.ModalManager) {
                window.ModalManager.open(modal);
                console.log('Modal aberto via ModalManager');
            } else {
                console.error('Modal ou ModalManager não encontrado');
            }
        }
        
        // Inicializar após o DOM estar carregado
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[CLIENTES] DOM carregado, inicializando...');
            
            // Inicializar managers necessários
            if (window.SidebarManager && typeof window.SidebarManager.init === 'function') {
                window.SidebarManager.init();
                console.log('[CLIENTES] SidebarManager inicializado');
            }
            
            // Garantir que o ModalManager esteja inicializado
            if (window.ModalManager && typeof window.ModalManager.init === 'function') {
                window.ModalManager.init();
                console.log('[CLIENTES] ModalManager inicializado');
            }
            
            // Aguardar um pouco para garantir que todos os elementos estejam prontos
            setTimeout(function() {
                initializeClientModals();
                initializeValidations();
            }, 200);
        });
        
        // Função para inicializar validações
        function initializeValidations() {
            console.log('[CLIENTES] Inicializando validações...');
            
            // Validação do campo nome do cliente
            const nomeInput = document.getElementById('nome');
            if (nomeInput) {
                nomeInput.addEventListener('input', function(e) {
                    const valor = e.target.value;
                    const regex = /^[A-Za-zÀ-ÿ\s]*$/;
                    
                    if (!regex.test(valor)) {
                        e.target.value = valor.replace(/[^A-Za-zÀ-ÿ\s]/g, '');
                        showAlert('Apenas letras e espaços são permitidos no nome', 'warning');
                    }
                });
                console.log('[CLIENTES] Validação do nome registrada');
            }
            
            // Máscara de telefone
            const telefoneInput = document.getElementById('telefone');
            if (telefoneInput) {
                telefoneInput.addEventListener('input', function(e) {
                    let valor = e.target.value.replace(/\D/g, '');
                    
                    // Limitar a 11 dígitos
                    if (valor.length > 11) {
                        valor = valor.substring(0, 11);
                    }
                    
                    // Aplicar máscara
                    if (valor.length <= 10) {
                        valor = valor.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                    } else {
                        valor = valor.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                    }
                    
                    e.target.value = valor;
                });
                console.log('[CLIENTES] Máscara de telefone registrada');
            }
            
            // Event listeners para botões cancelar
            const botoesCancelar = document.querySelectorAll('.modal-close');
            botoesCancelar.forEach(function(botao) {
                botao.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('[CLIENTES] Botão cancelar clicado');
                    const modal = botao.closest('.modal');
                    if (modal) {
                        if (window.ModalManager && typeof window.ModalManager.close === 'function') {
                            window.ModalManager.close(modal);
                        } else {
                            // Fallback: fechar modal manualmente
                            modal.style.display = 'none';
                            modal.classList.remove('show', 'active');
                            document.body.classList.remove('modal-open');
                        }
                        console.log('[CLIENTES] Modal fechado');
                    }
                });
            });
            
            // Event listeners para botões X (close)
            const botoesFechar = document.querySelectorAll('.close');
            botoesFechar.forEach(function(botao) {
                botao.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('[CLIENTES] Botão X clicado');
                    const modal = botao.closest('.modal');
                    if (modal) {
                        if (window.ModalManager && typeof window.ModalManager.close === 'function') {
                            window.ModalManager.close(modal);
                        } else {
                            // Fallback: fechar modal manualmente
                            modal.style.display = 'none';
                            modal.classList.remove('show', 'active');
                            document.body.classList.remove('modal-open');
                        }
                        console.log('[CLIENTES] Modal fechado');
                    }
                });
            });
            
            console.log('[CLIENTES] Event listeners dos botões cancelar e fechar registrados');
            
            // Validação do formulário de cliente
            const formCliente = document.getElementById('formCliente');
            if (formCliente) {
                formCliente.addEventListener('submit', function(e) {
            const nome = document.getElementById('nome').value.trim();
            const cpf = document.getElementById('cpf').value.trim();
            const telefone = document.getElementById('telefone').value.trim();
            const email = document.getElementById('email').value.trim();
            
            // Validar nome completo (mínimo 2 palavras)
            const nomeRegex = /^[A-Za-zÀ-ÿ\s]+$/;
            const palavras = nome.split(' ').filter(palavra => palavra.length > 0);
            
            if (!nomeRegex.test(nome)) {
                e.preventDefault();
                showAlert('O nome deve conter apenas letras e espaços', 'error');
                return false;
            }
            
            if (palavras.length < 2) {
                e.preventDefault();
                showAlert('Digite o nome completo (nome e sobrenome)', 'error');
                return false;
            }
            
            // Validar se cada palavra tem pelo menos 2 caracteres
            for (let palavra of palavras) {
                if (palavra.length < 2) {
                    e.preventDefault();
                    showAlert('Cada parte do nome deve ter pelo menos 2 caracteres', 'error');
                    return false;
                }
            }
            
            // Validar CPF
            if (!cpf || cpf.length < 11) {
                e.preventDefault();
                showAlert('CPF deve ser preenchido corretamente', 'error');
                return false;
            }
            
            // Validar telefone
            const telefoneNumeros = telefone.replace(/\D/g, '');
            if (telefoneNumeros.length < 10 || telefoneNumeros.length > 11) {
                e.preventDefault();
                showAlert('Telefone deve ter 10 ou 11 dígitos', 'error');
                return false;
            }
            
            // Validar email (se preenchido)
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                showAlert('Email deve ter um formato válido', 'error');
                return false;
            }
                });
                console.log('[CLIENTES] Validação do formulário registrada');
            }
        }
        
        // Função para mostrar alertas
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.padding = '10px 15px';
            alertDiv.style.borderRadius = '4px';
            alertDiv.style.color = 'white';
            alertDiv.style.backgroundColor = type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#28a745';
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
        
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
            margin-bottom: 1rem;
            gap: 1rem;
        }
        
        .search-form {
            display: flex !important;
            gap: 0;
            align-items: center;
            width: 100%;
        }
        
        .search-group {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            width: auto;
        }
        
        .search-group .form-control {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            margin: 0 !important;
            flex: 1 !important;
            min-width: 250px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }
        
        .search-group .btn {
            border: none !important;
            border-radius: 0 !important;
            margin: 0 !important;
            padding: 0.75rem 1rem;
            background: #667eea !important;
            color: white !important;
            flex-shrink: 0 !important;
            cursor: pointer;
        }
        
        .search-group .btn:hover {
            background: #5a67d8 !important;
        }
        
        /* Seção de Gerenciamento de Clientes */
        .client-management-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .client-management-section::before {
            content: '\f0c0';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 3rem;
            opacity: 0.2;
        }
        
        .client-management-section h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .client-management-section p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        /* Alertas */
        .alert {
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            border: 1px solid;
        }
        
        .alert-success {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-left: 4px solid #667eea;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .alert-error {
            background: rgba(118, 75, 162, 0.1);
            color: #764ba2;
            border: 1px solid rgba(118, 75, 162, 0.3);
            border-left: 4px solid #764ba2;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Tabela moderna */
        .table-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
            backdrop-filter: blur(10px);
        }
        
        .table {
            margin: 0;
            background: transparent;
        }
        
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table thead th {
            padding: 1.25rem 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            border: none;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
        }
        
        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(147, 51, 234, 0.05));
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
        }
        
        /* Botões */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 8px rgba(118, 75, 162, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(118, 75, 162, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #495057;
            border: 1px solid rgba(102, 126, 234, 0.2);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid rgba(226, 232, 240, 0.5);
        }
        
        /* Modais modernos */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 1050;
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
        
        .modal-content h2,
        .modal-content h3 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin: 0;
            padding: 1.5rem 2rem;
            font-weight: 600;
        }
        
        .modal-content .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            color: white;
            font-size: 1.5rem;
            z-index: 10;
        }
        
        .modal-content form {
            padding: 2rem;
        }
        
        .modal-content p {
            padding: 1rem 2rem;
            margin: 0;
            font-size: 1.1rem;
            color: #374151;
        }
        
        .text-center {
            text-align: center;
            color: #6b7280;
            font-style: italic;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            margin-right: 0.5rem;
            border-radius: 8px;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .page-actions {
                flex-direction: column;
                align-items: stretch;
                padding: 1rem;
            }
            
            .search-group {
                flex-direction: column;
            }
            
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
                font-size: 0.875rem;
            }
            
            .btn {
                padding: 0.625rem 1rem;
                font-size: 0.8rem;
            }
            
            .btn-sm {
                padding: 0.375rem 0.75rem;
                font-size: 0.75rem;
                margin-right: 0.25rem;
            }
            
            .modal-content {
                margin: 1rem;
                border-radius: 16px;
            }
            
            .modal-content h2,
            .modal-content h3 {
                padding: 1rem 1.5rem;
                font-size: 1.25rem;
            }
            
            .modal-content form,
            .modal-content p {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .page-actions {
                padding: 0.75rem;
            }
            
            .search-group input {
                padding-left: 35px;
                font-size: 0.875rem;
            }
            
            .table-container {
                border-radius: 8px;
            }
            
            .btn {
                padding: 0.5rem 0.875rem;
                font-size: 0.75rem;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
    </style>
</body>
</html>