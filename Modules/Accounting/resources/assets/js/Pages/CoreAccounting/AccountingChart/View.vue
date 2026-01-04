<template>
  <AppLayout title="Chart Of Accounting">
    <div class="p-4">

      <div class="mb-4 max-w-2xl mx-auto">
        <label for="">search</label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search on Account"
          class="w-full p-3 border rounded-lg shadow-sm focus:outline-none"
        />
      </div>

      <div class="overflow-x-auto">
        <AccountsTree :accounts="accounts" :search="searchQuery" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue'
import AccountsTree from './AccountsTree.vue'
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const { props } = usePage()
const accounts = computed(() => props.accounts ?? [])
const searchQuery = ref()
</script>

