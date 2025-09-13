import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import axe from 'axe-core'
import RecentLoansTable from '../components/RecentLoansTable.vue'

async function runAxe(wrapper: any) {
  document.body.appendChild(wrapper.element)
  return await new Promise<any>((resolve, reject) => {
    axe.run(
      wrapper.element,
      { rules: { 'color-contrast': { enabled: false } } },
      (err, results) => {
        if (err) reject(err)
        else resolve(results)
      }
    )
  })
}

describe('Accessibility checks', () => {
  it('RecentLoansTable is accessible', async () => {
    const wrapper = mount(RecentLoansTable, {
      props: { loans: [] },
      global: { stubs: { NuxtLink: { template: '<a><slot /></a>' } } }
    })
    const results = await runAxe(wrapper)
    expect(results.violations).toHaveLength(0)
  })
})
