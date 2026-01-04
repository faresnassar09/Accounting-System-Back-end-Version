<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto py-8">
      <h1 class="text-2xl font-bold mb-6">📒 General Ledger</h1>

      <form
  @submit.prevent="search"
  class="bg-white shadow rounded-lg p-6 mb-8 flex items-end space-x-8"
>

        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Account</label>

          <div v-if="form.errors['accountId']" class="text-red-600 text-sm mt-1">
              {{ form.errors['accountId'] }}
            </div>

          <Combobox v-model="form.accountId">
            <div class="relative">
              <ComboboxInput
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Select account..."
                @change="query = $event.target.value"
                :display-value="(id) => accounts.find(a => a.id === id)?.name"
              />
              <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
                ▼
              </ComboboxButton>
            </div>

            <ComboboxOptions
              class="mt-1 max-h-60 w-full overflow-auto rounded bg-white shadow-lg border z-10"
            >
              <ComboboxOption
                v-for="account in filteredAccounts"
                :key="account.id"
                :value="account.id"
                class="cursor-pointer px-3 py-2 hover:bg-gray-100"
              >
                {{ account.name }}
              </ComboboxOption>
            </ComboboxOptions>
          </Combobox>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            v-model="form.startDate"
            type="date"
            class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            v-model="form.endDate"
            type="date"
            class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

<div class="mt-5">
  <button
    type="submit"
    class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
  >
    Search
  </button>
</div>

      </form>

      <div v-if="results.length" class="bg-white shadow mt-5 rounded-lg overflow-hidden">
        <table class="min-w-full border-collapse">
          <thead class="bg-gray-100">
            <tr>

              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-b">Created At</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-b">Date</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-b">Reference</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-b">Description</th>
              <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700 border-b">Debit</th>
              <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700 border-b">Credit</th>
              <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700 border-b">Balance</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(entry, index) in results"
              :key="entry.id"
              class="hover:bg-gray-50"
            >

            <td class="px-4 py-2 border-b">
              {{ new Date(entry.journal_entry.date).toLocaleString() }}
            </td>
               
              <td class="px-4 py-2 border-b">
                {{ new Date(entry.journal_entry.date).toLocaleDateString() }}
              </td>
              <td class="px-4 py-2 border-b">{{ entry.journal_entry.reference }}</td>
              <td class="px-4 py-2 border-b">{{ entry.journal_entry.name }}</td>
              <td class="px-4 py-2 border-b text-right text-green-600">{{ entry.debit }}</td>
              <td class="px-4 py-2 border-b text-right text-red-600">{{ entry.credit }}</td>
              <td class="px-4 py-2 border-b text-right font-medium">
                {{
                  entry.running_balance
                }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center text-gray-500 mt-6">
        No entries found for this account & period.
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
} from '@headlessui/vue'
import AppLayout from '../../../Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  accounts: {
    type: Array,
    required: true,
  },
  results: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  accountId: null,
  startDate: null,
  endDate: null,
})

const query = ref('')
const filteredAccounts = computed(() =>
  query.value === ''
    ? props.accounts
    : props.accounts.filter((acc) =>
        acc.name.toLowerCase().includes(query.value.toLowerCase())
      )
)

function search() {
  form.post(route('accounting.general_ledger.get_account_entries'), {
  onSuccess: () => {
    form.reset()
  }
})
}
</script>
  