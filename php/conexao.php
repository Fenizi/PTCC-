<?php
/**
 * Arquivo de conexão simplificada com o banco de dados MySQL
 * Sistema GERE TECH
 * 
 * Versão híbrida que funciona com MySQLi ou PDO automaticamente
 * Versão: 2.0 - Sistema Híbrido Simplificado
 */

// Configurações do banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'geretech';
$porta = 3306;

// Variáveis de conexão
$conexao = null;
$pdo = null;
$usando_mysqli = false;

try {
    // Detectar qual extensão usar
    $mysqli_disponivel = class_exists('mysqli');
    $pdo_disponivel = class_exists('PDO') && in_array('mysql', PDO::getAvailableDrivers());
    
    if ($mysqli_disponivel) {
        // Usar MySQLi
        $conexao = new mysqli($host, $usuario, $senha, $banco, $porta);
        
        if ($conexao->connect_error) {
            throw new Exception("Erro MySQLi: " . $conexao->connect_error);
        }
        
        $conexao->set_charset("utf8mb4");
        $usando_mysqli = true;
        
    } elseif ($pdo_disponivel) {
        // Usar PDO
        $dsn = "mysql:host={$host};port={$porta};dbname={$banco};charset=utf8mb4";
        $opcoes = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        
        $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
        $usando_mysqli = false;
        
    } else {
        throw new Exception("Nenhuma extensão MySQL disponível (mysqli ou PDO)");
    }
    
} catch (Exception $e) {
    die("<div style='padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px; font-family: Arial, sans-serif;'>\n" .
        "<h4>🚫 Erro de Conexão</h4>\n" .
        "<p>Não foi possível conectar ao banco de dados.</p>\n" .
        "<p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n" .
        "<p><a href='../inicializar_sistema.php' style='color: #721c24;'>🔄 Verificar Sistema</a></p>\n" .
        "</div>");
}

/**
 * Função simplificada para executar queries
 */
function query($sql, $params = []) {
    global $conexao, $pdo, $usando_mysqli;
    
    try {
        if ($usando_mysqli) {
            if (empty($params)) {
                return $conexao->query($sql);
            } else {
                $stmt = $conexao->prepare($sql);
                $tipos = str_repeat('s', count($params));
                $stmt->bind_param($tipos, ...$params);
                $stmt->execute();
                return $stmt->get_result();
            }
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
    } catch (Exception $e) {
        error_log("Erro na query: " . $e->getMessage());
        return false;
    }
}

/**
 * Função para fechar conexão
 */
function fechar() {
    global $conexao, $pdo;
    
    if ($conexao) {
        $conexao->close();
    }
    
    $pdo = null;
}

// Fechar conexão ao final do script
register_shutdown_function('fechar');

?>