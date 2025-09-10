<?php
/**
 * Arquivo de conexão híbrida com o banco de dados MySQL
 * Sistema GERE TECH
 * 
 * Este arquivo estabelece a conexão com o banco de dados usando
 * MySQLi (preferencial) ou PDO (fallback) automaticamente
 * 
 * Versão: 2.0 - Sistema Híbrido
 * Data: Janeiro 2025
 */

// Configurações do banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'geretech';
$porta = 3306;

// Variáveis globais de conexão
$conexao = null;
$pdo = null;
$usando_mysqli = false;
$conexao_ativa = false;

/**
 * Função para detectar qual extensão usar
 */
function detectarExtensao() {
    $mysqli_disponivel = class_exists('mysqli');
    $pdo_disponivel = class_exists('PDO') && in_array('mysql', PDO::getAvailableDrivers());
    
    if ($mysqli_disponivel) {
        return 'mysqli';
    } elseif ($pdo_disponivel) {
        return 'pdo';
    } else {
        throw new Exception('Nenhuma extensão MySQL disponível (mysqli ou PDO)');
    }
}

/**
 * Função para conectar usando MySQLi
 */
function conectarMySQLi($host, $usuario, $senha, $banco, $porta) {
    global $conexao;
    
    // Primeiro, conectar sem especificar o banco
    $conexao_temp = new mysqli($host, $usuario, $senha, null, $porta);
    
    if ($conexao_temp->connect_error) {
        throw new Exception("Erro na conexão MySQLi: " . $conexao_temp->connect_error);
    }
    
    // Verificar se o banco existe
    $resultado = $conexao_temp->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$banco'");
    
    if ($resultado->num_rows == 0) {
        // Banco não existe, criar
        if (!$conexao_temp->query("CREATE DATABASE IF NOT EXISTS `$banco` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            throw new Exception("Erro ao criar banco MySQLi: " . $conexao_temp->error);
        }
    }
    
    // Fechar conexão temporária com verificação
    if ($conexao_temp && !$conexao_temp->connect_errno) {
        $conexao_temp->close();
    }
    
    // Conectar ao banco específico
    $conexao = new mysqli($host, $usuario, $senha, $banco, $porta);
    
    if ($conexao->connect_error) {
        throw new Exception("Erro na conexão com banco MySQLi: " . $conexao->connect_error);
    }
    
    // Configurações MySQLi
    $conexao->set_charset("utf8mb4");
    $conexao->query("SET time_zone = '-03:00'");
    $conexao->query("SET sql_mode = 'TRADITIONAL'");
    
    return true;
}

/**
 * Função para conectar usando PDO
 */
function conectarPDO($host, $usuario, $senha, $banco, $porta) {
    global $pdo;
    
    // Primeiro, conectar sem especificar o banco
    $dsn_temp = "mysql:host={$host};port={$porta};charset=utf8mb4";
    $opcoes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    
    $pdo_temp = new PDO($dsn_temp, $usuario, $senha, $opcoes);
    
    // Verificar se o banco existe
    $stmt = $pdo_temp->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->execute([$banco]);
    
    if ($stmt->rowCount() == 0) {
        // Banco não existe, criar
        $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS `$banco` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    // Conectar ao banco específico
    $dsn = "mysql:host={$host};port={$porta};dbname={$banco};charset=utf8mb4";
    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
    
    // Configurações PDO
    $pdo->exec("SET time_zone = '-03:00'");
    $pdo->exec("SET sql_mode = 'TRADITIONAL'");
    
    return true;
}

/**
 * Estabelecer conexão com o banco
 */
try {
    $extensao = detectarExtensao();
    
    if ($extensao === 'mysqli') {
        conectarMySQLi($host, $usuario, $senha, $banco, $porta);
        $usando_mysqli = true;
        $conexao_ativa = true;
    } else {
        conectarPDO($host, $usuario, $senha, $banco, $porta);
        $usando_mysqli = false;
        $conexao_ativa = true;
    }
    
} catch (Exception $e) {
    // Log do erro
    error_log("Erro de conexão com banco: " . $e->getMessage());
    
    // Exibir mensagem amigável
    $erro_html = "
    <div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px; font-family: Arial, sans-serif;'>
        <h3>🚫 Erro de Conexão com Banco de Dados</h3>
        <p><strong>O sistema não conseguiu conectar ao banco de dados.</strong></p>
        
        <h4>🔧 Verificações necessárias:</h4>
        <ul>
            <li>✅ Servidor MySQL está rodando?</li>
            <li>✅ Credenciais estão corretas? (usuário: $usuario, host: $host:$porta)</li>
            <li>✅ Extensões PHP habilitadas? (mysqli ou PDO)</li>
            <li>✅ Banco 'geretech' existe ou pode ser criado?</li>
        </ul>
        
        <h4>🛠️ Soluções rápidas:</h4>
        <ol>
            <li><a href='inicializar_sistema.php' style='color: #721c24;'>🔄 Verificar Sistema</a></li>
            <li><a href='setup_database.php' style='color: #721c24;'>⚙️ Configurar Banco</a></li>
            <li><a href='diagnostico_php.php' style='color: #721c24;'>🔍 Diagnóstico PHP</a></li>
        </ol>
        
        <details style='margin-top: 15px;'>
            <summary style='cursor: pointer; font-weight: bold;'>📋 Detalhes técnicos</summary>
            <pre style='background: #f1f1f1; padding: 10px; margin-top: 10px; border-radius: 3px; font-size: 12px;'>" . htmlspecialchars($e->getMessage()) . "</pre>
        </details>
    </div>";
    
    die($erro_html);
}

/**
 * Função universal para executar queries
 * Funciona com MySQLi ou PDO automaticamente
 */
function executarQuery($sql, $params = [], $tipos = '') {
    global $conexao, $pdo, $usando_mysqli;
    
    try {
        if ($usando_mysqli) {
            return executarQueryMySQLi($sql, $params, $tipos);
        } else {
            return executarQueryPDO($sql, $params);
        }
    } catch (Exception $e) {
        error_log("Erro na query: " . $e->getMessage());
        return false;
    }
}

/**
 * Executar query com MySQLi
 */
function executarQueryMySQLi($sql, $params = [], $tipos = '') {
    global $conexao;
    
    $stmt = $conexao->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar query MySQLi: " . $conexao->error);
    }
    
    if (!empty($params)) {
        if (empty($tipos)) {
            // Auto-detectar tipos se não fornecidos
            $tipos = str_repeat('s', count($params));
        }
        $stmt->bind_param($tipos, ...$params);
    }
    
    $stmt->execute();
    
    if ($stmt->error) {
        throw new Exception("Erro ao executar query MySQLi: " . $stmt->error);
    }
    
    return $stmt->get_result();
}

/**
 * Executar query com PDO
 */
function executarQueryPDO($sql, $params = []) {
    global $pdo;
    
    $stmt = $pdo->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar query PDO");
    }
    
    $stmt->execute($params);
    
    return $stmt;
}

/**
 * Função para obter informações da conexão
 */
function obterInfoConexao() {
    global $usando_mysqli, $conexao, $pdo, $host, $porta, $banco;
    
    $info = [
        'extensao' => $usando_mysqli ? 'MySQLi' : 'PDO',
        'host' => $host,
        'porta' => $porta,
        'banco' => $banco,
        'ativa' => $conexao_ativa
    ];
    
    if ($usando_mysqli && $conexao) {
        $info['versao'] = $conexao->server_info;
        $info['charset'] = $conexao->character_set_name();
    } elseif (!$usando_mysqli && $pdo) {
        $info['versao'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        $info['charset'] = 'utf8mb4';
    }
    
    return $info;
}

/**
 * Função para testar a conexão
 */
function testarConexao() {
    global $usando_mysqli, $conexao, $pdo;
    
    try {
        if ($usando_mysqli) {
            $resultado = $conexao->query("SELECT 1 as teste");
            return $resultado && $resultado->num_rows > 0;
        } else {
            $stmt = $pdo->query("SELECT 1 as teste");
            return $stmt && $stmt->rowCount() >= 0;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Função para fechar conexões
 */
function fecharConexao() {
    global $conexao, $pdo, $conexao_ativa;
    
    // Verificar se a conexão MySQLi existe e ainda está ativa
    if ($conexao && is_object($conexao) && !$conexao->connect_errno) {
        try {
            $conexao->close();
        } catch (Exception $e) {
            // Conexão já foi fechada, ignorar erro
            error_log("Aviso: Tentativa de fechar conexão MySQLi já fechada: " . $e->getMessage());
        }
        $conexao = null;
    }
    
    // Verificar se a conexão PDO existe
    if ($pdo && is_object($pdo)) {
        try {
            $pdo = null;
        } catch (Exception $e) {
            // Conexão já foi fechada, ignorar erro
            error_log("Aviso: Tentativa de fechar conexão PDO já fechada: " . $e->getMessage());
        }
    }
    
    $conexao_ativa = false;
}

/**
 * Função para obter estatísticas do banco
 */
function obterEstatisticasBanco() {
    global $usando_mysqli, $conexao, $pdo;
    
    try {
        $stats = [];
        
        if ($usando_mysqli) {
            // Contar tabelas
            $resultado = $conexao->query("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = DATABASE()");
            $stats['tabelas'] = $resultado->fetch_assoc()['total'];
            
            // Tamanho do banco
            $resultado = $conexao->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as tamanho_mb FROM information_schema.tables WHERE table_schema = DATABASE()");
            $stats['tamanho_mb'] = $resultado->fetch_assoc()['tamanho_mb'] ?? 0;
        } else {
            // Contar tabelas
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = DATABASE()");
            $stats['tabelas'] = $stmt->fetchColumn();
            
            // Tamanho do banco
            $stmt = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as tamanho_mb FROM information_schema.tables WHERE table_schema = DATABASE()");
            $stats['tamanho_mb'] = $stmt->fetchColumn() ?? 0;
        }
        
        return $stats;
    } catch (Exception $e) {
        return ['tabelas' => 0, 'tamanho_mb' => 0];
    }
}

// Registrar função para fechar conexão ao final do script
register_shutdown_function('fecharConexao');

// Definir constantes úteis
define('DB_CONECTADO', $conexao_ativa);
define('DB_EXTENSAO', $usando_mysqli ? 'MYSQLI' : 'PDO');
define('DB_HOST', $host);
define('DB_BANCO', $banco);

?>