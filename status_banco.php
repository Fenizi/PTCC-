<?php
/**
 * Script para verificar o status completo do banco de dados
 * Sistema GERE TECH
 */

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Status do Banco - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo "        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .info { color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .warning { color: #856404; background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .section { margin: 30px 0; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; }";
echo "        table { width: 100%; border-collapse: collapse; margin: 15px 0; }";
echo "        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "        th { background-color: #f8f9fa; font-weight: bold; }";
echo "        .status-ok { color: #28a745; font-weight: bold; }";
echo "        .status-error { color: #dc3545; font-weight: bold; }";
echo "        .status-warning { color: #ffc107; font-weight: bold; }";
echo "        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 0.9em; }";
echo "        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }";
echo "        .btn:hover { background: #0056b3; }";
echo "        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }";
echo "        .card { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <h1>📊 Status Completo do Banco de Dados</h1>";
echo "        <p>Verificação em tempo real do banco <strong>geretech</strong> - " . date('d/m/Y H:i:s') . "</p>";

// Configurações do banco
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'geretech';
$porta = 3306;

try {
    // Verificar se PDO está disponível
    if (!class_exists('PDO')) {
        throw new Exception("PDO não está disponível");
    }
    
    if (!in_array('mysql', PDO::getAvailableDrivers())) {
        throw new Exception("Driver MySQL para PDO não está disponível");
    }
    
    // Conectar ao MySQL
    $dsn = "mysql:host=$host;port=$porta;charset=utf8mb4";
    $opcoes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
    
    // Seção 1: Informações do Servidor
    echo "        <div class='section'>";
    echo "            <h2>🖥️ Informações do Servidor MySQL</h2>";
    echo "            <div class='grid'>";
    
    $versao = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    $info = $pdo->getAttribute(PDO::ATTR_SERVER_INFO);
    
    echo "                <div class='card'>";
    echo "                    <h4>📋 Detalhes do Servidor</h4>";
    echo "                    <p><strong>Host:</strong> $host:$porta</p>";
    echo "                    <p><strong>Versão:</strong> $versao</p>";
    echo "                    <p><strong>Info:</strong> $info</p>";
    echo "                    <p><strong>Status:</strong> <span class='status-ok'>✅ Online</span></p>";
    echo "                </div>";
    
    // Verificar uptime
    $stmt = $pdo->query("SHOW STATUS LIKE 'Uptime'");
    $uptime = $stmt->fetch();
    $uptime_seconds = $uptime['Value'];
    $uptime_formatted = gmdate('H:i:s', $uptime_seconds);
    
    echo "                <div class='card'>";
    echo "                    <h4>⏱️ Tempo de Atividade</h4>";
    echo "                    <p><strong>Uptime:</strong> $uptime_formatted</p>";
    echo "                    <p><strong>Segundos:</strong> " . number_format($uptime_seconds) . "</p>";
    echo "                </div>";
    
    echo "            </div>";
    echo "        </div>";
    
    // Seção 2: Status do Banco geretech
    echo "        <div class='section'>";
    echo "            <h2>🏛️ Status do Banco 'geretech'</h2>";
    
    // Verificar se o banco existe
    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->execute([$banco]);
    
    if ($stmt->rowCount() > 0) {
        echo "            <div class='success'>✅ Banco '$banco' existe e está acessível</div>";
        
        // Conectar ao banco específico
        $pdo->exec("USE `$banco`");
        
        // Obter informações do banco
        $stmt = $pdo->query("
            SELECT 
                DEFAULT_CHARACTER_SET_NAME as charset,
                DEFAULT_COLLATION_NAME as collation
            FROM INFORMATION_SCHEMA.SCHEMATA 
            WHERE SCHEMA_NAME = '$banco'
        ");
        $banco_info = $stmt->fetch();
        
        echo "            <div class='info'>";
        echo "                <h4>📋 Configurações do Banco</h4>";
        echo "                <p><strong>Charset:</strong> " . $banco_info['charset'] . "</p>";
        echo "                <p><strong>Collation:</strong> " . $banco_info['collation'] . "</p>";
        echo "            </div>";
        
    } else {
        echo "            <div class='error'>❌ Banco '$banco' não existe</div>";
        throw new Exception("Banco '$banco' não encontrado");
    }
    
    echo "        </div>";
    
    // Seção 3: Tabelas do Sistema
    echo "        <div class='section'>";
    echo "            <h2>📋 Tabelas do Sistema</h2>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tabelas) > 0) {
        echo "            <div class='success'>✅ Total de tabelas encontradas: " . count($tabelas) . "</div>";
        
        echo "            <table>";
        echo "                <tr><th>Tabela</th><th>Registros</th><th>Tamanho</th><th>Engine</th><th>Status</th></tr>";
        
        foreach ($tabelas as $tabela) {
            // Contar registros
            $stmt_count = $pdo->query("SELECT COUNT(*) as total FROM `$tabela`");
            $total_registros = $stmt_count->fetch()['total'];
            
            // Obter informações da tabela
            $stmt_info = $pdo->query("
                SELECT 
                    ENGINE,
                    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'tamanho_mb'
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = '$banco' AND TABLE_NAME = '$tabela'
            ");
            $info_tabela = $stmt_info->fetch();
            
            echo "                <tr>";
            echo "                    <td><strong>$tabela</strong></td>";
            echo "                    <td>" . number_format($total_registros) . "</td>";
            echo "                    <td>" . $info_tabela['tamanho_mb'] . " MB</td>";
            echo "                    <td>" . $info_tabela['ENGINE'] . "</td>";
            
            if ($total_registros > 0) {
                echo "                    <td><span class='status-ok'>✅ Ativa</span></td>";
            } else {
                echo "                    <td><span class='status-warning'>⚠️ Vazia</span></td>";
            }
            
            echo "                </tr>";
        }
        
        echo "            </table>";
        
    } else {
        echo "            <div class='warning'>⚠️ Nenhuma tabela encontrada no banco</div>";
    }
    
    echo "        </div>";
    
    // Seção 4: Verificação da Tabela teste_db
    echo "        <div class='section'>";
    echo "            <h2>🧪 Status da Tabela 'teste_db'</h2>";
    
    if (in_array('teste_db', $tabelas)) {
        echo "            <div class='success'>✅ Tabela 'teste_db' encontrada</div>";
        
        // Estrutura da tabela
        $stmt = $pdo->query("DESCRIBE teste_db");
        $estrutura = $stmt->fetchAll();
        
        echo "            <h4>📋 Estrutura da Tabela:</h4>";
        echo "            <table>";
        echo "                <tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        
        foreach ($estrutura as $campo) {
            echo "                <tr>";
            echo "                    <td><strong>" . $campo['Field'] . "</strong></td>";
            echo "                    <td>" . $campo['Type'] . "</td>";
            echo "                    <td>" . $campo['Null'] . "</td>";
            echo "                    <td>" . $campo['Key'] . "</td>";
            echo "                    <td>" . ($campo['Default'] ?? 'NULL') . "</td>";
            echo "                    <td>" . $campo['Extra'] . "</td>";
            echo "                </tr>";
        }
        
        echo "            </table>";
        
        // Dados da tabela
        $stmt = $pdo->query("SELECT * FROM teste_db ORDER BY id");
        $dados = $stmt->fetchAll();
        
        if (count($dados) > 0) {
            echo "            <h4>📊 Dados na Tabela (" . count($dados) . " registros):</h4>";
            echo "            <table>";
            echo "                <tr><th>ID</th><th>Mensagem</th><th>Data Criação</th><th>Status</th></tr>";
            
            foreach ($dados as $row) {
                echo "                <tr>";
                echo "                    <td>" . $row['id'] . "</td>";
                echo "                    <td>";
                
                if ($row['mensagem'] === 'teste ok') {
                    echo "<strong style='color: #28a745;'>" . htmlspecialchars($row['mensagem']) . " ✅</strong>";
                } else {
                    echo htmlspecialchars($row['mensagem']);
                }
                
                echo "</td>";
                echo "                    <td>" . $row['data_criacao'] . "</td>";
                echo "                    <td>" . ucfirst($row['status']) . "</td>";
                echo "                </tr>";
            }
            
            echo "            </table>";
            
            // Verificar se 'teste ok' existe
            $teste_ok = array_filter($dados, function($row) {
                return $row['mensagem'] === 'teste ok';
            });
            
            if (!empty($teste_ok)) {
                echo "            <div class='success'>";
                echo "                <h4>🎯 Registro 'teste ok' Confirmado!</h4>";
                $registro = array_values($teste_ok)[0];
                echo "                <p><strong>ID:</strong> " . $registro['id'] . "</p>";
                echo "                <p><strong>Data:</strong> " . $registro['data_criacao'] . "</p>";
                echo "                <p><strong>Status:</strong> " . ucfirst($registro['status']) . "</p>";
                echo "            </div>";
            }
            
        } else {
            echo "            <div class='warning'>⚠️ Tabela 'teste_db' está vazia</div>";
        }
        
    } else {
        echo "            <div class='error'>❌ Tabela 'teste_db' não encontrada</div>";
    }
    
    echo "        </div>";
    
    // Seção 5: Tabelas do Sistema Original
    echo "        <div class='section'>";
    echo "            <h2>🏢 Tabelas do Sistema GERE TECH</h2>";
    
    $tabelas_sistema = ['usuarios', 'clientes', 'produtos', 'vendas', 'configuracoes', 'logs_atividades', 'alertas'];
    $tabelas_encontradas = 0;
    
    echo "            <table>";
    echo "                <tr><th>Tabela</th><th>Status</th><th>Registros</th><th>Última Atualização</th></tr>";
    
    foreach ($tabelas_sistema as $tabela_sistema) {
        echo "                <tr>";
        echo "                    <td><strong>$tabela_sistema</strong></td>";
        
        if (in_array($tabela_sistema, $tabelas)) {
            $tabelas_encontradas++;
            
            // Contar registros
            $stmt_count = $pdo->query("SELECT COUNT(*) as total FROM `$tabela_sistema`");
            $total = $stmt_count->fetch()['total'];
            
            echo "                    <td><span class='status-ok'>✅ Existe</span></td>";
            echo "                    <td>" . number_format($total) . "</td>";
            
            // Tentar obter última atualização
            try {
                $stmt_update = $pdo->query("
                    SELECT UPDATE_TIME 
                    FROM INFORMATION_SCHEMA.TABLES 
                    WHERE TABLE_SCHEMA = '$banco' AND TABLE_NAME = '$tabela_sistema'
                ");
                $update_info = $stmt_update->fetch();
                echo "                    <td>" . ($update_info['UPDATE_TIME'] ?? 'N/A') . "</td>";
            } catch (Exception $e) {
                echo "                    <td>N/A</td>";
            }
            
        } else {
            echo "                    <td><span class='status-error'>❌ Não existe</span></td>";
            echo "                    <td>-</td>";
            echo "                    <td>-</td>";
        }
        
        echo "                </tr>";
    }
    
    echo "            </table>";
    
    $porcentagem = round(($tabelas_encontradas / count($tabelas_sistema)) * 100);
    
    if ($tabelas_encontradas === count($tabelas_sistema)) {
        echo "            <div class='success'>✅ Sistema completo: $tabelas_encontradas/" . count($tabelas_sistema) . " tabelas ($porcentagem%)</div>";
    } elseif ($tabelas_encontradas > 0) {
        echo "            <div class='warning'>⚠️ Sistema parcial: $tabelas_encontradas/" . count($tabelas_sistema) . " tabelas ($porcentagem%)</div>";
    } else {
        echo "            <div class='error'>❌ Sistema não configurado: 0/" . count($tabelas_sistema) . " tabelas</div>";
    }
    
    echo "        </div>";
    
    // Resumo Final
    echo "        <div class='section'>";
    echo "            <h2>📊 Resumo Geral</h2>";
    echo "            <div class='grid'>";
    
    echo "                <div class='card'>";
    echo "                    <h4>🏛️ Banco de Dados</h4>";
    echo "                    <p><strong>Nome:</strong> $banco</p>";
    echo "                    <p><strong>Status:</strong> <span class='status-ok'>✅ Online</span></p>";
    echo "                    <p><strong>Charset:</strong> " . $banco_info['charset'] . "</p>";
    echo "                </div>";
    
    echo "                <div class='card'>";
    echo "                    <h4>📋 Tabelas</h4>";
    echo "                    <p><strong>Total:</strong> " . count($tabelas) . "</p>";
    echo "                    <p><strong>Sistema:</strong> $tabelas_encontradas/" . count($tabelas_sistema) . "</p>";
    echo "                    <p><strong>Teste:</strong> " . (in_array('teste_db', $tabelas) ? '✅ Presente' : '❌ Ausente') . "</p>";
    echo "                </div>";
    
    echo "                <div class='card'>";
    echo "                    <h4>🔧 Servidor</h4>";
    echo "                    <p><strong>MySQL:</strong> $versao</p>";
    echo "                    <p><strong>Uptime:</strong> $uptime_formatted</p>";
    echo "                    <p><strong>Host:</strong> $host:$porta</p>";
    echo "                </div>";
    
    echo "            </div>";
    echo "        </div>";
    
} catch (Exception $e) {
    echo "        <div class='error'>";
    echo "            <h3>❌ Erro na Verificação</h3>";
    echo "            <p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "            <p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "            <p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "        </div>";
    
    echo "        <div class='info'>";
    echo "            <h4>💡 Possíveis Soluções:</h4>";
    echo "            <ul>";
    echo "                <li>Verifique se o MySQL está rodando</li>";
    echo "                <li>Confirme as credenciais de acesso</li>";
    echo "                <li>Execute o <a href='diagnostico_php.php'>diagnóstico PHP</a></li>";
    echo "                <li>Configure o banco com <a href='setup_database.php'>setup automático</a></li>";
    echo "            </ul>";
    echo "        </div>";
}

echo "        <div style='text-align: center; margin: 30px 0;'>";
echo "            <a href='status_banco.php' class='btn'>🔄 Atualizar Status</a>";
echo "            <a href='teste_banco_pdo.php' class='btn'>🧪 Testar Banco</a>";
echo "            <a href='diagnostico_php.php' class='btn'>🔍 Diagnóstico PHP</a>";
echo "            <a href='setup_database.php' class='btn'>⚙️ Configurar Banco</a>";
echo "            <a href='pages/login.php' class='btn'>🔐 Acessar Sistema</a>";
echo "        </div>";

echo "        <p style='text-align: center; margin-top: 30px; color: #666;'>";
echo "            <small>Status verificado em " . date('d/m/Y H:i:s') . " - Sistema GERE TECH</small>";
echo "        </p>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>