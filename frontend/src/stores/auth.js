import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authApi from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null)
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const isLoading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)
  const userName = computed(() => user.value?.name || 'User')
  const userEmail = computed(() => user.value?.email || '')
  const userRole = computed(() => user.value?.role || 'Staff')
  const userPermissions = computed(() => user.value?.permissions || [])

  async function login(credentials) {
    isLoading.value = true
    error.value = null

    try {
      const response = await authApi.login(credentials)
      if (response && response.success) {
        const data = response.data
        token.value = data.token

        // Construct user object
        user.value = {
          id: data.id,
          name: data.name,
          email: data.email || credentials.email,
          role: data.role,
          permissions: data.permissions || [],
        }

        // Persist to local storage
        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(user.value))

        return { success: true }
      } else {
        const errorMsg = response?.message || 'Login failed. Please check credentials.'
        error.value = errorMsg
        return { success: false, message: errorMsg }
      }
    } catch (err) {
      let message = 'An unexpected error occurred. Please try again.'
      if (err.response?.data?.message) {
        message = err.response.data.message
      } else if (err.message) {
        message = err.message
      }
      error.value = message
      return { success: false, message }
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    isLoading.value = true
    try {
      if (token.value) {
        await authApi.logout()
      }
    } catch (e) {
      console.warn('Backend logout failed or token already invalid', e)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      isLoading.value = false
    }
  }

  async function fetchProfile() {
    try {
      const response = await authApi.getProfile()
      if (response && response.success && response.data) {
        user.value = {
          ...user.value,
          ...response.data,
        }
        localStorage.setItem('user', JSON.stringify(user.value))
      }
    } catch (err) {
      console.error('Failed to fetch profile', err)
    }
  }

  return {
    token,
    user,
    isLoading,
    error,
    isAuthenticated,
    userName,
    userEmail,
    userRole,
    userPermissions,
    login,
    logout,
    fetchProfile,
  }
})
