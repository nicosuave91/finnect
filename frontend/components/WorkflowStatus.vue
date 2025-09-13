<template>
  <div class="space-y-4">
    <!-- Progress overview -->
    <div class="flex items-center justify-between">
      <div class="flex items-center">
        <div class="h-3 w-3 bg-primary-500 rounded-full mr-3"></div>
        <span class="text-sm font-medium text-gray-900 dark:text-white">
          Workflow Progress
        </span>
      </div>
      <div class="text-sm text-gray-500 dark:text-gray-400">
        {{ summary.completed_steps }} of {{ summary.total_steps }} steps
      </div>
    </div>

    <!-- Progress bar -->
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
      <div
        class="bg-primary-600 h-2 rounded-full transition-all duration-300"
        :style="{ width: `${progressPercentage}%` }"
      ></div>
    </div>

    <!-- Status breakdown -->
    <div class="grid grid-cols-2 gap-4">
      <div class="text-center">
        <div class="text-2xl font-bold text-success-600 dark:text-success-400">
          {{ summary.completed_steps }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
          Completed
        </div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-warning-600 dark:text-warning-400">
          {{ summary.pending_steps }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
          Pending
        </div>
      </div>
    </div>

    <!-- Overdue tasks alert -->
    <div v-if="summary.overdue_steps > 0" class="rounded-md bg-error-50 dark:bg-error-900 p-4">
      <div class="flex">
        <Icon name="heroicons:clock" class="h-5 w-5 text-error-400" />
        <div class="ml-3">
          <h3 class="text-sm font-medium text-error-800 dark:text-error-200">
            Overdue Tasks
          </h3>
          <div class="mt-2 text-sm text-error-700 dark:text-error-300">
            {{ summary.overdue_steps }} workflow steps are overdue and require immediate attention.
          </div>
        </div>
      </div>
    </div>

    <!-- All caught up message -->
    <div v-if="summary.overdue_steps === 0 && summary.pending_steps === 0" class="rounded-md bg-success-50 dark:bg-success-900 p-4">
      <div class="flex">
        <Icon name="heroicons:check-circle" class="h-5 w-5 text-success-400" />
        <div class="ml-3">
          <h3 class="text-sm font-medium text-success-800 dark:text-success-200">
            All Caught Up
          </h3>
          <div class="mt-2 text-sm text-success-700 dark:text-success-300">
            All workflow steps are completed. Great job!
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">

import { computed } from 'vue'
interface WorkflowSummary {
  total_steps: number
  completed_steps: number
  overdue_steps: number
  pending_steps: number

import type { WorkflowSummary } from '~/types'

interface Props {
  summary: WorkflowSummary
}

const props = defineProps<Props>()

// Computed
const progressPercentage = computed(() => {
  if (props.summary.total_steps === 0) return 0
  return Math.round((props.summary.completed_steps / props.summary.total_steps) * 100)
})

</script>

</script>

