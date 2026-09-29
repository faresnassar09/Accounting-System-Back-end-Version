<template>
  <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-20 shadow-xs">
    <!-- Left: Page Title / Breadcrumbs -->
    <div class="flex items-center space-x-4">
      <div class="flex flex-col">
        <h1 class="text-lg font-bold text-slate-900 tracking-tight leading-none">
          {{ pageTitle }}
        </h1>
        <div class="flex items-center text-xs text-slate-500 mt-1 space-x-1.5">
          <span>Tenant</span>
          <span>&bull;</span>
          <span class="font-mono text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 text-[11px] font-medium">
            {{ uiStore.tenantDomain }}
          </span>
        </div>
      </div>
    </div>

    <!-- Right: Tenant Status, Notifications & User Dropdown -->
    <div class="flex items-center space-x-4">
      <!-- Live API Status Pill -->
      <div class="hidden sm:flex items-center space-x-2 px-3 py-1.5 bg-slate-100 rounded-full border border-slate-200 text-xs text-slate-600">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <span class="font-medium">Backend Online</span>
      </div>

      <!-- User Menu Dropdown -->
      <div class="relative" ref="dropdownRef">
        <button
          @click="isDropdownOpen = !isDropdownOpen"
          class="flex items-center space-x-3 p-1.5 rounded-xl hover:bg-slate-100 transition focus:outline-none"
        >
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-800 to-slate-700 text-white flex items-center justify-center font-bold text-sm shadow-sm">
            {{ userInitials }}
          </div>
          <div class="hidden md:flex flex-col text-left">
            <span class="text-xs font-bold text-slate-800">{{ authStore.userName }}</span>
            <span class="text-[11px] text-slate-500 capitalize">{{ authStore.userRole }}</span>
          </div>
          <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isDropdownOpen }" />
        </button>

        <!-- Dropdown Menu -->
        <transition
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="transform scale-95 opacity-0"
          enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="transform scale-100 opacity-100"
          leave-to-class="transform scale-95 opacity-0"
        >
          <div
            v-show="isDropdownOpen"
            class="absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 divide-y divide-slate-100"
          >
            <!-- User summary -->
            <div class="px-4 py-3">
              <p class="text-xs text-slate-500">Signed in as</p>
              <p class="text-sm font-bold text-slate-900 truncate">{{ authStore.userName }}</p>
              <p class="text-xs text-slate-500 truncate mt-0.5">{{ authStore.userEmail || 'Tenant User' }}</p>
              <div class="mt-2 inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                Role: {{ authStore.userRole }}
              </div>
            </div>

            <!-- Tenant Info -->
            <div class="px-4 py-2 text-xs text-slate-500">
              <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tenant Domain</span>
              <span class="font-mono text-slate-700">{{ uiStore.tenantDomain }}</span>
            </div>

            <!-- Actions -->
            <div class="py-1">
              <button
                @click="handleLogout"
                class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 flex items-center space-x-2 transition font-medium"
              >
                <LogOut class="w-4 h-4" />
                <span>Sign Out</span>
              </button>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import { ChevronDown, LogOut } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()

const isDropdownOpen = ref(false)
const dropdownRef = ref(null)

const pageTitle = computed(() => route.meta.title || 'Overview')

const userInitials = computed(() => {
  const name = authStore.userName || 'Accountant'
  return name.slice(0, 2).toUpperCase()
})

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

async function handleLogout() {
  isDropdownOpen.value = false
  await authStore.logout()
  uiStore.addNotification('info', 'Signed out successfully.')
  router.push({ name: 'login' })
}
</script>
