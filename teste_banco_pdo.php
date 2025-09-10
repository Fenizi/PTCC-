<?php
/**
 * Script de teste do banco de dados usando PDO
 * Alternativa quando mysqli não está disponível
 * Sistema GERE TECH
 */

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Teste do Banco (PDO) - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo "        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .info { color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .warning { color: #856404; background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .test-step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }";
echo "        table { width: 100%; border-collapse: collapse; margin: 15px 0; }";
echo "        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "        th { background-color: #f8f9fa; font-weight: bold; }";
echo "        .status-ok { color: #28a745; font-weight: bold; }";
echo "        .status-error { color: #dc3545; font-weight: bold; }";
echo "        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 0.9em; }";
echo "        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }";
echo "        .btn:hover { background: #0056b3; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <h1>🧪 Teste do Banco de Dados (PDO)</h1>";
echo "        <p>Este script testa a conexão com o banco <strong>geretech</strong> usando PDO como alternativa ao MySQLi.</p>";

// Configurações do banco
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'geretech';
$porta = 3306;

$testes_executados = 0;
$testes_sucesso = 0;
$erros = [];
$pdo = null;

// Função para executar teste
function executarTeste($nome, $funcao) {
    global $testes_executados, $testes_sucesso, $erros;
    
    $testes_executados++;
    echo "        <div class='test-step'>";
    echo "            <h3>Teste $testes_executados: $nome</h3>";
    
    try {
        $resultado = $funcao();
        if ($resultado) {
            $testes_sucesso++;
            echo "            <div class='success'>✅ Teste executado com sucesso!</div>";
        } else {
            echo "            <div class='error'>❌ Teste falhou!</div>";
            $erros[] = "Teste $testes_executados ($nome) falhou";
        }
    } catch (Exception $e) {
        echo "            <div class='error'>❌ Erro no teste: " . $e->getMessage() . "</div>";
        $erros[] = "Teste $testes_executados ($nome): " . $e->getMessage();
    }
    
    echo "        </div>";
}

// Verificar se PDO está disponível
if (!class_exists('PDO')) {
    echo "        <div class='error'>";
    echo "            <h3>❌ PDO não está disponível</h3>";
    echo "            <p>A extensão PDO não está instalada ou habilitada no PHP.</p>";
    echo "            <p>Execute o <a href='diagnostico_php.php'>diagnóstico PHP</a> para mais informações.</p>";
    echo "        </div>";
    echo "    </div>";
    echo "</body>";
    echo "</html>";
    exit;
}

// Verificar se o driver MySQL para PDO está disponível
if (!in_array('mysql', PDO::getAvailableDrivers())) {
    echo "        <div class='error'>";
    echo "            <h3>❌ Driver MySQL para PDO não está disponível</h3>";
    echo "            <p>O driver pdo_mysql não está instalado ou habilitado.</p>";
    echo "            <p>Drivers disponíveis: " . implode(', ', PDO::getAvailableDrivers()) . "</p>";
    echo "        </div>";
    echo "    </div>";
    echo "</body>";
    echo "</html>";
    exit;
}

// Teste 1: Conectar ao MySQL (sem banco específico)
executarTeste("Conectar ao MySQL", function() use ($host, $usuario, $senha, $porta, &$pdo) {
    $dsn = "mysql:host=$host;port=$porta;charset=utf8mb4";
    $opcoes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
    
    echo "            <p><strong>Status:</strong> Conectado ao MySQL</p>";
    echo "            <p><strong>Versão:</strong> " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</p>";
    
    return true;
});

// Teste 2: Verificar/Criar banco geretech
executarTeste("Verificar/Criar Banco 'geretech'", function() use (&$pdo, $banco) {
    // Verificar se o banco existe
    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->execute([$banco]);
    
    if ($stmt->rowCount() == 0) {
        // Banco não existe, criar
        $pdo->exec("CREATE DATABASE `$banco` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "            <p><strong>Ação:</strong> Banco '$banco' criado</p>";
    } else {
        echo "            <p><strong>Status:</strong> Banco '$banco' já existe</p>";
    }
    
    // Conectar ao banco específico
    $pdo->exec("USE `$banco`");
    
    return true;
});

// Teste 3: Criar tabela de teste
executarTeste("Criar Tabela de Teste", function() use (&$pdo) {
    // Remover tabela se existir
    $pdo->exec("DROP TABLE IF EXISTS teste_tabela_pdo");
    
    $sql_create = "
        CREATE TABLE teste_tabela_pdo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE,
            idade INT,
            ativo BOOLEAN DEFAULT TRUE,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($sql_create);
    
    echo "            <p><strong>Tabela criada:</strong> teste_tabela_pdo</p>";
    echo "            <pre>" . htmlspecialchars($sql_create) . "</pre>";
    
    return true;
});

// Teste 4: Inserir dados usando prepared statements
executarTeste("Inserir Dados com Prepared Statements", function() use (&$pdo) {
    $dados_teste = [
        ['João Silva PDO', 'joao.pdo@teste.com', 30],
        ['Maria Santos PDO', 'maria.pdo@teste.com', 25],
        ['Pedro Oliveira PDO', 'pedro.pdo@teste.com', 35],
        ['Ana Costa PDO', 'ana.pdo@teste.com', 28],
        ['Carlos Ferreira PDO', 'carlos.pdo@teste.com', 42]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO teste_tabela_pdo (nome, email, idade) VALUES (?, ?, ?)");
    
    $inseridos = 0;
    foreach ($dados_teste as $dados) {
        if ($stmt->execute($dados)) {
            $inseridos++;
        }
    }
    
    echo "            <p><strong>Registros inseridos:</strong> $inseridos de " . count($dados_teste) . "</p>";
    
    return $inseridos > 0;
});

// Teste 5: Consultar dados
executarTeste("Consultar Dados", function() use (&$pdo) {
    $stmt = $pdo->query("SELECT * FROM teste_tabela_pdo ORDER BY nome");
    $resultados = $stmt->fetchAll();
    
    $total = count($resultados);
    echo "            <p><strong>Total de registros:</strong> $total</p>";
    
    if ($total > 0) {
        echo "            <table>";
        echo "                <tr><th>ID</th><th>Nome</th><th>Email</th><th>Idade</th><th>Ativo</th><th>Data Criação</th></tr>";
        
        foreach ($resultados as $row) {
            echo "                <tr>";
            echo "                    <td>" . $row['id'] . "</td>";
            echo "                    <td>" . htmlspecialchars($row['nome']) . "</td>";
            echo "                    <td>" . htmlspecialchars($row['email']) . "</td>";
            echo "                    <td>" . $row['idade'] . "</td>";
            echo "                    <td>" . ($row['ativo'] ? 'Sim' : 'Não') . "</td>";
            echo "                    <td>" . $row['data_criacao'] . "</td>";
            echo "                </tr>";
        }
        
        echo "            </table>";
    }
    
    return $total > 0;
});

// Teste 6: Testar transações
executarTeste("Testar Transações", function() use (&$pdo) {
    try {
        $pdo->beginTransaction();
        
        // Inserir um registro
        $stmt = $pdo->prepare("INSERT INTO teste_tabela_pdo (nome, email, idade) VALUES (?, ?, ?)");
        $stmt->execute(['Teste Transação PDO', 'transacao.pdo@teste.com', 99]);
        $id_inserido = $pdo->lastInsertId();
        
        // Atualizar o registro
        $stmt2 = $pdo->prepare("UPDATE teste_tabela_pdo SET ativo = FALSE WHERE id = ?");
        $stmt2->execute([$id_inserido]);
        
        $pdo->commit();
        
        echo "            <p><strong>Transação executada:</strong> Registro inserido (ID: $id_inserido) e atualizado</p>";
        
        return true;
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
});

// Teste 7: Consulta com parâmetros
executarTeste("Consulta com Parâmetros", function() use (&$pdo) {
    $idade_minima = 30;
    $stmt = $pdo->prepare("SELECT nome, email, idade FROM teste_tabela_pdo WHERE idade >= ? ORDER BY idade DESC");
    $stmt->execute([$idade_minima]);
    $resultados = $stmt->fetchAll();
    
    $encontrados = count($resultados);
    echo "            <p><strong>Registros encontrados (idade >= $idade_minima):</strong> $encontrados</p>";
    
    if ($encontrados > 0) {
        echo "            <ul>";
        foreach ($resultados as $row) {
            echo "                <li>" . htmlspecialchars($row['nome']) . " (" . $row['idade'] . " anos) - " . htmlspecialchars($row['email']) . "</li>";
        }
        echo "            </ul>";
    }
    
    return true;
});

// Teste 8: Limpar dados de teste
executarTeste("Limpar Dados de Teste", function() use (&$pdo) {
    // Contar registros antes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM teste_tabela_pdo");
    $antes = $stmt->fetch()['total'];
    
    // Remover tabela de teste
    $pdo->exec("DROP TABLE IF EXISTS teste_tabela_pdo");
    
    echo "            <p><strong>Registros removidos:</strong> $antes</p>";
    echo "            <p><strong>Tabela de teste removida:</strong> teste_tabela_pdo</p>";
    
    return true;
});

// Resumo final
echo "        <div class='info'>";
echo "            <h3>📊 Resumo dos Testes</h3>";
echo "            <p><strong>Total de testes:</strong> $testes_executados</p>";
echo "            <p><strong>Testes bem-sucedidos:</strong> <span class='status-ok'>$testes_sucesso</span></p>";
echo "            <p><strong>Testes falharam:</strong> <span class='" . (count($erros) > 0 ? 'status-error' : 'status-ok') . "'>" . count($erros) . "</span></p>";

if (count($erros) > 0) {
    echo "            <h4>❌ Erros encontrados:</h4>";
    echo "            <ul>";
    foreach ($erros as $erro) {
        echo "                <li>" . htmlspecialchars($erro) . "</li>";
    }
    echo "            </ul>";
}

if ($testes_sucesso === $testes_executados) {
    echo "            <div class='success'>";
    echo "                <h4>🎉 Todos os testes passaram!</h4>";
    echo "                <p>Seu banco de dados <strong>geretech</strong> está funcionando perfeitamente com PDO!</p>";
    echo "                <p>Agora você pode configurar o sistema para usar PDO em vez de MySQLi.</p>";
    echo "            </div>";
} else {
    echo "            <div class='error'>";
    echo "                <h4>⚠️ Alguns testes falharam</h4>";
    echo "                <p>Verifique os erros acima e corrija as configurações necessárias.</p>";
    echo "            </div>";
}

echo "        </div>";

echo "        <div style='text-align: center; margin: 30px 0;'>";
echo "            <a href='diagnostico_php.php' class='btn'>🔍 Diagnóstico PHP</a>";
echo "            <a href='teste_banco_pdo.php' class='btn'>🔄 Executar Novamente</a>";
echo "            <a href='setup_database.php' class='btn'>⚙️ Configurar Banco</a>";
echo "            <a href='pages/login.php' class='btn'>🔐 Acessar Sistema</a>";
echo "        </div>";

echo "        <p style='text-align: center; margin-top: 30px; color: #666;'>";
echo "            <small>Teste executado em " . date('d/m/Y H:i:s') . " - Sistema GERE TECH (PDO)</small>";
echo "        </p>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>