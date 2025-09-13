<template>
  <div>
    <!-- Page header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        Dashboard
      </h1>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        Welcome back, {{ authStore.userFullName }}! Here's what's happening with your loans.
      </p>
    </div>

    <!-- Stats overview -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
      <StatCard
        title="Total Loans"
        :value="stats.total_loans"
        :change="12"
        change-type="increase"
        icon="heroicons:document-text"
      />
      <StatCard
        title="Active Loans"
        :value="stats.active_loans"
        :change="8"
        change-type="increase"
        icon="heroicons:clock"
      />
      <StatCard
        title="Total Amount"
        :value="formatCurrency(stats.total_loan_amount)"
        :change="15"
        change-type="increase"
        icon="heroicons:currency-dollar"
      />
      <StatCard
        title="Compliance Issues"
        :value="stats.compliance_violations"
        :change="-3"
        change-type="decrease"
        icon="heroicons:exclamation-triangle"
        :is-error="stats.compliance_violations > 0"
      />
    </div>

    <!-- Main content grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Recent loans -->
      <div class="card">
        <div class="card-header">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Recent Loans
            </h3>
            <NuxtLink
              to="/loans"
              class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400"
            >
              View all
            </NuxtLink>
          </div>
        </div>
        <div class="card-body p-0">
          <RecentLoansTable :loans="recentLoans" />
        </div>
      </div>

      <!-- Compliance overview -->
      <div class="card">
        <div class="card-header">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Compliance Overview
            </h3>
            <NuxtLink
              to="/compliance"
              class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400"
            >
              View details
            </NuxtLink>
          </div>
        </div>
        <div class="card-body">
          <ComplianceOverview :summary="complianceSummary" />
        </div>
      </div>
    </div>

    <!-- Workflow status -->
    <div class="mt-6">
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Workflow Status
          </h3>
        </div>
        <div class="card-body">
          <WorkflowStatus :summary="workflowSummary" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Page meta
definePageMeta({
  layout: 'default',
  middleware: ['auth', 'tenant']
})

// Stores
const authStore = useAuthStore()
const loansStore = useLoansStore()

// Data
const stats = ref({
  total_loans: 0,
  active_loans: 0,
  total_loan_amount: 0,
  compliance_violations: 0,
  pending_approvals: 0,
  overdue_tasks: 0
})

const recentLoans = ref([])
const complianceSummary = ref({
  total_violations: 0,
  critical_violations: 0,
  high_violations: 0,
  violations_by_regulation: {}
})

const workflowSummary = ref({
  total_steps: 0,
  completed_steps: 0,
  overdue_steps: 0,
  pending_steps: 0
})

// Methods
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

// Load dashboard data
const loadDashboardData = async () => {
  try {
    // Load recent loans
    const loansResult = await loansStore.fetchLoans(1, 5)
    if (loansResult.success) {
      recentLoans.value = loansStore.loans
    }

    // Load stats (this would typically come from a dashboard API endpoint)
    stats.value = {
      total_loans: loansStore.loanStats.total,
      active_loans: loansStore.loanStats.processing + loansStore.loanStats.underwriting,
      total_loan_amount: loansStore.totalLoanAmount,
      compliance_violations: 0, // This would come from compliance API
      pending_approvals: loansStore.loanStats.underwriting,
      overdue_tasks: 0 // This would come from workflow API
    }
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  }
}

// Initialize
onMounted(() => {
  loadDashboardData()
})
</script>