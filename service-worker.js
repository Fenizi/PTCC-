// Service Worker para GERE TECH
// Implementa cache offline e funcionalidades PWA

const CACHE_NAME = 'geretech-cache-v1';
const STATIC_CACHE_NAME = 'geretech-static-v1';
const DYNAMIC_CACHE_NAME = 'geretech-dynamic-v1';

// Arquivos estáticos para cache
const STATIC_ASSETS = [
  '/',
  '/index.html',
  '/css/style.css',
  '/js/main.js',
  '/manifest.json',
  '/pages/login.php',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
  'https://cdn.jsdelivr.net/npm/chart.js'
];

// Arquivos dinâmicos que podem ser cacheados
const DYNAMIC_ASSETS = [
  '/pages/dashboard.php',
  '/pages/clientes.php',
  '/pages/produtos.php',
  '/pages/vendas.php',
  '/pages/configuracoes.php'
];

// Evento de instalação do Service Worker
self.addEventListener('install', event => {
  console.log('Service Worker: Instalando...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Cacheando arquivos estáticos');
        return cache.addAll(STATIC_ASSETS);
      })
      .catch(error => {
        console.error('Service Worker: Erro ao cachear arquivos estáticos:', error);
      })
  );
  
  // Força a ativação imediata do novo Service Worker
  self.skipWaiting();
});

// Evento de ativação do Service Worker
self.addEventListener('activate', event => {
  console.log('Service Worker: Ativando...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            // Remove caches antigos
            if (cacheName !== STATIC_CACHE_NAME && cacheName !== DYNAMIC_CACHE_NAME) {
              console.log('Service Worker: Removendo cache antigo:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
  );
  
  // Assume controle de todas as páginas imediatamente
  self.clients.claim();
});

// Evento de fetch (intercepta requisições)
self.addEventListener('fetch', event => {
  const requestUrl = new URL(event.request.url);
  
  // Ignora requisições que não são GET
  if (event.request.method !== 'GET') {
    return;
  }
  
  // Ignora requisições para APIs externas específicas
  if (requestUrl.origin !== location.origin && 
      !requestUrl.href.includes('cdnjs.cloudflare.com') &&
      !requestUrl.href.includes('cdn.jsdelivr.net')) {
    return;
  }
  
  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        // Se encontrou no cache, retorna
        if (cachedResponse) {
          console.log('Service Worker: Servindo do cache:', event.request.url);
          return cachedResponse;
        }
        
        // Se não encontrou no cache, busca na rede
        return fetch(event.request)
          .then(networkResponse => {
            // Verifica se a resposta é válida
            if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
              return networkResponse;
            }
            
            // Clona a resposta para poder usar tanto para retornar quanto para cachear
            const responseToCache = networkResponse.clone();
            
            // Determina qual cache usar
            let cacheName = DYNAMIC_CACHE_NAME;
            if (STATIC_ASSETS.includes(requestUrl.pathname) || 
                requestUrl.href.includes('cdnjs.cloudflare.com') ||
                requestUrl.href.includes('cdn.jsdelivr.net')) {
              cacheName = STATIC_CACHE_NAME;
            }
            
            // Adiciona ao cache
            caches.open(cacheName)
              .then(cache => {
                console.log('Service Worker: Cacheando nova requisição:', event.request.url);
                cache.put(event.request, responseToCache);
              });
            
            return networkResponse;
          })
          .catch(error => {
            console.log('Service Worker: Erro na rede, tentando cache:', error);
            
            // Se falhou na rede, tenta servir uma página offline
            if (event.request.destination === 'document') {
              return caches.match('/index.html');
            }
            
            // Para outros recursos, retorna uma resposta vazia
            return new Response('Recurso não disponível offline', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            });
          });
      })
  );
});

// Evento de sincronização em background
self.addEventListener('sync', event => {
  console.log('Service Worker: Sincronização em background:', event.tag);
  
  if (event.tag === 'background-sync') {
    event.waitUntil(
      // Aqui você pode implementar sincronização de dados offline
      console.log('Service Worker: Executando sincronização de dados')
    );
  }
});

// Evento de push notification
self.addEventListener('push', event => {
  console.log('Service Worker: Push notification recebida');
  
  const options = {
    body: event.data ? event.data.text() : 'Nova notificação do GERE TECH',
    icon: '/img/logo192.png',
    badge: '/img/logo192.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Abrir GERE TECH',
        icon: '/img/logo192.png'
      },
      {
        action: 'close',
        title: 'Fechar',
        icon: '/img/logo192.png'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('GERE TECH', options)
  );
});

// Evento de clique em notificação
self.addEventListener('notificationclick', event => {
  console.log('Service Worker: Clique em notificação:', event.action);
  
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/pages/dashboard.php')
    );
  }
});

// Evento de mensagem do cliente
self.addEventListener('message', event => {
  console.log('Service Worker: Mensagem recebida:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'GET_VERSION') {
    event.ports[0].postMessage({ version: CACHE_NAME });
  }
});

// Função utilitária para limpar caches antigos
function cleanupCaches() {
  return caches.keys()
    .then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== STATIC_CACHE_NAME && cacheName !== DYNAMIC_CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    });
}

// Função utilitária para pré-carregar recursos importantes
function precacheImportantResources() {
  return caches.open(DYNAMIC_CACHE_NAME)
    .then(cache => {
      return cache.addAll(DYNAMIC_ASSETS.filter(asset => {
        // Só adiciona se não estiver já no cache
        return !cache.match(asset);
      }));
    })
    .catch(error => {
      console.warn('Service Worker: Erro ao pré-carregar recursos:', error);
    });
}

// Executa limpeza e pré-carregamento periodicamente
setInterval(() => {
  cleanupCaches();
  precacheImportantResources();
}, 24 * 60 * 60 * 1000); // A cada 24 horas