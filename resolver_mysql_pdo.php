<?php
/**
 * Script para resolver o problema do driver MySQL para PDO
 * Sistema GERE TECH - Solução para "Driver MySQL para PDO não está disponível"
 */

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Resolver MySQL PDO - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo "        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .warning { color: #856404; background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .info { color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .section { margin: 30px 0; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; }";
echo "        .step { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; border-radius: 5px; }";
echo "        .command { background: #2d3748; color: #e2e8f0; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; overflow-x: auto; }";
echo "        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }";
echo "        .btn:hover { background: #0056b3; }";
echo "        .btn-success { background: #28a745; }";
echo "        .btn-warning { background: #ffc107; color: #212529; }";
echo "        .btn-danger { background: #dc3545; }";
echo "        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }";
echo "        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }";
echo "        .card { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; }";
echo "        .highlight { background: #fff3cd; padding: 2px 5px; border-radius: 3px; }";
echo "        ul li { margin: 8px 0; }";
echo "        .os-section { border: 2px solid #007bff; margin: 20px 0; }";
echo "        .os-header { background: #007bff; color: white; padding: 15px; margin: 0; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <h1>🔧 Resolver Problema: Driver MySQL para PDO</h1>";
echo "        <p>Guia completo para resolver o erro <strong>\"Driver MySQL para PDO não está disponível\"</strong></p>";

// Verificar status atual
echo "        <div class='section'>";
echo "            <h2>📊 Status Atual do Sistema</h2>";

$php_version = phpversion();
$pdo_available = class_exists('PDO');
$mysql_driver = in_array('mysql', PDO::getAvailableDrivers());
$mysqli_available = class_exists('mysqli');

echo "            <div class='grid'>";
echo "                <div class='card'>";
echo "                    <h4>🐘 PHP</h4>";
echo "                    <p><strong>Versão:</strong> $php_version</p>";
echo "                    <p><strong>PDO:</strong> " . ($pdo_available ? '<span style="color: #28a745;">✅ Disponível</span>' : '<span style="color: #dc3545;">❌ Não disponível</span>') . "</p>";
echo "                </div>";

echo "                <div class='card'>";
echo "                    <h4>🗄️ Drivers de Banco</h4>";
echo "                    <p><strong>MySQL PDO:</strong> " . ($mysql_driver ? '<span style="color: #28a745;">✅ Disponível</span>' : '<span style="color: #dc3545;">❌ Não disponível</span>') . "</p>";
echo "                    <p><strong>MySQLi:</strong> " . ($mysqli_available ? '<span style="color: #28a745;">✅ Disponível</span>' : '<span style="color: #dc3545;">❌ Não disponível</span>') . "</p>";
echo "                </div>";

if ($pdo_available) {
    $drivers = PDO::getAvailableDrivers();
    echo "                <div class='card'>";
    echo "                    <h4>🔌 Drivers PDO Disponíveis</h4>";
    if (!empty($drivers)) {
        foreach ($drivers as $driver) {
            $color = ($driver === 'mysql') ? '#28a745' : '#6c757d';
            echo "                    <p style='color: $color;'>• $driver</p>";
        }
    } else {
        echo "                    <p style='color: #dc3545;'>Nenhum driver encontrado</p>";
    }
    echo "                </div>";
}

echo "            </div>";
echo "        </div>";

// Diagnóstico do problema
echo "        <div class='section'>";
echo "            <h2>🔍 Diagnóstico do Problema</h2>";

if (!$pdo_available) {
    echo "            <div class='error'>";
    echo "                <h4>❌ Problema Principal: PDO não está disponível</h4>";
    echo "                <p>A extensão PDO não está instalada ou habilitada no PHP.</p>";
    echo "            </div>";
} elseif (!$mysql_driver) {
    echo "            <div class='error'>";
    echo "                <h4>❌ Problema Principal: Driver MySQL para PDO não está disponível</h4>";
    echo "                <p>O PDO está disponível, mas o driver MySQL não está instalado ou habilitado.</p>";
    echo "            </div>";
} else {
    echo "            <div class='success'>";
    echo "                <h4>✅ Sistema OK: Todos os drivers estão disponíveis</h4>";
    echo "                <p>O PDO e o driver MySQL estão funcionando corretamente.</p>";
    echo "            </div>";
}

echo "        </div>";

// Soluções por Sistema Operacional
echo "        <div class='section'>";
echo "            <h2>💻 Soluções por Sistema Operacional</h2>";

// Windows
echo "            <div class='os-section'>";
echo "                <h3 class='os-header'>🪟 Windows (XAMPP/WAMP/Laragon)</h3>";
echo "                <div style='padding: 20px;'>";

echo "                    <div class='step'>";
echo "                        <h4>📋 Passo 1: Verificar php.ini</h4>";
echo "                        <p>Localize o arquivo <code>php.ini</code> e verifique se estas linhas estão <strong>descomentadas</strong> (sem ; no início):</p>";
echo "                        <div class='command'>";
echo "extension=pdo<br>";
echo "extension=pdo_mysql<br>";
echo "extension=mysqli";
echo "                        </div>";
echo "                        <p><strong>Localização comum do php.ini:</strong></p>";
echo "                        <ul>";
echo "                            <li>XAMPP: <code>C:\\xampp\\php\\php.ini</code></li>";
echo "                            <li>WAMP: <code>C:\\wamp64\\bin\\php\\php[versão]\\php.ini</code></li>";
echo "                            <li>Laragon: <code>C:\\laragon\\bin\\php\\php[versão]\\php.ini</code></li>";
echo "                        </ul>";
echo "                    </div>";

echo "                    <div class='step'>";
echo "                        <h4>🔄 Passo 2: Reiniciar Servidor</h4>";
echo "                        <p>Após modificar o php.ini, reinicie o servidor web:</p>";
echo "                        <ul>";
echo "                            <li><strong>XAMPP:</strong> Pare e inicie o Apache no painel de controle</li>";
echo "                            <li><strong>WAMP:</strong> Clique no ícone do WAMP e selecione \"Restart All Services\"</li>";
echo "                            <li><strong>Laragon:</strong> Clique em \"Stop All\" e depois \"Start All\"</li>";
echo "                        </ul>";
echo "                    </div>";

echo "                    <div class='step'>";
echo "                        <h4>📦 Passo 3: Verificar Instalação</h4>";
echo "                        <p>Se as extensões não estiverem disponíveis, verifique se os arquivos DLL existem:</p>";
echo "                        <ul>";
echo "                            <li><code>php_pdo.dll</code></li>";
echo "                            <li><code>php_pdo_mysql.dll</code></li>";
echo "                            <li><code>php_mysqli.dll</code></li>";
echo "                        </ul>";
echo "                        <p>Estes arquivos devem estar na pasta <code>ext/</code> do PHP.</p>";
echo "                    </div>";

echo "                </div>";
echo "            </div>";

// Linux
echo "            <div class='os-section'>";
echo "                <h3 class='os-header'>🐧 Linux (Ubuntu/Debian)</h3>";
echo "                <div style='padding: 20px;'>";

echo "                    <div class='step'>";
echo "                        <h4>📦 Instalar Extensões PHP</h4>";
echo "                        <p>Execute os comandos abaixo no terminal:</p>";
echo "                        <div class='command'>";
echo "sudo apt update<br>";
echo "sudo apt install php-mysql php-pdo php-mysqli<br>";
echo "sudo systemctl restart apache2";
echo "                        </div>";
echo "                    </div>";

echo "                    <div class='step'>";
echo "                        <h4>🔧 Para PHP 8.x específico</h4>";
echo "                        <div class='command'>";
echo "sudo apt install php8.1-mysql php8.1-pdo<br>";
echo "# ou para PHP 8.2<br>";
echo "sudo apt install php8.2-mysql php8.2-pdo";
echo "                        </div>";
echo "                    </div>";

echo "                </div>";
echo "            </div>";

// macOS
echo "            <div class='os-section'>";
echo "                <h3 class='os-header'>🍎 macOS</h3>";
echo "                <div style='padding: 20px;'>";

echo "                    <div class='step'>";
echo "                        <h4>🍺 Usando Homebrew</h4>";
echo "                        <div class='command'>";
echo "brew install php<br>";
echo "brew services restart php";
echo "                        </div>";
echo "                    </div>";

echo "                    <div class='step'>";
echo "                        <h4>📝 Verificar php.ini</h4>";
echo "                        <p>Localize e edite o php.ini:</p>";
echo "                        <div class='command'>";
echo "php --ini<br>";
echo "# Edite o arquivo e descomente:<br>";
echo "extension=pdo<br>";
echo "extension=pdo_mysql";
echo "                        </div>";
echo "                    </div>";

echo "                </div>";
echo "            </div>";

echo "        </div>";

// Teste de Conexão Alternativo
echo "        <div class='section'>";
echo "            <h2>🧪 Teste de Conexão Alternativo</h2>";
echo "            <p>Se o PDO MySQL não estiver disponível, você pode usar MySQLi como alternativa:</p>";

echo "            <div class='step'>";
echo "                <h4>📝 Código de Exemplo com MySQLi</h4>";
echo "                <pre><code><?php
// Configurações
\$host = 'localhost';
\$usuario = 'root';
\$senha = '';
\$banco = 'geretech';

try {
    // Tentar conexão com MySQLi
    \$conexao = new mysqli(\$host, \$usuario, \$senha, \$banco);
    
    if (\$conexao->connect_error) {
        throw new Exception('Erro de conexão: ' . \$conexao->connect_error);
    }
    
    echo 'Conexão MySQLi estabelecida com sucesso!';
    
    // Teste simples
    \$resultado = \$conexao->query('SELECT VERSION() as versao');
    \$versao = \$resultado->fetch_assoc();
    echo 'Versão do MySQL: ' . \$versao['versao'];
    
    \$conexao->close();
    
} catch (Exception \$e) {
    echo 'Erro: ' . \$e->getMessage();
}
?></code></pre>";
echo "            </div>";

echo "        </div>";

// Comandos de Verificação
echo "        <div class='section'>";
echo "            <h2>🔍 Comandos de Verificação</h2>";
echo "            <div class='grid'>";

echo "                <div class='card'>";
echo "                    <h4>🐘 Verificar PHP</h4>";
echo "                    <div class='command'>";
echo "php -v<br>";
echo "php -m | grep -i pdo<br>";
echo "php -m | grep -i mysql";
echo "                    </div>";
echo "                </div>";

echo "                <div class='card'>";
echo "                    <h4>📄 Localizar php.ini</h4>";
echo "                    <div class='command'>";
echo "php --ini<br>";
echo "php -i | grep php.ini";
echo "                    </div>";
echo "                </div>";

echo "                <div class='card'>";
echo "                    <h4>🔌 Testar Extensões</h4>";
echo "                    <div class='command'>";
echo "php -r \"echo class_exists('PDO') ? 'PDO OK' : 'PDO ERRO';\"<br>";
echo "php -r \"echo in_array('mysql', PDO::getAvailableDrivers()) ? 'MySQL PDO OK' : 'MySQL PDO ERRO';\"";
echo "                    </div>";
echo "                </div>";

echo "            </div>";
echo "        </div>";

// Próximos Passos
echo "        <div class='section'>";
echo "            <h2>🎯 Próximos Passos</h2>";

if ($mysql_driver && $pdo_available) {
    echo "            <div class='success'>";
    echo "                <h4>✅ Sistema Pronto!</h4>";
    echo "                <p>Todas as extensões estão funcionando. Você pode:</p>";
    echo "                <ul>";
    echo "                    <li>Testar a conexão com o banco</li>";
    echo "                    <li>Executar o sistema GERE TECH</li>";
    echo "                    <li>Configurar o banco de dados</li>";
    echo "                </ul>";
    echo "            </div>";
} else {
    echo "            <div class='warning'>";
    echo "                <h4>⚠️ Ação Necessária</h4>";
    echo "                <ol>";
    echo "                    <li>Siga as instruções do seu sistema operacional acima</li>";
    echo "                    <li>Reinicie o servidor web após as modificações</li>";
    echo "                    <li>Recarregue esta página para verificar o status</li>";
    echo "                    <li>Se o problema persistir, use MySQLi como alternativa</li>";
    echo "                </ol>";
    echo "            </div>";
}

echo "        </div>";

// Links úteis
echo "        <div style='text-align: center; margin: 30px 0;'>";
echo "            <a href='resolver_mysql_pdo.php' class='btn'>🔄 Verificar Novamente</a>";
echo "            <a href='diagnostico_php.php' class='btn'>🔍 Diagnóstico Completo</a>";
echo "            <a href='teste_banco_pdo.php' class='btn btn-success'>🧪 Testar Conexão</a>";
echo "            <a href='status_banco.php' class='btn'>📊 Status do Banco</a>";
echo "        </div>";

echo "        <div class='info'>";
echo "            <h4>💡 Dica Importante</h4>";
echo "            <p>Se você estiver usando um servidor local como XAMPP, WAMP ou Laragon, certifique-se de que:</p>";
echo "            <ul>";
echo "                <li>O MySQL está rodando</li>";
echo "                <li>As extensões PHP estão habilitadas no php.ini</li>";
echo "                <li>O servidor foi reiniciado após as modificações</li>";
echo "            </ul>";
echo "        </div>";

echo "        <p style='text-align: center; margin-top: 30px; color: #666;'>";
echo "            <small>Guia de solução atualizado em " . date('d/m/Y H:i:s') . " - Sistema GERE TECH</small>";
echo "        </p>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>