import { describe, it, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../stores/auth'

const user = { first_name: 'Jane', last_name: 'Doe' } as any

describe('auth store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('reports authentication state and full name', () => {
    const store = useAuthStore()
    store.setAuth(user, 'token')

    expect(store.isAuthenticated).toBe(true)
    expect(store.userFullName).toBe('Jane Doe')
  })
})
