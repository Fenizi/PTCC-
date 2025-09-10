<?php
// Página de Cadastro - Sistema GERE TECH
// Registro de novos usuários

session_start();

// Se já estiver logado, redirecionar para dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';
$sucesso = '';

// Processar cadastro
if ($_POST) {
    require_once '../php/conexao.php';
    
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    // Validações server-side
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } elseif (strlen($nome) < 2) {
        $erro = 'O nome deve ter pelo menos 2 caracteres.';
    } elseif (str_word_count($nome) < 2) {
        $erro = 'Por favor, informe o nome completo (nome e sobrenome).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Por favor, informe um email válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } else {
        // Verificar se email já existe
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $erro = 'Este email já está cadastrado no sistema.';
        } else {
            // Hash da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Inserir usuário
            $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senha_hash);
            
            if ($stmt->execute()) {
                $sucesso = 'Conta criada com sucesso! Você será redirecionado para o login.';
                // Redirecionar após 2 segundos
                header("refresh:2;url=login.php");
            } else {
                $erro = 'Erro ao criar conta. Tente novamente.';
            }
        }
        
        $stmt->close();
    }
    
    $conexao->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - GERE TECH</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Estilos específicos da página de cadastro */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .cadastro-container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        
        .cadastro-logo {
            font-size: 3.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .cadastro-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .cadastro-subtitle {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .input-group .form-control {
            padding-left: 3rem;
        }
        
        .btn-cadastro {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 1rem;
        }
        
        .btn-cadastro:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-cadastro:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
        }
        
        .login-link {
            display: inline-block;
            color: #667eea;
            text-decoration: none;
            margin-top: 1rem;
            transition: color 0.3s;
        }
        
        .login-link:hover {
            color: #5a6fd8;
        }
        
        .password-strength {
            font-size: 0.8rem;
            margin-top: 0.5rem;
            color: #666;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>
<body>
    <div class="cadastro-container">
        <div class="cadastro-logo">GT</div>
        <h1 class="cadastro-title">Criar Conta</h1>
        <p class="cadastro-subtitle">Preencha os dados para se cadastrar</p>
        
        <?php if ($erro): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="cadastroForm">
            <div class="form-group">
                <label for="nome" class="form-label">Nome Completo</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="nome" name="nome" class="form-control" 
                           placeholder="Digite seu nome completo" 
                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"
                           required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="Digite seu email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="senha" class="form-label">Senha</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="senha" name="senha" class="form-control" 
                           placeholder="Digite sua senha" 
                           minlength="6" required>
                </div>
                <div id="passwordStrength" class="password-strength"></div>
            </div>
            
            <div class="form-group">
                <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" 
                           placeholder="Confirme sua senha" 
                           minlength="6" required>
                </div>
                <div id="passwordMatch" class="password-strength"></div>
            </div>
            
            <button type="submit" class="btn-cadastro">
                <i class="fas fa-user-plus"></i> Criar Conta
            </button>
        </form>
        
        <a href="login.php" class="login-link">
            <i class="fas fa-arrow-left"></i> Já tem uma conta? Faça login
        </a>
    </div>
    
    <script>
        // Validações client-side
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('cadastroForm');
            const nome = document.getElementById('nome');
            const email = document.getElementById('email');
            const senha = document.getElementById('senha');
            const confirmarSenha = document.getElementById('confirmar_senha');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            
            // Validação do nome completo
            nome.addEventListener('blur', function() {
                const nomeValue = this.value.trim();
                const palavras = nomeValue.split(' ').filter(p => p.length > 0);
                
                if (palavras.length < 2) {
                    this.style.borderColor = '#dc3545';
                } else {
                    this.style.borderColor = '#28a745';
                }
            });
            
            // Verificação da força da senha
            senha.addEventListener('input', function() {
                const senhaValue = this.value;
                let strength = 0;
                let message = '';
                
                if (senhaValue.length >= 6) strength++;
                if (senhaValue.match(/[a-z]/)) strength++;
                if (senhaValue.match(/[A-Z]/)) strength++;
                if (senhaValue.match(/[0-9]/)) strength++;
                if (senhaValue.match(/[^a-zA-Z0-9]/)) strength++;
                
                if (senhaValue.length === 0) {
                    message = '';
                    passwordStrength.className = 'password-strength';
                } else if (strength < 3) {
                    message = 'Senha fraca';
                    passwordStrength.className = 'password-strength strength-weak';
                } else if (strength < 4) {
                    message = 'Senha média';
                    passwordStrength.className = 'password-strength strength-medium';
                } else {
                    message = 'Senha forte';
                    passwordStrength.className = 'password-strength strength-strong';
                }
                
                passwordStrength.textContent = message;
            });
            
            // Verificação de senhas coincidentes
            function checkPasswordMatch() {
                const senhaValue = senha.value;
                const confirmarValue = confirmarSenha.value;
                
                if (confirmarValue.length === 0) {
                    passwordMatch.textContent = '';
                    passwordMatch.className = 'password-strength';
                    confirmarSenha.style.borderColor = '#e1e5e9';
                } else if (senhaValue === confirmarValue) {
                    passwordMatch.textContent = 'Senhas coincidem';
                    passwordMatch.className = 'password-strength strength-strong';
                    confirmarSenha.style.borderColor = '#28a745';
                } else {
                    passwordMatch.textContent = 'Senhas não coincidem';
                    passwordMatch.className = 'password-strength strength-weak';
                    confirmarSenha.style.borderColor = '#dc3545';
                }
            }
            
            senha.addEventListener('input', checkPasswordMatch);
            confirmarSenha.addEventListener('input', checkPasswordMatch);
            
            // Validação do formulário
            form.addEventListener('submit', function(e) {
                const nomeValue = nome.value.trim();
                const emailValue = email.value.trim();
                const senhaValue = senha.value;
                const confirmarValue = confirmarSenha.value;
                
                // Verificar nome completo
                const palavras = nomeValue.split(' ').filter(p => p.length > 0);
                if (palavras.length < 2) {
                    e.preventDefault();
                    alert('Por favor, informe o nome completo (nome e sobrenome).');
                    nome.focus();
                    return;
                }
                
                // Verificar email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailValue)) {
                    e.preventDefault();
                    alert('Por favor, informe um email válido.');
                    email.focus();
                    return;
                }
                
                // Verificar senha
                if (senhaValue.length < 6) {
                    e.preventDefault();
                    alert('A senha deve ter pelo menos 6 caracteres.');
                    senha.focus();
                    return;
                }
                
                // Verificar senhas coincidentes
                if (senhaValue !== confirmarValue) {
                    e.preventDefault();
                    alert('As senhas não coincidem.');
                    confirmarSenha.focus();
                    return;
                }
            });
        });
    </script>
</body>
</html>