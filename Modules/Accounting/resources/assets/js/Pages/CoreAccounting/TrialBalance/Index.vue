<template>
    <AppLayout>
      <div class="max-w-6xl mx-auto bg-white shadow-xl rounded-2xl p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">
          Trial Balance
        </h1>
  
        <div class="overflow-x-auto">
          <table class="min-w-full border-collapse">
            <thead>
              <tr class="bg-gray-100 text-gray-700 text-sm uppercase">
                <th class="px-4 py-3 text-left">Account Name</th>
                <th class="px-4 py-3 text-right">Initial Balance</th>
                <th class="px-4 py-3 text-right">Debit</th>
                <th class="px-4 py-3 text-right">Credit</th>
                <th class="px-4 py-3 text-right">Final Balance</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="account in results"
                :key="account.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-4 py-3 font-medium text-gray-800">
                  {{ account.name }}
                </td>
                <td class="px-4 py-3 text-right text-gray-700">
                  {{ formatCurrency(account.initial_balance) }}
                </td>
                <td class="px-4 py-3 text-right text-green-600 font-semibold">
                  {{ formatCurrency(account.account_entry_lines_sum_debit) }}
                </td>
                <td class="px-4 py-3 text-right text-red-600 font-semibold">
                  {{ formatCurrency(account.account_entry_lines_sum_credit) }}
                </td>
                <td class="px-4 py-3 text-right text-green-600 font-semibold">
                  {{ formatCurrency(account.balance) }}
                </td>
              </tr>
            </tbody>
  
            <tfoot>
              <tr class="bg-gray-200 text-gray-800 font-bold">
                <td class="px-4 py-3 text-left">Total</td>
                <td class="px-4 py-3 text-right">—</td>
                <td class="px-4 py-3 text-right">
                  {{ formatCurrency(totalDebit) }}
                </td>
                <td class="px-4 py-3 text-right">
                  {{ formatCurrency(totalCredit) }}
                </td>
                <td
                  class="px-4 py-3 text-right"
                  :class="{
                    'text-green-600': totalBalance >= 0,
                    'text-red-600': totalBalance < 0
                  }"
                >
                  {{ formatCurrency(totalBalance) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { computed } from "vue";
  import AppLayout from "../../../Layouts/AppLayout.vue";
  
  const props = defineProps({
    results: Array,
  });
  
  
  const totalDebit = computed(() =>
    props.results.reduce(
      (sum, a) => sum + parseFloat(a.account_entry_lines_sum_debit || 0),
      0
    )
  );
  
  const totalCredit = computed(() =>
    props.results.reduce(
      (sum, a) => sum + parseFloat(a.account_entry_lines_sum_credit || 0),
      0
    )
  );
  
  const totalBalance = computed(() =>
  props.results.reduce((sum, a) => sum + parseFloat(a.balance || 0), 0)
);

  
  const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
      minimumFractionDigits: 2,
    }).format(value || 0);
  };
  </script>
  