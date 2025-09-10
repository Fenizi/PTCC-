-- Script para corrigir constraints de foreign key
-- Adicionar CASCADE nas foreign keys das tabelas vendas e logs_atividades
-- Sistema GERE TECH

USE geretech;

-- Remover as constraints existentes que não têm CASCADE
ALTER TABLE vendas DROP FOREIGN KEY vendas_ibfk_2;
ALTER TABLE vendas DROP FOREIGN KEY vendas_ibfk_3;
ALTER TABLE logs_atividades DROP FOREIGN KEY logs_atividades_ibfk_1;

-- Recriar as constraints com CASCADE
ALTER TABLE vendas 
ADD CONSTRAINT vendas_ibfk_2 
FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE CASCADE;

ALTER TABLE vendas 
ADD CONSTRAINT vendas_ibfk_3 
FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE CASCADE;

-- Corrigir constraint da tabela logs_atividades
ALTER TABLE logs_atividades 
ADD CONSTRAINT logs_atividades_ibfk_1 
FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE;

-- Verificar todas as constraints atuais
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE 
    REFERENCED_TABLE_SCHEMA = 'geretech' 
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, COLUMN_NAME;