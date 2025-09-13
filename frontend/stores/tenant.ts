import { defineStore } from 'pinia'
import type { Tenant } from '~/types'

export const useTenantStore = defineStore('tenant', () => {
  // State
  const currentTenant = ref<Tenant | null>(null)
  const tenants = ref<Tenant[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const isMultiTenant = computed(() => tenants.value.length > 1)
  const tenantName = computed(() => currentTenant.value?.name || 'Finnect')
  const tenantDomain = computed(() => currentTenant.value?.domain || '')
  const tenantConfiguration = computed(() => currentTenant.value?.configuration || {})
  const complianceSettings = computed(() => currentTenant.value?.compliance_settings || {})

  // Actions
  const initialize = async () => {
    try {
      // Try to get tenant from subdomain
      const host = window.location.hostname
      const subdomain = host.split('.')[0]
      
      if (subdomain && subdomain !== 'www' && subdomain !== 'api') {
        await loadTenantByDomain(subdomain)
      } else {
        // Try to get tenant from cookie or header
        const tenantId = useCookie('tenant_id').value
        if (tenantId) {
          await loadTenantById(parseInt(tenantId))
        }
      }
    } catch (err) {
      console.error('Failed to initialize tenant:', err)
    }
  }

  const loadTenantByDomain = async (domain: string) => {
    try {
      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: Tenant }>(`/api/tenants/domain/${domain}`)
      currentTenant.value = data

      // Set tenant cookie
      const tenantCookie = useCookie('tenant_id')
      tenantCookie.value = data.id.toString()

      // Set tenant header for API calls
      const { $api } = useNuxtApp()
      $api.setTenantId(data.id)

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to load tenant'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const loadTenantById = async (tenantId: number) => {
    try {
      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: Tenant }>(`/api/tenants/${tenantId}`)
      currentTenant.value = data

      // Set tenant cookie
      const tenantCookie = useCookie('tenant_id')
      tenantCookie.value = data.id.toString()

      // Set tenant header for API calls
      const { $api } = useNuxtApp()
      $api.setTenantId(data.id)

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to load tenant'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const switchTenant = async (tenantId: number) => {
    try {
      const result = await loadTenantById(tenantId)
      if (result.success) {
        // Reload the page to ensure all components use the new tenant context
        await navigateTo(window.location.pathname, { replace: true })
      }
      return result
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to switch tenant'
      return { success: false, error: error.value }
    }
  }

  const loadTenants = async () => {
    try {
      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: Tenant[] }>('/api/tenants')
      tenants.value = data

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to load tenants'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const updateTenantConfiguration = async (configuration: Record<string, any>) => {
    try {
      if (!currentTenant.value) return { success: false, error: 'No tenant selected' }

      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: Tenant }>(`/api/tenants/${currentTenant.value.id}`, {
        method: 'PUT',
        body: { configuration }
      })

      currentTenant.value = data
      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to update tenant configuration'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const updateComplianceSettings = async (regulation: string, settings: Record<string, any>) => {
    try {
      if (!currentTenant.value) return { success: false, error: 'No tenant selected' }

      isLoading.value = true
      error.value = null

      const complianceSettings = { ...currentTenant.value.compliance_settings }
      complianceSettings[regulation] = settings

      const { data } = await $fetch<{ data: Tenant }>(`/api/tenants/${currentTenant.value.id}`, {
        method: 'PUT',
        body: { compliance_settings: complianceSettings }
      })

      currentTenant.value = data
      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to update compliance settings'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const getComplianceSetting = (regulation: string, key: string) => {
    const settings = complianceSettings.value[regulation]
    return settings?.[key]
  }

  const isComplianceEnabled = (regulation: string) => {
    return getComplianceSetting(regulation, 'enabled') ?? true
  }

  const getComplianceMode = () => {
    return tenantConfiguration.value.compliance_mode || 'strict'
  }

  const getThemeConfiguration = () => {
    return tenantConfiguration.value.theme || {
      primary_color: '#3b82f6',
      secondary_color: '#64748b',
      logo_url: null,
      favicon_url: null
    }
  }

  const getBrandingConfiguration = () => {
    return tenantConfiguration.value.branding || {
      company_name: currentTenant.value?.name || 'Finnect',
      tagline: 'Mortgage Broker-Dealer Platform',
      support_email: 'support@finnect.com',
      support_phone: '+1-800-FINNECT'
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    currentTenant: readonly(currentTenant),
    tenants: readonly(tenants),
    isLoading: readonly(isLoading),
    error: readonly(error),
    
    // Getters
    isMultiTenant,
    tenantName,
    tenantDomain,
    tenantConfiguration,
    complianceSettings,
    
    // Actions
    initialize,
    loadTenantByDomain,
    loadTenantById,
    switchTenant,
    loadTenants,
    updateTenantConfiguration,
    updateComplianceSettings,
    getComplianceSetting,
    isComplianceEnabled,
    getComplianceMode,
    getThemeConfiguration,
    getBrandingConfiguration,
    clearError
  }
})