<?php
// Sidebar - Sistema GERE TECH
// Barra lateral de navegação
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">GT</div>
        <div class="sidebar-title">GERE TECH</div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="clientes.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-users"></i></div>
                    <span class="nav-text">Clientes</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="produtos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'produtos.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-box"></i></div>
                    <span class="nav-text">Produtos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="vendas.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'vendas.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-shopping-cart"></i></div>
                    <span class="nav-text">Vendas</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="relatorios.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-chart-bar"></i></div>
                    <span class="nav-text">Relatórios</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="configuracoes.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'configuracoes.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-cog"></i></div>
                    <span class="nav-text">Configurações</span>
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