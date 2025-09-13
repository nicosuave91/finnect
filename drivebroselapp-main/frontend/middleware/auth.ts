export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()
  
  // Check if user is authenticated
  if (!authStore.isAuthenticated) {
    return navigateTo('/auth/login')
  }
})