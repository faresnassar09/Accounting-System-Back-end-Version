<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col space-y-2 pointer-events-none">
    <div
      v-for="item in uiStore.notifications"
      :key="item.id"
      class="pointer-events-auto flex items-center p-4 rounded-xl shadow-lg border transition-all duration-300 max-w-md"
      :class="{
        'bg-emerald-50 border-emerald-200 text-emerald-800': item.type === 'success',
        'bg-rose-50 border-rose-200 text-rose-800': item.type === 'error',
        'bg-sky-50 border-sky-200 text-sky-800': item.type === 'info',
        'bg-amber-50 border-amber-200 text-amber-800': item.type === 'warning'
      }"
    >
      <div class="mr-3 flex-shrink-0">
        <component :is="getIcon(item.type)" class="w-5 h-5" />
      </div>
      <div class="text-sm font-medium flex-1">
        {{ item.message }}
      </div>
      <button
        @click="uiStore.removeNotification(item.id)"
        class="ml-3 text-slate-400 hover:text-slate-600 transition"
      >
        <X class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { useUiStore } from '@/stores/ui'
import { CheckCircle2, AlertCircle, Info, AlertTriangle, X } from 'lucide-vue-next'

const uiStore = useUiStore()

const getIcon = (type) => {
  switch (type) {
    case 'success':
      return CheckCircle2
    case 'error':
      return AlertCircle
    case 'warning':
      return AlertTriangle
    default:
      return Info
  }
}
</script>
