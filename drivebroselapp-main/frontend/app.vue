<template>
  <div>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
// Global app setup
useHead({
  titleTemplate: '%s - Finnect',
  meta: [
    { name: 'description', content: 'Comprehensive mortgage broker-dealer platform' }
  ]
})

// Initialize app
onMounted(() => {
  // Initialize theme
  const colorMode = useColorMode()
  
  // Initialize auth state
  const authStore = useAuthStore()
  authStore.initialize()
  
  // Initialize tenant context
  const tenantStore = useTenantStore()
  tenantStore.initialize()
})
</script>

<style>
/* Global styles */
html {
  font-family: 'Inter', system-ui, sans-serif;
}

body {
  @apply bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  @apply bg-gray-100 dark:bg-gray-800;
}

::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-400 dark:bg-gray-500;
}

/* Focus styles */
.focus-ring {
  @apply focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900;
}

/* Animation utilities */
.animate-fade-in {
  animation: fadeIn 0.5s ease-in-out;
}

.animate-slide-in {
  animation: slideIn 0.3s ease-out;
}

.animate-bounce-in {
  animation: bounceIn 0.6s ease-out;
}

/* Print styles */
@media print {
  .no-print {
    display: none !important;
  }
}
</style>