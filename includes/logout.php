<?php
/**
 * Script de logout do sistema GERE TECH
 * Encerra a sessão do usuário e registra a ação
 */

session_start();

// Verificar se há uma sessão ativa
if (isset($_SESSION['usuario_id'])) {
    // Incluir arquivos necessários
    require_once 'conexao.php';
    require_once 'config.php';
    
    // Registrar logout no log
    registrarLog($_SESSION['usuario_id'], 'LOGOUT', 'usuarios', $_SESSION['usuario_id'], 'Usuário fez logout do sistema');
    
    // Destruir todas as variáveis de sessão
    $_SESSION = array();
    
    // Destruir o cookie de sessão se existir
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir a sessão
    session_destroy();
}

// Redirecionar para a página inicial
header('Location: ../index.html');
exit;
?>