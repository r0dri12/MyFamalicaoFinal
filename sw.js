const CACHE_NAME = 'myfamalicao-cache-v1';
const ASSETS_TO_CACHE = [
  'index.php',
  'sobre.php',
  'main_style.css',
  'style.css',
  'ui_notifications.css',
  'ui_notifications.js',
  'theme_handler.js',
  'script.js',
  'favicon.png'
];

// Instalação do Service Worker e Caching dos recursos estáticos
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[Service Worker] Caching recursos estáticos');
        return cache.addAll(ASSETS_TO_CACHE);
      })
      .then(() => self.skipWaiting())
  );
});

// Activação e Limpeza de caches antigas
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheKeys => {
      return Promise.all(
        cacheKeys.map(key => {
          if (key !== CACHE_NAME) {
            console.log('[Service Worker] A apagar cache antiga:', key);
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Interceção de pedidos de rede com estratégia Network-First (cai para Cache se falhar a rede)
self.addEventListener('fetch', event => {
  // Evitar interceptar chamadas de APIs ou PHP que requiram sessão ativa ou dados em tempo real
  if (
    event.request.url.includes('api_') || 
    event.request.method !== 'GET' ||
    !event.request.url.startsWith(self.location.origin)
  ) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Se a resposta for válida, guardar uma cópia na cache
        if (response && response.status === 200) {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
        }
        return response;
      })
      .catch(() => {
        // Se a rede falhar, tentar ir buscar à cache
        return caches.match(event.request);
      })
  );
});
