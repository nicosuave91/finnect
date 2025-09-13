import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import WorkflowStatus from '../components/WorkflowStatus.vue'

describe('WorkflowStatus', () => {
  it('renders progress information', () => {
    const wrapper = mount(WorkflowStatus, {
      props: {
        summary: {
          total_steps: 4,
          completed_steps: 2,
          overdue_steps: 1,
          pending_steps: 2
        }
      },
      global: {
        stubs: { Icon: true }
      }
    })

    expect(wrapper.text()).toContain('2 of 4 steps')
    const bar = wrapper.find('div > div.bg-primary-600')
    expect(bar.attributes('style')).toContain('50%')
  })
})
