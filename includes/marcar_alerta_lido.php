<?php
/**
 * Script para marcar alertas como lidos
 * Usado via AJAX pelo dashboard
 */

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !is_numeric($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do alerta inválido']);
    exit;
}

require_once 'conexao.php';
require_once 'config.php';

$alerta_id = (int)$input['id'];

// Marcar alerta como lido
if (marcarAlertaComoLido($alerta_id)) {
    // Registrar a ação no log
    registrarLog($_SESSION['usuario_id'], 'ALERTA_LIDO', 'alertas', $alerta_id, 'Alerta marcado como lido');
    
    echo json_encode(['success' => true, 'message' => 'Alerta marcado como lido']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao marcar alerta como lido']);
}
?>