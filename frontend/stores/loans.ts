import { defineStore } from 'pinia'
import type { Loan, LoanFilters, LoanStatus } from '~/types'

export const useLoansStore = defineStore('loans', () => {
  // State
  const loans = ref<Loan[]>([])
  const currentLoan = ref<Loan | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const filters = ref<LoanFilters>({
    status: '',
    loan_officer_id: '',
    date_from: '',
    date_to: '',
    search: ''
  })

  // Getters
  const loansByStatus = computed(() => {
    const grouped: Record<string, Loan[]> = {}
    loans.value.forEach(loan => {
      if (!grouped[loan.status]) {
        grouped[loan.status] = []
      }
      grouped[loan.status].push(loan)
    })
    return grouped
  })

  const loanStats = computed(() => {
    const stats = {
      total: loans.value.length,
      application: 0,
      processing: 0,
      underwriting: 0,
      approved: 0,
      denied: 0,
      closed: 0,
      funded: 0
    }

    loans.value.forEach(loan => {
      if (stats.hasOwnProperty(loan.status)) {
        stats[loan.status as keyof typeof stats]++
      }
    })

    return stats
  })

  const totalLoanAmount = computed(() => {
    return loans.value.reduce((sum, loan) => sum + loan.loan_amount, 0)
  })

  const averageLoanAmount = computed(() => {
    if (loans.value.length === 0) return 0
    return totalLoanAmount.value / loans.value.length
  })

  // Actions
  const fetchLoans = async (page = 1, perPage = 15) => {
    try {
      isLoading.value = true
      error.value = null

      const params = new URLSearchParams({
        page: page.toString(),
        per_page: perPage.toString(),
        ...Object.fromEntries(
          Object.entries(filters.value).filter(([_, value]) => value !== '')
        )
      })

      const { data, meta } = await $fetch<{ data: Loan[]; meta: any }>(`/api/loans?${params}`)
      
      loans.value = data
      pagination.value = {
        current_page: meta.current_page,
        last_page: meta.last_page,
        per_page: meta.per_page,
        total: meta.total
      }

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to fetch loans'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const fetchLoan = async (loanId: number) => {
    try {
      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: Loan }>(`/api/loans/${loanId}`)
      currentLoan.value = data

      // Update in loans array if exists
      const index = loans.value.findIndex(loan => loan.id === loanId)
      if (index !== -1) {
        loans.value[index] = data
      }

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to fetch loan'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const createLoan = async (loanData: Partial<Loan>) => {
    try {
      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: Loan }>('/api/loans', {
        method: 'POST',
        body: loanData
      })

      loans.value.unshift(data)
      currentLoan.value = data

      return { success: true, data }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to create loan'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const updateLoan = async (loanId: number, loanData: Partial<Loan>) => {
    try {
      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: Loan }>(`/api/loans/${loanId}`, {
        method: 'PUT',
        body: loanData
      })

      // Update in loans array
      const index = loans.value.findIndex(loan => loan.id === loanId)
      if (index !== -1) {
        loans.value[index] = data
      }

      // Update current loan if it's the same
      if (currentLoan.value?.id === loanId) {
        currentLoan.value = data
      }

      return { success: true, data }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to update loan'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const deleteLoan = async (loanId: number) => {
    try {
      isLoading.value = true
      error.value = null

      await $fetch(`/api/loans/${loanId}`, {
        method: 'DELETE'
      })

      // Remove from loans array
      const index = loans.value.findIndex(loan => loan.id === loanId)
      if (index !== -1) {
        loans.value.splice(index, 1)
      }

      // Clear current loan if it's the same
      if (currentLoan.value?.id === loanId) {
        currentLoan.value = null
      }

      return { success: true }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to delete loan'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const updateLoanStatus = async (loanId: number, status: LoanStatus, reason?: string) => {
    try {
      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: Loan }>(`/api/loans/${loanId}/status`, {
        method: 'POST',
        body: { status, reason }
      })

      // Update in loans array
      const index = loans.value.findIndex(loan => loan.id === loanId)
      if (index !== -1) {
        loans.value[index] = data
      }

      // Update current loan if it's the same
      if (currentLoan.value?.id === loanId) {
        currentLoan.value = data
      }

      return { success: true, data }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to update loan status'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const runComplianceCheck = async (loanId: number) => {
    try {
      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: any }>(`/api/loans/${loanId}/compliance-check`, {
        method: 'POST'
      })

      return { success: true, data }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to run compliance check'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const getAuditTrail = async (loanId: number, page = 1) => {
    try {
      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: any }>(`/api/loans/${loanId}/audit-trail?page=${page}`)

      return { success: true, data }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to fetch audit trail'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const getWorkflow = async (loanId: number) => {
    try {
      isLoading.value = true
      error.value = null

      const { data } = await $fetch<{ data: any }>(`/api/loans/${loanId}/workflow`)

      return { success: true, data }
    } catch (err: any) {
      error.value = err.data?.message || 'Failed to fetch workflow'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const setFilters = (newFilters: Partial<LoanFilters>) => {
    filters.value = { ...filters.value, ...newFilters }
  }

  const clearFilters = () => {
    filters.value = {
      status: '',
      loan_officer_id: '',
      date_from: '',
      date_to: '',
      search: ''
    }
  }

  const setCurrentLoan = (loan: Loan | null) => {
    currentLoan.value = loan
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    loans: readonly(loans),
    currentLoan: readonly(currentLoan),
    isLoading: readonly(isLoading),
    error: readonly(error),
    pagination: readonly(pagination),
    filters: readonly(filters),
    
    // Getters
    loansByStatus,
    loanStats,
    totalLoanAmount,
    averageLoanAmount,
    
    // Actions
    fetchLoans,
    fetchLoan,
    createLoan,
    updateLoan,
    deleteLoan,
    updateLoanStatus,
    runComplianceCheck,
    getAuditTrail,
    getWorkflow,
    setFilters,
    clearFilters,
    setCurrentLoan,
    clearError
  }
})