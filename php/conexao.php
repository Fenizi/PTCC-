<?php
// Arquivo de conexão com o banco de dados MySQL
// Sistema GERE TECH

$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'geretech';

// Criar conexão com o banco de dados
$conexao = new mysqli($host, $usuario, $senha, $banco);

// Verificar se houve erro na conexão
if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}

// Definir charset para UTF-8
$conexao->set_charset("utf8");
?>