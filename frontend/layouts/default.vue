<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <AppSidebar
      :is-open="sidebarOpen"
      @close="sidebarOpen = false"
    />
    
    <!-- Main content -->
    <div class="lg:pl-64">
      <!-- Top navigation -->
      <AppTopNav
        @toggle-sidebar="sidebarOpen = !sidebarOpen"
      />
      
      <!-- Page content -->
      <main class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <slot />
        </div>
      </main>
    </div>
    
    <!-- Global notifications -->
    <AppNotifications />
    
    <!-- Global modals -->
    <AppModals />
  </div>
</template>

<script setup lang="ts">
// Sidebar state
const sidebarOpen = ref(false)

// Initialize app
onMounted(() => {
  // Initialize real-time updates
  const { $socket } = useNuxtApp()
  if ($socket) {
    $socket.connect()
  }
})

// Handle keyboard shortcuts
onMounted(() => {
  const handleKeydown = (event: KeyboardEvent) => {
    // Toggle sidebar with Cmd/Ctrl + B
    if ((event.metaKey || event.ctrlKey) && event.key === 'b') {
      event.preventDefault()
      sidebarOpen.value = !sidebarOpen.value
    }
  }
  
  document.addEventListener('keydown', handleKeydown)
  
  onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
  })
})

// Close sidebar on route change
watch(() => useRoute().path, () => {
  sidebarOpen.value = false
})
</script>