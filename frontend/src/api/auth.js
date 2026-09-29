import apiClient from './axios'

export default {
  /**
   * Login user with email and password
   * @param {{ email: string, password: string }} credentials
   */
  async login(credentials) {
    const response = await apiClient.post('/v1/auth/login', credentials)
    return response.data
  },

  /**
   * Logout authenticated user
   */
  async logout() {
    const response = await apiClient.post('/v1/auth/logout')
    return response.data
  },

  /**
   * Fetch current user profile
   */
  async getProfile() {
    const response = await apiClient.get('/v1/profile')
    return response.data
  },
}
