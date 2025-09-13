import { defineStore } from 'pinia'
import type { User, LoginCredentials, RegisterData } from '~/types'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const isAuthenticated = computed(() => !!user.value && !!token.value)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const userFullName = computed(() => {
    if (!user.value) return ''
    return `${user.value.first_name} ${user.value.last_name}`
  })

  const userInitials = computed(() => {
    if (!user.value) return ''
    return `${user.value.first_name[0]}${user.value.last_name[0]}`.toUpperCase()
  })

  const hasRole = computed(() => (role: string) => {
    return user.value?.roles?.some(r => r.name === role) ?? false
  })

  const hasPermission = computed(() => (permission: string) => {
    return user.value?.permissions?.some(p => p.name === permission) ?? false
  })

  // Actions
  const initialize = async () => {
    try {
      const storedToken = useCookie('auth_token')
      if (storedToken.value) {
        token.value = storedToken.value
        await fetchUser()
      }
    } catch (err) {
      console.error('Failed to initialize auth:', err)
      logout()
    }
  }

  const login = async (credentials: LoginCredentials) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await $fetch<{ user: User; token: string }>('/api/auth/login', {
        method: 'POST',
        body: credentials
      })

      user.value = response.user
      token.value = response.token

      // Set cookie
      const authCookie = useCookie('auth_token', {
        maxAge: 60 * 60 * 24 * 7, // 7 days
        secure: true,
        sameSite: 'strict'
      })
      authCookie.value = response.token

      // Set default headers
      const { $api } = useNuxtApp()
      $api.setAuthToken(response.token)

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Login failed'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const register = async (data: RegisterData) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await $fetch<{ user: User; token: string }>('/api/auth/register', {
        method: 'POST',
        body: data
      })

      user.value = response.user
      token.value = response.token

      // Set cookie
      const authCookie = useCookie('auth_token', {
        maxAge: 60 * 60 * 24 * 7, // 7 days
        secure: true,
        sameSite: 'strict'
      })
      authCookie.value = response.token

      // Set default headers
      const { $api } = useNuxtApp()
      $api.setAuthToken(response.token)

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Registration failed'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await $fetch('/api/auth/logout', {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token.value}`
          }
        })
      }
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      // Clear state
      user.value = null
      token.value = null
      error.value = null

      // Clear cookie
      const authCookie = useCookie('auth_token')
      authCookie.value = null

      // Clear API token
      const { $api } = useNuxtApp()
      $api.clearAuthToken()

      // Redirect to login
      await navigateTo('/auth/login')
    }
  }

  const fetchUser = async () => {
    try {
      if (!token.value) return

      const response = await $fetch<{ user: User }>('/api/auth/me', {
        headers: {
          Authorization: `Bearer ${token.value}`
        }
      })

      user.value = response.user
    } catch (err) {
      console.error('Failed to fetch user:', err)
      logout()
    }
  }

  const updateProfile = async (profileData: Partial<User>) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await $fetch<{ user: User }>('/api/auth/profile', {
        method: 'PUT',
        headers: {
          Authorization: `Bearer ${token.value}`
        },
        body: profileData
      })

      user.value = response.user
      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Profile update failed'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const changePassword = async (passwordData: { current_password: string; new_password: string; new_password_confirmation: string }) => {
    try {
      isLoading.value = true
      error.value = null

      await $fetch('/api/auth/change-password', {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token.value}`
        },
        body: passwordData
      })

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Password change failed'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const forgotPassword = async (email: string) => {
    try {
      isLoading.value = true
      error.value = null

      await $fetch('/api/auth/forgot-password', {
        method: 'POST',
        body: { email }
      })

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Password reset request failed'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const resetPassword = async (resetData: { token: string; email: string; password: string; password_confirmation: string }) => {
    try {
      isLoading.value = true
      error.value = null

      await $fetch('/api/auth/reset-password', {
        method: 'POST',
        body: resetData
      })

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Password reset failed'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    user: readonly(user),
    token: readonly(token),
    isAuthenticated,
    isLoading: readonly(isLoading),
    error: readonly(error),
    
    // Getters
    userFullName,
    userInitials,
    hasRole,
    hasPermission,
    
    // Actions
    initialize,
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
    changePassword,
    forgotPassword,
    resetPassword,
    clearError
  }
})