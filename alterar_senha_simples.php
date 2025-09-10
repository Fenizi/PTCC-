<?php
/**
 * Script simples para alterar senha do admin - GERE TECH
 * Funciona mesmo sem extensões MySQL habilitadas
 */

echo "<h2>🔐 ALTERAÇÃO DE SENHA - GERE TECH</h2>";
echo "<p>Alterando senha do admin@geretech.com para 123456...</p>";
echo "<hr>";

// Configurações do banco
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'geretech';
$porta = 3306;

// Nova senha
$nova_senha = '123456';
$email_admin = 'admin@geretech.com';

try {
    // Tentar PDO primeiro
    if (class_exists('PDO') && in_array('mysql', PDO::getAvailableDrivers())) {
        echo "<p style='color: blue;'>ℹ️ Usando PDO...</p>";
        
        $dsn = "mysql:host={$host};port={$porta};dbname={$banco};charset=utf8mb4";
        $pdo = new PDO($dsn, $usuario, $senha);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p style='color: green;'>✅ Conectado ao MySQL com PDO!</p>";
        
        // Gerar hash da nova senha
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        echo "<p>Hash gerado: " . substr($senha_hash, 0, 50) . "...</p>";
        
        // Verificar se o usuário existe
        $stmt = $pdo->prepare("SELECT id, email FROM usuarios WHERE email = ?");
        $stmt->execute([$email_admin]);
        $usuario_existe = $stmt->fetch();
        
        if (!$usuario_existe) {
            echo "<p style='color: orange;'>⚠️ Usuário não encontrado. Criando usuário admin...</p>";
            
            // Criar usuário admin
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->