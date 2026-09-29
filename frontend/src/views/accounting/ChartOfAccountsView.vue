<template>
  <div class="space-y-6">
    <!-- Header with Refresh & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-900">Chart of Accounts</h2>
        <p class="text-xs text-slate-500 mt-0.5">Hierarchical structure of accounts for {{ uiStore.tenantDomain }}</p>
      </div>

      <div class="flex items-center space-x-3">
        <!-- Search Input -->
        <div class="relative">
          <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Filter accounts..."
            class="pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 w-48 sm:w-64"
          />
        </div>

        <button
          @click="fetchCharts"
          :disabled="isLoading"
          class="inline-flex items-center space-x-2 px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-2xs transition"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" />
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Accounts Tree Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      <!-- Loading State -->
      <div v-if="isLoading" class="p-12 text-center text-slate-500 flex flex-col items-center">
        <Loader2 class="w-8 h-8 text-emerald-500 animate-spin mb-3" />
        <p class="text-sm font-medium">Loading Chart of Accounts from tenant API...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="p-8 text-center text-rose-600">
        <p class="font-bold">Failed to load accounts</p>
        <p class="text-xs mt-1">{{ error }}</p>
        <button
          @click="fetchCharts"
          class="mt-4 px-4 py-2 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700 hover:bg-rose-100"
        >
          Try Again
        </button>
      </div>

      <!-- Accounts Table / Tree List -->
      <div v-else class="divide-y divide-slate-100">
        <div class="bg-slate-50 px-6 py-3 grid grid-cols-12 text-[11px] font-bold uppercase tracking-wider text-slate-500">
          <div class="col-span-3">Account Code & Number</div>
          <div class="col-span-5">Account Name</div>
          <div class="col-span-2">Type / Category</div>
          <div class="col-span-2 text-right">Balance</div>
        </div>

        <div v-if="filteredAccounts.length === 0" class="p-8 text-center text-slate-400 text-sm">
          No accounts found matching your search.
        </div>

        <!-- Render Account Rows -->
        <template v-for="account in filteredAccounts" :key="account.id">
          <div class="px-6 py-3.5 grid grid-cols-12 items-center hover:bg-slate-50/80 transition text-sm">
            <!-- Account Number -->
            <div class="col-span-3 font-mono text-xs font-semibold text-slate-700 flex items-center space-x-2">
              <span class="w-2 h-2 rounded-full" :class="getAccountColor(account.name)"></span>
              <span>{{ account.number }}</span>
            </div>

            <!-- Account Name -->
            <div class="col-span-5 font-semibold text-slate-900 capitalize">
              {{ account.name }}
            </div>

            <!-- Type -->
            <div class="col-span-2 text-xs">
              <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium capitalize text-[11px]">
                {{ account.description || 'Core Account' }}
              </span>
            </div>

            <!-- Calculated Balance -->
            <div class="col-span-2 text-right font-mono font-bold text-slate-800 text-xs">
              ${{ formatCurrency(account.calculated_balance) }}
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useUiStore } from '@/stores/ui'
import accountingApi from '@/api/accounting'
import { Search, RefreshCw, Loader2 } from 'lucide-vue-next'

const uiStore = useUiStore()
const isLoading = ref(true)
const error = ref(null)
const accounts = ref([])
const searchQuery = ref('')

async function fetchCharts() {
  isLoading.value = true
  error.value = null
  try {
    const response = await accountingApi.getCharts()
    if (response && response.success) {
      accounts.value = response.data || []
    } else {
      error.value = response?.message || 'Could not retrieve accounts'
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'API request failed'
  } finally {
    isLoading.value = false
  }
}

const filteredAccounts = computed(() => {
  if (!searchQuery.value) return accounts.value
  const q = searchQuery.value.toLowerCase()
  return accounts.value.filter(
    (acc) =>
      acc.name?.toLowerCase().includes(q) ||
      acc.number?.toString().includes(q) ||
      acc.description?.toLowerCase().includes(q)
  )
})

function formatCurrency(val) {
  const num = parseFloat(val) || 0
  return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function getAccountColor(name = '') {
  const n = name.toLowerCase()
  if (n.includes('asset')) return 'bg-emerald-500'
  if (n.includes('liabilit')) return 'bg-rose-500'
  if (n.includes('equity')) return 'bg-purple-500'
  if (n.includes('revenue')) return 'bg-teal-500'
  if (n.includes('expense')) return 'bg-amber-500'
  return 'bg-blue-500'
}

onMounted(() => {
  fetchCharts()
})
</script>
