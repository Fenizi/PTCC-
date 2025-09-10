<?php
/**
 * Script para habilitar extensões MySQL no PHP
 */

echo "<h2>Habilitando Extensões MySQL</h2>";

$php_ini_path = "C:\\Program Files\\php-8.4.3\\php.ini";

echo "<p>Arquivo php.ini: $php_ini_path</p>";

if (!file_exists($php_ini_path)) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
    echo "❌ Erro: Arquivo php.ini não encontrado em: $php_ini_path";
    echo "</div>";
    exit;
}

if (!is_writable($php_ini_path)) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
    echo "❌ Erro: Não é possível escrever no arquivo php.ini<br>";
    echo "Execute este script como administrador ou altere as permissões do arquivo.";
    echo "</div>";
    exit;
}

try {
    // Ler o arquivo php.ini
    $conteudo = file_get_contents($php_ini_path);
    
    if ($conteudo === false) {
        throw new Exception("Não foi possível ler o arquivo php.ini");
    }
    
    echo "<p>✅ Arquivo php.ini lido com sucesso</p>";
    
    // Fazer backup
    $backup_path = $php_ini_path . '.backup.' . date('Y-m-d_H-i-s');
    if (file_put_contents($backup_path, $conteudo) === false) {
        throw new Exception("Não foi possível criar backup");
    }
    
    echo "<p>✅ Backup criado: $backup_path</p>";
    
    // Habilitar extensões
    $alteracoes = 0;
    
    // Habilitar mysqli
    if (preg_match('/^;\s*extension=mysqli\s*$/m', $conteudo)) {
        $conteudo = preg_replace('/^;\s*extension=mysqli\s*$/m', 'extension=mysqli', $conteudo);
        $alteracoes++;
        echo "<p>✅ Extensão mysqli habilitada</p>";
    } else if (strpos($conteudo, 'extension=mysqli') !== false) {
        echo "<p>ℹ️ Extensão mysqli já estava habilitada</p>";
    } else {
        // Adicionar extensão mysqli se não existir
        $conteudo .= "\nextension=mysqli\n";
        $alteracoes++;
        echo "<p>✅ Extensão mysqli adicionada</p>";
    }
    
    // Habilitar pdo_mysql
    if (preg_match('/^;\s*extension=pdo_mysql\s*$/m', $conteudo)) {
        $conteudo = preg_replace('/^;\s*extension=pdo_mysql\s*$/m', 'extension=pdo_mysql', $conteudo);
        $alteracoes++;
        echo "<p>✅ Extensão pdo_mysql habilitada</p>";
    } else if (strpos($conteudo, 'extension=pdo_mysql') !== false) {
        echo "<p>ℹ️ Extensão pdo_mysql já estava habilitada</p>";
    } else {
        // Adicionar extensão pdo_mysql se não existir
        $conteudo .= "\nextension=pdo_mysql\n";
        $alteracoes++;
        echo "<p>✅ Extensão pdo_mysql adicionada</p>";
    }
    
    if ($alteracoes > 0) {
        // Salvar alterações
        if (file_put_contents($php_ini_path, $conteudo) === false) {
            throw new Exception("Não foi possível salvar as alterações no php.ini");
        }
        
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px;'>";
        echo "✅ Extensões MySQL habilitadas com sucesso!<br>";
        echo "Alterações realizadas: $alteracoes<br><br>";
        echo "<strong>IMPORTANTE:</strong> Você precisa reiniciar o servidor web para que as alterações tenham efeito.<br>";
        echo "Se estiver usando XAMPP, reinicie o Apache no painel de controle.";
        echo "</div>";
        
        echo "<div style='color: blue; padding: 10px; border: 1px solid blue; margin: 10px;'>";
        echo "<h3>Próximos passos:</h3>";
        echo "<ol>";
        echo "<li>Reinicie o servidor web (Apache/IIS)</li>";
        echo "<li>Execute novamente: <a href='alterar_senha_direto.php'>alterar_senha_direto.php</a></li>";
        echo "<li>Ou execute: <a href='alterar_senha.php'>alterar_senha.php</a></li>";
        echo "</ol>";
        echo "</div>";
        
    } else {
        echo "<div style='color: orange; padding: 10px; border: 1px solid orange; margin: 10px;'>";
        echo "ℹ️ Nenhuma alteração foi necessária. As extensões já estavam habilitadas.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
    echo "❌ Erro: " . $e->getMessage();
    echo "</div>";
}

echo "<br><a href='alterar_senha_direto.php'>🔄 Testar Conexão MySQL</a>";
echo " | <a href='pages/login.php'>🔐 Ir para Login</a>";
?>