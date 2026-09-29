import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const isSidebarOpen = ref(true)
  const tenantDomain = ref(import.meta.env.VITE_TENANT_DOMAIN || 'e-wallet.localhost')
  const notifications = ref([])

  function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value
  }

  function addNotification(type, message, duration = 4000) {
    const id = Date.now() + Math.random()
    notifications.value.push({ id, type, message })

    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, duration)
    }
  }

  function removeNotification(id) {
    notifications.value = notifications.value.filter((n) => n.id !== id)
  }

  return {
    isSidebarOpen,
    tenantDomain,
    notifications,
    toggleSidebar,
    addNotification,
    removeNotification,
  }
})
