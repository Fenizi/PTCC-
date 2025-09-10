// JavaScript Principal - Sistema GERE TECH
// Funcionalidades gerais do sistema

// Configuração global
const CONFIG = {
    sidebar: {
        key: 'ptcc-sidebar-state',
        breakpoint: 992
    },
    animation: {
        duration: 300,
        easing: 'ease-in-out'
    }
};

// Estado global da aplicação
const AppState = {
    sidebarOpen: false,
    isMobile: false,
    notifications: [],
    modals: new Map()
};

// Utilitários
const Utils = {
    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Throttle function
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    // Verificar se é dispositivo móvel
    isMobileDevice() {
        return window.innerWidth < CONFIG.sidebar.breakpoint;
    },

    // Animar elemento
    animate(element, properties, duration = CONFIG.animation.duration) {
        return new Promise(resolve => {
            const startTime = performance.now();
            const startValues = {};
            
            // Capturar valores iniciais
            Object.keys(properties).forEach(prop => {
                startValues[prop] = parseFloat(getComputedStyle(element)[prop]) || 0;
            });

            function animate(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Aplicar easing
                const easedProgress = 0.5 * (1 - Math.cos(Math.PI * progress));
                
                Object.keys(properties).forEach(prop => {
                    const startValue = startValues[prop];
                    const endValue = properties[prop];
                    const currentValue = startValue + (endValue - startValue) * easedProgress;
                    element.style[prop] = currentValue + (prop.includes('opacity') ? '' : 'px');
                });
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    resolve();
                }
            }
            
            requestAnimationFrame(animate);
        });
    },

    // Formatação de moeda
    formatCurrency(value, currency = 'BRL') {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2
        }).format(value);
    },

    // Formatação de data
    formatDate(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        };
        return new Intl.DateTimeFormat('pt-BR', { ...defaultOptions, ...options }).format(new Date(date));
    },

    // Validação de email
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Validação de CPF
    isValidCPF(cpf) {
        cpf = cpf.replace(/[^\d]/g, '');
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        
        let sum = 0;
        for (let i = 0; i < 9; i++) {
            sum += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.charAt(9))) return false;
        
        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(cpf.charAt(i)) * (11 - i);
        }
        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        return remainder === parseInt(cpf.charAt(10));
    },

    // Máscara para CPF
    maskCPF(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})/, '$1-$2')
            .replace(/(-\d{2})\d+?$/, '$1');
    },

    // Máscara para telefone
    maskPhone(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4})(\d)/, '$1-$2')
            .replace(/(\d{4})-\d+?$/, '$1');
    },

    // Máscara para CEP
    maskCEP(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .replace(/(-\d{3})\d+?$/, '$1');
    }
};

// Aguardar carregamento do DOM
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Inicialização principal da aplicação
function initializeApp() {
    // Registrar Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
            .then(registration => console.log('SW registrado:', registration))
            .catch(error => console.log('Erro no SW:', error));
    }
    
    // Detectar dispositivo móvel
    AppState.isMobile = Utils.isMobileDevice();
    
    // Inicializar componentes
    initializeForms();
    initializeCharts();
    initializeMobileMenu();
    
    console.log('PTCC App initialized successfully');
}

// Gerenciamento de Modais - REMOVIDO
// Usando apenas ModalManager para evitar conflitos

// Gerenciamento de Formulários
function initializeForms() {
    // Validação de formulários
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Máscaras de input
    applyInputMasks();
}

// Validação de formulário
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'Este campo é obrigatório');
            isValid = false;
        } else {
            clearFieldError(field);
        }
        
        // Validação específica por tipo
        if (field.type === 'email' && field.value) {
            if (!isValidEmail(field.value)) {
                showFieldError(field, 'Email inválido');
                isValid = false;
            }
        }
        
        if (field.name === 'cpf' && field.value) {
            if (!isValidCPF(field.value)) {
                showFieldError(field, 'CPF inválido');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

// Mostrar erro no campo
function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    field.parentNode.appendChild(errorDiv);
}

// Limpar erro do campo
function clearFieldError(field) {
    field.classList.remove('error');
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Aplicar máscaras de input
function applyInputMasks() {
    // Máscara de CPF
    document.querySelectorAll('input[name="cpf"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = maskCPF(this.value);
        });
    });
    
    // Máscara de telefone
    document.querySelectorAll('input[name="telefone"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = maskPhone(this.value);
        });
    });
    
    // Máscara de valor monetário
    document.querySelectorAll('input[name="valor"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = maskMoney(this.value);
        });
    });
}

// Máscara de CPF
function maskCPF(value) {
    return value
        .replace(/\D/g, '')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})/, '$1-$2')
        .replace(/(-\d{2})\d+?$/, '$1');
}

// Máscara de telefone
function maskPhone(value) {
    return value
        .replace(/\D/g, '')
        .replace(/(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{4,5})(\d{4})/, '$1-$2');
}

// Máscara de dinheiro
function maskMoney(value) {
    return value
        .replace(/\D/g, '')
        .replace(/(\d)(\d{2})$/, '$1,$2')
        .replace(/(?=\d{3})(\d)(\d{2}\,\d{2})$/, '$1.$2')
        .replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
}

// Validação de email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validação de CPF
function isValidCPF(cpf) {
    cpf = cpf.replace(/[^\d]/g, '');
    
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
        return false;
    }
    
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += parseInt(cpf.charAt(i)) * (10 - i);
    }
    
    let remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.charAt(9))) return false;
    
    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(cpf.charAt(i)) * (11 - i);
    }
    
    remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    
    return remainder === parseInt(cpf.charAt(10));
}

// Inicializar gráficos
function initializeCharts() {
    // Verificar se Chart.js está disponível
    if (typeof Chart !== 'undefined') {
        createSalesChart();
        createProductsChart();
    }
}

// Gráfico de vendas
function createSalesChart() {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Vendas',
                data: [12, 19, 3, 5, 2, 3],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Gráfico de produtos
function createProductsChart() {
    const ctx = document.getElementById('productsChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Notebooks', 'Mouses', 'Teclados', 'Outros'],
            datasets: [{
                data: [30, 25, 20, 25],
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#f5576c'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Novo Sistema de Gerenciamento de Temas




// Gerenciador de Sidebar
const SidebarManager = {
    init() {
        this.sidebar = document.querySelector('.sidebar');
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.mainContent = document.querySelector('.main-content') || document.querySelector('.page-container');
        this.header = document.querySelector('.main-header') || document.querySelector('.header');
        
        if (this.sidebar && this.sidebarToggle) {
            this.setupEventListeners();
            this.loadSavedState();
            this.handleResize();
        }
    },

    setupEventListeners() {
        // Toggle sidebar
        this.sidebarToggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggle();
        });

        // Fechar sidebar ao clicar no overlay (mobile)
        document.addEventListener('click', (e) => {
            if (AppState.isMobile && AppState.sidebarOpen) {
                if (!this.sidebar.contains(e.target) && !this.sidebarToggle.contains(e.target)) {
                    this.close();
                }
            }
        });

        // Fechar sidebar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && AppState.sidebarOpen && AppState.isMobile) {
                this.close();
            }
        });

        // Gerenciar redimensionamento
        window.addEventListener('resize', Utils.debounce(() => {
            this.handleResize();
        }, 250));
    },

    loadSavedState() {
        const savedState = localStorage.getItem(CONFIG.sidebar.key);
        if (savedState && !AppState.isMobile) {
            AppState.sidebarOpen = savedState === 'open';
            this.updateSidebarState(false);
        }
    },

    handleResize() {
        const wasMobile = AppState.isMobile;
        AppState.isMobile = Utils.isMobileDevice();
        
        if (wasMobile !== AppState.isMobile) {
            if (AppState.isMobile) {
                // Mudou para mobile
                this.close(false);
                this.removeOverlay();
            } else {
                // Mudou para desktop
                this.loadSavedState();
                this.removeOverlay();
            }
        }
    },

    toggle() {
        if (AppState.sidebarOpen) {
            this.close();
        } else {
            this.open();
        }
    },

    open(animate = true) {
        AppState.sidebarOpen = true;
        this.updateSidebarState(animate);
        
        if (AppState.isMobile) {
            this.createOverlay();
        } else {
            localStorage.setItem(CONFIG.sidebar.key, 'open');
        }
        
        this.dispatchSidebarEvent('opened');
    },

    close(animate = true) {
        AppState.sidebarOpen = false;
        this.updateSidebarState(animate);
        
        if (AppState.isMobile) {
            this.removeOverlay();
        } else {
            localStorage.setItem(CONFIG.sidebar.key, 'closed');
        }
        
        this.dispatchSidebarEvent('closed');
    },

    updateSidebarState(animate = true) {
        if (!this.sidebar) return;
        
        if (animate) {
            this.sidebar.style.transition = `transform ${CONFIG.animation.duration}ms ${CONFIG.animation.easing}`;
            if (this.header) {
                this.header.style.transition = 'margin-left 0.3s ease';
            }
        }
        
        if (AppState.sidebarOpen) {
            this.sidebar.classList.add('active');
            this.sidebar.setAttribute('aria-hidden', 'false');
            if (this.mainContent && !AppState.isMobile) {
                this.mainContent.classList.remove('sidebar-collapsed');
            }
        } else {
            this.sidebar.classList.remove('active');
            this.sidebar.setAttribute('aria-hidden', 'true');
            if (this.mainContent && !AppState.isMobile) {
                this.mainContent.classList.add('sidebar-collapsed');
            }
        }
        
        // Atualizar ícone do toggle
        this.updateToggleIcon();
        
        // Remover transição após animação
        if (animate) {
            setTimeout(() => {
                this.sidebar.style.transition = '';
                if (this.header) {
                    this.header.style.transition = '';
                }
            }, CONFIG.animation.duration);
        }
    },

    updateToggleIcon() {
        if (!this.sidebarToggle) return;
        
        const icon = this.sidebarToggle.querySelector('i');
        if (icon) {
            if (AppState.isMobile) {
                icon.className = 'fas fa-bars';
            } else {
                icon.className = AppState.sidebarOpen ? 'fas fa-chevron-left' : 'fas fa-chevron-right';
            }
        }
    },

    createOverlay() {
        if (document.querySelector('.sidebar-overlay')) return;
        
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.addEventListener('click', () => this.close());
        
        document.body.appendChild(overlay);
        
        // Animar entrada
        requestAnimationFrame(() => {
            overlay.classList.add('active');
        });
    },

    removeOverlay() {
        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
            overlay.classList.remove('active');
            setTimeout(() => {
                overlay.remove();
            }, CONFIG.animation.duration);
        }
    },

    dispatchSidebarEvent(action) {
        const event = new CustomEvent('sidebarChanged', {
            detail: { action, isOpen: AppState.sidebarOpen, isMobile: AppState.isMobile }
        });
        document.dispatchEvent(event);
    },

    isOpen() {
        return AppState.sidebarOpen;
    }
};

// Manter compatibilidade com código legado
function initializeMobileMenu() {
    SidebarManager.init();
}

// Sistema de Notificações
const NotificationManager = {
    container: null,
    notifications: new Map(),
    counter: 0,

    init() {
        this.createContainer();
        this.setupStyles();
    },

    createContainer() {
        if (this.container) return;
        
        this.container = document.createElement('div');
        this.container.className = 'notification-container';
        this.container.setAttribute('aria-live', 'polite');
        this.container.setAttribute('aria-atomic', 'false');
        document.body.appendChild(this.container);
    },

    setupStyles() {
        if (document.getElementById('notification-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                pointer-events: none;
            }
            
            .notification {
                background: var(--card-bg);
                color: var(--text-color);
                padding: 16px 20px;
                margin-bottom: 12px;
                border-radius: 8px;
                box-shadow: var(--shadow-lg);
                border-left: 4px solid var(--primary-color);
                min-width: 300px;
                max-width: 400px;
                pointer-events: auto;
                transform: translateX(100%);
                opacity: 0;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                display: flex;
                align-items: flex-start;
                gap: 12px;
            }
            
            .notification.show {
                transform: translateX(0);
                opacity: 1;
            }
            
            .notification.success {
                border-left-color: var(--success-color);
            }
            
            .notification.error {
                border-left-color: var(--danger-color);
            }
            
            .notification.warning {
                border-left-color: var(--warning-color);
            }
            
            .notification.info {
                border-left-color: var(--info-color);
            }
            
            .notification-icon {
                flex-shrink: 0;
                width: 20px;
                height: 20px;
                margin-top: 2px;
            }
            
            .notification-content {
                flex: 1;
            }
            
            .notification-title {
                font-weight: 600;
                margin-bottom: 4px;
                font-size: 14px;
            }
            
            .notification-message {
                font-size: 13px;
                line-height: 1.4;
                opacity: 0.9;
            }
            
            .notification-close {
                position: absolute;
                top: 8px;
                right: 8px;
                background: none;
                border: none;
                color: var(--text-color);
                cursor: pointer;
                padding: 4px;
                border-radius: 4px;
                opacity: 0.6;
                transition: opacity 0.2s;
            }
            
            .notification-close:hover {
                opacity: 1;
            }
            
            .notification-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 2px;
                background: var(--primary-color);
                border-radius: 0 0 8px 8px;
                transition: width linear;
            }
            
            @media (max-width: 480px) {
                .notification-container {
                    top: 10px;
                    right: 10px;
                    left: 10px;
                }
                
                .notification {
                    min-width: auto;
                    max-width: none;
                }
            }
        `;
        document.head.appendChild(styles);
    },

    show(options) {
        if (typeof options === 'string') {
            options = { message: options };
        }
        
        const config = {
            type: 'info',
            title: '',
            message: '',
            duration: 5000,
            closable: true,
            progress: true,
            ...options
        };
        
        const id = ++this.counter;
        const notification = this.createNotification(id, config);
        
        this.container.appendChild(notification);
        this.notifications.set(id, { element: notification, config });
        
        // Animar entrada
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });
        
        // Auto-remover se tiver duração
        if (config.duration > 0) {
            this.scheduleRemoval(id, config.duration);
        }
        
        return id;
    },

    createNotification(id, config) {
        const notification = document.createElement('div');
        notification.className = `notification ${config.type}`;
        notification.setAttribute('role', 'alert');
        notification.setAttribute('data-id', id);
        
        const iconMap = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        
        let html = `
            <div class="notification-icon">
                <i class="${iconMap[config.type] || iconMap.info}"></i>
            </div>
            <div class="notification-content">
        `;
        
        if (config.title) {
            html += `<div class="notification-title">${Utils.escapeHtml(config.title)}</div>`;
        }
        
        html += `<div class="notification-message">${Utils.escapeHtml(config.message)}</div>`;
        html += `</div>`;
        
        if (config.closable) {
            html += `
                <button class="notification-close" type="button" aria-label="Fechar notificação">
                    <i class="fas fa-times"></i>
                </button>
            `;
        }
        
        if (config.progress && config.duration > 0) {
            html += `<div class="notification-progress"></div>`;
        }
        
        notification.innerHTML = html;
        
        // Event listeners
        if (config.closable) {
            const closeBtn = notification.querySelector('.notification-close');
            closeBtn.addEventListener('click', () => this.remove(id));
        }
        
        return notification;
    },

    scheduleRemoval(id, duration) {
        const notification = this.notifications.get(id);
        if (!notification) return;
        
        const progressBar = notification.element.querySelector('.notification-progress');
        if (progressBar) {
            progressBar.style.width = '100%';
            progressBar.style.transitionDuration = `${duration}ms`;
            
            requestAnimationFrame(() => {
                progressBar.style.width = '0%';
            });
        }
        
        setTimeout(() => {
            this.remove(id);
        }, duration);
    },

    remove(id) {
        const notification = this.notifications.get(id);
        if (!notification) return;
        
        notification.element.classList.remove('show');
        
        setTimeout(() => {
            if (notification.element.parentNode) {
                notification.element.parentNode.removeChild(notification.element);
            }
            this.notifications.delete(id);
        }, 300);
    },

    clear() {
        this.notifications.forEach((_, id) => this.remove(id));
    },

    // Métodos de conveniência
    success(message, options = {}) {
        return this.show({ ...options, message, type: 'success' });
    },

    error(message, options = {}) {
        return this.show({ ...options, message, type: 'error', duration: 0 });
    },

    warning(message, options = {}) {
        return this.show({ ...options, message, type: 'warning' });
    },

    info(message, options = {}) {
        return this.show({ ...options, message, type: 'info' });
    }
};

// Manter compatibilidade com código legado
function showNotification(message, type = 'info', duration = 5000) {
    return NotificationManager.show({ message, type, duration });
}

// Gerenciador de Formulários
const FormManager = {
    forms: new Map(),
    validators: new Map(),

    init() {
        this.setupGlobalValidators();
        this.initializeForms();
        this.setupMasks();
    },

    setupGlobalValidators() {
        // Validadores básicos
        this.validators.set('required', (value) => {
            return value && value.toString().trim().length > 0;
        });

        this.validators.set('email', (value) => {
            return Utils.validateEmail(value);
        });

        this.validators.set('cpf', (value) => {
            return Utils.validateCPF(value);
        });

        this.validators.set('phone', (value) => {
            return Utils.validatePhone(value);
        });

        this.validators.set('cep', (value) => {
            return Utils.validateCEP(value);
        });

        this.validators.set('min', (value, min) => {
            return value && value.toString().length >= parseInt(min);
        });

        this.validators.set('max', (value, max) => {
            return value && value.toString().length <= parseInt(max);
        });

        this.validators.set('number', (value) => {
            return !isNaN(value) && !isNaN(parseFloat(value));
        });

        this.validators.set('url', (value) => {
            try {
                new URL(value);
                return true;
            } catch {
                return false;
            }
        });
    },

    initializeForms() {
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => this.registerForm(form));
    },

    registerForm(form) {
        if (this.forms.has(form)) return;

        const config = {
            realTimeValidation: form.dataset.realtime !== 'false',
            showErrors: form.dataset.showErrors !== 'false',
            preventSubmit: form.dataset.preventSubmit !== 'false'
        };

        this.forms.set(form, config);

        // Event listeners
        if (config.realTimeValidation) {
            form.addEventListener('input', Utils.debounce((e) => {
                if (e.target.matches('[data-validate]')) {
                    this.validateField(e.target);
                }
            }, 300));

            form.addEventListener('blur', (e) => {
                if (e.target.matches('[data-validate]')) {
                    this.validateField(e.target);
                }
            }, true);
        }

        form.addEventListener('submit', (e) => {
            if (config.preventSubmit && !this.validateForm(form)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    },

    validateField(field) {
        const rules = field.dataset.validate.split('|');
        const value = field.value;
        let isValid = true;
        let errorMessage = '';

        for (const rule of rules) {
            const [validatorName, ...params] = rule.split(':');
            const validator = this.validators.get(validatorName);

            if (validator && !validator(value, ...params)) {
                isValid = false;
                errorMessage = this.getErrorMessage(validatorName, field, params);
                break;
            }
        }

        this.updateFieldState(field, isValid, errorMessage);
        return isValid;
    },

    validateForm(form) {
        const fields = form.querySelectorAll('[data-validate]');
        let isValid = true;

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    },

    updateFieldState(field, isValid, errorMessage) {
        const formGroup = field.closest('.form-group') || field.closest('.form-col');
        const errorElement = formGroup?.querySelector('.field-error');

        // Remover classes anteriores
        field.classList.remove('is-valid', 'is-invalid');
        formGroup?.classList.remove('has-error', 'has-success');

        if (field.value.trim()) {
            if (isValid) {
                field.classList.add('is-valid');
                formGroup?.classList.add('has-success');
            } else {
                field.classList.add('is-invalid');
                formGroup?.classList.add('has-error');
            }
        }

        // Mostrar/ocultar mensagem de erro
        if (errorElement) {
            if (!isValid && errorMessage) {
                errorElement.textContent = errorMessage;
                errorElement.style.display = 'block';
            } else {
                errorElement.style.display = 'none';
            }
        } else if (!isValid && errorMessage) {
            this.createErrorElement(formGroup || field.parentNode, errorMessage);
        }
    },

    createErrorElement(container, message) {
        const existing = container.querySelector('.field-error');
        if (existing) {
            existing.textContent = message;
            existing.style.display = 'block';
            return;
        }

        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        errorElement.style.cssText = `
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        `;
        container.appendChild(errorElement);
    },

    getErrorMessage(validator, field, params) {
        const fieldName = field.dataset.fieldName || field.name || 'Campo';
        
        const messages = {
            required: `${fieldName} é obrigatório`,
            email: 'Digite um email válido',
            cpf: 'Digite um CPF válido',
            phone: 'Digite um telefone válido',
            cep: 'Digite um CEP válido',
            min: `${fieldName} deve ter pelo menos ${params[0]} caracteres`,
            max: `${fieldName} deve ter no máximo ${params[0]} caracteres`,
            number: `${fieldName} deve ser um número válido`,
            url: 'Digite uma URL válida'
        };

        return messages[validator] || `${fieldName} é inválido`;
    },

    setupMasks() {
        // Máscara para CPF
        document.addEventListener('input', (e) => {
            if (e.target.matches('[data-mask="cpf"]')) {
                e.target.value = Utils.maskCPF(e.target.value);
            }
        });

        // Máscara para telefone
        document.addEventListener('input', (e) => {
            if (e.target.matches('[data-mask="phone"]')) {
                e.target.value = Utils.maskPhone(e.target.value);
            }
        });

        // Máscara para CEP
        document.addEventListener('input', (e) => {
            if (e.target.matches('[data-mask="cep"]')) {
                e.target.value = Utils.maskCEP(e.target.value);
            }
        });

        // Máscara para moeda
        document.addEventListener('input', (e) => {
            if (e.target.matches('[data-mask="currency"]')) {
                const value = e.target.value.replace(/\D/g, '');
                const numericValue = parseFloat(value) / 100;
                e.target.value = Utils.formatCurrency(numericValue);
            }
        });
    },

    addValidator(name, validator) {
        this.validators.set(name, validator);
    },

    removeValidator(name) {
        this.validators.delete(name);
    },

    clearForm(form) {
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            field.value = '';
            field.classList.remove('is-valid', 'is-invalid');
            
            const formGroup = field.closest('.form-group') || field.closest('.form-col');
            formGroup?.classList.remove('has-error', 'has-success');
            
            const errorElement = formGroup?.querySelector('.field-error');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        });
    },

    getFormData(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (const [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        
        return data;
    }
};

// Gerenciador de Tabelas
const TableManager = {
    tables: new Map(),

    init() {
        this.initializeTables();
    },

    initializeTables() {
        const tables = document.querySelectorAll('table[data-sortable], table[data-searchable]');
        tables.forEach(table => this.registerTable(table));
    },

    registerTable(table) {
        if (this.tables.has(table)) return;

        const config = {
            sortable: table.dataset.sortable !== 'false',
            searchable: table.dataset.searchable !== 'false',
            currentSort: { column: -1, direction: 'asc' }
        };

        this.tables.set(table, config);

        if (config.sortable) {
            this.setupSorting(table);
        }

        if (config.searchable) {
            this.setupSearch(table);
        }
    },

    setupSorting(table) {
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.innerHTML += ' <i class="fas fa-sort sort-icon"></i>';
            
            header.addEventListener('click', () => {
                this.sortTable(table, index);
            });
        });
    },

    setupSearch(table) {
        const searchInput = document.querySelector(`[data-table-search="${table.id}"]`);
        if (searchInput) {
            searchInput.addEventListener('input', Utils.debounce((e) => {
                this.searchTable(table, e.target.value);
            }, 300));
        }
    },

    sortTable(table, columnIndex) {
        const config = this.tables.get(table);
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Determinar direção da ordenação
        let direction = 'asc';
        if (config.currentSort.column === columnIndex && config.currentSort.direction === 'asc') {
            direction = 'desc';
        }
        
        // Atualizar ícones
        table.querySelectorAll('.sort-icon').forEach(icon => {
            icon.className = 'fas fa-sort sort-icon';
        });
        
        const currentIcon = table.querySelectorAll('th')[columnIndex].querySelector('.sort-icon');
        currentIcon.className = `fas fa-sort-${direction === 'asc' ? 'up' : 'down'} sort-icon`;
        
        // Ordenar linhas
        rows.sort((a, b) => {
            const aValue = a.cells[columnIndex].textContent.trim();
            const bValue = b.cells[columnIndex].textContent.trim();
            
            // Tentar converter para número
            const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
            const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
            
            let comparison = 0;
            if (!isNaN(aNum) && !isNaN(bNum)) {
                comparison = aNum - bNum;
            } else {
                comparison = aValue.localeCompare(bValue, 'pt-BR');
            }
            
            return direction === 'asc' ? comparison : -comparison;
        });
        
        // Reordenar DOM
        rows.forEach(row => tbody.appendChild(row));
        
        // Atualizar estado
        config.currentSort = { column: columnIndex, direction };
    },

    searchTable(table, query) {
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        const searchTerm = query.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(searchTerm);
            row.style.display = matches ? '' : 'none';
        });
    }
};

// Gerenciador de Modais
const ModalManager = {
    modals: new Map(),
    activeModal: null,

    init() {
        this.setupGlobalListeners();
        this.initializeModals();
    },

    setupGlobalListeners() {
        // Fechar modal com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.close(this.activeModal);
            }
        });

        // Abrir modais via data-modal
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-modal]');
            if (trigger) {
                e.preventDefault();
                const modalId = trigger.dataset.modal;
                const modal = document.getElementById(modalId);
                if (modal) {
                    this.open(modal);
                }
            }
        });

        // Fechar modais
        document.addEventListener('click', (e) => {
            if (e.target.matches('.modal-close, [data-modal-close], .close')) {
                const modal = e.target.closest('.modal');
                if (modal) {
                    this.close(modal);
                }
            }
        });

        // Fechar ao clicar no backdrop
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal') && this.activeModal) {
                this.close(this.activeModal);
            }
        });
    },

    initializeModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => this.registerModal(modal));
    },

    registerModal(modal) {
        if (this.modals.has(modal)) return;

        const config = {
            closeOnBackdrop: modal.dataset.closeOnBackdrop !== 'false',
            closeOnEsc: modal.dataset.closeOnEsc !== 'false'
        };

        this.modals.set(modal, config);
    },

    open(modal) {
        if (this.activeModal) {
            this.close(this.activeModal);
        }

        this.activeModal = modal;
        modal.classList.add('active');
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Focar no primeiro elemento focável
        const focusable = modal.querySelector('input, button, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable) {
            focusable.focus();
        }

        // Dispatch evento
        modal.dispatchEvent(new CustomEvent('modalOpened'));
    },

    close(modal) {
        if (!modal || !modal.classList.contains('active')) return;

        modal.classList.remove('active');
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        this.activeModal = null;

        // Dispatch evento
        modal.dispatchEvent(new CustomEvent('modalClosed'));
    },

    isOpen(modal) {
        return modal && modal.classList.contains('active');
    }
};

// Gerenciador de Gráficos
const ChartManager = {
    charts: new Map(),

    init() {
        // Inicializar gráficos existentes
        this.initializeCharts();
    },

    initializeCharts() {
        const chartContainers = document.querySelectorAll('[data-chart]');
        chartContainers.forEach(container => {
            const chartType = container.dataset.chart;
            const chartData = container.dataset.chartData;
            
            if (chartData) {
                try {
                    const data = JSON.parse(chartData);
                    this.createChart(container, chartType, data);
                } catch (e) {
                    console.error('Erro ao parsear dados do gráfico:', e);
                }
            }
        });
    },

    createChart(container, type, data) {
        // Implementação básica para gráficos simples
        // Em um projeto real, você usaria uma biblioteca como Chart.js ou D3.js
        
        switch (type) {
            case 'bar':
                this.createBarChart(container, data);
                break;
            case 'line':
                this.createLineChart(container, data);
                break;
            case 'pie':
                this.createPieChart(container, data);
                break;
            default:
                console.warn(`Tipo de gráfico não suportado: ${type}`);
        }
    },

    createBarChart(container, data) {
        // Implementação simplificada de gráfico de barras
        const maxValue = Math.max(...data.values);
        
        let html = '<div class="simple-bar-chart">';
        data.labels.forEach((label, index) => {
            const value = data.values[index];
            const percentage = (value / maxValue) * 100;
            
            html += `
                <div class="bar-item">
                    <div class="bar-label">${label}</div>
                    <div class="bar-container">
                        <div class="bar" style="width: ${percentage}%"></div>
                        <span class="bar-value">${value}</span>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    },

    createLineChart(container, data) {
        // Placeholder para gráfico de linha
        container.innerHTML = '<div class="chart-placeholder">Gráfico de Linha (implementar com Chart.js)</div>';
    },

    createPieChart(container, data) {
        // Placeholder para gráfico de pizza
        container.innerHTML = '<div class="chart-placeholder">Gráfico de Pizza (implementar com Chart.js)</div>';
    },

    updateChart(container, newData) {
        const chartType = container.dataset.chart;
        this.createChart(container, chartType, newData);
    }
};

// Utilitários
function formatCurrency(value) {
    return Utils.formatCurrency(value);
}

function formatDate(date) {
    return Utils.formatDate(date);
}

// Exportar funções globais
window.showNotification = showNotification;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gerenciadores
    SidebarManager.init();
    NotificationManager.init();
    ModalManager.init();
    ChartManager.init();
    
    console.log('Sistema PTCC inicializado com sucesso');
});