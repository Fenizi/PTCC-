<?php
/**
 * Script para criar tabela teste_db e popular com dados
 * Sistema GERE TECH
 */

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Criar Tabela Teste - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo "        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .info { color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }";
echo "        table { width: 100%; border-collapse: collapse; margin: 15px 0; }";
echo "        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "        th { background-color: #f8f9fa; font-weight: bold; }";
echo "        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 0.9em; }";
echo "        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }";
echo "        .btn:hover { background: #0056b3; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <h1>🗃️ Criar Tabela teste_db</h1>";
echo "        <p>Este script irá criar uma tabela chamada <strong>teste_db</strong> e popular com dados de teste.</p>";

// Configurações do banco
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'geretech';
$porta = 3306;

try {
    // Verificar se PDO está disponível
    if (!class_exists('PDO')) {
        throw new Exception("PDO não está disponível. Execute o diagnóstico PHP.");
    }
    
    if (!in_array('mysql', PDO::getAvailableDrivers())) {
        throw new Exception("Driver MySQL para PDO não está disponível.");
    }
    
    echo "        <div class='step'>";
    echo "            <h3>Passo 1: Conectando ao banco de dados</h3>";
    
    // Conectar ao MySQL
    $dsn = "mysql:host=$host;port=$porta;charset=utf8mb4";
    $opcoes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
    
    // Verificar se o banco geretech existe
    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->execute([$banco]);
    
    if ($stmt->rowCount() == 0) {
        // Criar banco se não existir
        $pdo->exec("CREATE DATABASE `$banco` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "            <div class='success'>✅ Banco '$banco' criado com sucesso!</div>";
    } else {
        echo "            <div class='info'>ℹ️ Banco '$banco' já existe.</div>";
    }
    
    // Conectar ao banco específico
    $pdo->exec("USE `$banco`");
    echo "            <div class='success'>✅ Conectado ao banco '$banco'</div>";
    echo "        </div>";
    
    echo "        <div class='step'>";
    echo "            <h3>Passo 2: Criando tabela teste_db</h3>";
    
    // Verificar se a tabela já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) as existe FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'teste_db'");
    $stmt->execute([$banco]);
    $tabela_existe = $stmt->fetch()['existe'] > 0;
    
    if ($tabela_existe) {
        echo "            <div class='info'>ℹ️ Tabela 'teste_db' já existe. Removendo para recriar...</div>";
        $pdo->exec("DROP TABLE teste_db");
    }
    
    // Criar tabela teste_db
    $sql_create = "
        CREATE TABLE teste_db (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mensagem VARCHAR(255) NOT NULL,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('ativo', 'inativo') DEFAULT 'ativo'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql_create);
    echo "            <div class='success'>✅ Tabela 'teste_db' criada com sucesso!</div>";
    echo "            <pre>" . htmlspecialchars($sql_create) . "</pre>";
    echo "        </div>";
    
    echo "        <div class='step'>";
    echo "            <h3>Passo 3: Populando tabela com dados</h3>";
    
    // Inserir dados de teste
    $dados_teste = [
        'teste ok',
        'Sistema funcionando',
        'Banco de dados operacional',
        'Tabela criada com sucesso',
        'Dados inseridos corretamente'
    ];
    
    $stmt = $pdo->prepare("INSERT INTO teste_db (mensagem) VALUES (?)");
    
    $inseridos = 0;
    foreach ($dados_teste as $mensagem) {
        if ($stmt->execute([$mensagem])) {
            $inseridos++;
        }
    }
    
    echo "            <div class='success'>✅ $inseridos registros inseridos com sucesso!</div>";
    echo "        </div>";
    
    echo "        <div class='step'>";
    echo "            <h3>Passo 4: Verificando dados inseridos</h3>";
    
    // Consultar dados inseridos
    $stmt = $pdo->query("SELECT * FROM teste_db ORDER BY id");
    $resultados = $stmt->fetchAll();
    
    if (count($resultados) > 0) {
        echo "            <div class='success'>✅ Dados encontrados na tabela:</div>";
        echo "            <table>";
        echo "                <tr><th>ID</th><th>Mensagem</th><th>Data Criação</th><th>Status</th></tr>";
        
        foreach ($resultados as $row) {
            echo "                <tr>";
            echo "                    <td>" . $row['id'] . "</td>";
            echo "                    <td><strong>" . htmlspecialchars($row['mensagem']) . "</strong></td>";
            echo "                    <td>" . $row['data_criacao'] . "</td>";
            echo "                    <td>" . ucfirst($row['status']) . "</td>";
            echo "                </tr>";
        }
        
        echo "            </table>";
        
        // Destacar o registro principal
        $teste_ok = array_filter($resultados, function($row) {
            return $row['mensagem'] === 'teste ok';
        });
        
        if (!empty($teste_ok)) {
            $registro = array_values($teste_ok)[0];
            echo "            <div class='success'>";
            echo "                <h4>🎯 Registro Principal Encontrado:</h4>";
            echo "                <p><strong>ID:</strong> " . $registro['id'] . "</p>";
            echo "                <p><strong>Mensagem:</strong> \"" . htmlspecialchars($registro['mensagem']) . "\"</p>";
            echo "                <p><strong>Data:</strong> " . $registro['data_criacao'] . "</p>";
            echo "            </div>";
        }
        
    } else {
        echo "            <div class='error'>❌ Nenhum dado encontrado na tabela</div>";
    }
    
    echo "        </div>";
    
    echo "        <div class='step'>";
    echo "            <h3>Passo 5: Informações da tabela</h3>";
    
    // Mostrar estrutura da tabela
    $stmt = $pdo->query("DESCRIBE teste_db");
    $estrutura = $stmt->fetchAll();
    
    echo "            <div class='info'>";
    echo "                <h4>📋 Estrutura da Tabela:</h4>";
    echo "                <table>";
    echo "                    <tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    
    foreach ($estrutura as $campo) {
        echo "                    <tr>";
        echo "                        <td><strong>" . $campo['Field'] . "</strong></td>";
        echo "                        <td>" . $campo['Type'] . "</td>";
        echo "                        <td>" . $campo['Null'] . "</td>";
        echo "                        <td>" . $campo['Key'] . "</td>";
        echo "                        <td>" . ($campo['Default'] ?? 'NULL') . "</td>";
        echo "                        <td>" . $campo['Extra'] . "</td>";
        echo "                    </tr>";
    }
    
    echo "                </table>";
    echo "            </div>";
    echo "        </div>";
    
    // Resumo final
    echo "        <div class='success'>";
    echo "            <h3>🎉 Operação Concluída com Sucesso!</h3>";
    echo "            <ul>";
    echo "                <li>✅ Tabela <strong>teste_db</strong> criada</li>";
    echo "                <li>✅ Dados populados incluindo \"teste ok\"</li>";
    echo "                <li>✅ Total de registros: " . count($resultados) . "</li>";
    echo "                <li>✅ Estrutura da tabela verificada</li>";
    echo "            </ul>";
    echo "        </div>";
    
} catch (Exception $e) {
    echo "        <div class='error'>";
    echo "            <h3>❌ Erro na Operação</h3>";
    echo "            <p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "            <p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "            <p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "        </div>";
    
    echo "        <div class='info'>";
    echo "            <h4>💡 Possíveis Soluções:</h4>";
    echo "            <ul>";
    echo "                <li>Verifique se o MySQL está rodando</li>";
    echo "                <li>Confirme as credenciais de acesso (usuário: root, senha: vazia)</li>";
    echo "                <li>Execute o <a href='diagnostico_php.php'>diagnóstico PHP</a></li>";
    echo "                <li>Verifique se as extensões PDO e pdo_mysql estão habilitadas</li>";
    echo "            </ul>";
    echo "        </div>";
}

echo "        <div style='text-align: center; margin: 30px 0;'>";
echo "            <a href='criar_tabela_teste.php' class='btn'>🔄 Executar Novamente</a>";
echo "            <a href='teste_banco_pdo.php' class='btn'>🧪 Testar Banco</a>";
echo "            <a href='diagnostico_php.php' class='btn'>🔍 Diagnóstico PHP</a>";
echo "            <a href='pages/login.php' class='btn'>🔐 Acessar Sistema</a>";
echo "        </div>";

echo "        <p style='text-align: center; margin-top: 30px; color: #666;'>";
echo "            <small>Operação executada em " . date('d/m/Y H:i:s') . " - Sistema GERE TECH</small>";
echo "        </p>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>