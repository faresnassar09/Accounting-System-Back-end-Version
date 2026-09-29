<template>
  <aside
    class="relative flex flex-col bg-slate-900 text-slate-300 transition-all duration-300 ease-in-out z-30 select-none shadow-xl border-r border-slate-800"
    :class="uiStore.isSidebarOpen ? 'w-64' : 'w-20'"
  >
    <!-- Brand / Tenant Header -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800 bg-slate-950/60">
      <router-link to="/dashboard" class="flex items-center space-x-3 overflow-hidden">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-md flex-shrink-0">
          <Wallet class="w-5 h-5" />
        </div>
        <div v-show="uiStore.isSidebarOpen" class="flex flex-col min-w-0 transition-opacity duration-200">
          <span class="font-bold text-white text-base tracking-tight truncate">E-Wallet ERP</span>
          <span class="text-[11px] text-emerald-400 font-medium tracking-wide uppercase truncate">
            {{ uiStore.tenantDomain }}
          </span>
        </div>
      </router-link>

      <button
        @click="uiStore.toggleSidebar"
        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
        :title="uiStore.isSidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'"
      >
        <ChevronLeft
          class="w-4 h-4 transition-transform duration-300"
          :class="{ 'rotate-180': !uiStore.isSidebarOpen }"
        />
      </button>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
      <!-- Section: Core -->
      <div>
        <div
          v-show="uiStore.isSidebarOpen"
          class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500"
        >
          Overview
        </div>
        <div class="space-y-1">
          <router-link
            to="/dashboard"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 group"
            :class="isActive('/dashboard') ? 'bg-emerald-500/10 text-emerald-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'"
          >
            <LayoutDashboard class="w-5 h-5 flex-shrink-0 mr-3" :class="isActive('/dashboard') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200'" />
            <span v-show="uiStore.isSidebarOpen" class="truncate">Dashboard</span>
          </router-link>
        </div>
      </div>

      <!-- Section: General Ledger -->
      <div>
        <div
          v-show="uiStore.isSidebarOpen"
          class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500"
        >
          General Ledger
        </div>
        <div class="space-y-1">
          <router-link
            to="/accounting/charts"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 group"
            :class="isActive('/accounting/charts') ? 'bg-emerald-500/10 text-emerald-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'"
          >
            <FolderTree class="w-5 h-5 flex-shrink-0 mr-3" :class="isActive('/accounting/charts') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200'" />
            <span v-show="uiStore.isSidebarOpen" class="truncate">Chart of Accounts</span>
          </router-link>

          <router-link
            to="/accounting/journal-entries"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 group"
            :class="isActive('/accounting/journal-entries') ? 'bg-emerald-500/10 text-emerald-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'"
          >
            <BookOpenCheck class="w-5 h-5 flex-shrink-0 mr-3" :class="isActive('/accounting/journal-entries') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200'" />
            <span v-show="uiStore.isSidebarOpen" class="truncate">Journal Entries</span>
          </router-link>
        </div>
      </div>

      <!-- Section: Financial Reports -->
      <div>
        <div
          v-show="uiStore.isSidebarOpen"
          class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500"
        >
          Financial Reports
        </div>
        <div class="space-y-1">
          <div
            v-for="report in reportsList"
            :key="report.label"
            class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 cursor-pointer transition group"
            @click="notifyUpcomingReport(report.label)"
          >
            <div class="flex items-center min-w-0">
              <component :is="report.icon" class="w-5 h-5 flex-shrink-0 mr-3 text-slate-400 group-hover:text-slate-200" />
              <span v-show="uiStore.isSidebarOpen" class="truncate">{{ report.label }}</span>
            </div>
            <span
              v-show="uiStore.isSidebarOpen"
              class="text-[10px] bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded border border-slate-700/50"
            >
              API Ready
            </span>
          </div>
        </div>
      </div>

      <!-- Section: Operations -->
      <div>
        <div
          v-show="uiStore.isSidebarOpen"
          class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500"
        >
          Operations
        </div>
        <div class="space-y-1">
          <div
            v-for="op in operationsList"
            :key="op.label"
            class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 cursor-pointer transition group"
            @click="notifyUpcomingOperation(op.label)"
          >
            <div class="flex items-center min-w-0">
              <component :is="op.icon" class="w-5 h-5 flex-shrink-0 mr-3 text-slate-400 group-hover:text-slate-200" />
              <span v-show="uiStore.isSidebarOpen" class="truncate">{{ op.label }}</span>
            </div>
            <span
              v-show="uiStore.isSidebarOpen"
              class="text-[10px] bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded border border-slate-700/50"
            >
              ERP
            </span>
          </div>
        </div>
      </div>
    </nav>

    <!-- Bottom User Section -->
    <div class="p-3 border-t border-slate-800 bg-slate-950/40">
      <div class="flex items-center" :class="uiStore.isSidebarOpen ? 'justify-between' : 'justify-center'">
        <div class="flex items-center space-x-3 overflow-hidden">
          <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 text-emerald-400 flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-inner">
            {{ userInitials }}
          </div>
          <div v-show="uiStore.isSidebarOpen" class="flex flex-col min-w-0">
            <span class="text-sm font-semibold text-white truncate">{{ authStore.userName }}</span>
            <span class="text-xs text-slate-400 capitalize truncate">{{ authStore.userRole }}</span>
          </div>
        </div>

        <button
          v-show="uiStore.isSidebarOpen"
          @click="handleLogout"
          class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition"
          title="Sign Out"
        >
          <LogOut class="w-4 h-4" />
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import {
  Wallet,
  LayoutDashboard,
  FolderTree,
  BookOpenCheck,
  Scale,
  FileSpreadsheet,
  TrendingUp,
  Landmark,
  Clock,
  Coins,
  Repeat,
  ChevronLeft,
  LogOut,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()

const isActive = (path) => route.path === path

const userInitials = computed(() => {
  const name = authStore.userName || 'Accountant'
  return name.slice(0, 2).toUpperCase()
})

const reportsList = [
  { label: 'Trial Balance', icon: Scale },
  { label: 'Balance Sheet', icon: FileSpreadsheet },
  { label: 'Income Statement', icon: TrendingUp },
  { label: 'Cash Flow (Indirect)', icon: Landmark },
  { label: 'AR & AP Aging', icon: Clock },
]

const operationsList = [
  { label: 'Fixed Assets', icon: Coins },
  { label: 'Bank Reconciliation', icon: Landmark },
  { label: 'Recurring Entries', icon: Repeat },
]

function notifyUpcomingReport(name) {
  uiStore.addNotification('info', `${name} API is ready in the backend. Detailed frontend view coming up next!`)
}

function notifyUpcomingOperation(name) {
  uiStore.addNotification('info', `${name} is configured on the tenant backend. Detailed frontend view coming up next!`)
}

async function handleLogout() {
  await authStore.logout()
  uiStore.addNotification('info', 'You have been signed out successfully.')
  router.push({ name: 'login' })
}
</script>
