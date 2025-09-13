<template>
  <div class="space-y-4">
    <!-- Compliance status -->
    <div class="flex items-center justify-between">
      <div class="flex items-center">
        <div
          :class="[
            'h-3 w-3 rounded-full mr-3',
            summary.is_compliant ? 'bg-success-500' : 'bg-error-500'
          ]"
        ></div>
        <span class="text-sm font-medium text-gray-900 dark:text-white">
          {{ summary.is_compliant ? 'Compliant' : 'Non-Compliant' }}
        </span>
      </div>
      <div class="text-sm text-gray-500 dark:text-gray-400">
        {{ summary.total_violations }} violations
      </div>
    </div>

    <!-- Violations breakdown -->
    <div v-if="summary.total_violations > 0" class="space-y-3">
      <div class="text-sm font-medium text-gray-900 dark:text-white">
        Violations by Regulation:
      </div>
      
      <div class="space-y-2">
        <div
          v-for="(count, regulation) in summary.violations_by_regulation"
          :key="regulation"
          class="flex items-center justify-between"
        >
          <div class="flex items-center">
            <div class="h-2 w-2 bg-error-500 rounded-full mr-2"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">
              {{ getRegulationName(regulation) }}
            </span>
          </div>
          <span class="text-sm font-medium text-error-600 dark:text-error-400">
            {{ count }}
          </span>
        </div>
      </div>
    </div>

    <!-- Critical violations alert -->
    <div v-if="summary.critical_violations > 0" class="rounded-md bg-error-50 dark:bg-error-900 p-4">
      <div class="flex">
        <Icon name="heroicons:exclamation-triangle" class="h-5 w-5 text-error-400" />
        <div class="ml-3">
          <h3 class="text-sm font-medium text-error-800 dark:text-error-200">
            Critical Violations
          </h3>
          <div class="mt-2 text-sm text-error-700 dark:text-error-300">
            {{ summary.critical_violations }} critical compliance violations require immediate attention.
          </div>
        </div>
      </div>
    </div>

    <!-- High violations alert -->
    <div v-if="summary.high_violations > 0" class="rounded-md bg-warning-50 dark:bg-warning-900 p-4">
      <div class="flex">
        <Icon name="heroicons:exclamation-triangle" class="h-5 w-5 text-warning-400" />
        <div class="ml-3">
          <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
            High Priority Violations
          </h3>
          <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
            {{ summary.high_violations }} high priority violations need to be addressed.
          </div>
        </div>
      </div>
    </div>

    <!-- All clear message -->
    <div v-if="summary.total_violations === 0" class="rounded-md bg-success-50 dark:bg-success-900 p-4">
      <div class="flex">
        <Icon name="heroicons:check-circle" class="h-5 w-5 text-success-400" />
        <div class="ml-3">
          <h3 class="text-sm font-medium text-success-800 dark:text-success-200">
            All Clear
          </h3>
          <div class="mt-2 text-sm text-success-700 dark:text-success-300">
            No compliance violations detected. All regulations are being followed correctly.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ComplianceSummary } from '~/types'

interface Props {
  summary: ComplianceSummary
}

defineProps<Props>()

// Methods
const getRegulationName = (regulation: string) => {
  const names = {
    TRID: 'TRID (TILA-RESPA)',
    ECOA: 'ECOA (Equal Credit Opportunity)',
    RESPA: 'RESPA (Real Estate Settlement Procedures)',
    GLBA: 'GLBA (Gramm-Leach-Bliley)',
    FCRA: 'FCRA (Fair Credit Reporting)',
    AML_BSA: 'AML/BSA (Anti-Money Laundering)',
    SAFE_ACT: 'SAFE Act (Secure and Fair Enforcement)'
  }
  return names[regulation] || regulation
}
</script>