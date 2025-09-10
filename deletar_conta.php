<?php
// Arquivo para processar a exclusão de conta do usuário
// Sistema GERE TECH - Versão com tratamento avançado de erros

// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para log de debug
function debug_log($message) {
    error_log("[DELETAR_CONTA] " . date('Y-m-d H:i:s') . " - " . $message);
}

session_start();
debug_log("Iniciando processo de exclusão de conta");

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    debug_log("Usuário não logado, redirecionando");
    header('Location: pages/login.php');
    exit;
}

$response = ['success' => false, 'message' => '', 'debug' => []];

try {
    debug_log("Tentando incluir arquivo de conexão");
    require_once 'php/conexao.php';
    debug_log("Arquivo de conexão incluído com sucesso");
    
    // Verificar se a conexão foi estabelecida
    if (!isset($conexao) || $conexao->connect_error) {
        throw new Exception("Falha na conexão com o banco de dados: " . ($conexao->connect_error ?? 'Conexão não estabelecida'));
    }
    
    debug_log("Conexão com banco estabelecida");
    
} catch (Exception $e) {
    debug_log("Erro ao conectar com banco: " . $e->getMessage());
    $response['message'] = 'Erro de conexão com o banco de dados.';
    $response['debug'][] = $e->getMessage();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("Processando requisição POST");
    
    $senha_confirmacao = $_POST['senha_confirmacao'] ?? '';
    $palavra_confirmacao = $_POST['palavra_confirmacao'] ?? '';
    $usuario_id = $_SESSION['usuario_id'];
    
    debug_log("Dados recebidos - Usuario ID: $usuario_id, Senha preenchida: " . (!empty($senha_confirmacao) ? 'Sim' : 'Não') . ", Palavra: $palavra_confirmacao");
    
    // Validações básicas
    if (empty($senha_confirmacao)) {
        $response['message'] = 'A senha de confirmação é obrigatória.';
        debug_log("Validação falhou: senha vazia");
    } elseif (empty($palavra_confirmacao)) {
        $response['message'] = 'Você deve digitar "DELETAR" para confirmar.';
        debug_log("Validação falhou: palavra vazia");
    } elseif ($palavra_confirmacao !== 'DELETAR') {
        $response['message'] = 'Você deve digitar exatamente "DELETAR" (em maiúsculas) para confirmar.';
        debug_log("Validação falhou: palavra incorreta - recebido: '$palavra_confirmacao'");
    } else {
        debug_log("Validações básicas passaram, verificando senha do usuário");
        
        try {
            // Verificar a senha atual do usuário
            $stmt = $conexao->prepare("SELECT senha FROM usuarios WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Erro ao preparar statement: " . $conexao->error);
            }
            
            $stmt->bind_param("i", $usuario_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query de verificação: " . $stmt->error);
            }
            
            $resultado = $stmt->get_result();
            debug_log("Query de verificação executada, linhas encontradas: " . $resultado->num_rows);
            
            if ($resultado->num_rows === 0) {
                $response['message'] = 'Usuário não encontrado.';
                debug_log("Usuário não encontrado no banco");
            } else {
                $usuario_data = $resultado->fetch_assoc();
                debug_log("Dados do usuário recuperados");
                
                // Verificar se a senha está correta
                if (!password_verify($senha_confirmacao, $usuario_data['senha'])) {
                    $response['message'] = 'Senha incorreta.';
                    debug_log("Senha incorreta fornecida");
                } else {
                    debug_log("Senha correta, iniciando processo de exclusão");
                    
                    // Verificar constraints antes de deletar
                    debug_log("Verificando dados relacionados antes da exclusão");
                    
                    // Contar registros relacionados
                    $tabelas_relacionadas = [
                        'clientes' => 'user_id',
                        'produtos' => 'user_id', 
                        'vendas' => 'user_id',
                        'configuracoes' => 'user_id',
                        'alertas' => 'user_id'
                    ];
                    
                    $total_registros = 0;
                    foreach ($tabelas_relacionadas as $tabela => $campo) {
                        $stmt_count = $conexao->prepare("SELECT COUNT(*) as total FROM $tabela WHERE $campo = ?");
                        if ($stmt_count) {
                            $stmt_count->bind_param("i", $usuario_id);
                            $stmt_count->execute();
                            $result_count = $stmt_count->get_result();
                            $count_data = $result_count->fetch_assoc();
                            $total_registros += $count_data['total'];
                            debug_log("Tabela $tabela: {$count_data['total']} registros");
                            $stmt_count->close();
                        }
                    }
                    
                    debug_log("Total de registros relacionados: $total_registros");
                    
                    // Iniciar transação para garantir integridade dos dados
                    debug_log("Iniciando transação");
                    $conexao->autocommit(false);
                    
                    try {
                        // Deletar o usuário (CASCADE irá deletar todos os dados relacionados)
                        debug_log("Preparando query de exclusão");
                        $stmt_delete = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
                        
                        if (!$stmt_delete) {
                            throw new Exception("Erro ao preparar statement de exclusão: " . $conexao->error);
                        }
                        
                        $stmt_delete->bind_param("i", $usuario_id);
                        debug_log("Executando exclusão do usuário ID: $usuario_id");
                        
                        if ($stmt_delete->execute()) {
                            $linhas_afetadas = $stmt_delete->affected_rows;
                            debug_log("Exclusão executada com sucesso. Linhas afetadas: $linhas_afetadas");
                            
                            if ($linhas_afetadas > 0) {
                                // Confirmar a transação
                                $conexao->commit();
                                debug_log("Transação confirmada");
                                
                                // Destruir a sessão
                                session_destroy();
                                debug_log("Sessão destruída");
                                
                                $response['success'] = true;
                                $response['message'] = 'Conta deletada com sucesso.';
                                debug_log("Processo de exclusão concluído com sucesso");
                            } else {
                                throw new Exception("Nenhuma linha foi afetada pela exclusão");
                            }
                        } else {
                            throw new Exception("Erro ao executar exclusão: " . $stmt_delete->error);
                        }
                        
                        $stmt_delete->close();
                        
                    } catch (Exception $e) {
                        // Reverter a transação em caso de exceção
                        debug_log("Erro durante exclusão, revertendo transação: " . $e->getMessage());
                        $conexao->rollback();
                        $response['message'] = 'Erro ao processar exclusão: ' . $e->getMessage();
                        $response['debug'][] = $e->getMessage();
                    }
                    
                    // Restaurar autocommit
                    $conexao->autocommit(true);
                    debug_log("Autocommit restaurado");
                }
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            debug_log("Erro durante verificação de senha: " . $e->getMessage());
            $response['message'] = 'Erro interno durante verificação: ' . $e->getMessage();
            $response['debug'][] = $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Método de requisição inválido.';
    debug_log("Método de requisição inválido: " . $_SERVER['REQUEST_METHOD']);
}

// Fechar conexão
if (isset($conexao)) {
    $conexao->close();
    debug_log("Conexão fechada");
}

debug_log("Finalizando processo. Sucesso: " . ($response['success'] ? 'Sim' : 'Não'));

// Retornar resposta em JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>