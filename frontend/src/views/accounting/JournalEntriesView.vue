<template>
  <div class="space-y-6">
    <!-- Header with Search & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-900">Journal Entries</h2>
        <p class="text-xs text-slate-500 mt-0.5">Double-entry ledger transactions for {{ uiStore.tenantDomain }}</p>
      </div>

      <div class="flex items-center space-x-3">
        <button
          @click="fetchEntries"
          :disabled="isLoading"
          class="inline-flex items-center space-x-2 px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-2xs transition"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" />
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      <!-- Loading State -->
      <div v-if="isLoading" class="p-12 text-center text-slate-500 flex flex-col items-center">
        <Loader2 class="w-8 h-8 text-emerald-500 animate-spin mb-3" />
        <p class="text-sm font-medium">Fetching journal entries...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="p-8 text-center text-rose-600">
        <p class="font-bold">Failed to load journal entries</p>
        <p class="text-xs mt-1">{{ error }}</p>
        <button
          @click="fetchEntries"
          class="mt-4 px-4 py-2 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700 hover:bg-rose-100"
        >
          Try Again
        </button>
      </div>

      <!-- Entries Table -->
      <div v-else class="divide-y divide-slate-100 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
            <tr>
              <th class="px-6 py-3">Reference</th>
              <th class="px-6 py-3">Date</th>
              <th class="px-6 py-3">Description</th>
              <th class="px-6 py-3">Status</th>
              <th class="px-6 py-3 text-right">Debit ($)</th>
              <th class="px-6 py-3 text-right">Credit ($)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="entries.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                No journal entries recorded in this tenant yet.
              </td>
            </tr>
            <tr
              v-for="entry in entries"
              :key="entry.id"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="px-6 py-3.5 font-mono text-xs font-bold text-slate-900">
                {{ entry.reference || `#JE-${entry.id}` }}
              </td>
              <td class="px-6 py-3.5 text-xs text-slate-600">
                {{ entry.entry_date || entry.date || 'N/A' }}
              </td>
              <td class="px-6 py-3.5 text-xs text-slate-800 max-w-xs truncate">
                {{ entry.description || 'General Journal Entry' }}
              </td>
              <td class="px-6 py-3.5 text-xs">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                  :class="entry.status === 'reversed' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                >
                  {{ entry.status || 'posted' }}
                </span>
              </td>
              <td class="px-6 py-3.5 font-mono text-xs font-semibold text-right text-slate-900">
                ${{ formatCurrency(entry.total_debit || entry.debit) }}
              </td>
              <td class="px-6 py-3.5 font-mono text-xs font-semibold text-right text-slate-900">
                ${{ formatCurrency(entry.total_credit || entry.credit) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useUiStore } from '@/stores/ui'
import accountingApi from '@/api/accounting'
import { RefreshCw, Loader2 } from 'lucide-vue-next'

const uiStore = useUiStore()
const isLoading = ref(true)
const error = ref(null)
const entries = ref([])

async function fetchEntries() {
  isLoading.value = true
  error.value = null
  try {
    const response = await accountingApi.getJournalEntries()
    if (response && response.success) {
      // response.data can be an array or paginated object
      const data = response.data
      entries.value = Array.isArray(data) ? data : (data.data || [])
    } else {
      error.value = response?.message || 'Could not load journal entries'
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'API request failed'
  } finally {
    isLoading.value = false
  }
}

function formatCurrency(val) {
  const num = parseFloat(val) || 0
  return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

onMounted(() => {
  fetchEntries()
})
</script>
