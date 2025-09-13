<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold" id="investor-title">Investor Portal</h1>
    <p>View loan packages available for investment.</p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: ['auth', 'role'], roles: ['investor'] })
useTheme().updatePageTitle('Investor Portal')
</script>

