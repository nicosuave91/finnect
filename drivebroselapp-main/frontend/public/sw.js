importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.5.3/workbox-sw.js')

const { precaching, routing, strategies, backgroundSync, core } = workbox

core.setCacheNameDetails({ prefix: 'finnect' })

// Precache essential assets
precaching.precacheAndRoute([
  { url: '/', revision: null },
  { url: '/favicon.ico', revision: null }
])

// Cache static resources
routing.registerRoute(
  ({ request }) => ['script', 'style', 'image'].includes(request.destination),
  new strategies.StaleWhileRevalidate({ cacheName: 'static-resources' })
)

// Queue API requests when offline
const bgSyncPlugin = new backgroundSync.BackgroundSyncPlugin('apiQueue', {
  maxRetentionTime: 24 * 60 // minutes
})

;['POST', 'PUT', 'DELETE'].forEach(method => {
  routing.registerRoute(
    ({ url }) => url.pathname.startsWith('/api'),
    new strategies.NetworkOnly({ plugins: [bgSyncPlugin] }),
    method
  )
})
