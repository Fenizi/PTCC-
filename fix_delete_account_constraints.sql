-- ========================================
-- CORREÇÃO DEFINITIVA - FOREIGN KEY CONSTRAINTS
-- Sistema GERE TECH
-- ========================================
-- Este script corrige o problema de exclusão de usuários
-- Execute este código no phpMyAdmin para corrigir o banco existente

USE geretech;

-- ========================================
-- VERIFICAR E CORRIGIR CONSTRAINTS EXISTENTES
-- ========================================

-- Verificar se a constraint logs_atividades_ibfk_1 existe e removê-la
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'geretech' 
     AND TABLE_NAME = 'logs_atividades' 
     AND CONSTRAINT_NAME = 'logs_atividades_ibfk_1') > 0,
    'ALTER TABLE logs_atividades DROP FOREIGN KEY logs_atividades_ibfk_1',
    'SELECT "Constraint logs_atividades_ibfk_1 não existe - pulando"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Recriar constraint da tabela logs_atividades com CASCADE
ALTER TABLE logs_atividades 
ADD CONSTRAINT logs_atividades_ibfk_1 
FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE;

-- ========================================
-- VERIFICAR OUTRAS TABELAS (OPCIONAL)
-- ========================================
-- As tabelas clientes, produtos, vendas, configuracoes e alertas
-- já foram criadas com ON DELETE CASCADE no arquivo principal

-- ========================================
-- PASSO 3: Verificar se as correções funcionaram
-- ========================================

-- Mostrar todas as constraints atuais
SELECT 
    TABLE_NAME as 'Tabela',
    COLUMN_NAME as 'Coluna',
    CONSTRAINT_NAME as 'Nome da Constraint',
    REFERENCED_TABLE_NAME as 'Tabela Referenciada',
    REFERENCED_COLUMN_NAME as 'Coluna Referenciada'
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE 
    REFERENCED_TABLE_SCHEMA = 'geretech' 
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, COLUMN_NAME;

-- ========================================
-- CONFIRMAÇÃO
-- ========================================
SELECT 'CORREÇÃO APLICADA COM SUCESSO!' as 'Status',
       'Agora você pode deletar usuários sem erro de foreign key' as 'Resultado';

-- ========================================
-- INSTRUÇÕES DE USO:
-- ========================================
-- 1. Copie todo este código
-- 2. Abra o phpMyAdmin
-- 3. Selecione o banco 'geretech'
-- 4. Vá na aba 'SQL'
-- 5. Cole este código e clique em 'Executar'
-- 6. Verifique se aparece 'CORREÇÃO APLICADA COM SUCESSO!'
-- ========================================