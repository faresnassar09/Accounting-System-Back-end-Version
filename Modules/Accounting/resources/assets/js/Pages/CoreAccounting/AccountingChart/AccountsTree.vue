<template>
  <div class="tree text-center">
    <ul>
      <li v-for="account in displayedAccounts" :key="account.id">
        <div 
          class="node inline-flex items-center space-x-2 px-4 py-2 bg-white rounded-xl shadow cursor-pointer hover:bg-gray-100 transition"
          @click.stop="toggle(account.id)"
        >
          <span class="font-semibold text-gray-800">{{ account.name }}</span>
          <span class="text-gray-500 text-sm">({{ account.number }})</span>
          <span class="ml-2 text-green-600 font-bold">
            {{ formatCurrency(account.initial_balance) }}
          </span>

          <span v-if="account.sub_accounts?.length && !searchQuery" class="ml-2 text-xs text-blue-500">
            [{{ isOpen(account.id) ? '-' : '+' }}]
          </span>
        </div>

        <ul v-if="account.sub_accounts?.length && isOpen(account.id) && !searchQuery">
          <AccountsTree :accounts="account.sub_accounts" :search="searchQuery" />
        </ul>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  accounts: { type: Array, required: true },
  search: { type: String }
})

const openAccounts = ref([])

const searchQuery = computed(() => props.search || '')

function toggle(id) {
  if (openAccounts.value.includes(id)) {
    openAccounts.value = openAccounts.value.filter(openId => openId !== id)
  } else {
    openAccounts.value.push(id)
  }
}

function isOpen(id) {
  return openAccounts.value.includes(id)
}

function formatCurrency(value) {
  if (!value) return '0.00'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value)
}

function findAccountsByQuery(accounts, query) {
  const lower = query.toLowerCase()
  const result = []

  for (const acc of accounts) {
    if (acc.name.toLowerCase().includes(lower)) {
      result.push({ ...acc, sub_accounts: [] })
    }
    if (acc.sub_accounts?.length) {
      result.push(...findAccountsByQuery(acc.sub_accounts, query))
    }
  }

  return result
}

const displayedAccounts = computed(() => {
  if (!searchQuery.value) return props.accounts
  return findAccountsByQuery(props.accounts, searchQuery.value)
})
</script>



<style scoped>
.tree ul {
  padding-left: 5rem;
  display: block; 
}

      .tree li { list-style-type: none;
       position: relative; padding: 30px 10px 0 0; text-align: center; }

.tree li::before, .tree li::after
         { content: ''; position: absolute; top: 0; right: 50%; border-top: 1px solid #ccc; width: 50%; height: 20px; } .tree li::after { right: auto; left: 50%; border-left: 1px solid #ccc; } .tree li:only-child::before, .tree li:only-child::after
          { display: none; } .tree li:only-child { padding-top: 5; }
           .tree li:first-child::before, .tree li:last-child::after { border: 0 none; }
            .tree li:last-child::before { border-right: 1px solid #ccc; border-radius: 0 5px 0 0; }
            .tree li:first-child::after { border-radius: 5px 0 0 0; } .node { border: 1px solid #ccc; background: #fff; display: inline-block; min-width: 120px; }
            
            
            </style>
