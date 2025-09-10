-- Script para corrigir a constraint de chave estrangeira na tabela logs_atividades
-- Adiciona ON DELETE CASCADE para permitir exclusão de usuários

-- Primeiro, remover a constraint existente
ALTER TABLE logs_atividades DROP FOREIGN KEY logs_atividades_ibfk_1;

-- Adicionar a nova constraint com ON DELETE CASCADE
ALTER TABLE logs_atividades 
ADD CONSTRAINT logs_atividades_ibfk_1 
FOREIGN KEY (id_usuario) 
REFERENCES usuarios (id) 
ON DELETE CASCADE;

-- Verificar se a constraint foi criada corretamente
SHOW CREATE TABLE logs_atividades;