export default defineNuxtPlugin(() => {
  const { initializeTheme } = useTheme()

  // Initialize theme when the app starts
  onMounted(() => {
    initializeTheme()
  })
})