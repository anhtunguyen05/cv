<script setup lang="ts">
import type { SkillMatch } from '../types/match.types'
import AppBadge from '@/shared/components/atoms/AppBadge.vue'
import { CheckCircle, XCircle, AlertCircle } from 'lucide-vue-next'

interface Props {
  matched: SkillMatch[]
  missing: SkillMatch[]
  weak: SkillMatch[]
}

defineProps<Props>()

const statusConfig = {
  matched: { variant: 'success' as const, icon: CheckCircle, label: 'Matched' },
  missing: { variant: 'danger' as const, icon: XCircle, label: 'Missing' },
  weak: { variant: 'warning' as const, icon: AlertCircle, label: 'Weak evidence' },
}
</script>

<template>
  <div class="space-y-6">
    <!-- Matched -->
    <div v-if="matched.length">
      <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider mb-3">
        Matched ({{ matched.length }})
      </p>
      <div class="space-y-2">
        <div
          v-for="item in matched"
          :key="item.skill"
          class="flex items-center justify-between py-2 border-b border-[#f1f5f9] last:border-0"
        >
          <span class="text-sm text-[#0f172a] font-medium">{{ item.skill }}</span>
          <AppBadge variant="success" :label="statusConfig.matched.label">
            <template #icon>
              <CheckCircle :size="12" :stroke-width="1.5" />
            </template>
          </AppBadge>
        </div>
      </div>
    </div>

    <!-- Weak evidence -->
    <div v-if="weak.length">
      <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider mb-3">
        Weak evidence ({{ weak.length }})
      </p>
      <div class="space-y-2">
        <div
          v-for="item in weak"
          :key="item.skill"
          class="flex items-center justify-between py-2 border-b border-[#f1f5f9] last:border-0"
        >
          <span class="text-sm text-[#0f172a] font-medium">{{ item.skill }}</span>
          <AppBadge variant="warning" :label="statusConfig.weak.label">
            <template #icon>
              <AlertCircle :size="12" :stroke-width="1.5" />
            </template>
          </AppBadge>
        </div>
      </div>
    </div>

    <!-- Missing -->
    <div v-if="missing.length">
      <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider mb-3">
        Missing ({{ missing.length }})
      </p>
      <div class="space-y-2">
        <div
          v-for="item in missing"
          :key="item.skill"
          class="flex items-center justify-between py-2 border-b border-[#f1f5f9] last:border-0"
        >
          <span class="text-sm text-[#0f172a] font-medium">{{ item.skill }}</span>
          <AppBadge variant="danger" :label="statusConfig.missing.label">
            <template #icon>
              <XCircle :size="12" :stroke-width="1.5" />
            </template>
          </AppBadge>
        </div>
      </div>
    </div>
  </div>
</template>
