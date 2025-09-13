export default defineNuxtPlugin(() => {
  if (process.server) return
  const eventSource = new EventSource('/api/loans/stream/events')
  eventSource.onmessage = (e) => {
    console.debug('SSE', e.data)
  }
})

