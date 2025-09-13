export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()
  
  // Redirect authenticated users to dashboard
  if (authStore.isAuthenticated) {
    return navigateTo('/')
  }
})