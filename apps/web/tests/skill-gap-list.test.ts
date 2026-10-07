import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import SkillGapList from '@/features/match/components/SkillGapList.vue'

describe('SkillGapList evidence ordering', () => {
  it('keeps preferred weak evidence visible in All and Weak filters', async () => {
    const wrapper = mount(SkillGapList, {
      props: {
        matched: [],
        missing: [],
        weak: [{
          signal_id: 'docker',
          label: 'Docker',
          importance: 'preferred',
          evidence_level: 'weak',
          source_references: ['skills[0]'],
        }],
      },
    })

    expect(wrapper.text()).toContain('Docker')
    await wrapper.get('button:nth-of-type(3)').trigger('click')
    expect(wrapper.text()).toContain('Docker')
  })
})
