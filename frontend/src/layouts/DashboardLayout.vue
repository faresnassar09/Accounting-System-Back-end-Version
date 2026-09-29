<template>
  <div class="h-screen flex overflow-hidden bg-slate-50">
    <!-- Sidebar Navigation -->
    <AppSidebar />

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- App Header -->
      <AppHeader />

      <!-- Page Content View -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-7xl mx-auto">
          <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </main>
    </div>

    <!-- Notification Toast Alerts -->
    <NotificationToast />
  </div>
</template>

<script setup>
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import NotificationToast from '@/components/common/NotificationToast.vue'
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease-in-out;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
