<?php
// Página de Configurações - Sistema GERE TECH
// Configurações do sistema e usuário

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../php/conexao.php';

$mensagem = '';
$tipo_mensagem = '';

// Buscar dados do usuário atual
$stmt = $conexao->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Processar ações
if ($_POST) {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'alterar_perfil') {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        
        if (empty($nome) || empty($email)) {
            $mensagem = 'Nome e email são obrigatórios.';
            $tipo_mensagem = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = 'Email inválido.';
            $tipo_mensagem = 'error';
        } else {
            // Verificar se email já existe (exceto o próprio usuário)
            $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $_SESSION['usuario_id']);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $mensagem = 'Este email já está sendo usado por outro usuário.';
                $tipo_mensagem = 'error';
            } else {
                // Atualizar perfil
                $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $nome, $email, $_SESSION['usuario_id']);
                
                if ($stmt->execute()) {
                    $_SESSION['usuario_nome'] = $nome;
                    $usuario['nome'] = $nome;
                    $usuario['email'] = $email;
                    $mensagem = 'Perfil atualizado com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao atualizar perfil.';
                    $tipo_mensagem = 'error';
                }
            }
            $stmt->close();
        }
    }
    
    if ($acao === 'alterar_senha') {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        $confirmar_senha = $_POST['confirmar_senha'];
        
        if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
            $mensagem = 'Todos os campos de senha são obrigatórios.';
            $tipo_mensagem = 'error';
        } elseif ($nova_senha !== $confirmar_senha) {
            $mensagem = 'A nova senha e a confirmação não coincidem.';
            $tipo_mensagem = 'error';
        } elseif (strlen($nova_senha) < 6) {
            $mensagem = 'A nova senha deve ter pelo menos 6 caracteres.';
            $tipo_mensagem = 'error';
        } else {
            // Verificar senha atual
            $stmt = $conexao->prepare("SELECT senha FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['usuario_id']);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $senha_hash = $resultado->fetch_assoc()['senha'];
            
            if (!password_verify($senha_atual, $senha_hash)) {
                $mensagem = 'Senha atual incorreta.';
                $tipo_mensagem = 'error';
            } else {
                // Atualizar senha
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                $stmt->bind_param("si", $nova_senha_hash, $_SESSION['usuario_id']);
                
                if ($stmt->execute()) {
                    $mensagem = 'Senha alterada com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao alterar senha.';
                    $tipo_mensagem = 'error';
                }
            }
            $stmt->close();
        }
    }
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - GERE TECH</title>
    
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
            
            <div class="settings-management-section">
                <!-- Mensagens -->
                <?php if ($mensagem): ?>
                    <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                        <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        <?php echo htmlspecialchars($mensagem); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Configurações -->
                <div class="config-container">
                <!-- Perfil do Usuário -->
                <div class="config-section">
                    <div class="section-header">
                        <h2>
                            <i class="fas fa-user"></i>
                            Perfil do Usuário
                        </h2>
                        <p>Altere suas informações pessoais</p>
                    </div>
                    
                    <form method="POST" class="config-form">
                        <input type="hidden" name="acao" value="alterar_perfil">
                        
                        <div class="form-group">
                            <label for="nome" class="form-label">Nome completo</label>
                            <input 
                                type="text" 
                                id="nome" 
                                name="nome" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($usuario['nome']); ?>"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($usuario['email']); ?>"
                                required
                            >
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Alterar Senha -->
                <div class="config-section">
                    <div class="section-header">
                        <h2>
                            <i class="fas fa-lock"></i>
                            Segurança
                        </h2>
                        <p>Altere sua senha de acesso</p>
                    </div>
                    
                    <form method="POST" class="config-form">
                        <input type="hidden" name="acao" value="alterar_senha">
                        
                        <div class="form-group">
                            <label for="senha_atual" class="form-label">Senha atual</label>
                            <div class="password-input">
                                <input 
                                    type="password" 
                                    id="senha_atual" 
                                    name="senha_atual" 
                                    class="form-control" 
                                    required
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('senha_atual')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="nova_senha" class="form-label">Nova senha</label>
                            <div class="password-input">
                                <input 
                                    type="password" 
                                    id="nova_senha" 
                                    name="nova_senha" 
                                    class="form-control" 
                                    minlength="6"
                                    required
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('nova_senha')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-help">Mínimo de 6 caracteres</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmar_senha" class="form-label">Confirmar nova senha</label>
                            <div class="password-input">
                                <input 
                                    type="password" 
                                    id="confirmar_senha" 
                                    name="confirmar_senha" 
                                    class="form-control" 
                                    minlength="6"
                                    required
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('confirmar_senha')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i>
                                Alterar Senha
                            </button>
                        </div>
                    </form>
                </div>
                

                
                <!-- Informações do Sistema -->
                <div class="config-section">
                    <div class="section-header">
                        <h2>
                            <i class="fas fa-info-circle"></i>
                            Informações do Sistema
                        </h2>
                        <p>Detalhes sobre o GERE TECH</p>
                    </div>
                    
                    <div class="system-info">
                        <div class="info-item">
                            <span class="info-label">Versão:</span>
                            <span class="info-value">1.0.0</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Desenvolvido por:</span>
                            <span class="info-value">Equipe GERE TECH</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Última atualização:</span>
                            <span class="info-value"><?php echo date('d/m/Y'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Usuário logado desde:</span>
                            <span class="info-value"><?php echo date('d/m/Y H:i', $_SESSION['login_time'] ?? time()); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Zona de Perigo -->
                <div class="config-section danger-zone">
                    <div class="section-header danger-header">
                        <h2>
                            <i class="fas fa-exclamation-triangle"></i>
                            Zona de Perigo
                        </h2>
                        <p>Ações irreversíveis - proceda com extrema cautela</p>
                    </div>
                    
                    <div class="danger-content">
                        <div class="danger-warning">
                            <i class="fas fa-skull-crossbones"></i>
                            <div class="warning-text">
                                <h3>Deletar Conta Permanentemente</h3>
                                <p>Esta ação <strong>NÃO PODE SER DESFEITA</strong>. Todos os seus dados serão permanentemente removidos, incluindo:</p>
                                <ul>
                                    <li>Todos os clientes cadastrados</li>
                                    <li>Todos os produtos e estoque</li>
                                    <li>Histórico completo de vendas</li>
                                    <li>Configurações personalizadas</li>
                                    <li>Logs e relatórios</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="danger-actions">
                            <button type="button" class="btn btn-danger" onclick="openDeleteModal()">
                                <i class="fas fa-trash-alt"></i>
                                Deletar Minha Conta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </main>
    </div>
    
    <!-- Modal de Confirmação para Deletar Conta -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-exclamation-triangle"></i>
                    Confirmar Exclusão da Conta
                </h2>
                <button type="button" class="modal-close" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="delete-warning">
                    <i class="fas fa-skull-crossbones"></i>
                    <p><strong>ATENÇÃO:</strong> Esta ação é <span class="text-danger">IRREVERSÍVEL</span>!</p>
                </div>
                
                <form id="deleteForm">
                    <div class="form-group">
                        <label for="senha_confirmacao" class="form-label">Digite sua senha atual para confirmar:</label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="senha_confirmacao" 
                                name="senha_confirmacao" 
                                class="form-control" 
                                required
                                placeholder="Sua senha atual"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('senha_confirmacao')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="palavra_confirmacao" class="form-label">Digite <strong>DELETAR</strong> (em maiúsculas) para confirmar:</label>
                        <input 
                            type="text" 
                            id="palavra_confirmacao" 
                            name="palavra_confirmacao" 
                            class="form-control" 
                            required
                            placeholder="DELETAR"
                            autocomplete="off"
                        >
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                            <i class="fas fa-trash-alt"></i>
                            Deletar Permanentemente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="../js/main.js"></script>
    
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        

        
        // Password confirmation validation
        document.getElementById('confirmar_senha').addEventListener('input', function() {
            const novaSenha = document.getElementById('nova_senha').value;
            const confirmarSenha = this.value;
            
            if (novaSenha !== confirmarSenha) {
                this.setCustomValidity('As senhas não coincidem');
            } else {
                this.setCustomValidity('');
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
        
        // Funções do modal de deletar conta
        function openDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('show');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            // Garantir que o modal apareça na frente
            modal.style.zIndex = '9999';
            
            // Garantir que os inputs funcionem corretamente
            setTimeout(() => {
                const senhaInput = document.getElementById('senha_confirmacao');
                const palavraInput = document.getElementById('palavra_confirmacao');
                
                // Forçar propriedades dos inputs
                if (senhaInput) {
                    senhaInput.style.pointerEvents = 'auto';
                    senhaInput.style.zIndex = '10002';
                    senhaInput.style.position = 'relative';
                    senhaInput.removeAttribute('readonly');
                    senhaInput.removeAttribute('disabled');
                    senhaInput.focus();
                }
                
                if (palavraInput) {
                    palavraInput.style.pointerEvents = 'auto';
                    palavraInput.style.zIndex = '10002';
                    palavraInput.style.position = 'relative';
                    palavraInput.removeAttribute('readonly');
                    palavraInput.removeAttribute('disabled');
                }
            }, 100);
        }
        
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            // Limpar campos
            document.getElementById('deleteForm').reset();
            document.getElementById('confirmDeleteBtn').disabled = true;
        }
        
        // Validação em tempo real dos campos de confirmação
        function validateDeleteForm() {
            const senha = document.getElementById('senha_confirmacao').value;
            const palavra = document.getElementById('palavra_confirmacao').value;
            const btn = document.getElementById('confirmDeleteBtn');
            
            if (senha.length > 0 && palavra === 'DELETAR') {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }
        
        // Adicionar listeners para validação
        document.getElementById('senha_confirmacao').addEventListener('input', validateDeleteForm);
        document.getElementById('palavra_confirmacao').addEventListener('input', validateDeleteForm);
        
        // Processar formulário de exclusão
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const senha = document.getElementById('senha_confirmacao').value;
            const palavra = document.getElementById('palavra_confirmacao').value;
            
            if (!senha || palavra !== 'DELETAR') {
                alert('Por favor, preencha todos os campos corretamente.');
                return;
            }
            
            // Confirmação final
            if (!confirm('Tem certeza absoluta de que deseja deletar sua conta? Esta ação NÃO PODE SER DESFEITA!')) {
                return;
            }
            
            // Desabilitar botão durante o processamento
            const btn = document.getElementById('confirmDeleteBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            
            // Enviar requisição
            const formData = new FormData();
            formData.append('senha_confirmacao', senha);
            formData.append('palavra_confirmacao', palavra);
            
            fetch('../deletar_conta.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Conta deletada com sucesso. Você será redirecionado para a página de login.');
                    window.location.href = 'login.php';
                } else {
                    alert('Erro: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash-alt"></i> Deletar Permanentemente';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro interno. Tente novamente.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash-alt"></i> Deletar Permanentemente';
            });
        });
        
        // Fechar modal ao clicar fora dele
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
    
    <style>
        /* Estilos específicos da página de configurações */
        .settings-management-section {
            background: var(--bg-primary);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }
        
        .config-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .config-section {
            background: var(--bg-primary);
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }
        
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .section-header h2 {
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .section-header h2 i {
            font-size: 1rem;
            padding: 0.25rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 4px;
        }
        
        .section-header p {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 400;
        }
        
        .config-form {
            padding: 1.5rem;
            background: var(--bg-primary);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.9rem;
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.25);
        }
        
        .form-control:hover {
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        }
        
        .password-input {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(102, 126, 234, 0.1);
            border: none;
            color: #667eea;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .password-toggle:hover {
            background: rgba(102, 126, 234, 0.2);
            color: #5a67d8;
            transform: translateY(-50%) scale(1.1);
        }
        
        .form-help {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-muted);
            font-style: italic;
            padding-left: 0.5rem;
            border-left: 3px solid rgba(102, 126, 234, 0.3);
        }
        
        .form-actions {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid rgba(226, 232, 240, 0.5);
            text-align: center;
        }
        


        
        .system-info {
            padding: 2.5rem;
            background: var(--bg-primary);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            margin-bottom: 0.5rem;
            border-radius: 12px;
            background: var(--bg-secondary);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }
        
        .info-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .info-item:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            color: var(--text-primary);
            font-weight: 500;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 8px;
            font-family: 'Courier New', monospace;
        }
        
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            animation: slideInDown 0.5s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: currentColor;
            opacity: 0.6;
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(72, 187, 120, 0.1) 0%, rgba(56, 178, 172, 0.1) 100%);
            color: #2f855a;
            border-color: rgba(72, 187, 120, 0.3);
        }
        
        .alert-error {
            background: linear-gradient(135deg, rgba(245, 101, 101, 0.1) 0%, rgba(229, 62, 62, 0.1) 100%);
            color: #c53030;
            border-color: rgba(245, 101, 101, 0.3);
        }
        
        .alert i {
            font-size: 1.2rem;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            backdrop-filter: blur(10px);
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
        
        .btn {
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }
        

        
        /* Estilos da Zona de Perigo */
        .danger-zone {
            border: 2px solid #dc3545 !important;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, rgba(220, 53, 69, 0.1) 100%) !important;
        }
        
        .danger-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
            color: white !important;
        }
        
        .danger-content {
            padding: 2rem;
        }
        
        .danger-warning {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 8px;
        }
        
        .danger-warning > i {
            font-size: 2rem;
            color: #dc3545;
            margin-top: 0.25rem;
        }
        
        .warning-text h3 {
            margin: 0 0 1rem 0;
            color: #dc3545;
            font-weight: 600;
        }
        
        .warning-text p {
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        .warning-text ul {
            margin: 0;
            padding-left: 1.5rem;
            color: var(--text-secondary);
        }
        
        .warning-text li {
            margin-bottom: 0.5rem;
        }
        
        .danger-actions {
            text-align: center;
            padding-top: 1rem;
            border-top: 2px solid rgba(220, 53, 69, 0.2);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
            box-shadow: 0 8px 16px rgba(220, 53, 69, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268 0%, #545b62 100%);
            box-shadow: 0 8px 16px rgba(108, 117, 125, 0.4);
            transform: translateY(-2px);
        }
        
        /* Estilos do Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
            pointer-events: auto;
        }
        
        .modal.show {
            display: flex !important;
            pointer-events: auto;
        }
        
        .modal-content {
            background: var(--bg-primary);
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            border: 2px solid #dc3545;
            animation: slideIn 0.3s ease;
            position: relative;
            z-index: 10000;
            pointer-events: auto;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.2rem;
        }
        
        .modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        .modal-body {
            padding: 2rem;
            position: relative;
            z-index: 10001;
            pointer-events: auto;
        }
        
        /* Estilos específicos para inputs do modal */
        .modal .form-control {
            position: relative;
            z-index: 10002;
            pointer-events: auto;
            background: #ffffff !important;
            border: 2px solid #ddd !important;
            color: #333 !important;
            font-size: 14px;
            padding: 12px 15px;
            border-radius: 6px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .modal .form-control:focus {
            outline: none !important;
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25) !important;
            background: #ffffff !important;
            pointer-events: auto;
        }
        
        .modal .form-control:hover {
            border-color: #667eea !important;
            pointer-events: auto;
        }
        
        .modal .password-input {
            position: relative;
            z-index: 10002;
            pointer-events: auto;
        }
        
        .modal .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(102, 126, 234, 0.1);
            border: none;
            color: #667eea;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            z-index: 10003;
            pointer-events: auto;
        }
        
        .modal .form-group {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 10001;
            pointer-events: auto;
        }
        
        .modal .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
            font-size: 14px;
            pointer-events: auto;
        }
        
        .delete-warning {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 8px;
        }
        
        .delete-warning i {
            font-size: 1.5rem;
            color: #dc3545;
        }
        
        .text-danger {
            color: #dc3545 !important;
            font-weight: 600;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid rgba(226, 232, 240, 0.5);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        @media (max-width: 768px) {
            .settings-management-section {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .config-container {
                margin: 0;
                padding: 0 1rem;
            }
            
            .config-section {
                margin-bottom: 1.5rem;
            }
            
            .config-form,
            .system-info {
                padding: 1.5rem;
            }
            
            .section-header {
                padding: 1.5rem;
            }
            
            .section-header h2 {
                font-size: 1.2rem;
            }
            
            .theme-options {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .theme-preview {
                width: 100px;
                height: 60px;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
                padding: 1rem;
            }
            
            .info-value {
                align-self: stretch;
                text-align: center;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
                padding: 1rem 2rem;
            }
            
            .modal-content {
                width: 95%;
                margin: 1rem;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
            
            .modal-actions {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .danger-warning {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .danger-content {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .settings-management-section {
                padding: 1rem;
                border-radius: 15px;
            }
            
            .config-section {
                border-radius: 15px;
            }
            
            .config-form,
            .system-info {
                padding: 1rem;
            }
            
            .section-header {
                padding: 1rem;
            }
            
            .section-header h2 {
                font-size: 1.1rem;
                gap: 0.5rem;
            }
            
            .form-control {
                padding: 0.875rem 1rem;
            }
            
            .theme-preview {
                width: 80px;
                height: 50px;
            }
            
            .alert {
                padding: 1rem;
                font-size: 0.9rem;
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