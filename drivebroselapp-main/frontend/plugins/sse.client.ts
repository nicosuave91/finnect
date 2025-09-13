import { useLoansStore } from '~/stores/loans'

export default defineNuxtPlugin(() => {
  const loansStore = useLoansStore()
  const eventSource = new EventSource('/api/loans/stream')

  eventSource.addEventListener('loan-status', (event: MessageEvent) => {
    const data = JSON.parse(event.data) as { loan_id: number; status: string }
    loansStore.$patch((state: any) => {
      const loan = state.loans.find((l: any) => l.id === data.loan_id)
      if (loan) {
        loan.status = data.status
      }
      if (state.currentLoan && state.currentLoan.id === data.loan_id) {
        state.currentLoan.status = data.status
      }
    })
  })
})
