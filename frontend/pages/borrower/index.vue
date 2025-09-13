<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold" id="borrower-title">Borrower Portal</h1>
    <p>Welcome to your loan dashboard.</p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: ['auth', 'role'], roles: ['borrower'] })
useTheme().updatePageTitle('Borrower Portal')
</script>

