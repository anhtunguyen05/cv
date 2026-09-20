<script setup lang="ts">
import { ref, computed } from 'vue'
import type { SkillMatch } from '../types/match.types'
import AppBadge from '@/shared/components/atoms/AppBadge.vue'
import { CheckCircle2, XCircle, AlertCircle } from 'lucide-vue-next'

interface Props {
  matched: SkillMatch[]
  missing: SkillMatch[]
  weak: SkillMatch[]
}

const props = defineProps<Props>()

type FilterTab = 'all' | 'matched' | 'weak' | 'missing'
const activeTab = ref<FilterTab>('all')

const allSkills = computed(() => [
  ...props.matched.map((s) => ({ ...s, category: 'matched' as const })),
  ...props.weak.map((s) => ({ ...s, category: 'weak' as const })),
  ...props.missing.map((s) => ({ ...s, category: 'missing' as const })),
])

const filteredSkills = computed(() => {
  if (activeTab.value === 'all') return allSkills.value
  return allSkills.value.filter((s) => s.category === activeTab.value)
})

const statusConfig = {
  matched: { variant: 'success' as const, icon: CheckCircle2, label: 'Matched' },
  weak: { variant: 'warning' as const, icon: AlertCircle, label: 'Weak Evidence' },
  missing: { variant: 'danger' as const, icon: XCircle, label: 'Missing Keyword' },
}
</script>

<template>
  <div class="space-y-4">
    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 pb-2.5 border-b border-border overflow-x-auto">
      <button
        type="button"
        :class="[
          'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer select-none whitespace-nowrap',
          activeTab === 'all'
            ? 'bg-text text-white shadow-2xs'
            : 'text-text-muted hover:bg-surface-muted hover:text-text border border-transparent',
        ]"
        @click="activeTab = 'all'"
      >
        All ({{ allSkills.length }})
      </button>

      <button
        type="button"
        :class="[
          'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer select-none flex items-center gap-1.5 whitespace-nowrap',
          activeTab === 'matched'
            ? 'bg-success-muted text-success-text border border-success-border'
            : 'text-text-muted hover:bg-surface-muted hover:text-text border border-transparent',
        ]"
        @click="activeTab = 'matched'"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-success" />
        Matched ({{ matched.length }})
      </button>

      <button
        type="button"
        :class="[
          'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer select-none flex items-center gap-1.5 whitespace-nowrap',
          activeTab === 'weak'
            ? 'bg-warning-muted text-warning-text border border-warning-border'
            : 'text-text-muted hover:bg-surface-muted hover:text-text border border-transparent',
        ]"
        @click="activeTab === 'weak'"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-warning" />
        Weak ({{ weak.length }})
      </button>

      <button
        type="button"
        :class="[
          'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer select-none flex items-center gap-1.5 whitespace-nowrap',
          activeTab === 'missing'
            ? 'bg-danger-muted text-danger-text border border-danger-border'
            : 'text-text-muted hover:bg-surface-muted hover:text-text border border-transparent',
        ]"
        @click="activeTab === 'missing'"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-danger" />
        Missing ({{ missing.length }})
      </button>
    </div>

    <!-- Skills List -->
    <div class="space-y-2.5">
      <div
        v-for="item in filteredSkills"
        :key="item.skill"
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 sm:p-4 rounded-xl border border-border bg-white hover:border-border-hover hover:shadow-2xs transition-all"
      >
        <div class="flex items-start sm:items-center gap-3">
          <component
            :is="statusConfig[item.category].icon"
            :size="18"
            :stroke-width="2"
            :class="[
              item.category === 'matched' ? 'text-success' :
              item.category === 'weak' ? 'text-warning' : 'text-danger',
              'flex-shrink-0 mt-0.5 sm:mt-0',
            ]"
          />
          <div>
            <span class="text-sm sm:text-[15px] font-bold text-text">{{ item.skill }}</span>
            <p v-if="item.evidence" class="text-xs text-text-muted mt-0.5">
              Evidence: <span class="font-medium text-text-secondary">{{ item.evidence }}</span>
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto flex-shrink-0">
          <span
            v-if="item.category === 'weak'"
            class="text-xs font-mono font-semibold text-warning-text bg-warning-muted px-2 py-0.5 rounded-full border border-warning-border"
          >
            +6 pts
          </span>
          <span
            v-else-if="item.category === 'missing'"
            class="text-xs font-mono font-semibold text-danger-text bg-danger-muted px-2 py-0.5 rounded-full border border-danger-border"
          >
            +10 pts
          </span>

          <AppBadge :variant="statusConfig[item.category].variant" :label="statusConfig[item.category].label" size="sm" />
        </div>
      </div>
    </div>
  </div>
</template>
