<?php
/**
 * Script de teste para verificar a conexão com o banco de dados
 * Sistema GERE TECH
 */

// Incluir o arquivo de conexão
require_once 'includes/conexao.php';

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Teste de Conexão - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo "        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .info { color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        table { width: 100%; border-collapse: collapse; margin: 20px 0; }";
echo "        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "        th { background-color: #f8f9fa; font-weight: bold; }";
echo "        .status-ok { color: #28a745; font-weight: bold; }";
echo "        .status-error { color: #dc3545; font-weight: bold; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <h1>🔧 Teste de Conexão - Sistema GERE TECH</h1>";

// Verificar se a conexão foi estabelecida
if (isset($conexao) && $conexao instanceof mysqli) {
    echo "        <div class='success'>";
    echo "            <h3>✅ Conexão Estabelecida com Sucesso!</h3>";
    echo "            <p>A conexão com o banco de dados foi estabelecida corretamente.</p>";
    echo "        </div>";
    
    // Informações da conexão
    echo "        <div class='info'>";
    echo "            <h3>📊 Informações da Conexão</h3>";
    echo "            <table>";
    echo "                <tr><th>Servidor</th><td>" . $conexao->host_info . "</td></tr>";
    echo "                <tr><th>Versão do MySQL</th><td>" . $conexao->server_info . "</td></tr>";
    echo "                <tr><th>Versão do Cliente</th><td>" . $conexao->client_info . "</td></tr>";
    echo "                <tr><th>Charset</th><td>" . $conexao->character_set_name() . "</td></tr>";
    echo "                <tr><th>Status da Conexão</th><td class='status-ok'>Ativa</td></tr>";
    echo "            </table>";
    echo "        </div>";
    
    // Testar se o banco existe
    $resultado = $conexao->query("SELECT DATABASE() as banco_atual");
    if ($resultado && $row = $resultado->fetch_assoc()) {
        echo "        <div class='success'>";
        echo "            <h3>🗄️ Banco de Dados Ativo</h3>";
        echo "            <p>Banco atual: <strong>" . $row['banco_atual'] . "</strong></p>";
        echo "        </div>";
    }
    
    // Verificar tabelas do sistema
    echo "        <div class='info'>";
    echo "            <h3>📋 Verificação das Tabelas</h3>";
    
    $tabelas_esperadas = ['usuarios', 'clientes', 'produtos', 'vendas', 'configuracoes', 'logs_atividades', 'alertas'];
    $tabelas_existentes = [];
    
    $resultado = $conexao->query("SHOW TABLES");
    if ($resultado) {
        while ($row = $resultado->fetch_array()) {
            $tabelas_existentes[] = $row[0];
        }
    }
    
    echo "            <table>";
    echo "                <tr><th>Tabela</th><th>Status</th><th>Registros</th></tr>";
    
    foreach ($tabelas_esperadas as $tabela) {
        $existe = in_array($tabela, $tabelas_existentes);
        $status = $existe ? "<span class='status-ok'>✅ Existe</span>" : "<span class='status-error'>❌ Não encontrada</span>";
        
        $count = 0;
        if ($existe) {
            $count_result = $conexao->query("SELECT COUNT(*) as total FROM $tabela");
            if ($count_result && $count_row = $count_result->fetch_assoc()) {
                $count = $count_row['total'];
            }
        }
        
        echo "                <tr>";
        echo "                    <td>$tabela</td>";
        echo "                    <td>$status</td>";
        echo "                    <td>" . ($existe ? $count . " registros" : "-") . "</td>";
        echo "                </tr>";
    }
    
    echo "            </table>";
    echo "        </div>";
    
    // Teste de funcionalidade básica
    echo "        <div class='info'>";
    echo "            <h3>🧪 Teste de Funcionalidades</h3>";
    
    // Testar função executarQuery
    $teste_query = executarQuery("SELECT 1 as teste");
    if ($teste_query && $teste_query->fetch_assoc()) {
        echo "            <p>✅ Função executarQuery() funcionando corretamente</p>";
    } else {
        echo "            <p>❌ Erro na função executarQuery()</p>";
    }
    
    // Testar configurações
    if (in_array('configuracoes', $tabelas_existentes)) {
        $config_test = $conexao->query("SELECT COUNT(*) as total FROM configuracoes");
        if ($config_test && $config_row = $config_test->fetch_assoc()) {
            echo "            <p>✅ Tabela de configurações acessível (" . $config_row['total'] . " configurações)</p>";
        }
    }
    
    echo "        </div>";
    
} else {
    echo "        <div class='error'>";
    echo "            <h3>❌ Erro na Conexão</h3>";
    echo "            <p>Não foi possível estabelecer conexão com o banco de dados.</p>";
    echo "            <p>Verifique as configurações no arquivo <code>includes/conexao.php</code></p>";
    echo "        </div>";
}

echo "        <div class='info'>";
echo "            <h3>📝 Próximos Passos</h3>";
echo "            <ol>";
echo "                <li>Se houver tabelas faltando, execute o arquivo <code>db/geretech.sql</code> no seu MySQL</li>";
echo "                <li>Verifique se o usuário 'root' tem permissões adequadas</li>";
echo "                <li>Confirme se o MySQL está rodando na porta 3306</li>";
echo "                <li>Teste o login no sistema através da página <code>pages/login.php</code></li>";
echo "            </ol>";
echo "        </div>";

echo "        <p style='text-align: center; margin-top: 30px; color: #666;'>";
echo "            <small>Sistema GERE TECH - Teste realizado em " . date('d/m/Y H:i:s') . "</small>";
echo "        </p>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>