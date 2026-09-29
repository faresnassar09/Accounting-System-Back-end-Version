<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 text-center">
    <div class="max-w-md w-full bg-white p-8 rounded-3xl border border-slate-200 shadow-xl">
      <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 font-extrabold text-2xl">
        404
      </div>
      <h1 class="text-xl font-bold text-slate-900">Page Not Found</h1>
      <p class="text-xs text-slate-500 mt-2">
        The page you are looking for does not exist in the ERP system or has been moved.
      </p>
      <div class="mt-6">
        <router-link
          to="/dashboard"
          class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-md transition"
        >
          Return to Dashboard
        </router-link>
      </div>
    </div>
  </div>
</template>
