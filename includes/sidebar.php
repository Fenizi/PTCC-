<?php
// Sidebar - Sistema GERE TECH
// Barra lateral de navegação
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">GT</div>
        <h3>GERE TECH</h3>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li>
                <a href="clientes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
            </li>
            
            <li>
                <a href="produtos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'produtos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Produtos</span>
                </a>
            </li>
            
            <li>
                <a href="vendas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'vendas.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Vendas</span>
                </a>
            </li>
            
            <li>
                <a href="relatorios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Relatórios</span>
                </a>
            </li>
            
            <li>
                <a href="configuracoes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'configuracoes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="../php/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Sair</span>
        </a>
    </div>
</div>

<style>
.sidebar {
    width: 250px;
    height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    transition: all 0.3s;
}

.sidebar-header {
    padding: 2rem 1rem;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header .logo {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 1.2rem;
    opacity: 0.9;
}

.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav li {
    margin-bottom: 0.5rem;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.sidebar-nav a:hover,
.sidebar-nav a.active {
    background: rgba(255,255,255,0.1);
    color: white;
    border-left-color: white;
}

.sidebar-nav a i {
    margin-right: 1rem;
    width: 20px;
    text-align: center;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.logout-btn {
    display: flex;
    align-items: center;
    padding: 1rem;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s;
    border-radius: 8px;
}

.logout-btn:hover {
    background: rgba(255,255,255,0.1);
    color: white;
}

.logout-btn i {
    margin-right: 1rem;
}

/* Responsivo */
@media (max-width: 768px) {
    .sidebar {
        width: 70px;
        overflow: hidden;
    }
    
    .sidebar:hover {
        width: 250px;
    }
    
    .sidebar-header h3,
    .sidebar-nav span,
    .logout-btn span {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .sidebar:hover .sidebar-header h3,
    .sidebar:hover .sidebar-nav span,
    .sidebar:hover .logout-btn span {
        opacity: 1;
    }
}
</style>