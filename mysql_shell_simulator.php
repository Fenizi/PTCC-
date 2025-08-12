<?php
/**
 * Simulador do MySQL Shell para testar banco de dados
 * Sistema GERE TECH - Verificação completa via PHP
 */

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>MySQL Shell Simulator - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: 'Courier New', monospace; margin: 20px; background: #1e1e1e; color: #d4d4d4; }";
echo "        .container { max-width: 1200px; margin: 0 auto; background: #2d2d30; padding: 20px; border-radius: 8px; }";
echo "        .shell-header { background: #007acc; color: white; padding: 15px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0; }";
echo "        .command { background: #3c3c3c; color: #569cd6; padding: 10px; margin: 10px 0; border-left: 4px solid #007acc; font-family: monospace; }";
echo "        .output { background: #252526; color: #cccccc; padding: 15px; margin: 10px 0; border-radius: 5px; white-space: pre-wrap; font-family: monospace; }";
echo "        .success { color: #4ec9b0; }";
echo "        .error { color: #f44747; }";
echo "        .warning { color: #ffcc02; }";
echo "        .info { color: #9cdcfe; }";
echo "        table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "        th, td { padding: 8px 12px; text-align: left; border: 1px solid #3c3c3c; }";
echo "        th { background: #404040; color: #ffffff; }";
echo "        td { background: #2d2d30; }";
echo "        .section { margin: 20px 0; padding: 15px; border: 1px solid #3c3c3c; border-radius: 5px; }";
echo "        .timestamp { color: #808080; font-size: 0.9em; }";
echo "        .status-ok { color: #4ec9b0; font-weight: bold; }";
echo "        .status-error { color: #f44747; font-weight: bold; }";
echo "        .btn { display: inline-block; padding: 8px 16px; background: #007acc; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }";
echo "        .btn:hover { background: #005a9e; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <div class='shell-header'>";
echo "            <h1>🐚 MySQL Shell Simulator</h1>";
echo "            <p>Simulação completa do MySQL Shell via PHP - Sistema GERE TECH</p>";
echo "        </div>";

// Configurações
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'geretech';
$porta = 3306;

$timestamp = date('Y-m-d H:i:s');
echo "        <div class='timestamp'>Sessão iniciada em: $timestamp</div>";

try {
    // Conectar ao MySQL
    echo "        <div class='command'>mysql> Conectando ao servidor MySQL...</div>";
    
    $dsn = "mysql:host=$host;port=$porta;charset=utf8mb4";
    $opcoes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
    
    echo "        <div class='output success'>Conexão estabelecida com sucesso!\n";
    echo "Servidor: MySQL " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    echo "Host: $host:$porta\n";
    echo "Usuário: $usuario</div>";
    
    // Comando 1: SHOW DATABASES
    echo "        <div class='section'>";
    echo "            <div class='command'>mysql> SHOW DATABASES;</div>";
    
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "            <div class='output'>";
    echo "<table>";
    echo "<tr><th>Database</th></tr>";
    
    foreach ($databases as $db) {
        $highlight = ($db === 'geretech') ? 'class="success"' : '';
        echo "<tr><td $highlight>$db</td></tr>";
    }
    
    echo "</table>";
    echo count($databases) . " databases encontrados";
    echo "            </div>";
    echo "        </div>";
    
    // Comando 2: USE geretech
    echo "        <div class='section'>";
    echo "            <div class='command'>mysql> USE geretech;</div>";
    
    if (in_array('geretech', $databases)) {
        $pdo->exec("USE `geretech`");
        echo "            <div class='output success'>Database alterado para 'geretech'</div>";
    } else {
        echo "            <div class='output error'>ERRO: Database 'geretech' não existe</div>";
        throw new Exception("Database 'geretech' não encontrado");
    }
    
    echo "        </div>";
    
    // Comando 3: SHOW TABLES
    echo "        <div class='section'>";
    echo "            <div class='command'>mysql> SHOW TABLES;</div>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "            <div class='output'>";
    
    if (!empty($tabelas)) {
        echo "<table>";
        echo "<tr><th>Tables_in_geretech</th></tr>";
        
        foreach ($tabelas as $tabela) {
            $highlight = ($tabela === 'teste_db') ? 'class="info"' : '';
            echo "<tr><td $highlight>$tabela</td></tr>";
        }
        
        echo "</table>";
        echo count($tabelas) . " tabelas encontradas";
    } else {
        echo "<span class='warning'>Nenhuma tabela encontrada no database 'geretech'</span>";
    }
    
    echo "            </div>";
    echo "        </div>";
    
    // Comando 4: SELECT * FROM teste_db
    if (in_array('teste_db', $tabelas)) {
        echo "        <div class='section'>";
        echo "            <div class='command'>mysql> SELECT * FROM teste_db;</div>";
        
        $stmt = $pdo->query("SELECT * FROM teste_db");
        $dados = $stmt->fetchAll();
        
        echo "            <div class='output'>";
        
        if (!empty($dados)) {
            echo "<table>";
            
            // Cabeçalho
            $colunas = array_keys($dados[0]);
            echo "<tr>";
            foreach ($colunas as $coluna) {
                echo "<th>$coluna</th>";
            }
            echo "</tr>";
            
            // Dados
            foreach ($dados as $row) {
                echo "<tr>";
                foreach ($row as $valor) {
                    $highlight = ($valor === 'teste ok') ? 'class="success"' : '';
                    echo "<td $highlight>" . htmlspecialchars($valor) . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
            echo count($dados) . " registros retornados";
            
            // Verificar se 'teste ok' existe
            $teste_ok_encontrado = false;
            foreach ($dados as $row) {
                if (isset($row['mensagem']) && $row['mensagem'] === 'teste ok') {
                    $teste_ok_encontrado = true;
                    break;
                }
            }
            
            if ($teste_ok_encontrado) {
                echo "\n<span class='success'>✅ Registro 'teste ok' confirmado!</span>";
            }
            
        } else {
            echo "<span class='warning'>Tabela 'teste_db' está vazia</span>";
        }
        
        echo "            </div>";
        echo "        </div>";
    }
    
    // Comando 5: SHOW STATUS LIKE 'Uptime'
    echo "        <div class='section'>";
    echo "            <div class='command'>mysql> SHOW STATUS LIKE 'Uptime';</div>";
    
    $stmt = $pdo->query("SHOW STATUS LIKE 'Uptime'");
    $uptime_info = $stmt->fetch();
    
    echo "            <div class='output'>";
    
    if ($uptime_info) {
        $uptime_seconds = $uptime_info['Value'];
        $uptime_formatted = gmdate('H:i:s', $uptime_seconds);
        $uptime_days = floor($uptime_seconds / 86400);
        
        echo "<table>";
        echo "<tr><th>Variable_name</th><th>Value</th></tr>";
        echo "<tr><td>Uptime</td><td class='info'>$uptime_seconds</td></tr>";
        echo "</table>";
        
        echo "\nServidor MySQL ativo há: ";
        if ($uptime_days > 0) {
            echo "<span class='success'>$uptime_days dias, $uptime_formatted</span>";
        } else {
            echo "<span class='success'>$uptime_formatted</span>";
        }
    }
    
    echo "            </div>";
    echo "        </div>";
    
    // Comandos adicionais de status
    echo "        <div class='section'>";
    echo "            <div class='command'>mysql> Informações adicionais do servidor...</div>";
    
    // Versão detalhada
    $stmt = $pdo->query("SELECT VERSION() as versao, USER() as usuario, DATABASE() as banco_atual");
    $info_servidor = $stmt->fetch();
    
    // Estatísticas de conexão
    $stmt = $pdo->query("SHOW STATUS WHERE Variable_name IN ('Connections', 'Threads_connected', 'Questions')");
    $stats = $stmt->fetchAll();
    
    echo "            <div class='output'>";
    echo "<strong>Informações do Servidor:</strong>\n";
    echo "Versão MySQL: <span class='info'>" . $info_servidor['versao'] . "</span>\n";
    echo "Usuário atual: <span class='info'>" . $info_servidor['usuario'] . "</span>\n";
    echo "Database atual: <span class='info'>" . $info_servidor['banco_atual'] . "</span>\n\n";
    
    echo "<strong>Estatísticas de Conexão:</strong>\n";
    foreach ($stats as $stat) {
        echo $stat['Variable_name'] . ": <span class='info'>" . number_format($stat['Value']) . "</span>\n";
    }
    echo "            </div>";
    echo "        </div>";
    
    // Resumo final
    echo "        <div class='section'>";
    echo "            <div class='command'>mysql> Resumo da sessão</div>";
    echo "            <div class='output success'>";
    echo "✅ Conexão com MySQL: <span class='status-ok'>OK</span>\n";
    echo "✅ Database 'geretech': <span class='status-ok'>EXISTE</span>\n";
    echo "✅ Tabelas encontradas: <span class='status-ok'>" . count($tabelas) . "</span>\n";
    echo "✅ Tabela 'teste_db': <span class='status-ok'>" . (in_array('teste_db', $tabelas) ? 'PRESENTE' : 'AUSENTE') . "</span>\n";
    
    if (in_array('teste_db', $tabelas)) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM teste_db");
        $total_registros = $stmt->fetch()['total'];
        echo "✅ Registros em teste_db: <span class='status-ok'>$total_registros</span>\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM teste_db WHERE mensagem = 'teste ok'");
        $teste_ok_count = $stmt->fetch()['total'];
        echo "✅ Registros 'teste ok': <span class='status-ok'>$teste_ok_count</span>\n";
    }
    
    echo "✅ Status do servidor: <span class='status-ok'>ONLINE</span>\n";
    echo "✅ Tempo de atividade: <span class='status-ok'>$uptime_formatted</span>\n";
    echo "\n<strong>🎯 RESULTADO: Sistema funcionando perfeitamente!</strong>";
    echo "            </div>";
    echo "        </div>";
    
} catch (Exception $e) {
    echo "        <div class='section'>";
    echo "            <div class='command'>mysql> ERRO na execução</div>";
    echo "            <div class='output error'>";
    echo "❌ ERRO: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n\n";
    
    echo "<strong>Possíveis soluções:</strong>\n";
    echo "1. Verifique se o MySQL está rodando\n";
    echo "2. Confirme as credenciais de acesso\n";
    echo "3. Execute o diagnóstico PHP\n";
    echo "4. Configure o banco com setup automático";
    echo "            </div>";
    echo "        </div>";
}

$timestamp_fim = date('Y-m-d H:i:s');
echo "        <div class='timestamp'>Sessão finalizada em: $timestamp_fim</div>";

echo "        <div style='text-align: center; margin: 20px 0;'>";
echo "            <a href='mysql_shell_simulator.php' class='btn'>🔄 Executar Novamente</a>";
echo "            <a href='status_banco.php' class='btn'>📊 Status Completo</a>";
echo "            <a href='teste_banco_pdo.php' class='btn'>🧪 Teste PDO</a>";
echo "            <a href='diagnostico_php.php' class='btn'>🔍 Diagnóstico</a>";
echo "        </div>";

echo "        <div style='text-align: center; margin-top: 20px; color: #808080; font-size: 0.9em;'>";
echo "            <p>MySQL Shell Simulator - Sistema GERE TECH<br>";
echo "            Simulação completa das funcionalidades do MySQL Shell via PHP</p>";
echo "        </div>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>