export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuthStore()
  const required = (to.meta.roles as string[]) || []
  if (!required.length) return

  const hasRole = (auth.hasRole as any)
  if (!auth.isAuthenticated || !required.some((r) => hasRole(r))) {
    return navigateTo('/auth/login')
  }
})

