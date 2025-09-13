export default defineNuxtRouteMiddleware(async (to) => {
  const tenantStore = useTenantStore()
  
  // Check if tenant is loaded
  if (!tenantStore.currentTenant) {
    // Try to initialize tenant
    await tenantStore.initialize()
    
    // If still no tenant, redirect to tenant selection
    if (!tenantStore.currentTenant) {
      return navigateTo('/tenant/select')
    }
  }
})