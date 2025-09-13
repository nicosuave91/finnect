<template>
  <div class="card">
    <div class="card-body">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div
            :class="[
              'h-8 w-8 rounded-lg flex items-center justify-center',
              isError ? 'bg-error-100 dark:bg-error-900' : 'bg-primary-100 dark:bg-primary-900'
            ]"
          >
            <Icon
              :name="icon"
              :class="[
                'h-5 w-5',
                isError ? 'text-error-600 dark:text-error-400' : 'text-primary-600 dark:text-primary-400'
              ]"
            />
          </div>
        </div>
        <div class="ml-4 flex-1">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
            {{ title }}
          </p>
          <p
            :class="[
              'text-2xl font-semibold',
              isError ? 'text-error-600 dark:text-error-400' : 'text-gray-900 dark:text-white'
            ]"
          >
            {{ value }}
          </p>
        </div>
        <div v-if="change !== undefined" class="flex-shrink-0">
          <div
            :class="[
              'flex items-center text-sm font-medium',
              changeType === 'increase' ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400'
            ]"
          >
            <Icon
              :name="changeType === 'increase' ? 'heroicons:arrow-trending-up' : 'heroicons:arrow-trending-down'"
              class="h-4 w-4 mr-1"
            />
            {{ Math.abs(change) }}%
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title: string
  value: string | number
  change?: number
  changeType?: 'increase' | 'decrease'
  icon: string
  isError?: boolean
}

defineProps<Props>()
</script>