<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto bg-white shadow-md rounded-xl p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-800">Entry Journal</h1>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b pb-4">
          <div>
            <label for="reference" class="block text-sm font-medium text-gray-700">Reference</label>
            <div v-if="form.errors['header.reference']" class="text-red-600 text-sm mt-1">
              {{ form.errors['header.reference'] }}
            </div>
            <input required="1" v-model="form.header.reference" type="text"
            placeholder="Enter A Reference"
              class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          </div>

          <div>
            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
            <div v-if="form.errors['header.date']" class="text-red-600 text-sm mt-1">
              {{ form.errors['header.date'] }}
            </div>

            <h2>


            </h2>

            <input v-model="form.header.date" type="date"
              class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">



          </div>

          <div class="md:col-span-3">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <div v-if="form.errors['header.description']" class="text-red-600 text-sm mt-1">
              {{ form.errors['header.description'] }}
            </div>
            <input v-model="form.header.description" type="text"
            placeholder="Enter The Description of The Entry"
              class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          </div>
        </div>

        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-3">Journal Entries</h2>
          <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
            <thead class="bg-gray-100">
              <tr>
                <th required min="1" class="px-4 py-2 text-left text-sm font-medium text-gray-600">Account</th>
                <th required min="1" class="px-4 py-2 text-right text-sm font-medium text-gray-600">Debit</th>
                <th required min="1" class="px-4 py-2 text-right text-sm font-medium text-gray-600">Credit</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(line, index) in form.lines" :key="index" class="border-t">
                <td class="px-4 py-2">
                  <select v-model="line.account_id" required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option disabled selected>Chose An Account</option>
                    <option v-for="account in accounts" :key="account.id" :value="account.id">
                      {{ account.name }}
                    </option>
                  </select>
                  <div v-if="form.errors[`lines.${index}.account_id`]" class="text-red-600 text-sm mt-1">
                    {{ form.errors[`lines.${index}.account_id`] }}
                  </div>
                </td>
                <td class="px-4 py-2 text-right">
                  <input v-model.number="line.debit" type="number" placeholder="Enter The Debit Amount"
                    class="w-full text-right rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">


                  <div v-if="form.errors[`lines.${index}.debit`]" class="text-red-600 text-sm mt-1">
                    {{ form.errors[`lines.${index}.debit`] }}
                  </div>

                </td>

                <td class="px-4 py-2 text-right">
                  <input v-model.number="line.credit" type="number" placeholder="Enter The Credit Amount"
                    class="w-full text-right rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                  <div v-if="form.errors[`lines.${index}.credit`]" class="text-red-600 text-sm mt-1">
                    {{ form.errors[`lines.${index}.credit`] }}
                  </div>
                </td>

                <td class="px-4 py-2 text-right">
                  <button @click="removeLine(index)"
                    class="w-full  text-white rounded-lg shadow-sm bg-red-600 focus:border-indigo-500 focus:ring-indigo-500">
                    remove
                  </button>
                </td>
              </tr>
            </tbody>
          </table>


          <button type="button" @click="addLine"
            class="mt-3 inline-flex items-center px-4 py-2 bg-green-500 text-black text-sm font-medium rounded-lg shadow hover:bg-green-600">
            Add Line +
          </button>
        </div>

        <div class="flex justify-between items-center mt-4 border-t pt-4">
          <div>
            <span class="font-semibold text-gray-700">Total Debit:</span>
            <span :class="{ 'text-red-600': !isBalanced }">{{ totalDebit }}</span>
          </div>
          <div>
            <span class="font-semibold text-gray-700">Total Credit:</span>
            <span :class="{ 'text-red-600': !isBalanced }">{{ totalCredit }}</span>
          </div>
        </div>

        <div v-if="!isBalanced" class="text-red-600 text-sm">
          ⚠️ Debit and Credit must be equal before saving.
        </div>

        <div class="flex justify-end">

          <button type="submit" :disabled="!isBalanced"
            class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
            Save
          </button>
        </div>
      </form>

    </div>



  </AppLayout>
</template>

<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

defineProps({
  accounts: Object,
})



const form = useForm({
  header: {
    date: null,
    reference: '',
    description: '',
  },

  lines: [
    {
      account_id: null,
      debit: null,
      credit: null,
    },
  ],
})

function addLine() {
  form.lines.push({ account_id: null, debit: 0, credit: 0 })
}
const totalDebit = computed(() =>
  form.lines.reduce((sum, line) => sum + (Number(line.debit) || 0), 0)
)
const totalCredit = computed(() =>
  form.lines.reduce((sum, line) => sum + (Number(line.credit) || 0), 0)
)

const isBalanced = computed(() => totalDebit.value === totalCredit.value)

function removeLine(index) {

  form.lines.splice(index, 1)
}


function submit() {
  if (!isBalanced.value) return
  form.post(route('accounting.journal_entries.store'), {


    onSuccess: () => {

      form.reset()
      alert('The Entry Saved Successfully')
    },
    

  })
}


</script>