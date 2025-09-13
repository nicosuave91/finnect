export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  
  // Create API instance
  const api = {
    baseURL: config.public.apiBaseUrl,
    token: null as string | null,
    tenantId: null as number | null,
    
    // Set auth token
    setAuthToken(token: string) {
      this.token = token
    },
    
    // Clear auth token
    clearAuthToken() {
      this.token = null
    },
    
    // Set tenant ID
    setTenantId(tenantId: number) {
      this.tenantId = tenantId
    },
    
    // Clear tenant ID
    clearTenantId() {
      this.tenantId = null
    },
    
    // Make request
    async request<T = any>(url: string, options: any = {}): Promise<T> {
      const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers
      }
      
      // Add auth token
      if (this.token) {
        headers.Authorization = `Bearer ${this.token}`
      }
      
      // Add tenant ID
      if (this.tenantId) {
        headers['X-Tenant-ID'] = this.tenantId.toString()
      }
      
      // Make request
      const response = await $fetch<T>(url, {
        baseURL: this.baseURL,
        ...options,
        headers
      })
      
      return response
    },
    
    // GET request
    async get<T = any>(url: string, options: any = {}): Promise<T> {
      return this.request<T>(url, { ...options, method: 'GET' })
    },
    
    // POST request
    async post<T = any>(url: string, body: any, options: any = {}): Promise<T> {
      return this.request<T>(url, { ...options, method: 'POST', body })
    },
    
    // PUT request
    async put<T = any>(url: string, body: any, options: any = {}): Promise<T> {
      return this.request<T>(url, { ...options, method: 'PUT', body })
    },
    
    // PATCH request
    async patch<T = any>(url: string, body: any, options: any = {}): Promise<T> {
      return this.request<T>(url, { ...options, method: 'PATCH', body })
    },
    
    // DELETE request
    async delete<T = any>(url: string, options: any = {}): Promise<T> {
      return this.request<T>(url, { ...options, method: 'DELETE' })
    }
  }
  
  // Provide API instance
  return {
    provide: {
      api
    }
  }
})