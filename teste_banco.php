<?php
/**
 * Script de teste do banco de dados
 * Cria uma tabela de teste e executa operações básicas
 * Sistema GERE TECH
 */

// Incluir arquivo de conexão
require_once 'includes/conexao.php';

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Teste do Banco de Dados - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo "        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .info { color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
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
echo "        <h1>🧪 Teste Completo do Banco de Dados</h1>";
echo "        <p>Este script irá testar a conexão e funcionalidades do banco <strong>geretech</strong></p>";

$testes_executados = 0;
$testes_sucesso = 0;
$erros = [];

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

// Teste 1: Verificar conexão
executarTeste("Verificar Conexão com MySQL", function() {
    global $conexao;
    
    if (!isset($conexao) || !($conexao instanceof mysqli)) {
        throw new Exception("Variável de conexão não encontrada");
    }
    
    if ($conexao->connect_error) {
        throw new Exception("Erro de conexão: " . $conexao->connect_error);
    }
    
    echo "            <p><strong>Status:</strong> Conectado ao MySQL</p>";
    echo "            <p><strong>Servidor:</strong> " . $conexao->host_info . "</p>";
    echo "            <p><strong>Versão:</strong> " . $conexao->server_info . "</p>";
    
    return true;
});

// Teste 2: Verificar banco geretech
executarTeste("Verificar Banco 'geretech'", function() {
    global $conexao;
    
    $resultado = $conexao->query("SELECT DATABASE() as banco_atual");
    if (!$resultado) {
        throw new Exception("Erro ao verificar banco: " . $conexao->error);
    }
    
    $row = $resultado->fetch_assoc();
    if ($row['banco_atual'] !== 'geretech') {
        throw new Exception("Banco atual: " . $row['banco_atual'] . " (esperado: geretech)");
    }
    
    echo "            <p><strong>Banco ativo:</strong> " . $row['banco_atual'] . "</p>";
    
    return true;
});

// Teste 3: Criar tabela de teste
executarTeste("Criar Tabela de Teste", function() {
    global $conexao;
    
    // Remover tabela se existir
    $conexao->query("DROP TABLE IF EXISTS teste_tabela");
    
    $sql_create = "
        CREATE TABLE teste_tabela (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE,
            idade INT,
            ativo BOOLEAN DEFAULT TRUE,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    if (!$conexao->query($sql_create)) {
        throw new Exception("Erro ao criar tabela: " . $conexao->error);
    }
    
    echo "            <p><strong>Tabela criada:</strong> teste_tabela</p>";
    echo "            <pre>" . htmlspecialchars($sql_create) . "</pre>";
    
    return true;
});

// Teste 4: Inserir dados de teste
executarTeste("Inserir Dados de Teste", function() {
    global $conexao;
    
    $dados_teste = [
        ['João Silva', 'joao@teste.com', 30],
        ['Maria Santos', 'maria@teste.com', 25],
        ['Pedro Oliveira', 'pedro@teste.com', 35],
        ['Ana Costa', 'ana@teste.com', 28],
        ['Carlos Ferreira', 'carlos@teste.com', 42]
    ];
    
    $stmt = $conexao->prepare("INSERT INTO teste_tabela (nome, email, idade) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar statement: " . $conexao->error);
    }
    
    $inseridos = 0;
    foreach ($dados_teste as $dados) {
        $stmt->bind_param("ssi", $dados[0], $dados[1], $dados[2]);
        if ($stmt->execute()) {
            $inseridos++;
        }
    }
    
    echo "            <p><strong>Registros inseridos:</strong> $inseridos de " . count($dados_teste) . "</p>";
    
    return $inseridos > 0;
});

// Teste 5: Consultar dados
executarTeste("Consultar Dados", function() {
    global $conexao;
    
    $resultado = $conexao->query("SELECT * FROM teste_tabela ORDER BY nome");
    
    if (!$resultado) {
        throw new Exception("Erro na consulta: " . $conexao->error);
    }
    
    $total = $resultado->num_rows;
    echo "            <p><strong>Total de registros:</strong> $total</p>";
    
    if ($total > 0) {
        echo "            <table>";
        echo "                <tr><th>ID</th><th>Nome</th><th>Email</th><th>Idade</th><th>Ativo</th><th>Data Criação</th></tr>";
        
        while ($row = $resultado->fetch_assoc()) {
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

// Teste 6: Atualizar dados
executarTeste("Atualizar Dados", function() {
    global $conexao;
    
    $sql_update = "UPDATE teste_tabela SET idade = idade + 1 WHERE nome LIKE '%Silva%'";
    
    if (!$conexao->query($sql_update)) {
        throw new Exception("Erro na atualização: " . $conexao->error);
    }
    
    $afetados = $conexao->affected_rows;
    echo "            <p><strong>Registros atualizados:</strong> $afetados</p>";
    echo "            <p><strong>SQL executado:</strong> <code>" . htmlspecialchars($sql_update) . "</code></p>";
    
    return true;
});

// Teste 7: Testar transações
executarTeste("Testar Transações", function() {
    global $conexao;
    
    // Iniciar transação
    $conexao->begin_transaction();
    
    try {
        // Inserir um registro
        $stmt = $conexao->prepare("INSERT INTO teste_tabela (nome, email, idade) VALUES (?, ?, ?)");
        $nome = "Teste Transação";
        $email = "transacao@teste.com";
        $idade = 99;
        $stmt->bind_param("ssi", $nome, $email, $idade);
        $stmt->execute();
        
        $id_inserido = $conexao->insert_id;
        
        // Atualizar o registro
        $stmt2 = $conexao->prepare("UPDATE teste_tabela SET ativo = FALSE WHERE id = ?");
        $stmt2->bind_param("i", $id_inserido);
        $stmt2->execute();
        
        // Confirmar transação
        $conexao->commit();
        
        echo "            <p><strong>Transação executada:</strong> Registro inserido (ID: $id_inserido) e atualizado</p>";
        
        return true;
        
    } catch (Exception $e) {
        $conexao->rollback();
        throw $e;
    }
});

// Teste 8: Testar prepared statements
executarTeste("Testar Prepared Statements", function() {
    global $conexao;
    
    // Buscar por idade específica
    $idade_busca = 30;
    $stmt = $conexao->prepare("SELECT nome, email FROM teste_tabela WHERE idade >= ? ORDER BY nome");
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar statement: " . $conexao->error);
    }
    
    $stmt->bind_param("i", $idade_busca);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $encontrados = $resultado->num_rows;
    echo "            <p><strong>Registros encontrados (idade >= $idade_busca):</strong> $encontrados</p>";
    
    if ($encontrados > 0) {
        echo "            <ul>";
        while ($row = $resultado->fetch_assoc()) {
            echo "                <li>" . htmlspecialchars($row['nome']) . " (" . htmlspecialchars($row['email']) . ")</li>";
        }
        echo "            </ul>";
    }
    
    return true;
});

// Teste 9: Limpar dados de teste
executarTeste("Limpar Dados de Teste", function() {
    global $conexao;
    
    // Contar registros antes
    $resultado = $conexao->query("SELECT COUNT(*) as total FROM teste_tabela");
    $antes = $resultado->fetch_assoc()['total'];
    
    // Remover tabela de teste
    if (!$conexao->query("DROP TABLE IF EXISTS teste_tabela")) {
        throw new Exception("Erro ao remover tabela: " . $conexao->error);
    }
    
    echo "            <p><strong>Registros removidos:</strong> $antes</p>";
    echo "            <p><strong>Tabela de teste removida:</strong> teste_tabela</p>";
    
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
    echo "                <p>Seu banco de dados <strong>geretech</strong> está funcionando perfeitamente!</p>";
    echo "            </div>";
} else {
    echo "            <div class='error'>";
    echo "                <h4>⚠️ Alguns testes falharam</h4>";
    echo "                <p>Verifique os erros acima e corrija as configurações necessárias.</p>";
    echo "            </div>";
}

echo "        </div>";

echo "        <div style='text-align: center; margin: 30px 0;'>";
echo "            <a href='teste_conexao.php' class='btn'>🔧 Teste de Conexão</a>";
echo "            <a href='setup_database.php' class='btn'>⚙️ Configurar Banco</a>";
echo "            <a href='pages/login.php' class='btn'>🔐 Acessar Sistema</a>";
echo "        </div>";

echo "        <p style='text-align: center; margin-top: 30px; color: #666;'>";
echo "            <small>Teste executado em " . date('d/m/Y H:i:s') . " - Sistema GERE TECH</small>";
echo "        </p>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>