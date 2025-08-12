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
                        <a href="vendas.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Vendas</span>
                        </a>
                    </li>
                    <li>
                        <a href="configuracoes.php" class="active">
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
                    <h1>Configurações</h1>
                </div>
                
                <div class="header-right">
                    <button id="themeToggle" class="theme-toggle" title="Alternar tema">
                        <i class="fas fa-moon"></i>
                    </button>
                    
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
                
                <!-- Aparência -->
                <div class="config-section">
                    <div class="section-header">
                        <h2>
                            <i class="fas fa-palette"></i>
                            Aparência
                        </h2>
                        <p>Personalize a aparência do sistema</p>
                    </div>
                    
                    <div class="config-form">
                        <div class="form-group">
                            <label class="form-label">Tema</label>
                            <div class="theme-options">
                                <div class="theme-option" data-theme="light">
                                    <div class="theme-preview theme-light">
                                        <div class="preview-header"></div>
                                        <div class="preview-content">
                                            <div class="preview-sidebar"></div>
                                            <div class="preview-main"></div>
                                        </div>
                                    </div>
                                    <span>Claro</span>
                                </div>
                                
                                <div class="theme-option" data-theme="dark">
                                    <div class="theme-preview theme-dark">
                                        <div class="preview-header"></div>
                                        <div class="preview-content">
                                            <div class="preview-sidebar"></div>
                                            <div class="preview-main"></div>
                                        </div>
                                    </div>
                                    <span>Escuro</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
            </div>
        </main>
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
        
        // Theme selection
        document.querySelectorAll('.theme-option').forEach(option => {
            option.addEventListener('click', function() {
                const theme = this.dataset.theme;
                
                // Remove active class from all options
                document.querySelectorAll('.theme-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                
                // Add active class to selected option
                this.classList.add('active');
                
                // Apply theme
                document.body.classList.remove('theme-light', 'theme-dark');
                document.body.classList.add(`theme-${theme}`);
                
                // Save theme preference
                localStorage.setItem('theme', theme);
                
                // Update theme toggle icon
                const themeToggle = document.getElementById('themeToggle');
                const icon = themeToggle.querySelector('i');
                if (theme === 'dark') {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            });
        });
        
        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            const themeOption = document.querySelector(`[data-theme="${savedTheme}"]`);
            if (themeOption) {
                themeOption.click();
            }
        });
        
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
    </script>
    
    <style>
        /* Estilos específicos da página de configurações */
        .config-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .config-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
        }
        
        .section-header h2 {
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
        }
        
        .section-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .config-form {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .password-input {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        .password-toggle:hover {
            color: #333;
        }
        
        .form-help {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #666;
        }
        
        .form-actions {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .theme-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .theme-option {
            text-align: center;
            cursor: pointer;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .theme-option:hover {
            border-color: #667eea;
        }
        
        .theme-option.active {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .theme-preview {
            width: 100px;
            height: 60px;
            border-radius: 5px;
            margin: 0 auto 0.5rem;
            overflow: hidden;
            border: 1px solid #ddd;
        }
        
        .theme-light {
            background: #fff;
        }
        
        .theme-dark {
            background: #2c3e50;
        }
        
        .preview-header {
            height: 15px;
            background: #667eea;
        }
        
        .preview-content {
            display: flex;
            height: 45px;
        }
        
        .theme-light .preview-sidebar {
            width: 30%;
            background: #f8f9fa;
        }
        
        .theme-light .preview-main {
            flex: 1;
            background: #fff;
        }
        
        .theme-dark .preview-sidebar {
            width: 30%;
            background: #34495e;
        }
        
        .theme-dark .preview-main {
            flex: 1;
            background: #2c3e50;
        }
        
        .system-info {
            padding: 2rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
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
        
        .theme-toggle {
            background: none;
            border: none;
            color: #666;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s;
            margin-right: 1rem;
        }
        
        .theme-toggle:hover {
            background: #f0f0f0;
            color: #333;
        }
        
        /* Dark theme styles */
        .theme-dark {
            background: #2c3e50;
            color: #ecf0f1;
        }
        
        .theme-dark .config-section {
            background: #34495e;
        }
        
        .theme-dark .form-control {
            background: #2c3e50;
            border-color: #4a5f7a;
            color: #ecf0f1;
        }
        
        .theme-dark .form-control:focus {
            border-color: #667eea;
        }
        
        .theme-dark .form-label,
        .theme-dark .info-label,
        .theme-dark .info-value {
            color: #ecf0f1;
        }
        
        .theme-dark .theme-toggle {
            color: #ecf0f1;
        }
        
        .theme-dark .theme-toggle:hover {
            background: #4a5f7a;
        }
        
        @media (max-width: 768px) {
            .config-container {
                margin: 0;
            }
            
            .config-form,
            .system-info {
                padding: 1rem;
            }
            
            .section-header {
                padding: 1rem;
            }
            
            .theme-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>