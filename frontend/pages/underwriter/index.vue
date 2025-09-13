<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold" id="underwriter-title">Underwriter Portal</h1>
    <p>Manage loan underwriting tasks.</p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: ['auth', 'role'], roles: ['underwriter'] })
useTheme().updatePageTitle('Underwriter Portal')
</script>

