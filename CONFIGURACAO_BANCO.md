# 🗄️ Configuração do Banco de Dados - Sistema GERE TECH

## 📋 Pré-requisitos

Antes de configurar o banco de dados, certifique-se de que você tem:

- ✅ **MySQL Server** instalado e rodando
- ✅ **PHP** com extensão MySQLi habilitada
- ✅ **Servidor web** (Apache, Nginx, ou servidor PHP built-in)
- ✅ Acesso ao MySQL com usuário **root** ou com privilégios administrativos

## 🚀 Configuração Automática (Recomendado)

### Opção 1: Script de Configuração Automática

1. **Execute o script de configuração:**
   ```
   http://localhost/seu-projeto/setup_database.php
   ```

2. **Siga as instruções na tela** - o script irá:
   - Criar o banco de dados `geretech`
   - Executar todas as tabelas necessárias
   - Inserir dados de exemplo
   - Criar usuário administrador padrão

3. **Teste a conexão:**
   ```
   http://localhost/seu-projeto/teste_conexao.php
   ```

### Opção 2: Configuração Manual

1. **Acesse o MySQL:**
   ```sql
   mysql -u root -p
   ```

2. **Execute o arquivo SQL:**
   ```sql
   source /caminho/para/db/geretech.sql
   ```

   Ou copie e cole o conteúdo do arquivo `db/geretech.sql` no seu cliente MySQL.

## ⚙️ Configurações de Conexão

### Arquivo: `includes/conexao.php`

As configurações padrão são:

```php
$host = 'localhost';     // Servidor MySQL
$usuario = 'root';       // Usuário MySQL
$senha = '';             // Senha (vazia por padrão no XAMPP/WAMP)
$banco = 'geretech';     // Nome do banco
$porta = 3306;           // Porta MySQL
```

### Personalizando as Configurações

Se suas configurações forem diferentes, edite o arquivo `includes/conexao.php`:

```php
// Exemplo para configurações personalizadas
$host = '192.168.1.100';  // IP do servidor
$usuario = 'meu_usuario'; // Seu usuário MySQL
$senha = 'minha_senha';   // Sua senha MySQL
$banco = 'geretech';      // Manter o nome do banco
$porta = 3306;            // Porta padrão
```

## 🏗️ Estrutura do Banco de Dados

### Tabelas Criadas:

| Tabela | Descrição | Registros Iniciais |
|--------|-----------|--------------------|
| `usuarios` | Usuários do sistema | 1 (admin) |
| `clientes` | Cadastro de clientes | 2 exemplos |
| `produtos` | Catálogo de produtos | 5 exemplos |
| `vendas` | Registro de vendas | 5 exemplos |
| `configuracoes` | Configurações do sistema | 8 configurações |
| `logs_atividades` | Log de ações dos usuários | 4 exemplos |
| `alertas` | Alertas do sistema | 3 exemplos |

### Usuário Administrador Padrão:

- **Email:** `admin@geretech.com`
- **Senha:** `admin123`
- **Nome:** `Administrador`

> ⚠️ **Importante:** Altere a senha padrão após o primeiro login!

## 🔧 Resolução de Problemas

### Erro: "Access denied for user 'root'@'localhost'"

**Solução:**
1. Verifique se o MySQL está rodando
2. Confirme usuário e senha no MySQL
3. Tente resetar a senha do root:
   ```bash
   # No Windows (XAMPP)
   mysql -u root
   
   # No Linux/Mac
   sudo mysql -u root
   ```

### Erro: "Unknown database 'geretech'"

**Solução:**
1. Execute o script `setup_database.php`
2. Ou crie manualmente:
   ```sql
   CREATE DATABASE geretech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

### Erro: "Table doesn't exist"

**Solução:**
1. Execute o arquivo `db/geretech.sql`
2. Ou use o script `setup_database.php`

### Erro: "Connection refused"

**Solução:**
1. Verifique se o MySQL está rodando:
   ```bash
   # Windows
   net start mysql
   
   # Linux
   sudo systemctl start mysql
   
   # Mac
   brew services start mysql
   ```

2. Verifique a porta (padrão: 3306)

## 🧪 Testando a Configuração

### 1. Teste de Conexão
```
http://localhost/seu-projeto/teste_conexao.php
```

Este script verifica:
- ✅ Conexão com MySQL
- ✅ Existência do banco `geretech`
- ✅ Todas as tabelas necessárias
- ✅ Dados de exemplo
- ✅ Funcionalidades básicas

### 2. Teste de Login
```
http://localhost/seu-projeto/pages/login.php
```

Use as credenciais:
- **Email:** `admin@geretech.com`
- **Senha:** `admin123`

## 📊 Backup e Restauração

### Fazer Backup
```bash
mysqldump -u root -p geretech > backup_geretech.sql
```

### Restaurar Backup
```bash
mysql -u root -p geretech < backup_geretech.sql
```

## 🔒 Segurança

### Recomendações de Segurança:

1. **Altere a senha padrão** do usuário administrador
2. **Use senhas fortes** para o MySQL
3. **Não use root em produção** - crie um usuário específico:
   ```sql
   CREATE USER 'geretech_user'@'localhost' IDENTIFIED BY 'senha_forte';
   GRANT ALL PRIVILEGES ON geretech.* TO 'geretech_user'@'localhost';
   FLUSH PRIVILEGES;
   ```
4. **Configure SSL** para conexões em produção
5. **Mantenha backups regulares**

## 📞 Suporte

Se você encontrar problemas:

1. ✅ Verifique os logs de erro do PHP
2. ✅ Execute o `teste_conexao.php`
3. ✅ Confirme as configurações no `includes/conexao.php`
4. ✅ Verifique se todas as extensões PHP estão habilitadas

---

**Sistema GERE TECH** - Configuração do Banco de Dados  
*Última atualização: Janeiro 2025*