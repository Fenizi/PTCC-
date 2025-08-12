<?php
/**
 * Script de configuração automática do banco de dados - Versão Corrigida
 * Sistema GERE TECH
 * 
 * Este script cria o banco de dados e todas as tabelas necessárias usando PDO
 */

// Configurações do banco
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'geretech';
$porta = 3306;

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Configuração do Banco - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo "        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .warning { color: #856404; background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .info { color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }";
echo "        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }";
echo "        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }";
echo "        .btn:hover { background: #0056b3; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <h1>🚀 Configuração do Banco de Dados - GERE TECH</h1>";

try {
    // Conectar ao MySQL sem especificar banco
    echo "        <div class='step'>";
    echo "            <h3>Passo 1: Conectando ao MySQL...</h3>";
    
    $dsn = "mysql:host={$host};port={$porta}";
    $pdo = new PDO($dsn, $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "            <div class='success'>✅ Conectado ao MySQL com sucesso!</div>";
    echo "        </div>";
    
    // Criar banco de dados se não existir
    echo "        <div class='step'>";
    echo "            <h3>Passo 2: Criando banco de dados 'geretech'...</h3>";
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$banco}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "            <div class='success'>✅ Banco de dados '{$banco}' criado/verificado com sucesso!</div>";
    echo "        </div>";
    
    // Conectar ao banco específico
    $dsn = "mysql:host={$host};port={$porta};dbname={$banco}";
    $pdo = new PDO($dsn, $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar tabelas
    echo "        <div class='step'>";
    echo "            <h3>Passo 3: Criando estrutura das tabelas...</h3>";
    
    // Tabela usuarios
    $sql_usuarios = "
    CREATE TABLE IF NOT EXISTS `usuarios` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nome` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL UNIQUE,
        `senha` varchar(255) NOT NULL,
        `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_usuarios);
    echo "            <div class='success'>✅ Tabela 'usuarios' criada!</div>";
    
    // Tabela clientes
    $sql_clientes = "
    CREATE TABLE IF NOT EXISTS `clientes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nome` varchar(100) NOT NULL,
        `email` varchar(100),
        `telefone` varchar(20),
        `endereco` text,
        `data_cadastro` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_clientes);
    echo "            <div class='success'>✅ Tabela 'clientes' criada!</div>";
    
    // Tabela produtos
    $sql_produtos = "
    CREATE TABLE IF NOT EXISTS `produtos` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nome` varchar(100) NOT NULL,
        `descricao` text,
        `preco` decimal(10,2) NOT NULL,
        `estoque` int(11) DEFAULT 0,
        `categoria` varchar(50),
        `data_cadastro` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_produtos);
    echo "            <div class='success'>✅ Tabela 'produtos' criada!</div>";
    
    // Tabela vendas
    $sql_vendas = "
    CREATE TABLE IF NOT EXISTS `vendas` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `cliente_id` int(11),
        `produto_id` int(11),
        `quantidade` int(11) NOT NULL,
        `preco_unitario` decimal(10,2) NOT NULL,
        `total` decimal(10,2) NOT NULL,
        `data_venda` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`produto_id`) REFERENCES `produtos`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_vendas);
    echo "            <div class='success'>✅ Tabela 'vendas' criada!</div>";
    
    // Tabela configuracoes
    $sql_configuracoes = "
    CREATE TABLE IF NOT EXISTS `configuracoes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `chave` varchar(100) NOT NULL UNIQUE,
        `valor` text,
        `descricao` varchar(255),
        `data_atualizacao` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_configuracoes);
    echo "            <div class='success'>✅ Tabela 'configuracoes' criada!</div>";
    
    // Tabela logs_atividades
    $sql_logs = "
    CREATE TABLE IF NOT EXISTS `logs_atividades` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `usuario_id` int(11),
        `acao` varchar(100) NOT NULL,
        `descricao` text,
        `ip` varchar(45),
        `data_hora` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_logs);
    echo "            <div class='success'>✅ Tabela 'logs_atividades' criada!</div>";
    
    // Tabela alertas
    $sql_alertas = "
    CREATE TABLE IF NOT EXISTS `alertas` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tipo` varchar(50) NOT NULL,
        `titulo` varchar(100) NOT NULL,
        `mensagem` text NOT NULL,
        `lido` tinyint(1) DEFAULT 0,
        `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_alertas);
    echo "            <div class='success'>✅ Tabela 'alertas' criada!</div>";
    echo "        </div>";
    
    // Inserir dados iniciais
    echo "        <div class='step'>";
    echo "            <h3>Passo 4: Inserindo dados iniciais...</h3>";
    
    // Verificar se já existe usuário admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@geretech.com']);
    $admin_existe = $stmt->fetchColumn() > 0;
    
    if (!$admin_existe) {
        // Inserir usuário administrador
        $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute(['Administrador', 'admin@geretech.com', $senha_hash]);
        echo "            <div class='success'>✅ Usuário administrador criado!</div>";
    } else {
        echo "            <div class='info'>ℹ️ Usuário administrador já existe!</div>";
    }
    
    // Inserir configurações padrão
    $configuracoes_padrao = [
        ['empresa_nome', 'GERE TECH', 'Nome da empresa'],
        ['empresa_cnpj', '00.000.000/0001-00', 'CNPJ da empresa'],
        ['empresa_endereco', 'São Paulo, SP - Brasil', 'Endereço da empresa'],
        ['empresa_telefone', '(11) 99999-9999', 'Telefone da empresa'],
        ['empresa_email', 'contato@geretech.com', 'Email da empresa'],
        ['estoque_minimo_alerta', '5', 'Quantidade mínima para alerta de estoque'],
        ['backup_automatico', 'true', 'Ativar backup automático'],
        ['tema_padrao', 'claro', 'Tema padrão do sistema']
    ];
    
    foreach ($configuracoes_padrao as $config) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO configuracoes (chave, valor, descricao) VALUES (?, ?, ?)");
        $stmt->execute($config);
    }
    echo "            <div class='success'>✅ Configurações padrão inseridas!</div>";
    echo "        </div>";
    
    // Verificação final
    echo "        <div class='step'>";
    echo "            <h3>Passo 5: Verificação final...</h3>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "            <div class='info'>";
    echo "                <strong>Tabelas criadas:</strong><br>";
    foreach ($tabelas as $tabela) {
        echo "                • {$tabela}<br>";
    }
    echo "            </div>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $total_usuarios = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM configuracoes");
    $total_configuracoes = $stmt->fetchColumn();
    
    echo "            <div class='success'>";
    echo "                <strong>🎉 Configuração concluída com sucesso!</strong><br>";
    echo "                • Total de usuários: {$total_usuarios}<br>";
    echo "                • Total de configurações: {$total_configuracoes}<br>";
    echo "            </div>";
    echo "        </div>";
    
    echo "        <div class='step'>";
    echo "            <h3>📚 Informações de Acesso:</h3>";
    echo "            <div class='info'>";
    echo "                <strong>Credenciais do Administrador:</strong><br>";
    echo "                • Email: <code>admin@geretech.com</code><br>";
    echo "                • Senha: <code>admin123</code><br><br>";
    echo "                <strong>Banco de Dados:</strong><br>";
    echo "                • Nome: <code>{$banco}</code><br>";
    echo "                • Servidor: <code>{$host}:{$porta}</code><br>";
    echo "            </div>";
    echo "        </div>";
    
    echo "        <div style='text-align: center; margin: 30px 0;'>";
    echo "            <a href='inicializar_sistema_corrigido.php' class='btn'>🔄 Verificar Sistema</a>";
    echo "            <a href='pages/login.php' class='btn'>🔐 Fazer Login</a>";
    echo "        </div>";
    
} catch (Exception $e) {
    echo "        <div class='error'>";
    echo "            <strong>❌ Erro durante a configuração:</strong><br>";
    echo "            {$e->getMessage()}";
    echo "        </div>";
    
    echo "        <div class='step'>";
    echo "            <h3>🔧 Possíveis soluções:</h3>";
    echo "            <ol>";
    echo "                <li>Verifique se o MySQL está rodando</li>";
    echo "                <li>Confirme as credenciais de acesso</li>";
    echo "                <li>Verifique se a porta 3306 está disponível</li>";
    echo "                <li>Confirme se o usuário 'root' tem permissões adequadas</li>";
    echo "            </ol>";
    echo "        </div>";
    
    echo "        <div style='text-align: center; margin: 30px 0;'>";
    echo "            <a href='setup_database_corrigido.php' class='btn'>🔄 Tentar Novamente</a>";
    echo "        </div>";
}

echo "        <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 0.9em; text-align: center;'>";
echo "            <p>Sistema GERE TECH - Configuração realizada em " . date('d/m/Y H:i:s') . "</p>";
echo "        </div>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>