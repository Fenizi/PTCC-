<?php
/**
 * Script para forçar a alteração PERMANENTE da senha do admin@geretech.com para '123456'
 * Este script atualiza diretamente no banco de dados
 */

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <title>Alteração Permanente de Senha - GERE TECH</title>";
echo "    <style>";
echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo "        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "        .info { color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "    </style>";
echo "</head>";
echo "<body>";
echo "    <div class='container'>";
echo "        <h1>🔐 Alteração Permanente de Senha</h1>";

// Configurações do banco
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'geretech';
$porta = 3306;

// Hash da senha '123456'
$nova_senha_hash = '$2y$12$3H9HyvGSUMxRxx4xWze33u7LpgcUKpvNQVfF8e0j1CONBFWcQ3Pom';

try {
    // Tentar conexão com PDO primeiro
    $dsn = "mysql:host={$host};port={$porta};dbname={$banco}";
    $pdo = new PDO($dsn, $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "        <div class='info'>✅ Conectado ao banco de dados com sucesso!</div>";
    
    // Verificar se o usuário existe
    $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@geretech.com']);
    $usuario_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario_existente) {
        // Atualizar senha do usuário existente
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
        $stmt->execute([$nova_senha_hash, 'admin@geretech.com']);
        
        echo "        <div class='success'>";
        echo "            <strong>🎉 Senha alterada com sucesso!</strong><br>";
        echo "            • Usuário: {$usuario_existente['nome']}<br>";
        echo "            • Email: admin@geretech.com<br>";
        echo "            • Nova senha: 123456<br>";
        echo "            • Hash atualizado no banco de dados";
        echo "        </div>";
    } else {
        // Criar usuário se não existir
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute(['Administrador', 'admin@geretech.com', $nova_senha_hash]);
        
        echo "        <div class='success'>";
        echo "            <strong>🎉 Usuário administrador criado com sucesso!</strong><br>";
        echo "            • Nome: Administrador<br>";
        echo "            • Email: admin@geretech.com<br>";
        echo "            • Senha: 123456<br>";
        echo "            • Hash inserido no banco de dados";
        echo "        </div>";
    }
    
    // Verificação final
    $stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@geretech.com']);
    $admin_final = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin_final) {
        echo "        <div class='info'>";
        echo "            <strong>✅ Verificação final:</strong><br>";
        echo "            • ID: {$admin_final['id']}<br>";
        echo "            • Nome: {$admin_final['nome']}<br>";
        echo "            • Email: {$admin_final['email']}<br>";
        echo "            • Status: Senha permanentemente alterada";
        echo "        </div>";
    }
    
    echo "        <div class='success'>";
    echo "            <strong>🔐 Credenciais de Login:</strong><br>";
    echo "            • Email: <code>admin@geretech.com</code><br>";
    echo "            • Senha: <code>123456</code>";
    echo "        </div>";
    
} catch (Exception $e) {
    echo "        <div class='error'>";
    echo "            <strong>❌ Erro:</strong><br>";
    echo "            {$e->getMessage()}";
    echo "        </div>";
    
    echo "        <div class='info'>";
    echo "            <strong>💡 Soluções alternativas:</strong><br>";
    echo "            1. Execute o arquivo SQL 'atualizar_senha_admin.sql' no phpMyAdmin<br>";
    echo "            2. Verifique se o MySQL está rodando<br>";
    echo "            3. Confirme se o banco 'geretech' existe";
    echo "        </div>";
}

echo "        <div style='margin-top: 30px; text-align: center;'>";
echo "            <a href='pages/login.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🔐 Fazer Login</a>";
echo "        </div>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>