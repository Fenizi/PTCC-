<?php
/**
 * Arquivo de configuração do sistema GERE TECH
 * Contém funções para gerenciar configurações, alertas e logs
 */

// Configurar codificação UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

require_once 'conexao.php';

/**
 * Função para obter uma configuração do sistema
 * @param string $chave - Chave da configuração
 * @return string|null - Valor da configuração ou null se não encontrada
 */
function getConfiguracao($chave) {
    global $conexao;
    
    $user_id = $_SESSION['usuario_id'] ?? 1;
    $sql = "SELECT valor FROM configuracoes WHERE chave = ? AND user_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("si", $chave, $user_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($row = $resultado->fetch_assoc()) {
        return $row['valor'];
    }
    
    return null;
}

/**
 * Função para definir uma configuração do sistema
 * @param string $chave - Chave da configuração
 * @param string $valor - Valor da configuração
 * @param string $descricao - Descrição da configuração
 * @return bool - True se sucesso, false se erro
 */
function setConfiguracao($chave, $valor, $descricao = '') {
    global $conexao;
    
    $user_id = $_SESSION['usuario_id'] ?? 1;
    $sql = "INSERT INTO configuracoes (chave, valor, descricao, user_id) VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE valor = VALUES(valor), descricao = VALUES(descricao)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssi", $chave, $valor, $descricao, $user_id);
    
    return $stmt->execute();
}

/**
 * Função para criar um alerta no sistema
 * @param string $tipo - Tipo do alerta (estoque_baixo, backup, sistema)
 * @param string $titulo - Título do alerta
 * @param string $mensagem - Mensagem do alerta
 * @return bool - True se sucesso, false se erro
 */
function criarAlerta($tipo, $titulo, $mensagem) {
    global $conexao;
    
    $user_id = $_SESSION['usuario_id'] ?? 1;
    $sql = "INSERT INTO alertas (tipo, titulo, mensagem, user_id) VALUES (?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssi", $tipo, $titulo, $mensagem, $user_id);
    
    return $stmt->execute();
}

/**
 * Função para obter alertas não lidos
 * @param int $limite - Limite de alertas a retornar
 * @return array - Array com os alertas
 */
function getAlertasNaoLidos($limite = 10) {
    global $conexao;
    
    $user_id = $_SESSION['usuario_id'] ?? 1;
    $sql = "SELECT * FROM alertas WHERE lido = FALSE AND user_id = ? ORDER BY data_criacao DESC LIMIT ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limite);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $alertas = [];
    while ($row = $resultado->fetch_assoc()) {
        $alertas[] = $row;
    }
    
    return $alertas;
}

/**
 * Função para marcar um alerta como lido
 * @param int $id - ID do alerta
 * @return bool - True se sucesso, false se erro
 */
function marcarAlertaComoLido($id) {
    global $conexao;
    
    $user_id = $_SESSION['usuario_id'] ?? 1;
    $sql = "UPDATE alertas SET lido = TRUE WHERE id = ? AND user_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    
    return $stmt->execute();
}

/**
 * Função para registrar uma atividade no log
 * @param int $id_usuario - ID do usuário
 * @param string $acao - Ação realizada
 * @param string $tabela_afetada - Tabela afetada (opcional)
 * @param int $id_registro - ID do registro afetado (opcional)
 * @param string $detalhes - Detalhes da ação (opcional)
 * @return bool - True se sucesso, false se erro
 */
function registrarLog($id_usuario, $acao, $tabela_afetada = null, $id_registro = null, $detalhes = null) {
    global $conexao;
    
    $sql = "INSERT INTO logs_atividades (id_usuario, acao, tabela_afetada, id_registro, detalhes) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("issis", $id_usuario, $acao, $tabela_afetada, $id_registro, $detalhes);
    
    return $stmt->execute();
}

/**
 * Função para verificar produtos com estoque baixo
 * @return array - Array com produtos com estoque baixo
 */
function verificarEstoqueBaixo() {
    global $conexao;
    
    $user_id = $_SESSION['usuario_id'] ?? 1;
    $estoque_minimo = getConfiguracao('estoque_minimo_alerta') ?: 5;
    
    $sql = "SELECT * FROM produtos WHERE estoque <= ? AND user_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $estoque_minimo, $user_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $produtos = [];
    while ($row = $resultado->fetch_assoc()) {
        $produtos[] = $row;
        
        // Criar alerta se não existir um recente para este produto
        $sql_check = "SELECT id FROM alertas WHERE tipo = 'estoque_baixo' 
                      AND mensagem LIKE ? AND data_criacao > DATE_SUB(NOW(), INTERVAL 1 DAY) AND user_id = ?";
        $stmt_check = $conexao->prepare($sql_check);
        $like_pattern = '%' . $row['nome'] . '%';
        $stmt_check->bind_param("si", $like_pattern, $user_id);
        $stmt_check->execute();
        $resultado_check = $stmt_check->get_result();
        
        if ($resultado_check->num_rows == 0) {
            $titulo = "Estoque Baixo";
            $mensagem = "O produto \"" . $row['nome'] . "\" está com estoque baixo (" . $row['estoque'] . " unidades)";
            criarAlerta('estoque_baixo', $titulo, $mensagem);
        }
    }
    
    return $produtos;
}

/**
 * Função para formatar valor monetário
 * @param float $valor - Valor a ser formatado
 * @return string - Valor formatado
 */
function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Função para formatar data
 * @param string $data - Data a ser formatada
 * @param string $formato - Formato desejado
 * @return string - Data formatada
 */
function formatarData($data, $formato = 'd/m/Y H:i') {
    return date($formato, strtotime($data));
}

/**
 * Função para obter estatísticas do dashboard
 * @return array - Array com estatísticas
 */
function getEstatisticasDashboard() {
    global $conexao;
    
    $user_id = $_SESSION['usuario_id'] ?? 1;
    $stats = [];
    
    // Total de clientes
    $sql = "SELECT COUNT(*) as total FROM clientes WHERE user_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stats['total_clientes'] = $resultado->fetch_assoc()['total'];
    
    // Total de produtos
    $sql = "SELECT COUNT(*) as total FROM produtos WHERE user_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stats['total_produtos'] = $resultado->fetch_assoc()['total'];
    
    // Total de vendas do mês
    $sql = "SELECT COUNT(*) as total, SUM(v.quantidade * p.valor) as faturamento 
            FROM vendas v JOIN produtos p ON v.id_produto = p.id 
            WHERE MONTH(v.data_venda) = MONTH(CURRENT_DATE()) 
            AND YEAR(v.data_venda) = YEAR(CURRENT_DATE()) AND v.user_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $row = $resultado->fetch_assoc();
    $stats['vendas_mes'] = $row['total'] ?: 0;
    $stats['faturamento_mes'] = $row['faturamento'] ?: 0;
    
    // Produtos com estoque baixo
    $estoque_minimo = getConfiguracao('estoque_minimo_alerta') ?: 5;
    $sql = "SELECT COUNT(*) as total FROM produtos WHERE estoque <= ? AND user_id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $estoque_minimo, $user_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stats['produtos_estoque_baixo'] = $resultado->fetch_assoc()['total'];
    
    return $stats;
}

// Verificar estoque baixo automaticamente (executar apenas uma vez por sessão)
if (!isset($_SESSION['estoque_verificado'])) {
    verificarEstoqueBaixo();
    $_SESSION['estoque_verificado'] = true;
}
?>