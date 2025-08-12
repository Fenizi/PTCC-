# GERE TECH - Sistema de Gestão Empresarial

## 📋 Sobre o Sistema

O GERE TECH é um sistema completo de gestão empresarial desenvolvido com tecnologias web modernas. O sistema oferece funcionalidades para gerenciamento de clientes, produtos, vendas e relatórios, com uma interface intuitiva e responsiva.

## 🚀 Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 8.0+
- **Bibliotecas**: 
  - Font Awesome (ícones)
  - Chart.js (gráficos)
  - PWA (Progressive Web App)

## 📁 Estrutura do Projeto

```
GERE-TECH/
├── css/
│   └── style.css          # Estilos principais
├── db/
│   └── geretech.sql       # Script do banco de dados
├── includes/
│   ├── conexao.php        # Configuração do banco
│   ├── config.php         # Funções do sistema
│   ├── header.php         # Cabeçalho das páginas
│   ├── sidebar.php        # Menu lateral
│   ├── logout.php         # Script de logout
│   └── marcar_alerta_lido.php # AJAX para alertas
├── js/
│   └── main.js           # JavaScript principal
├── pages/
│   ├── dashboard.php     # Painel principal
│   ├── clientes.php      # Gestão de clientes
│   ├── produtos.php      # Gestão de produtos
│   ├── vendas.php        # Gestão de vendas
│   └── relatorios.php    # Relatórios do sistema
├── index.html            # Página inicial
├── login.php             # Página de login
├── manifest.json         # Configuração PWA
└── README.md            # Este arquivo
```

## 🚀 Como usar

### Configuração Automática (Recomendado)

1. **Clone ou baixe o projeto**
2. **Configure o servidor web** (Apache, Nginx, XAMPP, WAMP, ou use o servidor built-in do PHP)
3. **Inicie o MySQL** (certifique-se de que está rodando na porta 3306)
4. **Acesse o sistema** através do navegador:
   ```
   http://localhost/seu-projeto/
   ```
5. **Clique em "Acessar Sistema"** - o sistema irá verificar automaticamente:
   - ✅ Conexão com MySQL
   - ✅ Criação do banco de dados
   - ✅ Estrutura das tabelas
   - ✅ Dados iniciais

### Configuração Manual (Alternativa)

1. **Execute o script de configuração:**
   ```
   http://localhost/seu-projeto/setup_database.php
   ```
2. **Ou importe manualmente** o arquivo `db/geretech.sql` no MySQL
3. **Teste a conexão:**
   ```
   http://localhost/seu-projeto/teste_conexao.php
   ```

### Credenciais padrão:
- **Email:** admin@geretech.com
- **Senha:** admin123

> ⚠️ **Importante:** Altere a senha padrão após o primeiro login!

## ⚙️ Configuração do Ambiente

### Pré-requisitos

1. **XAMPP** ou **WAMP** instalado
2. **MySQL Workbench** (opcional, mas recomendado)
3. **Navegador web moderno**

### Passo a Passo - Configuração no MySQL Workbench

#### 1. Abrir o MySQL Workbench
- Inicie o MySQL Workbench
- Conecte-se ao servidor MySQL local (geralmente `localhost:3306`)
- Use as credenciais padrão: usuário `root` e senha vazia (ou sua senha personalizada)

#### 2. Criar o Banco de Dados
- Clique em "Create a new schema" ou use o ícone de banco de dados
- Nome do schema: `geretech`
- Clique em "Apply"

#### 3. Importar a Estrutura do Banco
- Abra o arquivo `db/geretech.sql` no MySQL Workbench
- Ou copie todo o conteúdo do arquivo
- Execute o script completo (Ctrl+Shift+Enter)
- Verifique se todas as tabelas foram criadas:
  - `usuarios`
  - `clientes`
  - `produtos`
  - `vendas`
  - `configuracoes`
  - `logs_atividades`
  - `alertas`

#### 4. Verificar os Dados de Exemplo
Após executar o script, você deve ter:
- **1 usuário administrador**: email `admin@geretech.com`, senha `admin123`
- **2 clientes** de exemplo
- **5 produtos** de exemplo
- **5 vendas** de exemplo
- **Configurações** do sistema
- **Alertas** de estoque baixo

### Configuração do Servidor Web

#### Usando XAMPP:
1. Copie a pasta do projeto para `C:\xampp\htdocs\`
2. Inicie o Apache e MySQL no painel do XAMPP
3. Acesse `http://localhost/TCC-3Ds-2025/`

#### Usando PHP Built-in Server:
1. Abra o terminal na pasta do projeto
2. Execute: `php -S localhost:8000`
3. Acesse `http://localhost:8000/`

## 🔐 Credenciais de Acesso

**Usuário Administrador:**
- Email: `admin@geretech.com`
- Senha: `admin123`

## 🎯 Funcionalidades Principais

### 📊 Dashboard
- Estatísticas em tempo real
- Gráficos de vendas por mês
- Gráficos de formas de pagamento
- Alertas de estoque baixo
- Vendas recentes

### 👥 Gestão de Clientes
- Cadastro completo de clientes
- Busca e filtros
- Histórico de compras

### 📦 Gestão de Produtos
- Controle de estoque
- Alertas automáticos de estoque baixo
- Categorização de produtos

### 💰 Gestão de Vendas
- Registro de vendas
- Múltiplas formas de pagamento:
  - Dinheiro
  - Cartão de Débito
  - Cartão de Crédito
  - PIX

### 📈 Relatórios
- Relatório de vendas por período
- Produtos mais vendidos
- Relatório de clientes
- Exportação para PDF (simulado)

### 🔔 Sistema de Alertas
- Alertas de estoque baixo
- Notificações do sistema
- Marcação de alertas como lidos

### 📱 Recursos Adicionais
- Interface responsiva
- Tema claro/escuro
- Progressive Web App (PWA)
- Logs de atividades
- Sistema de configurações

## 🛠️ Personalização

### Configurações do Sistema
As configurações podem ser alteradas através da tabela `configuracoes`:
- Nome da empresa
- CNPJ
- Endereço
- Telefone
- Email
- Estoque mínimo para alertas
- Tema padrão

### Adicionando Novos Usuários
```sql
INSERT INTO usuarios (nome, email, senha) VALUES 
('Seu Nome', 'seu@email.com', '$2y$10$hash_da_senha');
```

**Nota**: Use `password_hash('sua_senha', PASSWORD_DEFAULT)` no PHP para gerar o hash.

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
- Verifique se o MySQL está rodando
- Confirme as credenciais em `includes/conexao.php`
- Teste a conexão no MySQL Workbench

### Página em Branco
- Ative a exibição de erros PHP
- Verifique os logs do Apache
- Confirme se todos os arquivos estão no lugar correto

### Alertas Não Aparecem
- Verifique se há produtos com estoque baixo
- Confirme se a tabela `alertas` foi criada
- Teste a função `verificarEstoqueBaixo()`

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique este README
2. Consulte os comentários no código
3. Teste as funcionalidades passo a passo

## 📄 Licença

Este projeto foi desenvolvido para fins educacionais e pode ser usado livremente.

---

**Desenvolvido com ❤️ para o TCC 2025**