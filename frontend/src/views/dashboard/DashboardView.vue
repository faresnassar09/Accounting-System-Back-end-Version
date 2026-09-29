<template>
  <div class="space-y-8">
    <!-- Welcome Header Banner -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-slate-800">
      <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold mb-3 border border-emerald-500/30">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>Tenant Active: {{ uiStore.tenantDomain }}</span>
          </div>
          <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Welcome back, {{ authStore.userName }}
          </h2>
          <p class="text-slate-300 text-sm mt-1 max-w-xl">
            You are signed in with the <span class="text-emerald-400 font-semibold capitalize">{{ authStore.userRole }}</span> role. Manage your general ledger, chart of accounts, and financial statements below.
          </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex items-center gap-3">
          <router-link
            to="/accounting/charts"
            class="inline-flex items-center space-x-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-semibold transition shadow-md"
          >
            <FolderTree class="w-4 h-4" />
            <span>Chart of Accounts</span>
          </router-link>

          <router-link
            to="/accounting/journal-entries"
            class="inline-flex items-center space-x-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-sm font-semibold transition border border-slate-700"
          >
            <BookOpenCheck class="w-4 h-4" />
            <span>Journal Entries</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Tenant & System Diagnostics Status Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Tenant Status Card -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center space-x-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
          <Building2 class="w-6 h-6" />
        </div>
        <div class="min-w-0">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tenant Domain</p>
          <p class="text-base font-bold text-slate-900 truncate">{{ uiStore.tenantDomain }}</p>
          <p class="text-xs text-emerald-600 font-medium mt-0.5">Database Isolated</p>
        </div>
      </div>

      <!-- Current User Role -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center space-x-4">
        <div class="w-12 h-12 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600 flex-shrink-0">
          <ShieldCheck class="w-6 h-6" />
        </div>
        <div class="min-w-0">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">RBAC Role</p>
          <p class="text-base font-bold text-slate-900 capitalize truncate">{{ authStore.userRole }}</p>
          <p class="text-xs text-sky-600 font-medium mt-0.5">Passport OAuth2 Authenticated</p>
        </div>
      </div>

      <!-- Live API Heartbeat Test -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 flex-shrink-0">
            <Activity class="w-6 h-6" />
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Backend API</p>
            <p class="text-sm font-bold text-slate-900">
              <span v-if="apiLatency !== null" class="text-emerald-600">{{ apiLatency }}ms latency</span>
              <span v-else class="text-slate-400">Untested</span>
            </p>
          </div>
        </div>
        <button
          @click="testApiConnection"
          :disabled="isPinging"
          class="p-2 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
          title="Ping backend"
        >
          <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isPinging }" />
        </button>
      </div>
    </div>

    <!-- Quick Navigation Modules Grid -->
    <div>
      <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center space-x-2">
        <span>Accounting & ERP Modules</span>
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <router-link
          v-for="item in moduleCards"
          :key="item.title"
          :to="item.to"
          class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs hover:shadow-md hover:border-emerald-300 transition-all duration-200 group flex flex-col justify-between"
        >
          <div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4 transition-transform group-hover:scale-105" :class="item.bgClass">
              <component :is="item.icon" class="w-5 h-5" :class="item.iconClass" />
            </div>
            <h4 class="font-bold text-slate-900 text-base group-hover:text-emerald-600 transition">
              {{ item.title }}
            </h4>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
              {{ item.description }}
            </p>
          </div>

          <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-600">
            <span>{{ item.actionText }}</span>
            <ArrowRight class="w-4 h-4 transform group-hover:translate-x-1 transition" />
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import accountingApi from '@/api/accounting'
import {
  FolderTree,
  BookOpenCheck,
  Building2,
  ShieldCheck,
  Activity,
  RefreshCw,
  Scale,
  FileSpreadsheet,
  Coins,
  Repeat,
  Landmark,
  ArrowRight,
} from 'lucide-vue-next'

const authStore = useAuthStore()
const uiStore = useUiStore()

const isPinging = ref(false)
const apiLatency = ref(null)

const moduleCards = [
  {
    title: 'Chart of Accounts',
    description: 'Hierarchical tree of Assets, Liabilities, Equity, Revenue, and Expense accounts.',
    to: '/accounting/charts',
    actionText: 'View Accounts Tree',
    icon: FolderTree,
    bgClass: 'bg-emerald-50 text-emerald-600',
    iconClass: 'text-emerald-600',
  },
  {
    title: 'Journal Entries',
    description: 'Double-entry transaction records, audit status, and reversal history.',
    to: '/accounting/journal-entries',
    actionText: 'Browse Entries',
    icon: BookOpenCheck,
    bgClass: 'bg-blue-50 text-blue-600',
    iconClass: 'text-blue-600',
  },
  {
    title: 'Trial Balance',
    description: 'Live debit and credit totals verifying double-entry equilibrium across all accounts.',
    to: '/accounting/charts',
    actionText: 'View Statements',
    icon: Scale,
    bgClass: 'bg-indigo-50 text-indigo-600',
    iconClass: 'text-indigo-600',
  },
  {
    title: 'Balance Sheet & P&L',
    description: 'Real-time financial position and profit & loss statements.',
    to: '/accounting/charts',
    actionText: 'Generate Report',
    icon: FileSpreadsheet,
    bgClass: 'bg-amber-50 text-amber-600',
    iconClass: 'text-amber-600',
  },
  {
    title: 'Fixed Assets',
    description: 'Asset depreciation schedules (straight-line & declining-balance) and disposal tracking.',
    to: '/dashboard',
    actionText: 'Manage Assets',
    icon: Coins,
    bgClass: 'bg-teal-50 text-teal-600',
    iconClass: 'text-teal-600',
  },
  {
    title: 'Bank Reconciliation',
    description: 'Statement import, auto-match algorithms, and discrepancy adjustments.',
    to: '/dashboard',
    actionText: 'Reconcile',
    icon: Landmark,
    bgClass: 'bg-rose-50 text-rose-600',
    iconClass: 'text-rose-600',
  },
]

async function testApiConnection() {
  isPinging.value = true
  const startTime = performance.now()
  try {
    await accountingApi.getCharts()
    const endTime = performance.now()
    apiLatency.value = Math.round(endTime - startTime)
    uiStore.addNotification('success', `Backend API responded in ${apiLatency.value}ms`)
  } catch (error) {
    uiStore.addNotification('error', 'Backend API failed to respond. Check server status.')
  } finally {
    isPinging.value = false
  }
}

onMounted(() => {
  testApiConnection()
})
</script>
