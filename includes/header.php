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
                    <a href="#" class="view-all-btn" onclick="showAllNotifications()">Ver todas as notificações</a>
                </div>
            </div>
        </div>
        
        
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
    background: var(--bg-primary);
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    position: static;
    top: 0;
    z-index: 999;
    transition: background-color var(--transition-normal), box-shadow var(--transition-normal);
    border-bottom: 1px solid var(--border-color);
}



@media (max-width: 768px) {
    .main-header {
        margin-left: 0;
    }
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
    color: var(--text-secondary);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 5px;
    transition: all var(--transition-fast);
}

.sidebar-toggle:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.breadcrumb {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
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
    color: var(--text-secondary);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    position: relative;
    transition: all var(--transition-fast);
}

.notification-btn:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
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
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    box-shadow: var(--shadow-lg);
    min-width: 250px;
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
    padding: 1rem;
    margin: 0;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.9rem;
    color: var(--text-secondary);
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
    transition: all var(--transition-fast);
}

.user-btn:hover {
    background: var(--bg-tertiary);
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
    color: var(--text-primary);
}

.user-role {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.user-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    box-shadow: var(--shadow-lg);
    min-width: 250px;
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

/* Modal de Notificações */
.modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal.show {
    opacity: 1;
}

.modal-content {
    background-color: var(--bg-primary);
    margin: 5% auto;
    padding: 0;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    transform: translateY(-20px);
    transition: transform 0.3s ease;
}

.modal.show .modal-content {
    transform: translateY(0);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--bg-secondary);
}

.modal-header h3 {
    margin: 0;
    color: var(--text-primary);
    font-size: 1.25rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-secondary);
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.modal-body {
    padding: 0;
    max-height: 50vh;
    overflow-y: auto;
}

.notification-list-full {
    padding: 0;
}

.notification-list-full .notification-item {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    transition: background 0.2s ease;
}

.notification-list-full .notification-item:hover {
    background: var(--bg-tertiary);
}

.notification-list-full .notification-item.unread {
    background: rgba(102, 126, 234, 0.1);
    border-left: 3px solid #667eea;
}

.notification-list-full .notification-item:last-child {
    border-bottom: none;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    background: var(--bg-secondary);
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.btn-secondary {
    background: var(--bg-tertiary);
    color: var(--text-secondary);
}

.btn-secondary:hover {
    background: var(--border-color);
    color: var(--text-primary);
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a6fd8;
}

/* Cores dos ícones */
.text-warning { color: #ffc107; }
.text-success { color: #28a745; }
.text-info { color: #17a2b8; }
.text-primary { color: #667eea; }
.text-danger { color: #dc3545; }

/* Os estilos do tema escuro agora são gerenciados pelas variáveis CSS */
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

// Função para mostrar todas as notificações
function showAllNotifications() {
    // Fechar o dropdown de notificações
    document.getElementById('notificationMenu').classList.remove('show');
    
    // Criar o modal se não existir
    let modal = document.getElementById('allNotificationsModal');
    if (!modal) {
        modal = createAllNotificationsModal();
        document.body.appendChild(modal);
    }
    
    // Mostrar o modal
    modal.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

// Função para criar o modal de todas as notificações
function createAllNotificationsModal() {
    const modal = document.createElement('div');
    modal.id = 'allNotificationsModal';
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Todas as Notificações</h3>
                <button class="modal-close" onclick="closeAllNotificationsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="notification-list-full">
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
                    <div class="notification-item">
                        <i class="fas fa-box text-primary"></i>
                        <div class="notification-content">
                            <p>Produto "Teclado Mecânico" adicionado ao estoque</p>
                            <small>2 dias atrás</small>
                        </div>
                    </div>
                    <div class="notification-item">
                        <i class="fas fa-chart-line text-success"></i>
                        <div class="notification-content">
                            <p>Relatório mensal de vendas gerado</p>
                            <small>3 dias atrás</small>
                        </div>
                    </div>
                    <div class="notification-item">
                        <i class="fas fa-user-check text-info"></i>
                        <div class="notification-content">
                            <p>Cliente "João Santos" atualizou seus dados</p>
                            <small>1 semana atrás</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeAllNotificationsModal()">Fechar</button>
                <button class="btn btn-primary" onclick="markAllAsRead(); closeAllNotificationsModal();">Marcar Todas como Lidas</button>
            </div>
        </div>
    `;
    return modal;
}

// Função para fechar o modal de todas as notificações
function closeAllNotificationsModal() {
    const modal = document.getElementById('allNotificationsModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
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