-- Script para atualizar permanentemente a senha do admin@geretech.com para '123456'
-- Execute este script no phpMyAdmin ou MySQL Workbench

USE geretech;

-- Atualizar a senha do usuário admin@geretech.com
UPDATE usuarios 
SET senha = '$2y$12$3H9HyvGSUMxRxx4xWze33u7LpgcUKpvNQVfF8e0j1CONBFWcQ3Pom' 
WHERE email = 'admin@geretech.com';

-- Verificar se a alteração foi feita
SELECT id, nome, email, 'Senha alterada para 123456' as status 
FROM usuarios 
WHERE email = 'admin@geretech.com';

-- Caso o usuário não exista, criar ele
INSERT IGNORE INTO usuarios (nome, email, senha) 
VALUES ('Administrador', 'admin@geretech.com', '$2y$12$3H9HyvGSUMxRxx4xWze33u7LpgcUKpvNQVfF8e0j1CONBFWcQ3Pom');