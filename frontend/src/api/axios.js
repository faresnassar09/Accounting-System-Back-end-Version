import axios from 'axios'

// Dynamically determine the base API URL based on hostname or environment
const getBaseApiUrl = () => {
  if (import.meta.env.VITE_API_BASE_URL) {
    return import.meta.env.VITE_API_BASE_URL
  }

  // If running in browser on a tenant domain like e-wallet.localhost:5173
  const hostname = window.location.hostname || 'e-wallet.localhost'
  return `http://${hostname}:8000/api`
}

const apiClient = axios.create({
  baseURL: getBaseApiUrl(),
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
})

// Request interceptor: Attach Bearer token if present
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor: Handle 401 unauthorized & format errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      // Clear expired or invalid credentials
      localStorage.removeItem('token')
      localStorage.removeItem('user')

      // Avoid redirect loop if already on login
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default apiClient
