<template>
  <div class="overflow-hidden">
    <table class="table">
      <thead class="table-header">
        <tr>
          <th class="table-header-cell">Loan Number</th>
          <th class="table-header-cell">Borrower</th>
          <th class="table-header-cell">Amount</th>
          <th class="table-header-cell">Status</th>
          <th class="table-header-cell">Date</th>
        </tr>
      </thead>
      <tbody class="table-body">
        <tr v-for="loan in loans" :key="loan.id" class="table-row">
          <td class="table-cell">
            <NuxtLink
              :to="`/loans/${loan.id}`"
              class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400"
            >
              {{ loan.loan_number }}
            </NuxtLink>
          </td>
          <td class="table-cell">
            <div class="flex items-center">
              <div class="h-8 w-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mr-3">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                  {{ getInitials(loan.borrower) }}
                </span>
              </div>
              <div>
                <div class="font-medium text-gray-900 dark:text-white">
                  {{ getBorrowerName(loan.borrower) }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ loan.borrower?.email }}
                </div>
              </div>
            </div>
          </td>
          <td class="table-cell">
            <div class="font-medium text-gray-900 dark:text-white">
              {{ formatCurrency(loan.loan_amount) }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              {{ loan.loan_type.toUpperCase() }}
            </div>
          </td>
          <td class="table-cell">
            <span :class="getStatusBadgeClass(loan.status)">
              {{ getStatusLabel(loan.status) }}
            </span>
          </td>
          <td class="table-cell">
            <div class="text-sm text-gray-900 dark:text-white">
              {{ formatDate(loan.application_date) }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              {{ formatTime(loan.application_date) }}
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    
    <div v-if="loans.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      No recent loans found
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Loan } from '~/types'

interface Props {
  loans: Loan[]
}

defineProps<Props>()

// Methods
const getInitials = (borrower: any) => {
  if (!borrower) return '??'
  return `${borrower.first_name[0]}${borrower.last_name[0]}`.toUpperCase()
}

const getBorrowerName = (borrower: any) => {
  if (!borrower) return 'Unknown'
  return `${borrower.first_name} ${borrower.last_name}`
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString()
}

const formatTime = (dateString: string) => {
  return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const getStatusLabel = (status: string) => {
  const labels = {
    application: 'Application',
    processing: 'Processing',
    underwriting: 'Underwriting',
    approved: 'Approved',
    denied: 'Denied',
    closed: 'Closed',
    funded: 'Funded'
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status: string) => {
  const classes = {
    application: 'status-badge status-neutral',
    processing: 'status-badge status-info',
    underwriting: 'status-badge status-warning',
    approved: 'status-badge status-success',
    denied: 'status-badge status-error',
    closed: 'status-badge status-neutral',
    funded: 'status-badge status-success'
  }
  return classes[status] || 'status-badge status-neutral'
}
</script>