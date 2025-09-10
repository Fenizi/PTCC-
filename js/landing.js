// JavaScript para a página inicial (Landing Page)

// Controle do menu mobile e sidebar
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navMenuOverlay = document.querySelector('.nav-menu-overlay');
    const sidebar = document.querySelector('.index-sidebar');
    const sidebarOverlay = document.querySelector('.index-sidebar-overlay');
    const sidebarClose = document.querySelector('.sidebar-close');
    const body = document.body;
    
    // Criar o botão de menu toggle se não existir
    if (!menuToggle) {
        const headerContent = document.querySelector('.header-content');
        if (headerContent) {
            const toggleButton = document.createElement('button');
            toggleButton.className = 'menu-toggle';
            toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
            toggleButton.setAttribute('aria-label', 'Toggle menu');
            headerContent.appendChild(toggleButton);
        }
    }
    
    // Criar overlay se não existir
    if (!navMenuOverlay && navMenu) {
        const overlay = document.createElement('div');
        overlay.className = 'nav-menu-overlay';
        document.body.appendChild(overlay);
    }
    
    // Função para abrir/fechar menu/sidebar baseado no tamanho da tela
    function toggleMenu() {
        const currentMenuToggle = document.querySelector('.menu-toggle');
        const currentNavMenu = document.querySelector('.nav-menu');
        const currentOverlay = document.querySelector('.nav-menu-overlay');
        const currentSidebar = document.querySelector('.index-sidebar');
        const currentSidebarOverlay = document.querySelector('.index-sidebar-overlay');
        
        // Em dispositivos móveis (< 768px), usar sidebar
        if (window.innerWidth < 768) {
            if (currentSidebar && currentSidebarOverlay && currentMenuToggle) {
                const isActive = currentSidebar.classList.contains('active');
                
                if (isActive) {
                    // Fechar sidebar
                    currentSidebar.classList.remove('active');
                    currentSidebarOverlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                    currentMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    currentMenuToggle.setAttribute('aria-expanded', 'false');
                } else {
                    // Abrir sidebar
                    currentSidebar.classList.add('active');
                    currentSidebarOverlay.classList.add('active');
                    body.classList.add('sidebar-open');
                    currentMenuToggle.innerHTML = '<i class="fas fa-times"></i>';
                    currentMenuToggle.setAttribute('aria-expanded', 'true');
                }
            }
        } else {
            // Em telas maiores, usar menu normal
            if (currentNavMenu && currentMenuToggle) {
                const isActive = currentNavMenu.classList.contains('active');
                
                if (isActive) {
                    // Fechar menu
                    currentNavMenu.classList.remove('active');
                    if (currentOverlay) currentOverlay.classList.remove('active');
                    body.classList.remove('menu-open');
                    currentMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    currentMenuToggle.setAttribute('aria-expanded', 'false');
                } else {
                    // Abrir menu
                    currentNavMenu.classList.add('active');
                    if (currentOverlay) currentOverlay.classList.add('active');
                    body.classList.add('menu-open');
                    currentMenuToggle.innerHTML = '<i class="fas fa-times"></i>';
                    currentMenuToggle.setAttribute('aria-expanded', 'true');
                }
            }
        }
    }
    
    // Função para fechar menu e sidebar
    function closeMenuAndSidebar() {
        const currentNavMenu = document.querySelector('.nav-menu');
        const currentOverlay = document.querySelector('.nav-menu-overlay');
        const currentSidebar = document.querySelector('.index-sidebar');
        const currentSidebarOverlay = document.querySelector('.index-sidebar-overlay');
        const currentMenuToggle = document.querySelector('.menu-toggle');
        
        // Fechar menu
        if (currentNavMenu && currentNavMenu.classList.contains('active')) {
            currentNavMenu.classList.remove('active');
            if (currentOverlay) currentOverlay.classList.remove('active');
            body.classList.remove('menu-open');
        }
        
        // Fechar sidebar
        if (currentSidebar && currentSidebar.classList.contains('active')) {
            currentSidebar.classList.remove('active');
            if (currentSidebarOverlay) currentSidebarOverlay.classList.remove('active');
            body.classList.remove('sidebar-open');
        }
        
        // Resetar ícone do toggle
        if (currentMenuToggle) {
            currentMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            currentMenuToggle.setAttribute('aria-expanded', 'false');
        }
    }
    
    // Função para fechar sidebar
    function closeSidebar() {
        if (sidebar && sidebarOverlay) {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            body.classList.remove('sidebar-open');
        }
    }
    
    // Event listeners
    document.addEventListener('click', function(e) {
        if (e.target.closest('.menu-toggle')) {
            e.preventDefault();
            toggleMenu();
        }
        
        // Controle da sidebar
        if (e.target.closest('.sidebar-close')) {
            e.preventDefault();
            closeSidebar();
        }
        
        // Fechar menu ao clicar no overlay
        if (e.target.classList.contains('nav-menu-overlay')) {
            toggleMenu();
        }
        
        // Fechar sidebar ao clicar no overlay
        if (e.target.classList.contains('index-sidebar-overlay')) {
            closeSidebar();
        }
        
        // Fechar menu ao clicar em um link do menu
        if (e.target.closest('.nav-menu a')) {
            const navMenu = document.querySelector('.nav-menu');
            if (navMenu && navMenu.classList.contains('active')) {
                setTimeout(() => closeMenuAndSidebar(), 150);
            }
        }
        
        // Fechar sidebar ao clicar em um link da sidebar
        if (e.target.closest('.index-sidebar .nav-link')) {
            setTimeout(() => closeMenuAndSidebar(), 150);
        }
    });
    
    // Fechar menu com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const navMenu = document.querySelector('.nav-menu');
            if (navMenu && navMenu.classList.contains('active')) {
                toggleMenu();
            }
        }
    });
    
    // Smooth scroll para links internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#' || href === '#top') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const headerHeight = document.querySelector('.header')?.offsetHeight || 70;
                const targetPosition = target.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Header scroll effect
    let lastScrollTop = 0;
    const header = document.querySelector('.header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Adicionar classe quando rolar para baixo
            if (scrollTop > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScrollTop = scrollTop;
        });
    }
    
    // Animações de entrada para elementos
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observar elementos para animação
    document.querySelectorAll('.feature-item, .card, .contact-item').forEach(el => {
        observer.observe(el);
    });
    
    // Redimensionamento da janela
    window.addEventListener('resize', function() {
        // Fechar menu/sidebar ao redimensionar
        closeMenuAndSidebar();
    });
});

// CSS adicional para animações
const additionalStyles = `
.menu-open {
    overflow: hidden;
}

.header.scrolled {
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
}

.animate-in {
    animation: slideInUp 0.6s ease-out forwards;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.feature-item,
.card,
.contact-item {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease-out;
}

.menu-toggle i {
    transition: all 0.3s ease;
}

.nav-menu a {
    position: relative;
    overflow: hidden;
}

.nav-menu a::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
    transition: left 0.5s;
}

.nav-menu a:hover::before {
    left: 100%;
}
`;

// Adicionar estilos adicionais
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);