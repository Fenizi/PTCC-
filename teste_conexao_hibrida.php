<?php
/**
 * Teste da Nova Conexão Híbrida
 * Sistema GERE TECH
 * 
 * Este arquivo testa a nova conexão híbrida que funciona
 * com MySQLi ou PDO automaticamente
 */

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Teste Conexão Híbrida - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo "        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .info { color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .warning { color: #856404; background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; }";
echo "        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }";
echo "        .card { background: white; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; }";
echo "        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 0.9em; }";
echo "        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }";
echo "        .btn:hover { background: #0056b3; }";
echo "        .status-ok { color: #28a745; font-weight: bold; }";
echo "        .status-error { color: #dc3545; font-weight: bold; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <h1>🧪 Teste da Conexão Híbrida - GERE TECH</h1>";
echo "        <p>Este teste verifica se a nova conexão híbrida está funcionando corretamente.</p>";

// Incluir o arquivo de conexão
try {
    include_once 'includes/conexao.php';
    
    echo "        <div class='success'>";
    echo "            <h3>✅ Arquivo de conexão carregado com sucesso!</h3>";
    echo "        </div>";
    
} catch (Exception $e) {
    echo "        <div class='error'>";
    echo "            <h3>❌ Erro ao carregar arquivo de conexão</h3>";
    echo "            <p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "        </div>";
    echo "    </div>";
    echo "</body>";
    echo "</html>";
    exit;
}

// Teste 1: Informações da Conexão
echo "        <div class='test-section'>";
echo "            <h2>📊 Informações da Conexão</h2>";

if (function_exists('obterInfoConexao')) {
    $info = obterInfoConexao();
    
    echo "            <div class='grid'>";
    echo "                <div class='card'>";
    echo "                    <h4>🔧 Configuração</h4>";
    echo "                    <p><strong>Extensão:</strong> <span class='status-ok'>" . $info['extensao'] . "</span></p>";
    echo "                    <p><strong>Host:</strong> " . $info['host'] . ":" . $info['porta'] . "</p>";
    echo "                    <p><strong>Banco:</strong> " . $info['banco'] . "</p>";
    echo "                    <p><strong>Status:</strong> " . ($info['ativa'] ? "<span class='status-ok'>✅ Conectado</span>" : "<span class='status-error'>❌ Desconectado</span>") . "</p>";
    echo "                </div>";
    
    if (isset($info['versao'])) {
        echo "                <div class='card'>";
        echo "                    <h4>🖥️ Servidor MySQL</h4>";
        echo "                    <p><strong>Versão:</strong> " . $info['versao'] . "</p>";
        echo "                    <p><strong>Charset:</strong> " . $info['charset'] . "</p>";
        echo "                </div>";
    }
    echo "            </div>";
} else {
    echo "            <div class='warning'>⚠️ Função obterInfoConexao() não disponível</div>";
}

echo "        </div>";

// Teste 2: Teste de Conectividade
echo "        <div class='test-section'>";
echo "            <h2>🔗 Teste de Conectividade</h2>";

if (function_exists('testarConexao')) {
    $teste_ok = testarConexao();
    
    if ($teste_ok) {
        echo "            <div class='success'>";
        echo "                <h4>✅ Teste de conectividade passou!</h4>";
        echo "                <p>A conexão com o banco de dados está funcionando corretamente.</p>";
        echo "            </div>";
    } else {
        echo "            <div class='error'>";
        echo "                <h4>❌ Teste de conectividade falhou!</h4>";
        echo "                <p>Há problemas na conexão com o banco de dados.</p>";
        echo "            </div>";
    }
} else {
    echo "            <div class='warning'>⚠️ Função testarConexao() não disponível</div>";
}

echo "        </div>";

// Teste 3: Estatísticas do Banco
echo "        <div class='test-section'>";
echo "            <h2>📈 Estatísticas do Banco</h2>";

if (function_exists('obterEstatisticasBanco')) {
    $stats = obterEstatisticasBanco();
    
    echo "            <div class='grid'>";
    echo "                <div class='card'>";
    echo "                    <h4>📋 Tabelas</h4>";
    echo "                    <p style='font-size: 2em; margin: 10px 0; color: #007bff;'><strong>" . $stats['tabelas'] . "</strong></p>";
    echo "                    <p>Total de tabelas no banco</p>";
    echo "                </div>";
    
    echo "                <div class='card'>";
    echo "                    <h4>💾 Tamanho</h4>";
    echo "                    <p style='font-size: 2em; margin: 10px 0; color: #28a745;'><strong>" . $stats['tamanho_mb'] . " MB</strong></p>";
    echo "                    <p>Tamanho total do banco</p>";
    echo "                </div>";
    echo "            </div>";
} else {
    echo "            <div class='warning'>⚠️ Função obterEstatisticasBanco() não disponível</div>";
}

echo "        </div>";

// Teste 4: Teste de Query
echo "        <div class='test-section'>";
echo "            <h2>🔍 Teste de Query</h2>";

try {
    if (function_exists('executarQuery')) {
        // Testar query simples
        $resultado = executarQuery("SELECT VERSION() as versao, NOW() as agora, DATABASE() as banco");
        
        if ($resultado) {
            if ($usando_mysqli) {
                $dados = $resultado->fetch_assoc();
            } else {
                $dados = $resultado->fetch();
            }
            
            echo "            <div class='success'>";
            echo "                <h4>✅ Query executada com sucesso!</h4>";
            echo "                <div class='grid'>";
            echo "                    <div class='card'>";
            echo "                        <h5>📊 Resultados</h5>";
            echo "                        <p><strong>Versão MySQL:</strong> " . $dados['versao'] . "</p>";
            echo "                        <p><strong>Data/Hora:</strong> " . $dados['agora'] . "</p>";
            echo "                        <p><strong>Banco Atual:</strong> " . $dados['banco'] . "</p>";
            echo "                    </div>";
            echo "                </div>";
            echo "            </div>";
        } else {
            echo "            <div class='error'>";
            echo "                <h4>❌ Falha ao executar query</h4>";
            echo "                <p>A query retornou resultado vazio ou falso.</p>";
            echo "            </div>";
        }
    } else {
        echo "            <div class='warning'>⚠️ Função executarQuery() não disponível</div>";
    }
} catch (Exception $e) {
    echo "            <div class='error'>";
    echo "                <h4>❌ Erro ao executar query</h4>";
    echo "                <p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "            </div>";
}

echo "        </div>";

// Teste 5: Verificar Tabelas do Sistema
echo "        <div class='test-section'>";
echo "            <h2>🗃️ Tabelas do Sistema</h2>";

try {
    $tabelas_esperadas = ['usuarios', 'clientes', 'produtos', 'vendas', 'configuracoes', 'logs_atividades', 'alertas'];
    $tabelas_encontradas = [];
    
    if (function_exists('executarQuery')) {
        $resultado = executarQuery("SHOW TABLES");
        
        if ($resultado) {
            while ($row = ($usando_mysqli ? $resultado->fetch_array() : $resultado->fetch(PDO::FETCH_NUM))) {
                $tabelas_encontradas[] = $row[0];
            }
        }
    }
    
    echo "            <div class='grid'>";
    
    foreach ($tabelas_esperadas as $tabela) {
        $existe = in_array($tabela, $tabelas_encontradas);
        
        echo "                <div class='card'>";
        echo "                    <h5>" . ($existe ? "✅" : "❌") . " " . $tabela . "</h5>";
        
        if ($existe) {
            // Contar registros
            try {
                $resultado = executarQuery("SELECT COUNT(*) as total FROM `$tabela`");
                if ($resultado) {
                    $count = $usando_mysqli ? $resultado->fetch_assoc()['total'] : $resultado->fetchColumn();
                    echo "                    <p><strong>Registros:</strong> $count</p>";
                }
            } catch (Exception $e) {
                echo "                    <p><strong>Registros:</strong> Erro ao contar</p>";
            }
            
            echo "                    <p style='color: #28a745;'><strong>Status:</strong> OK</p>";
        } else {
            echo "                    <p style='color: #dc3545;'><strong>Status:</strong> Não encontrada</p>";
        }
        
        echo "                </div>";
    }
    
    echo "            </div>";
    
    $tabelas_ok = count(array_intersect($tabelas_esperadas, $tabelas_encontradas));
    $total_esperadas = count($tabelas_esperadas);
    
    if ($tabelas_ok == $total_esperadas) {
        echo "            <div class='success'>";
        echo "                <h4>✅ Todas as tabelas estão presentes! ($tabelas_ok/$total_esperadas)</h4>";
        echo "            </div>";
    } else {
        echo "            <div class='warning'>";
        echo "                <h4>⚠️ Algumas tabelas estão faltando ($tabelas_ok/$total_esperadas)</h4>";
        echo "                <p><a href='setup_database.php' class='btn'>⚙️ Configurar Banco</a></p>";
        echo "            </div>";
    }
    
} catch (Exception $e) {
    echo "            <div class='error'>";
    echo "                <h4>❌ Erro ao verificar tabelas</h4>";
    echo "                <p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "            </div>";
}

echo "        </div>";

// Resumo Final
echo "        <div class='test-section'>";
echo "            <h2>📋 Resumo do Teste</h2>";

$extensao_usada = $usando_mysqli ? 'MySQLi' : 'PDO';
echo "            <div class='info'>";
echo "                <h4>🎯 Resultado Geral</h4>";
echo "                <p><strong>Extensão utilizada:</strong> $extensao_usada</p>";
echo "                <p><strong>Conexão:</strong> " . (DB_CONECTADO ? "<span class='status-ok'>✅ Ativa</span>" : "<span class='status-error'>❌ Inativa</span>") . "</p>";
echo "                <p><strong>Banco:</strong> " . DB_BANCO . "</p>";
echo "                <p><strong>Host:</strong> " . DB_HOST . "</p>";
echo "            </div>";

echo "        </div>";

// Links de Navegação
echo "        <div style='text-align: center; margin: 30px 0;'>";
echo "            <a href='inicializar_sistema.php' class='btn'>🔄 Verificar Sistema</a>";
echo "            <a href='setup_database.php' class='btn'>⚙️ Configurar Banco</a>";
echo "            <a href='pages/login.php' class='btn'>🔐 Fazer Login</a>";
echo "            <a href='diagnostico_php.php' class='btn'>🔍 Diagnóstico PHP</a>";
echo "        </div>";

echo "        <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 0.9em; text-align: center;'>";
echo "            <p>Sistema GERE TECH - Teste de Conexão Híbrida realizado em " . date('d/m/Y H:i:s') . "</p>";
echo "        </div>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>