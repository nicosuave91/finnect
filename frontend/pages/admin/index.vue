<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold" id="admin-title">Admin Portal</h1>
    <p>Administration tools and tenant management.</p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: ['auth', 'role'], roles: ['admin'] })
useTheme().updatePageTitle('Admin Portal')
</script>

