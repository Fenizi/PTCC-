<?php
// Header - Sistema GERE TECH
// Cabeçalho das páginas internas
?>

<header class="main-header">
    <div class="header-left">
        <button id="sidebarToggle" class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="breadcrumb">
            <span class="breadcrumb-item">
                <?php 
                $page_titles = [
                    'dashboard.php' => 'Dashboard',
                    'clientes.php' => 'Clientes',
                    'produtos.php' => 'Produtos',
                    'vendas.php' => 'Vendas',
                    'relatorios.php' => 'Relatórios',
                    'configuracoes.php' => 'Configurações'
                ];
                
                $current_page = basename($_SERVER['PHP_SELF']);
                echo $page_titles[$current_page] ?? 'Sistema';
                ?>
            </span>
        </div>
    </div>
    
    <div class="header-right">
        <!-- Notificações -->
        <div class="notification-dropdown">
            <button class="notification-btn" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge">3</span>
            </button>
            
            <div class="notification-menu" id="notificationMenu">
                <div class="notification-header">
                    <h4>Notificações</h4>
                    <button onclick="markAllAsRead()" class="mark-read-btn">Marcar todas como lidas</button>
                </div>
                
                <div class="notification-list">
                    <div class="notification-item unread">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        <div class="notification-content">
                            <p>Produto "Mouse Logitech" com estoque baixo</p>
                            <small>2 horas atrás</small>
                        </div>
                    </div>
                    
                    <div class="notification-item unread">
                        <i class="fas fa-shopping-cart text-success"></i>
                        <div class="notification-content">
                            <p>Nova venda registrada - R$ 299,99</p>
                            <small>4 horas atrás</small>
                        </div>
                    </div>
                    
                    <div class="notification-item unread">
                        <i class="fas fa-user-plus text-info"></i>
                        <div class="notification-content">
                            <p>Novo cliente cadastrado: Maria Silva</p>
                            <small>1 dia atrás</small>
                        </div>
                    </div>
                </div>
                
                <div class="notification-footer">
                    <a href="#" class="view-all-btn">Ver todas as notificações</a>
                </div>
            </div>
        </div>
        
        <!-- Alternador de tema -->
        <button id="themeToggle" class="theme-toggle" onclick="toggleTheme()">
            <i class="fas fa-moon"></i>
        </button>
        
        <!-- Perfil do usuário -->
        <div class="user-dropdown">
            <button class="user-btn" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                    <small class="user-role">Administrador</small>
                </div>
                <i class="fas fa-chevron-down"></i>
            </button>
            
            <div class="user-menu" id="userMenu">
                <a href="configuracoes.php" class="user-menu-item">
                    <i class="fas fa-user-cog"></i>
                    Meu Perfil
                </a>
                <a href="configuracoes.php" class="user-menu-item">
                    <i class="fas fa-cog"></i>
                    Configurações
                </a>
                <div class="user-menu-divider"></div>
                <a href="../php/logout.php" class="user-menu-item text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Sair
                </a>
            </div>
        </div>
    </div>
</header>

<style>
.main-header {
    height: 70px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    position: sticky;
    top: 0;
    z-index: 999;
    margin-left: 250px;
    transition: margin-left 0.3s;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sidebar-toggle {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #666;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 5px;
    transition: all 0.3s;
}

.sidebar-toggle:hover {
    background: #f8f9fa;
    color: #333;
}

.breadcrumb {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Notificações */
.notification-dropdown {
    position: relative;
}

.notification-btn {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #666;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    position: relative;
    transition: all 0.3s;
}

.notification-btn:hover {
    background: #f8f9fa;
    color: #333;
}

.notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: #dc3545;
    color: white;
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    border-radius: 50%;
    min-width: 18px;
    text-align: center;
}

.notification-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    width: 350px;
    max-height: 400px;
    overflow-y: auto;
    display: none;
    z-index: 1000;
}

.notification-menu.show {
    display: block;
}

.notification-header {
    padding: 1rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h4 {
    margin: 0;
    font-size: 1.1rem;
}

.mark-read-btn {
    background: none;
    border: none;
    color: #667eea;
    font-size: 0.8rem;
    cursor: pointer;
}

.notification-item {
    padding: 1rem;
    border-bottom: 1px solid #f8f9fa;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    transition: background 0.3s;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread {
    background: #f0f8ff;
}

.notification-content p {
    margin: 0 0 0.5rem 0;
    font-size: 0.9rem;
}

.notification-content small {
    color: #666;
    font-size: 0.8rem;
}

.notification-footer {
    padding: 1rem;
    text-align: center;
    border-top: 1px solid #eee;
}

.view-all-btn {
    color: #667eea;
    text-decoration: none;
    font-size: 0.9rem;
}

/* Alternador de tema */
.theme-toggle {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #666;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.3s;
}

.theme-toggle:hover {
    background: #f8f9fa;
    color: #333;
}

/* Perfil do usuário */
.user-dropdown {
    position: relative;
}

.user-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s;
}

.user-btn:hover {
    background: #f8f9fa;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.user-info {
    text-align: left;
}

.user-name {
    display: block;
    font-weight: 600;
    color: #333;
}

.user-role {
    color: #666;
    font-size: 0.8rem;
}

.user-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    width: 200px;
    display: none;
    z-index: 1000;
}

.user-menu.show {
    display: block;
}

.user-menu-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    color: #333;
    text-decoration: none;
    transition: background 0.3s;
}

.user-menu-item:hover {
    background: #f8f9fa;
}

.user-menu-item.text-danger {
    color: #dc3545;
}

.user-menu-divider {
    height: 1px;
    background: #eee;
    margin: 0.5rem 0;
}

/* Responsivo */
@media (max-width: 768px) {
    .main-header {
        margin-left: 70px;
        padding: 0 1rem;
    }
    
    .user-info {
        display: none;
    }
    
    .notification-menu,
    .user-menu {
        width: 280px;
    }
}

/* Tema escuro */
body.dark-theme .main-header {
    background: #2d3748;
    color: white;
}

body.dark-theme .breadcrumb {
    color: white;
}

body.dark-theme .sidebar-toggle,
body.dark-theme .notification-btn,
body.dark-theme .theme-toggle {
    color: #cbd5e0;
}

body.dark-theme .sidebar-toggle:hover,
body.dark-theme .notification-btn:hover,
body.dark-theme .theme-toggle:hover {
    background: #4a5568;
    color: white;
}

body.dark-theme .user-btn:hover {
    background: #4a5568;
}

body.dark-theme .user-name {
    color: white;
}

body.dark-theme .notification-menu,
body.dark-theme .user-menu {
    background: #2d3748;
    color: white;
}

body.dark-theme .notification-item:hover,
body.dark-theme .user-menu-item:hover {
    background: #4a5568;
}
</style>

<script>
// Função para alternar notificações
function toggleNotifications() {
    const menu = document.getElementById('notificationMenu');
    menu.classList.toggle('show');
    
    // Fechar menu do usuário se estiver aberto
    document.getElementById('userMenu').classList.remove('show');
}

// Função para alternar menu do usuário
function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    menu.classList.toggle('show');
    
    // Fechar menu de notificações se estiver aberto
    document.getElementById('notificationMenu').classList.remove('show');
}

// Função para marcar todas as notificações como lidas
function markAllAsRead() {
    const unreadItems = document.querySelectorAll('.notification-item.unread');
    unreadItems.forEach(item => {
        item.classList.remove('unread');
    });
    
    const badge = document.getElementById('notificationBadge');
    badge.style.display = 'none';
    
    showNotification('Todas as notificações foram marcadas como lidas', 'success');
}

// Fechar menus ao clicar fora
document.addEventListener('click', function(e) {
    if (!e.target.closest('.notification-dropdown')) {
        document.getElementById('notificationMenu').classList.remove('show');
    }
    
    if (!e.target.closest('.user-dropdown')) {
        document.getElementById('userMenu').classList.remove('show');
    }
});

// Toggle da sidebar
document.getElementById('sidebarToggle').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const header = document.querySelector('.main-header');
    
    sidebar.classList.toggle('collapsed');
    
    if (sidebar.classList.contains('collapsed')) {
        mainContent.style.marginLeft = '70px';
        header.style.marginLeft = '70px';
    } else {
        mainContent.style.marginLeft = '250px';
        header.style.marginLeft = '250px';
    }
});
</script>