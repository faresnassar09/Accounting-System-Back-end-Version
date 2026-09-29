<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Subtle Background Glows -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <!-- Brand Logo & Title -->
      <div class="flex justify-center">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-xl ring-4 ring-emerald-500/20">
          <Wallet class="w-8 h-8" />
        </div>
      </div>
      <h2 class="mt-4 text-center text-2xl font-extrabold text-white tracking-tight">
        E-Wallet ERP
      </h2>
      <p class="mt-1 text-center text-sm text-slate-400">
        Enterprise Double-Entry Accounting
      </p>

      <!-- Tenant Badge -->
      <div class="mt-3 flex justify-center">
        <div class="inline-flex items-center space-x-2 px-3 py-1 bg-slate-800/80 border border-slate-700/80 rounded-full text-xs text-slate-300 shadow-inner">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span class="text-slate-400">Tenant:</span>
          <span class="font-mono font-semibold text-emerald-400">{{ uiStore.tenantDomain }}</span>
        </div>
      </div>
    </div>

    <!-- Login Card -->
    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
      <div class="bg-white/95 backdrop-blur-md py-8 px-6 sm:px-10 shadow-2xl rounded-3xl border border-slate-200">
        <!-- Error Alert -->
        <div
          v-if="errorMessage"
          class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start space-x-3"
        >
          <AlertCircle class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" />
          <div class="flex-1">
            <p class="font-semibold">Authentication Error</p>
            <p class="text-xs text-rose-700 mt-0.5">{{ errorMessage }}</p>
          </div>
        </div>

        <form class="space-y-5" @submit.prevent="handleSubmit">
          <!-- Email Input -->
          <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
              Email Address
            </label>
            <div class="relative rounded-xl shadow-xs">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <Mail class="w-4 h-4" />
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                placeholder="name@company.com"
                class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
              />
            </div>
          </div>

          <!-- Password Input -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                Password
              </label>
            </div>
            <div class="relative rounded-xl shadow-xs">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <Lock class="w-4 h-4" />
              </div>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition"
              >
                <EyeOff v-if="showPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="pt-2">
            <button
              type="submit"
              :disabled="authStore.isLoading"
              class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <Loader2 v-if="authStore.isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" />
              <span>{{ authStore.isLoading ? 'Authenticating...' : 'Sign In to ERP' }}</span>
            </button>
          </div>
        </form>

        <!-- Pre-fill helper badge for quick access -->
        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
          <span>Tenant user demo:</span>
          <button
            type="button"
            @click="prefillCredentials"
            class="font-medium text-emerald-600 hover:text-emerald-700 underline focus:outline-none"
          >
            Auto-fill fares@gmail.com
          </button>
        </div>

        <!-- Strict Account Provisioning Notice -->
        <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-600 flex items-start space-x-2">
          <ShieldAlert class="w-4 h-4 text-slate-500 flex-shrink-0 mt-0.5" />
          <p class="leading-relaxed">
            <strong class="text-slate-800">Tenant Security Notice:</strong> Public account registration is disabled. User accounts and roles are provisioned exclusively through the Filament Admin Console.
          </p>
        </div>
      </div>
    </div>

    <!-- Notification Toast Alerts -->
    <NotificationToast />
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import NotificationToast from '@/components/common/NotificationToast.vue'
import {
  Wallet,
  Mail,
  Lock,
  Eye,
  EyeOff,
  AlertCircle,
  Loader2,
  ShieldAlert,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()

const showPassword = ref(false)
const errorMessage = ref('')

const form = reactive({
  email: '',
  password: '',
})

function prefillCredentials() {
  form.email = 'fares@gmail.com'
  form.password = '00000000'
}

async function handleSubmit() {
  errorMessage.value = ''

  const result = await authStore.login({
    email: form.email,
    password: form.password,
  })

  if (result.success) {
    uiStore.addNotification('success', `Welcome back, ${authStore.userName}!`)
    router.push({ name: 'dashboard' })
  } else {
    errorMessage.value = result.message || 'Invalid credentials or user does not exist in this tenant.'
  }
}
</script>
