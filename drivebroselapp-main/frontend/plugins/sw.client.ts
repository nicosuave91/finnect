import { Workbox } from 'workbox-window'

export default defineNuxtPlugin(() => {
  if ('serviceWorker' in navigator) {
    const wb = new Workbox('/sw.js')
    wb.addEventListener('waiting', () => {
      wb.addEventListener('controlling', () => {
        window.location.reload()
      })
      wb.messageSkipWaiting()
    })
    wb.register()
  }
})
