<?php
// Página de Produtos - Sistema GERE TECH
// Gerenciamento de produtos

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../php/conexao.php';

$mensagem = '';
$tipo_mensagem = '';

// Processar ações
if ($_POST) {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'cadastrar') {
        $codigo = trim($_POST['codigo']);
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);
        $estoque = (int)$_POST['estoque'];
        
        // Validações server-side
        if (empty($nome) || empty($valor) || $estoque < 0) {
            $mensagem = 'Nome, valor e estoque são obrigatórios.';
            $tipo_mensagem = 'error';
        } elseif (!preg_match('/^[A-Za-zÀ-ÿ0-9\s]+$/', $nome)) {
            $mensagem = 'O nome do produto deve conter apenas letras, números e espaços.';
            $tipo_mensagem = 'error';
        } elseif (strlen($nome) < 2) {
            $mensagem = 'O nome do produto deve ter pelo menos 2 caracteres.';
            $tipo_mensagem = 'error';
        } elseif (!empty($codigo) && !preg_match('/^[A-Za-z0-9]+$/', $codigo)) {
            $mensagem = 'O código deve conter apenas letras e números.';
            $tipo_mensagem = 'error';
        } elseif ($valor <= 0) {
            $mensagem = 'O valor deve ser maior que zero.';
            $tipo_mensagem = 'error';
        } else {
            // Inserir produto
            $stmt = $conexao->prepare("INSERT INTO produtos (user_id, codigo, nome, descricao, valor, estoque) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssdi", $_SESSION['usuario_id'], $codigo, $nome, $descricao, $valor, $estoque);
            
            if ($stmt->execute()) {
                $mensagem = 'Produto cadastrado com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao cadastrar produto.';
                $tipo_mensagem = 'error';
            }
            $stmt->close();
        }
    }
    
    if ($acao === 'editar') {
        $id = $_POST['id'];
        $codigo = trim($_POST['codigo']);
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);
        $estoque = (int)$_POST['estoque'];
        
        // Validações server-side
        if (empty($nome) || empty($valor) || $estoque < 0) {
            $mensagem = 'Nome, valor e estoque são obrigatórios.';
            $tipo_mensagem = 'error';
        } elseif (!preg_match('/^[A-Za-zÀ-ÿ0-9\s]+$/', $nome)) {
            $mensagem = 'O nome do produto deve conter apenas letras, números e espaços.';
            $tipo_mensagem = 'error';
        } elseif (strlen($nome) < 2) {
            $mensagem = 'O nome do produto deve ter pelo menos 2 caracteres.';
            $tipo_mensagem = 'error';
        } elseif (!empty($codigo) && !preg_match('/^[A-Za-z0-9]+$/', $codigo)) {
            $mensagem = 'O código deve conter apenas letras e números.';
            $tipo_mensagem = 'error';
        } elseif ($valor <= 0) {
            $mensagem = 'O valor deve ser maior que zero.';
            $tipo_mensagem = 'error';
        } else {
            $stmt = $conexao->prepare("UPDATE produtos SET codigo = ?, nome = ?, descricao = ?, valor = ?, estoque = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssdiii", $codigo, $nome, $descricao, $valor, $estoque, $id, $_SESSION['usuario_id']);
            
            if ($stmt->execute()) {
                $mensagem = 'Produto atualizado com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao atualizar produto.';
                $tipo_mensagem = 'error';
            }
            $stmt->close();
        }
    }
    
    if ($acao === 'excluir') {
        $id = $_POST['id'];
        
        // Verificar se produto tem vendas
        $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM vendas WHERE id_produto = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $vendas = $resultado->fetch_assoc()['total'];
        
        if ($vendas > 0) {
            $mensagem = 'Não é possível excluir produto com vendas registradas.';
            $tipo_mensagem = 'error';
        } else {
            $stmt = $conexao->prepare("DELETE FROM produtos WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
            
            if ($stmt->execute()) {
                $mensagem = 'Produto excluído com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao excluir produto.';
                $tipo_mensagem = 'error';
            }
        }
        $stmt->close();
    }
}

// Processar movimento de estoque
if (isset($_POST['movimento_estoque'])) {
    $produto_id = (int)$_POST['produto_id'];
    $quantidade = (int)$_POST['quantidade'];
    $tipo_movimento = $_POST['tipo_movimento'];
    
    if ($produto_id > 0 && $quantidade > 0) {
        // Buscar estoque atual
        $stmt = $conexao->prepare("SELECT estoque, nome FROM produtos WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $produto_id, $_SESSION['usuario_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($produto = $result->fetch_assoc()) {
            $estoque_atual = $produto['estoque'];
            $nome_produto = $produto['nome'];
            
            if ($tipo_movimento == 'entrada') {
                $novo_estoque = $estoque_atual + $quantidade;
                $mensagem = "Entrada de {$quantidade} unidades registrada para {$nome_produto}";
                $tipo_mensagem = 'success';
            } else {
                if ($quantidade > $estoque_atual) {
                    $mensagem = "Quantidade insuficiente em estoque. Disponível: {$estoque_atual}";
                    $tipo_mensagem = 'error';
                } else {
                    $novo_estoque = $estoque_atual - $quantidade;
                    $mensagem = "Saída de {$quantidade} unidades registrada para {$nome_produto}";
                    $tipo_mensagem = 'success';
                }
            }
            
            // Atualizar estoque se não houver erro
            if ($tipo_mensagem == 'success') {
                $stmt = $conexao->prepare("UPDATE produtos SET estoque = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("iii", $novo_estoque, $produto_id, $_SESSION['usuario_id']);
                
                if (!$stmt->execute()) {
                    $mensagem = "Erro ao atualizar estoque: " . $conexao->error;
                    $tipo_mensagem = 'error';
                }
            }
        } else {
            $mensagem = "Produto não encontrado";
            $tipo_mensagem = 'error';
        }
    } else {
        $mensagem = "Dados inválidos para movimento de estoque";
        $tipo_mensagem = 'error';
    }
}

// Buscar produtos
$busca = $_GET['busca'] ?? '';
$filtro_estoque = $_GET['filtro_estoque'] ?? '';
$where_conditions = [];

// Sempre filtrar por user_id
$where_conditions[] = "user_id = " . $_SESSION['usuario_id'];

if ($busca) {
    $where_conditions[] = "(nome LIKE '%$busca%' OR descricao LIKE '%$busca%')";
}

if ($filtro_estoque === 'baixo') {
    $where_conditions[] = "estoque <= 5";
} elseif ($filtro_estoque === 'zero') {
    $where_conditions[] = "estoque = 0";
}

$where = 'WHERE ' . implode(' AND ', $where_conditions);

$produtos = $conexao->query("SELECT * FROM produtos $where ORDER BY nome");

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - GERE TECH</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Header -->
            <?php include '../includes/header.php'; ?>
            
            <!-- Mensagens -->
            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>
            
            <!-- Seção de Entrada e Saída -->
            <div class="stock-actions">
                <div class="stock-card entrada">
                    <h3><i class="fas fa-arrow-down"></i> Entrada e Saída</h3>
                    <form class="stock-form" method="POST">
                        <div class="form-group">
                            <label for="produto_entrada">Produto:</label>
                            <select class="form-control" id="produto_entrada" name="produto_id" required>
                                <option value="">Selecione um produto</option>
                                <?php
                                // Reabrir conexão para buscar produtos no select
                                $conexao_temp = new mysqli($host, $usuario, $senha, $banco);
                                $produtos_query = "SELECT id, nome FROM produtos WHERE user_id = " . $_SESSION['usuario_id'] . " ORDER BY nome";
                                $produtos_result = $conexao_temp->query($produtos_query);
                                while($produto = $produtos_result->fetch_assoc()) {
                                    echo "<option value='{$produto['id']}'>{$produto['nome']}</option>";
                                }
                                $conexao_temp->close();
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantidade">Quantidade:</label>
                            <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="tipo_movimento">Tipo:</label>
                            <select class="form-control" id="tipo_movimento" name="tipo_movimento" required>
                                <option value="entrada">Entrada</option>
                                <option value="saida">Saída</option>
                            </select>
                        </div>
                        <button type="submit" name="movimento_estoque" class="btn btn-primary">
                            <i class="fas fa-save"></i> Registrar Movimento
                        </button>
                    </form>
                </div>
                
                <div class="stock-card">
                    <h3><i class="fas fa-chart-line"></i> Produtos</h3>
                    <div class="stock-form">
                        <div class="form-group">
                            <label>Pesquisar produto:</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Digite o nome do produto..." onkeyup="filterTable()">
                        </div>
                        <button type="button" class="btn btn-success" data-modal="modalProduto">
                            <i class="fas fa-plus"></i> Novo Produto
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Tabela de Produtos -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Custo</th>
                            <th>Venda</th>
                            <th>Quantidade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($produtos->num_rows > 0): ?>
                            <?php while ($produto = $produtos->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo str_pad($produto['id'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><?php echo htmlspecialchars($produto['codigo'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                    <td>R$ <?php echo number_format($produto['valor'] * 0.7, 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($produto['valor'], 2, ',', '.'); ?></td>
                                    <td>
                                        <?php 
                                        $estoque = $produto['estoque'];
                                        if($estoque == 0) {
                                            $badge_class = 'sem-estoque';
                                        } elseif($estoque <= 5) {
                                            $badge_class = 'baixo-estoque';
                                        } else {
                                            $badge_class = 'estoque-ok';
                                        }
                                        ?>
                                        <span class="estoque-badge <?php echo $badge_class; ?>">
                                            <?php echo $estoque; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $produto['estoque'] > 0 ? 'status-success' : 'status-danger'; ?>">
                                            <?php echo $produto['estoque'] > 0 ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button 
                                            class="btn btn-sm btn-secondary"
                                            onclick="editarProduto(<?php echo htmlspecialchars(json_encode($produto)); ?>)"
                                            title="Editar"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button 
                                            class="btn btn-sm btn-danger"
                                            onclick="excluirProduto(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>')"
                                            title="Excluir"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <?php if ($busca || $filtro_estoque): ?>
                                        Nenhum produto encontrado com os filtros aplicados
                                    <?php else: ?>
                                        Nenhum produto cadastrado
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal de Produto -->
    <div id="modalProduto" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle" class="modal-title">Novo Produto</h2>
                <span class="close">&times;</span>
            </div>
            
            <div class="modal-body">
                <form id="formProduto" method="POST">
                    <input type="hidden" name="acao" id="acao" value="cadastrar">
                    <input type="hidden" name="id" id="produtoId">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" id="codigo" name="codigo" class="form-control" placeholder="Ex: PROD001" pattern="[A-Za-z0-9]+" title="Apenas letras e números são permitidos">
                        </div>
                        
                        <div class="form-group" style="flex: 2;">
                            <label for="nome" class="form-label">Nome do Produto *</label>
                            <input type="text" id="nome" name="nome" class="form-control" required pattern="[A-Za-zÀ-ÿ0-9\s]+" title="Letras, números e espaços são permitidos" placeholder="Digite o nome do produto">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea id="descricao" name="descricao" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="valor" class="form-label">Valor (R$) *</label>
                            <input type="text" id="valor" name="valor" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="estoque" class="form-label">Estoque *</label>
                            <input type="number" id="estoque" name="estoque" class="form-control" min="0" required>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary modal-close">Cancelar</button>
                    <button type="submit" form="formProduto" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Confirmação -->
    <div id="modalConfirmacao" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirmar Exclusão</h3>
                <span class="close">&times;</span>
            </div>
            
            <div class="modal-body">
                <p id="mensagemConfirmacao"></p>
            </div>
            
            <div class="modal-footer">
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary modal-close">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">Excluir</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Formulário oculto para exclusão -->
    <form id="formExcluir" method="POST" style="display: none;">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id" id="idExcluir">
    </form>
    
    <script>
        let produtoParaExcluir = null;
        
        // Debug logs
        console.log('[PRODUTOS] Script carregado');
        
        // Editar produto
        function editarProduto(produto) {
            console.log('[PRODUTOS] editarProduto chamada:', produto);
            
            try {
                document.getElementById('modalTitle').textContent = 'Editar Produto';
                document.getElementById('acao').value = 'editar';
                document.getElementById('produtoId').value = produto.id;
                document.getElementById('codigo').value = produto.codigo || '';
                document.getElementById('nome').value = produto.nome;
                document.getElementById('descricao').value = produto.descricao || '';
                document.getElementById('valor').value = formatCurrency(produto.valor);
                document.getElementById('estoque').value = produto.estoque;
                
                const modal = document.getElementById('modalProduto');
                console.log('[PRODUTOS] Modal encontrado:', modal);
                console.log('[PRODUTOS] ModalManager disponível:', window.ModalManager);
                
                if (window.ModalManager && window.ModalManager.open) {
                    window.ModalManager.open(modal);
                } else {
                    console.error('[PRODUTOS] ModalManager não disponível');
                    modal.style.display = 'flex';
                    modal.classList.add('show');
                }
            } catch (error) {
                console.error('[PRODUTOS] Erro em editarProduto:', error);
            }
        }
        
        // Excluir produto
        function excluirProduto(id, nome) {
            console.log('[PRODUTOS] excluirProduto chamada:', id, nome);
            
            try {
                produtoParaExcluir = id;
                document.getElementById('mensagemConfirmacao').textContent = 
                    `Tem certeza que deseja excluir o produto "${nome}"?`;
                const modal = document.getElementById('modalConfirmacao');
                console.log('[PRODUTOS] Modal confirmação encontrado:', modal);
                
                if (window.ModalManager && window.ModalManager.open) {
                    window.ModalManager.open(modal);
                } else {
                    console.error('[PRODUTOS] ModalManager não disponível para confirmação');
                    modal.style.display = 'flex';
                    modal.classList.add('show');
                }
            } catch (error) {
                console.error('[PRODUTOS] Erro em excluirProduto:', error);
            }
        }
        
        // Confirmar exclusão
        function confirmarExclusao() {
            if (produtoParaExcluir) {
                document.getElementById('idExcluir').value = produtoParaExcluir;
                document.getElementById('formExcluir').submit();
            }
        }
        
        // Funções de fechamento removidas - agora gerenciadas pelo ModalManager global
        
        // Limpar formulário
        function limparFormulario() {
            document.getElementById('modalTitle').textContent = 'Novo Produto';
            document.getElementById('acao').value = 'cadastrar';
            document.getElementById('produtoId').value = '';
            document.getElementById('formProduto').reset();
        }
        
        // Formatar valor monetário
        function formatCurrency(value) {
            return parseFloat(value).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        // Inicialização quando DOM estiver carregado
        function initProdutos() {
            console.log('[PRODUTOS] Inicializando página de produtos');
            
            // Aguardar um pouco mais para garantir que ModalManager está disponível
            setTimeout(function() {
                // Garantir que ModalManager está inicializado
                if (window.ModalManager && typeof window.ModalManager.init === 'function') {
                    try {
                        window.ModalManager.init();
                        console.log('[PRODUTOS] ModalManager inicializado');
                    } catch (error) {
                        console.error('[PRODUTOS] Erro ao inicializar ModalManager:', error);
                    }
                } else {
                    console.warn('[PRODUTOS] ModalManager não encontrado, usando fallback');
                }
                
                // Event listener para botão "Novo Produto"
                const btnNovoProduto = document.querySelector('[data-modal="modalProduto"]');
                if (btnNovoProduto) {
                    btnNovoProduto.addEventListener('click', function() {
                        console.log('[PRODUTOS] Botão Novo Produto clicado');
                        limparFormulario();
                        const modal = document.getElementById('modalProduto');
                        if (window.ModalManager && window.ModalManager.open) {
                            window.ModalManager.open(modal);
                        } else {
                            modal.style.display = 'flex';
                            modal.classList.add('show');
                        }
                    });
                    console.log('[PRODUTOS] Event listener do botão Novo Produto registrado');
                } else {
                    console.error('[PRODUTOS] Botão Novo Produto não encontrado');
                }
                
                // Event listeners para botões cancelar
                const botoesCancelar = document.querySelectorAll('.modal-close');
                botoesCancelar.forEach(function(botao) {
                    botao.addEventListener('click', function() {
                        console.log('[PRODUTOS] Botão cancelar clicado');
                        const modal = botao.closest('.modal');
                        if (modal) {
                            if (window.ModalManager && window.ModalManager.close) {
                                window.ModalManager.close(modal);
                            } else {
                                modal.style.display = 'none';
                                modal.classList.remove('show');
                            }
                        }
                    });
                });
                
                // Event listeners para botões X (close)
                const botoesFechar = document.querySelectorAll('.close');
                botoesFechar.forEach(function(botao) {
                    botao.addEventListener('click', function() {
                        console.log('[PRODUTOS] Botão X clicado');
                        const modal = botao.closest('.modal');
                        if (modal) {
                            if (window.ModalManager && window.ModalManager.close) {
                                window.ModalManager.close(modal);
                            } else {
                                modal.style.display = 'none';
                                modal.classList.remove('show');
                            }
                        }
                    });
                });
                
                console.log('[PRODUTOS] Event listeners dos botões cancelar e fechar registrados');
                
                // Registrar event listeners dos botões de ação
                const botoesEditar = document.querySelectorAll('button[onclick^="editarProduto"]');
                const botoesExcluir = document.querySelectorAll('button[onclick^="excluirProduto"]');
                
                console.log('[PRODUTOS] Botões encontrados - Editar:', botoesEditar.length, 'Excluir:', botoesExcluir.length);
            }, 200);
        }
        
        // Função para filtrar tabela
        function filterTable() {
            const busca = document.getElementById('searchInput').value.toLowerCase();
            const linhas = document.querySelectorAll('#tabelaProdutos tbody tr');
            
            linhas.forEach(linha => {
                const nome = linha.cells[1].textContent.toLowerCase();
                
                if (nome.includes(busca)) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        }
        
        // Validação do formulário de movimento
        function initFormularioMovimento() {
            const form = document.querySelector('.stock-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const produto = document.getElementById('produto_entrada').value;
                    const quantidade = document.getElementById('quantidade').value;
                    const tipo = document.getElementById('tipo_movimento').value;
                    
                    if (!produto || !quantidade || !tipo) {
                        e.preventDefault();
                        alert('Por favor, preencha todos os campos.');
                        return false;
                    }
                    
                    if (parseInt(quantidade) <= 0) {
                        e.preventDefault();
                        alert('A quantidade deve ser maior que zero.');
                        return false;
                    }
                });
                console.log('[PRODUTOS] Validação do formulário de movimento registrada');
            }
        }
        
        // Inicializar validações de formulário
        function initValidacoesProduto() {
            // Validação do campo nome do produto
            const nomeInput = document.getElementById('nome');
            if (nomeInput) {
                nomeInput.addEventListener('input', function(e) {
                    const valor = e.target.value;
                    const regex = /^[A-Za-zÀ-ÿ0-9\s]*$/;
                    
                    if (!regex.test(valor)) {
                        e.target.value = valor.replace(/[^A-Za-zÀ-ÿ0-9\s]/g, '');
                        showAlert('Apenas letras, números e espaços são permitidos no nome do produto', 'warning');
                    }
                });
                console.log('[PRODUTOS] Validação do campo nome registrada - permite letras, números e espaços');
            }
            
            // Validação do formulário de produto
            const formProduto = document.getElementById('formProduto');
            if (formProduto) {
                formProduto.addEventListener('submit', function(e) {
                    const nome = document.getElementById('nome').value.trim();
                    const codigo = document.getElementById('codigo').value.trim();
                    const valor = document.getElementById('valor').value.trim();
                    const estoque = document.getElementById('estoque').value.trim();
                    
                    // Validar nome (letras, números e espaços)
                    const nomeRegex = /^[A-Za-zÀ-ÿ0-9\s]+$/;
                    if (!nomeRegex.test(nome)) {
                        e.preventDefault();
                        showAlert('O nome do produto deve conter apenas letras, números e espaços', 'error');
                        return false;
                    }
                    
                    // Validar se nome não está vazio
                    if (nome.length < 2) {
                        e.preventDefault();
                        showAlert('O nome do produto deve ter pelo menos 2 caracteres', 'error');
                        return false;
                    }
                    
                    // Validar código (se preenchido)
                    if (codigo && !/^[A-Za-z0-9]+$/.test(codigo)) {
                        e.preventDefault();
                        showAlert('O código deve conter apenas letras e números', 'error');
                        return false;
                    }
                    
                    // Validar valor
                    if (!valor || parseFloat(valor.replace(',', '.')) <= 0) {
                        e.preventDefault();
                        showAlert('O valor deve ser maior que zero', 'error');
                        return false;
                    }
                    
                    // Validar estoque
                    if (!estoque || parseInt(estoque) < 0) {
                        e.preventDefault();
                        showAlert('O estoque deve ser um número válido', 'error');
                        return false;
                    }
                });
                console.log('[PRODUTOS] Validação do formulário registrada');
            }
        }
        
        // Função para mostrar alertas
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.padding = '10px 15px';
            alertDiv.style.borderRadius = '4px';
            alertDiv.style.color = 'white';
            alertDiv.style.backgroundColor = type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#28a745';
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
        
        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
        
        // Código JavaScript removido - alertas automáticos desabilitados
    </script>
    
    <style>
        /* Prevenir overflow horizontal */
        html, body {
            overflow-x: hidden;
            max-width: 100%;
        }
        
        .dashboard {
            overflow-x: hidden;
            max-width: 100%;
        }
        
        .main-content {
            overflow-x: hidden;
            max-width: 100%;
        }
        
        /* Estilos específicos da página de produtos */
        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1.5rem;
            padding: 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        
        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-group {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            position: relative;
        }
        
        .search-group input {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .search-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            align-items: end;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        /* Seção de movimentação de estoque */
        .stock-movement-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.2);
            position: relative;
        }
        
        .stock-movement-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 12px 12px 0 0;
        }
        
        .stock-movement-section h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        /* Stock Actions e Cards */
        .stock-actions {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .stock-card {
            flex: 1;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.2);
            position: relative;
        }
        
        .stock-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 12px 12px 0 0;
        }
        
        .stock-card h3 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .stock-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .estoque-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        /* Badges de estoque */
        .estoque-ok {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }
        
        .baixo-estoque {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(118, 75, 162, 0.3);
        }
        
        .sem-estoque {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            opacity: 0.7;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Status badges modernizados */
        .status-success {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }
        
        .status-warning {
            background: linear-gradient(135deg, #764ba2, #667eea);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(118, 75, 162, 0.3);
        }
        
        .status-danger {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
            opacity: 0.8;
        }
        
        /* Alertas modernizados */
        .alert {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            border: none;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            animation: slideInDown 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: opacity 0.3s;
        }
        
        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-success::before {
            background: linear-gradient(90deg, #10b981, #059669);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #7f1d1d;
            border-left: 4px solid #ef4444;
        }
        
        .alert-error::before {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Tabela modernizada */
        .table-container {
            background: var(--bg-primary);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            position: relative;
            width: 100%;
            max-width: 100%;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
            max-width: 100%;
        }
        
        .table {
            width: 100%;
            min-width: 800px;
            table-layout: auto;
        }
        
        .table-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .table {
            margin: 0;
        }
        
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
        }
        
        .table thead th {
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 1.5rem 1rem;
            text-align: left;
            border: none;
            position: relative;
        }
        
        .table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: all var(--transition-normal);
            position: relative;
        }
        
        .table tbody tr:hover {
            background: var(--bg-secondary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid transparent;
            border-left-color: var(--primary-color);
        }
        
        .table tbody td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            border: none;
            transition: all var(--transition-fast);
        }
        
        /* Botões de ação */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background-color 0.2s;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border: none;
            box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .text-center {
            text-align: center;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin-right: 0.25rem;
        }
        
        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 1050;
        }
        
        .modal.show {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            animation: fadeIn 0.3s ease;
        }
        
        .modal.active {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            margin: 2rem;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInScale 0.3s ease;
        }
        
        @keyframes slideInScale {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(-50px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        .modal-header {
            background: #f8f9fa;
            border-radius: 4px 4px 0 0;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .modal-header h5 {
            font-weight: 600;
            font-size: 1.1rem;
            margin: 0;
            color: #495057;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        .modal-footer {
            padding: 1rem;
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 4px 4px;
        }
        
        @media (max-width: 768px) {
            .page-actions {
                flex-direction: column;
                align-items: stretch;
                padding: 1rem;
            }
            
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .form-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 12px;
            }
            
            .table tbody tr:hover {
                transform: none;
            }
            
            .btn {
                padding: 1rem;
                font-size: 0.875rem;
            }
            
            .modal-content {
                margin: 1rem;
                width: calc(100% - 2rem);
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .page-actions {
                padding: 0.75rem;
            }
            
            .stock-movement-section {
                padding: 1.5rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 1rem 0.5rem;
                font-size: 0.8rem;
            }
        }
    </style>
    
    <!-- JavaScript -->
    <script src="../js/main.js"></script>
    <script>
    // Inicialização unificada
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[PRODUTOS] DOM carregado, inicializando...');
        
        // Aguardar mais tempo para garantir que main.js foi totalmente carregado
        setTimeout(function() {
            // Inicializar componentes na ordem correta
            if (window.SidebarManager && typeof window.SidebarManager.init === 'function') {
                window.SidebarManager.init();
                console.log('[PRODUTOS] SidebarManager inicializado');
            }
            
            // Aguardar mais um pouco antes de inicializar os produtos
             setTimeout(function() {
                 initProdutos();
                 initFormularioMovimento();
                 initValidacoesProduto();
             }, 200);
        }, 300);
    });
    </script>
</body>
</html>