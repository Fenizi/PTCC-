-- Banco de dados GERE TECH
-- Criação das tabelas do sistema

CREATE DATABASE IF NOT EXISTS geretech;
USE geretech;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de produtos
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    codigo VARCHAR(50),
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    valor DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de vendas
CREATE TABLE vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    id_cliente INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    forma_pagamento ENUM('dinheiro', 'cartao_debito', 'cartao_credito', 'pix') DEFAULT 'dinheiro',
    valor_total DECIMAL(10,2) NOT NULL,
    data_venda DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Tabela de configurações do sistema
CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    chave VARCHAR(100) NOT NULL,
    valor TEXT,
    descricao TEXT,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_chave (user_id, chave)
);

-- Tabela de logs de atividades
CREATE TABLE logs_atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    acao VARCHAR(100) NOT NULL,
    tabela_afetada VARCHAR(50),
    id_registro INT,
    detalhes TEXT,
    data_acao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de alertas
CREATE TABLE alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tipo ENUM('estoque_baixo', 'backup', 'sistema') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensagem TEXT NOT NULL,
    lido BOOLEAN DEFAULT FALSE,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Inserir usuário administrador padrão (senha: 123456)
INSERT INTO usuarios (nome, email, senha) VALUES 
('Administrador', 'admin@geretech.com', '$2y$12$3H9HyvGSUMxRxx4xWze33u7LpgcUKpvNQVfF8e0j1CONBFWcQ3Pom');

-- Inserir alguns dados de exemplo
INSERT INTO clientes (user_id, nome, cpf, telefone, email) VALUES 
(1, 'João Silva', '123.456.789-00', '(11) 99999-9999', 'joao@email.com'),
(1, 'Maria Santos', '987.654.321-00', '(11) 88888-8888', 'maria@email.com');

INSERT INTO produtos (user_id, nome, descricao, valor, estoque) VALUES 
(1, 'Notebook Dell', 'Notebook Dell Inspiron 15 3000', 2500.00, 10),
(1, 'Mouse Logitech', 'Mouse óptico sem fio', 89.90, 5),
(1, 'Teclado Mecânico', 'Teclado mecânico RGB', 299.99, 15),
(1, 'Monitor Samsung', 'Monitor LED 24 polegadas Full HD', 899.99, 8),
(1, 'Impressora HP', 'Impressora multifuncional jato de tinta', 450.00, 3);

INSERT INTO vendas (user_id, id_cliente, id_produto, quantidade, forma_pagamento, valor_total) VALUES 
(1, 1, 1, 1, 'cartao_credito', 2500.00),
(1, 2, 2, 2, 'pix', 179.80),
(1, 1, 3, 1, 'dinheiro', 299.99),
(1, 2, 4, 1, 'cartao_debito', 899.99),
(1, 1, 5, 1, 'pix', 450.00);

-- Inserir configurações padrão
INSERT INTO configuracoes (user_id, chave, valor, descricao) VALUES 
(1, 'empresa_nome', 'GERE TECH', 'Nome da empresa'),
(1, 'empresa_cnpj', '00.000.000/0001-00', 'CNPJ da empresa'),
(1, 'empresa_endereco', 'São Paulo, SP - Brasil', 'Endereço da empresa'),
(1, 'empresa_telefone', '(11) 99999-9999', 'Telefone da empresa'),
(1, 'empresa_email', 'contato@geretech.com', 'Email da empresa'),
(1, 'estoque_minimo_alerta', '5', 'Quantidade mínima para alerta de estoque'),
(1, 'backup_automatico', 'true', 'Ativar backup automático'),
(1, 'tema_padrao', 'claro', 'Tema padrão do sistema');

-- Inserir alertas de exemplo
INSERT INTO alertas (user_id, tipo, titulo, mensagem) VALUES 
(1, 'estoque_baixo', 'Estoque Baixo', 'O produto "Mouse Logitech" está com estoque baixo (5 unidades)'),
(1, 'estoque_baixo', 'Estoque Baixo', 'O produto "Impressora HP" está com estoque baixo (3 unidades)'),
(1, 'sistema', 'Bem-vindo!', 'Sistema GERE TECH iniciado com sucesso');

-- Inserir logs de exemplo
INSERT INTO logs_atividades (id_usuario, acao, tabela_afetada, id_registro, detalhes) VALUES 
(1, 'LOGIN', 'usuarios', 1, 'Usuário fez login no sistema'),
(1, 'CADASTRO', 'produtos', 4, 'Produto "Monitor Samsung" cadastrado'),
(1, 'VENDA', 'vendas', 1, 'Venda registrada - R$ 2.500,00'),
(1, 'CADASTRO', 'clientes', 1, 'Cliente "João Silva" cadastrado');