<?php
/**
 * Script de inicialização do Sistema GERE TECH - Versão Corrigida
 * 
 * Este script verifica e configura automaticamente:
 * - Conexão com banco de dados (usando PDO)
 * - Estrutura das tabelas
 * - Dados iniciais
 * - Configurações do sistema
 */

session_start();

// Função para verificar se o MySQL está acessível
function verificarMySQL() {
    $host = 'localhost';
    $usuario = 'root';
    $senha = '';
    $porta = 3306;
    
    try {
        $dsn = "mysql:host={$host};port={$porta}";
        $pdo = new PDO($dsn, $usuario, $senha);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Função para verificar se o banco existe
function verificarBanco() {
    $host = 'localhost';
    $usuario = 'root';
    $senha = '';
    $banco = 'geretech';
    $porta = 3306;
    
    try {
        $dsn = "mysql:host={$host};port={$porta};dbname={$banco}";
        $pdo = new PDO($dsn, $usuario, $senha);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Função para verificar tabelas
function verificarTabelas() {
    try {
        $host = 'localhost';
        $usuario = 'root';
        $senha = '';
        $banco = 'geretech';
        $porta = 3306;
        
        $dsn = "mysql:host={$host};port={$porta};dbname={$banco}";
        $pdo = new PDO($dsn, $usuario, $senha);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $tabelas_necessarias = ['usuarios', 'clientes', 'produtos', 'vendas', 'configuracoes', 'logs_atividades', 'alertas'];
        $tabelas_existentes = [];
        
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tabelas_existentes[] = $row[0];
        }
        
        foreach ($tabelas_necessarias as $tabela) {
            if (!in_array($tabela, $tabelas_existentes)) {
                return false;
            }
        }
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Verificar status do sistema
$mysql_ok = verificarMySQL();
$banco_ok = verificarBanco();
$tabelas_ok = $banco_ok ? verificarTabelas() : false;

// Se tudo estiver OK, redirecionar para o sistema
if ($mysql_ok && $banco_ok && $tabelas_ok) {
    // Sistema configurado, redirecionar para login ou dashboard
    if (isset($_SESSION['usuario_id'])) {
        header('Location: pages/dashboard.php');
    } else {
        header('Location: pages/login.php');
    }
    exit;
}

// Caso contrário, mostrar página de configuração
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicialização - Sistema GERE TECH</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        
        .logo {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        .status-ok {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        
        .status-error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        
        .status-icon {
            font-size: 1.5em;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 10px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .instructions {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .instructions h3 {
            color: #1976d2;
            margin-bottom: 15px;
        }
        
        .instructions ol {
            margin-left: 20px;
        }
        
        .instructions li {
            margin: 8px 0;
        }
        
        .alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🏢 GERE TECH</div>
        <div class="subtitle">Sistema de Gerenciamento Empresarial</div>
        
        <h2>🚀 Inicialização do Sistema</h2>
        
        <div class="status-item <?php echo $mysql_ok ? 'status-ok' : 'status-error'; ?>">
            <span>Conexão com MySQL</span>
            <span class="status-icon"><?php echo $mysql_ok ? '✅' : '❌'; ?></span>
        </div>
        
        <div class="status-item <?php echo $banco_ok ? 'status-ok' : 'status-error'; ?>">
            <span>Banco de Dados 'geretech'</span>
            <span class="status-icon"><?php echo $banco_ok ? '✅' : '❌'; ?></span>
        </div>
        
        <div class="status-item <?php echo $tabelas_ok ? 'status-ok' : 'status-error'; ?>">
            <span>Estrutura das Tabelas</span>
            <span class="status-icon"><?php echo $tabelas_ok ? '✅' : '❌'; ?></span>
        </div>
        
        <?php if (!$mysql_ok): ?>
            <div class="alert">
                <strong>⚠️ MySQL não está acessível!</strong><br>
                Verifique se o servidor MySQL está rodando e as credenciais estão corretas.
            </div>
            
            <div class="instructions">
                <h3>📋 Como resolver:</h3>
                <ol>
                    <li>Inicie o servidor MySQL (XAMPP, WAMP, ou serviço do sistema)</li>
                    <li>Verifique as credenciais no arquivo <code>includes/conexao.php</code></li>
                    <li>Confirme se a porta 3306 está disponível</li>
                    <li>Recarregue esta página</li>
                </ol>
            </div>
            
        <?php elseif (!$banco_ok || !$tabelas_ok): ?>
            <div class="alert">
                <strong>⚠️ Banco de dados precisa ser configurado!</strong><br>
                O sistema detectou que o banco ou as tabelas não estão configurados corretamente.
            </div>
            
            <div class="instructions">
                <h3>🔧 Configuração Automática:</h3>
                <ol>
                    <li>Clique no botão "Configurar Banco" abaixo</li>
                    <li>Aguarde a criação automática do banco e tabelas</li>
                    <li>Teste a conexão</li>
                    <li>Faça login no sistema</li>
                </ol>
            </div>
            
            <div style="margin: 30px 0;">
                <a href="setup_database_corrigido.php" class="btn btn-success">🔧 Configurar Banco Automaticamente</a>
                <a href="teste_conexao.php" class="btn">🧪 Testar Conexão</a>
            </div>
            
        <?php else: ?>
            <div class="status-item status-ok">
                <span><strong>🎉 Sistema Configurado!</strong></span>
                <span class="status-icon">✅</span>
            </div>
            
            <div style="margin: 30px 0;">
                <a href="pages/login.php" class="btn btn-success">🔐 Acessar Sistema</a>
                <a href="teste_conexao.php" class="btn">🧪 Testar Conexão</a>
            </div>
        <?php endif; ?>
        
        <div class="instructions">
            <h3>📚 Informações do Sistema:</h3>
            <ul style="list-style: none; padding: 0;">
                <li><strong>Usuário padrão:</strong> admin@geretech.com</li>
                <li><strong>Senha padrão:</strong> admin123</li>
                <li><strong>Banco de dados:</strong> geretech</li>
                <li><strong>Servidor:</strong> localhost:3306</li>
            </ul>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 0.9em;">
            <p>Sistema GERE TECH - Verificação realizada em <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>