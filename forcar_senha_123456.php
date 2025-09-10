<?php
/**
 * Script para forçar alteração da senha para 123456
 * GERE TECH - Alteração Permanente
 */

echo "<h2>🔐 FORÇANDO ALTERAÇÃO DE SENHA - GERE TECH</h2>";
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
    // Conectar usando mysqli
    $conexao = new mysqli($host, $usuario, $senha, $banco, $porta);
    
    if ($conexao->connect_error) {
        throw new Exception("Erro de conexão: " . $conexao->connect_error);
    }
    
    echo "<p style='color: green;'>✅ Conectado ao MySQL com sucesso!</p>";
    
    // Gerar hash da nova senha
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    echo "<p>Hash gerado: " . substr($senha_hash, 0, 50) . "...</p>";
    
    // Primeiro, verificar se o usuário existe
    $stmt_check = $conexao->prepare("SELECT id, email FROM usuarios WHERE email = ?");
    $stmt_check->bind_param("s", $email_admin);
    $stmt_check->execute();
    $resultado_check = $stmt_check->get_result();
    
    if ($resultado_check->num_rows == 0) {
        echo "<p style='color: orange;'>⚠️ Usuário não encontrado. Criando usuário admin...</p>";
        
        // Criar usuário admin
        $stmt_create = $conexao->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $nome_admin = 'Administrador';
        $stmt_create->bind_param("sss", $nome_admin, $email_admin, $senha_hash);
        
        if ($stmt_create->execute()) {
            echo "<p style='color: green;'>✅ Usuário admin criado com sucesso!</p>";
        } else {
            throw new Exception("Erro ao criar usuário: " . $stmt_create->error);
        }
        $stmt_create->close();
        
    } else {
        echo "<p style='color: blue;'>ℹ️ Usuário encontrado. Atualizando senha...</p>";
        
        // Atualizar senha
        $stmt_update = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
        $stmt_update->bind_param("ss", $senha_hash, $email_admin);
        
        if ($stmt_update->execute()) {
            if ($stmt_update->affected_rows > 0) {
                echo "<p style='color: green;'>✅ Senha atualizada com sucesso!</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Nenhuma linha foi afetada (senha pode já estar correta)</p>";
            }
        } else {
            throw new Exception("Erro ao atualizar senha: " . $stmt_update->error);
        }
        $stmt_update->close();
    }
    
    $stmt_check->close();
    
    // Verificação final
    echo "<h3>🧪 Verificação Final</h3>";
    $stmt_verify = $conexao->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
    $stmt_verify->bind_param("s", $email_admin);
    $stmt_verify->execute();
    $resultado_verify = $stmt_verify->get_result();
    
    if ($row = $resultado_verify->fetch_assoc()) {
        echo "<p><strong>Usuário encontrado:</strong></p>";
        echo "<ul>";
        echo "<li>ID: " . $row['id'] . "</li>";
        echo "<li>Nome: " . $row['nome'] . "</li>";
        echo "<li>Email: " . $row['email'] . "</li>";
        echo "<li>Hash da senha: " . substr($row['senha'], 0, 50) . "...</li>";
        echo "</ul>";
        
        // Testar se a senha funciona
        if (password_verify($nova_senha, $row['senha'])) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>🎉 SUCESSO TOTAL!</h4>";
            echo "<p><strong>A senha foi alterada permanentemente!</strong></p>";
            echo "<p>Credenciais de login:</p>";
            echo "<ul>";
            echo "<li><strong>Email:</strong> " . $email_admin . "</li>";
            echo "<li><strong>Senha:</strong> " . $nova_senha . "</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>❌ ERRO NA VERIFICAÇÃO</h4>";
            echo "<p>A senha não está funcionando corretamente!</p>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>❌ Erro: Usuário não encontrado após a operação!</p>";
    }
    
    $stmt_verify->close();
    $conexao->close();
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ ERRO</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
    
    echo "<h3>Informações de Debug:</h3>";
    echo "<ul>";
    echo "<li>Host: $host</li>";
    echo "<li>Usuário: $usuario</li>";
    echo "<li>Banco: $banco</li>";
    echo "<li>Porta: $porta</li>";
    echo "<li>PHP Version: " . phpversion() . "</li>";
    echo "<li>MySQLi disponível: " . (extension_loaded('mysqli') ? 'Sim' : 'Não') . "</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='pages/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Testar Login</a></p>";
echo "<p><small>⚠️ <strong>Importante:</strong> Após confirmar que funciona, delete este arquivo por segurança!</small></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #f8f9fa;
}
h2, h3, h4 {
    color: #333;
}
hr {
    margin: 20px 0;
    border: none;
    border-top: 2px solid #dee2e6;
}
ul {
    background: #fff;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}
</style>