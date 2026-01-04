<template>
  <aside class="hidden lg:block w-64 bg-white text-black border-r border-white h-screen overflow-y-auto">
    <div class="p-4">
      <div class="text-xl font-semibold mb-6 flex items-center space-x-2">
        <svg class="h-8 w-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6.253v13m0-13C10.832 5.433 9.475 5 8 5c-4 0-5.908 5.73-3.66 10.153C6.388 18.067 8.52 19 12 19c3.48 0 5.612-.933 7.66-3.847C20.66 10.73 20 5 16 5c-1.475 0-2.832.433-4 1.253z">
          </path>
        </svg>
        <span class="truncate max-w-[10rem] block">
          Hello Accountant ,{{$page.props.auth.user.name.split(' ')[0]}}
        </span>
      </div>
      <nav class="space-y-2">
        <a href="/dashboard" class="flex items-center p-3 rounded-lg hover:bg-gray-300 transition duration-150">
          <span>Overview</span>
        </a>

        <div v-for="(section, index) in sections" :key="index" class="relative">
          <button @click="toggleSection(index)"
            class="flex items-center justify-between w-full p-3 rounded-lg hover:bg-gray-300 transition duration-150 focus:outline-none">
            <span class="flex items-center space-x-2">
              <span v-html="section.icon" class="w-5 h-5 text-gray-600"></span>
              <span>{{ section.name }}</span>
            </span>
            <svg class="h-4 w-4 transform transition-transform duration-200"
              :class="{ 'rotate-180': section.open }" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"></path>
            </svg>
          </button>
          <div v-show="section.open" class="mt-1 space-y-1">
            <router-link  v-for="(item, idx) in section.items" :key="idx"   :to="{ name: item.route }"
              class="block py-2 px-6 rounded-lg hover:bg-gray-300 transition duration-150 text-sm">
                {{ item.icon }} {{ item.name }}
            </router-link >
          </div>
        </div>
      </nav>
    </div>
  </aside>
</template>

<script setup>
import { reactive } from 'vue'

const sections = reactive([
  {
    name: 'Core Accounting',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h13v6M9 11V5h13v6m-6 11H5V5h4"/>
           </svg>`,
    open: false,
    items: [
      { name: 'Chart of Accounts', route: 'accounting.chart.view', icon: '📊' },
      { name: 'Journal Entries', route: 'accounting.journal_entries.create', icon: '📝' },
      { name: 'General Ledger', route: 'accounting.general_ledger.view', icon: '📒' },
      { name: 'Trial Balance', route: 'accounting.trial_balance.index', icon: '⚖️' },
      // { name: 'Financial Statements', route: '/accounting/financial-statements', icon: '💹' },
      // { name: 'Taxes', route: '/accounting/taxes', icon: '💰' },
    ]
  },
  // {
  //   name: 'Accounts Management',
  //   icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  //            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
  //          </svg>`,
  //   open: false,
  //   items: [
  //     { name: 'Accounts Receivable', route: '/accounting/accounts-receivable', icon: '💵' },
  //     { name: 'Accounts Payable', route: '/accounting/accounts-payable', icon: '💳' },
  //     { name: 'Bank Reconciliation', route: '/accounting/bank-reconciliation', icon: '🏦' },
  //     { name: 'Cash & Bank', route: '/accounting/cash-bank', icon: '💲' },
  //   ]
  // },
  // {
  //   name: 'Assets & Expenses',
  //   icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  //            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.1 0-2 .9-2 2h2v2h-2v2h2a2 2 0 100-4h-2"/>
  //          </svg>`,
  //   open: false,
  //   items: [
  //     { name: 'Fixed Assets', route: '/accounting/fixed-assets', icon: '🏗️' },
  //     { name: 'Depreciation', route: '/accounting/depreciation', icon: '📉' },
  //     { name: 'Expense Management', route: '/accounting/expenses', icon: '💸' },
  //     { name: 'Cost Accounting', route: '/accounting/cost-accounting', icon: '📊' },
  //   ]
  // },
  // {
  //   name: 'Reports & Analysis',
  //   icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  //            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 11V3a1 1 0 112 0v8h-2zM5.293 9.707a1 1 0 011.414 0L12 15.586l5.293-5.879a1 1 0 111.414 1.415l-6 6.667a1 1 0 01-1.414 0l-6-6.667a1 1 0 010-1.415z"/>
  //          </svg>`,
  //   open: false,
  //   items: [
  //     { name: 'Aged Receivables', route: '/accounting/reports/aged-receivables', icon: '📆' },
  //     { name: 'Aged Payables', route: '/accounting/reports/aged-payables', icon: '⏳' },
  //     { name: 'Budgeting & Forecasting', route: '/accounting/reports/budgeting', icon: '📈' },
  //     { name: 'Financial Ratios', route: '/accounting/reports/ratios', icon: '📊' },
  //   ]
  // },
// ]
])

function toggleSection(index) {
  sections[index].open = !sections[index].open
}
</script>
