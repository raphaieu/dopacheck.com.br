// Service Worker para DOPA Check
const CACHE_NAME = 'dopacheck-v2'
const STATIC_CACHE_NAME = 'dopacheck-static-v2'
const DYNAMIC_CACHE_NAME = 'dopacheck-dynamic-v2'

// Assets para cache estático
const STATIC_ASSETS = [
  '/',
  '/site.webmanifest',
  '/android-chrome-192x192.png',
  '/android-chrome-512x512.png',
  '/apple-touch-icon.png',
  '/favicon-32x32.png',
  '/favicon-16x16.png',
  '/favicon.ico',
]

// Instalação do Service Worker
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...')
  event.waitUntil((async () => {
    const cache = await caches.open(STATIC_CACHE_NAME)
    console.log('[Service Worker] Caching static assets')

    // cache.addAll falha se 1 item falhar (404/500/redirect/etc). Aqui a gente
    // tenta individualmente e segue em frente quando algum asset não puder ser cacheado.
    await Promise.allSettled(
      STATIC_ASSETS.map(async (url) => {
        try {
          await cache.add(url)
        } catch (err) {
          console.warn('[Service Worker] Failed to cache:', url, err)
        }
      })
    )

    await self.skipWaiting()
  })())
})

// Ativação do Service Worker
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...')
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => {
            return cacheName !== STATIC_CACHE_NAME && 
                   cacheName !== DYNAMIC_CACHE_NAME
          })
          .map((cacheName) => {
            console.log('[Service Worker] Deleting old cache:', cacheName)
            return caches.delete(cacheName)
          })
      )
    })
    .then(() => self.clients.claim())
  )
})

// Estratégia: Network First com fallback para cache
self.addEventListener('fetch', (event) => {
  const { request } = event
  const url = new URL(request.url)

  // Ignorar requisições não-GET
  if (request.method !== 'GET') {
    return
  }

  // Ignorar requisições de API (sempre buscar do servidor)
  if (url.pathname.startsWith('/api/')) {
    return
  }

  // Ignorar requisições de webhook
  if (url.pathname.startsWith('/webhook/')) {
    return
  }

  event.respondWith(
    fetch(request)
      .then((response) => {
        // Clonar resposta para cache
        const responseToCache = response.clone()

        // Cachear apenas respostas válidas
        if (response.status === 200) {
          caches.open(DYNAMIC_CACHE_NAME).then((cache) => {
            cache.put(request, responseToCache)
          })
        }

        return response
      })
      .catch(() => {
        // Fallback para cache se network falhar
        return caches.match(request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse
          }

          // Se for navegação, retornar página offline
          if (request.mode === 'navigate') {
            return caches.match('/')
          }
        })
      })
  )
})

// Limpeza de cache antigo (executar periodicamente)
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'CLEAN_CACHE') {
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== STATIC_CACHE_NAME && cacheName !== DYNAMIC_CACHE_NAME) {
            return caches.delete(cacheName)
          }
        })
      )
    })
  }
})

