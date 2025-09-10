<?php
/**
 * Script de Diagnóstico PHP
 * Verifica configurações e extensões necessárias para o sistema GERE TECH
 */

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Diagnóstico PHP - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo "        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px10px rgba(0,0,0,0.1); }";
echo "        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .warning { color: #856404; background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .info { color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        table { width: 100%; border-collapse: collapse; margin: 15px 0; }";
echo "        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "        th { background-color: #f8f9fa; font-weight: bold; }";
echo "        .status-ok { color: #28a745; font-weight: bold; }";
echo "        .status-error { color: #dc3545; font-weight: bold; }";
echo "        .status-warning { color: #ffc107; font-weight: bold; }";
echo "        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 0.9em; }";
echo "        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }";
echo "        .btn:hover { background: #0056b3; }";
echo "        .section { margin: 30px 0; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <h1>🔍 Diagnóstico PHP - Sistema GERE TECH</h1>";
echo "        <p>Este script verifica se o PHP está configurado corretamente para executar o sistema.</p>";

// Informações básicas do PHP
echo "        <div class='section'>";
echo "            <h2>📋 Informações Básicas do PHP</h2>";
echo "            <table>";
echo "                <tr><th>Configuração</th><th>Valor</th><th>Status</th></tr>";
echo "                <tr><td>Versão do PHP</td><td>" . PHP_VERSION . "</td><td>";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "<span class='status-ok'>✅ OK</span>";
} else {
    echo "<span class='status-error'>❌ Versão muito antiga</span>";
}
echo "</td></tr>";

echo "                <tr><td>Sistema Operacional</td><td>" . PHP_OS . "</td><td><span class='status-ok'>✅ OK</span></td></tr>";
echo "                <tr><td>Arquitetura</td><td>" . (PHP_INT_SIZE * 8) . " bits</td><td><span class='status-ok'>✅ OK</span></td></tr>";
echo "                <tr><td>SAPI</td><td>" . php_sapi_name() . "</td><td><span class='status-ok'>✅ OK</span></td></tr>";
echo "            </table>";
echo "        </div>";

// Verificar extensões necessárias
echo "        <div class='section'>";
echo "            <h2>🔌 Extensões PHP Necessárias</h2>";

$extensoes_necessarias = [
    'mysqli' => 'Conexão com banco de dados MySQL',
    'pdo' => 'Interface de banco de dados PDO',
    'pdo_mysql' => 'Driver MySQL para PDO',
    'json' => 'Manipulação de dados JSON',
    'session' => 'Gerenciamento de sessões',
    'mbstring' => 'Manipulação de strings multibyte',
    'openssl' => 'Criptografia e segurança',
    'curl' => 'Requisições HTTP',
    'gd' => 'Manipulação de imagens',
    'zip' => 'Compressão de arquivos'
];

echo "            <table>";
echo "                <tr><th>Extensão</th><th>Descrição</th><th>Status</th><th>Ação</th></tr>";

$extensoes_faltando = [];
foreach ($extensoes_necessarias as $ext => $desc) {
    echo "                <tr>";
    echo "                    <td><strong>$ext</strong></td>";
    echo "                    <td>$desc</td>";
    
    if (extension_loaded($ext)) {
        echo "                    <td><span class='status-ok'>✅ Instalada</span></td>";
        echo "                    <td>-</td>";
    } else {
        echo "                    <td><span class='status-error'>❌ Não encontrada</span></td>";
        echo "                    <td><span class='status-error'>Instalar</span></td>";
        $extensoes_faltando[] = $ext;
    }
    
    echo "                </tr>";
}

echo "            </table>";
echo "        </div>";

// Configurações importantes
echo "        <div class='section'>";
echo "            <h2>⚙️ Configurações Importantes</h2>";
echo "            <table>";
echo "                <tr><th>Configuração</th><th>Valor Atual</th><th>Recomendado</th><th>Status</th></tr>";

$configs = [
    'memory_limit' => ['atual' => ini_get('memory_limit'), 'recomendado' => '128M ou mais'],
    'max_execution_time' => ['atual' => ini_get('max_execution_time'), 'recomendado' => '30 ou mais'],
    'upload_max_filesize' => ['atual' => ini_get('upload_max_filesize'), 'recomendado' => '10M ou mais'],
    'post_max_size' => ['atual' => ini_get('post_max_size'), 'recomendado' => '10M ou mais'],
    'display_errors' => ['atual' => ini_get('display_errors') ? 'On' : 'Off', 'recomendado' => 'Off (produção)'],
    'log_errors' => ['atual' => ini_get('log_errors') ? 'On' : 'Off', 'recomendado' => 'On'],
    'date.timezone' => ['atual' => ini_get('date.timezone') ?: 'Não definido', 'recomendado' => 'America/Sao_Paulo']
];

foreach ($configs as $config => $info) {
    echo "                <tr>";
    echo "                    <td><strong>$config</strong></td>";
    echo "                    <td>" . $info['atual'] . "</td>";
    echo "                    <td>" . $info['recomendado'] . "</td>";
    echo "                    <td><span class='status-ok'>ℹ️ Info</span></td>";
    echo "                </tr>";
}

echo "            </table>";
echo "        </div>";

// Teste de funcionalidades
echo "        <div class='section'>";
echo "            <h2>🧪 Testes de Funcionalidades</h2>";

// Teste de escrita de arquivo
echo "            <h3>📝 Teste de Escrita de Arquivos</h3>";
$arquivo_teste = 'teste_escrita_' . time() . '.txt';
try {
    if (file_put_contents($arquivo_teste, 'Teste de escrita')) {
        unlink($arquivo_teste);
        echo "            <div class='success'>✅ Escrita de arquivos funcionando</div>";
    } else {
        echo "            <div class='error'>❌ Erro na escrita de arquivos</div>";
    }
} catch (Exception $e) {
    echo "            <div class='error'>❌ Erro na escrita: " . $e->getMessage() . "</div>";
}

// Teste de sessão
echo "            <h3>🔐 Teste de Sessões</h3>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['teste'] = 'funcionando';
    if (isset($_SESSION['teste'])) {
        echo "            <div class='success'>✅ Sessões funcionando</div>";
        unset($_SESSION['teste']);
    } else {
        echo "            <div class='error'>❌ Problema com sessões</div>";
    }
} catch (Exception $e) {
    echo "            <div class='error'>❌ Erro nas sessões: " . $e->getMessage() . "</div>";
}

// Teste JSON
echo "            <h3>📄 Teste JSON</h3>";
$teste_json = ['teste' => 'funcionando', 'numero' => 123];
$json_encoded = json_encode($teste_json);
$json_decoded = json_decode($json_encoded, true);
if ($json_decoded && $json_decoded['teste'] === 'funcionando') {
    echo "            <div class='success'>✅ JSON funcionando</div>";
} else {
    echo "            <div class='error'>❌ Problema com JSON</div>";
}

echo "        </div>";

// Instruções para resolver problemas
if (!empty($extensoes_faltando)) {
    echo "        <div class='section'>";
    echo "            <h2>🔧 Como Resolver os Problemas</h2>";
    
    echo "            <div class='error'>";
    echo "                <h3>❌ Extensões PHP Faltando</h3>";
    echo "                <p>As seguintes extensões precisam ser instaladas:</p>";
    echo "                <ul>";
    foreach ($extensoes_faltando as $ext) {
        echo "                    <li><strong>$ext</strong> - " . $extensoes_necessarias[$ext] . "</li>";
    }
    echo "                </ul>";
    echo "            </div>";
    
    echo "            <div class='info'>";
    echo "                <h3>💡 Soluções por Sistema</h3>";
    
    echo "                <h4>🪟 Windows (XAMPP/WAMP)</h4>";
    echo "                <ol>";
    echo "                    <li>Abra o arquivo <code>php.ini</code></li>";
    echo "                    <li>Procure pelas linhas das extensões (ex: <code>;extension=mysqli</code>)</li>";
    echo "                    <li>Remova o <code>;</code> do início da linha para habilitar</li>";
    echo "                    <li>Reinicie o Apache/servidor web</li>";
    echo "                </ol>";
    
    echo "                <h4>🐧 Linux (Ubuntu/Debian)</h4>";
    echo "                <pre>sudo apt update\nsudo apt install php-mysql php-mysqli php-pdo php-mbstring php-json php-curl php-gd php-zip</pre>";
    
    echo "                <h4>🍎 macOS (Homebrew)</h4>";
    echo "                <pre>brew install php\nbrew install php-mysql</pre>";
    
    echo "            </div>";
    echo "        </div>";
}

// Resumo final
echo "        <div class='section'>";
echo "            <h2>📊 Resumo do Diagnóstico</h2>";

if (empty($extensoes_faltando)) {
    echo "            <div class='success'>";
    echo "                <h3>🎉 Tudo Pronto!</h3>";
    echo "                <p>Seu PHP está configurado corretamente para executar o sistema GERE TECH.</p>";
    echo "                <p>Você pode prosseguir com os testes do banco de dados.</p>";
    echo "            </div>";
} else {
    echo "            <div class='warning'>";
    echo "                <h3>⚠️ Ação Necessária</h3>";
    echo "                <p>Algumas extensões PHP estão faltando. Instale-as seguindo as instruções acima.</p>";
    echo "                <p>Após instalar, reinicie o servidor web e execute este diagnóstico novamente.</p>";
    echo "            </div>";
}

echo "        </div>";

echo "        <div style='text-align: center; margin: 30px 0;'>";
echo "            <a href='diagnostico_php.php' class='btn'>🔄 Executar Novamente</a>";
if (empty($extensoes_faltando)) {
    echo "            <a href='teste_banco.php' class='btn'>🧪 Testar Banco</a>";
}
echo "            <a href='setup_database.php' class='btn'>⚙️ Configurar Banco</a>";
echo "            <a href='index.html' class='btn'>🏠 Página Inicial</a>";
echo "        </div>";

echo "        <p style='text-align: center; margin-top: 30px; color: #666;'>";
echo "            <small>Diagnóstico executado em " . date('d/m/Y H:i:s') . " - Sistema GERE TECH</small>";
echo "        </p>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>