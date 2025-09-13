<template>
  <button
    class="focus-ring px-4 py-2 bg-primary-500 text-white rounded"
    :aria-label="label"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
defineProps<{ label: string }>()
</script>

