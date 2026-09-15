<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  LayoutDashboard,
  FileText,
  Briefcase,
  BarChart2,
  PanelLeft,
  LayoutTemplate,
} from 'lucide-vue-next'
import { ROUTES } from '@/shared/constants/routes'

interface Props {
  collapsed?: boolean
}

const props = withDefaults(defineProps<Props>(), { collapsed: false })
const emit = defineEmits<{ 'update:collapsed': [value: boolean] }>()

const isCollapsed = ref(props.collapsed)

function toggle() {
  isCollapsed.value = !isCollapsed.value
  emit('update:collapsed', isCollapsed.value)
}

const navItems = [
  { label: 'Dashboard', to: ROUTES.DASHBOARD, icon: LayoutDashboard },
  { label: 'CV Editor', to: ROUTES.CV_EDIT('new'), icon: FileText },
  { label: 'Job Descriptions', to: ROUTES.JD_NEW, icon: Briefcase },
  { label: 'Match Reports', to: '/match', icon: BarChart2 },
  { label: 'Templates', to: ROUTES.TEMPLATES, icon: LayoutTemplate },
]
</script>

<template>
  <aside
    :class="[
      'flex flex-col border-r border-[#e2e8f0] bg-[#f8fafc] transition-all duration-200 ease-out',
      isCollapsed ? 'w-14' : 'w-56',
    ]"
  >
    <!-- Collapse toggle -->
    <div class="flex items-center justify-end p-2 border-b border-[#e2e8f0]">
      <button
        class="p-1.5 rounded-md text-[#64748b] hover:bg-[#e2e8f0] hover:text-[#0f172a] transition-colors"
        :aria-label="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        @click="toggle"
      >
        <PanelLeft :size="15" :stroke-width="1.5" />
      </button>
    </div>

    <!-- Nav items -->
    <nav class="flex-1 px-2 py-3 space-y-0.5">
      <RouterLink
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        :title="isCollapsed ? item.label : undefined"
        :class="[
          'flex items-center gap-2.5 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
          $route.path.startsWith(item.to)
            ? 'bg-[#eef2ff] text-[#4338ca] border-l-2 border-[#6366f1]'
            : 'text-[#64748b] hover:bg-white hover:text-[#0f172a]',
        ]"
      >
        <component :is="item.icon" :size="16" :stroke-width="1.5" class="flex-shrink-0" />
        <span v-if="!isCollapsed" class="truncate">{{ item.label }}</span>
      </RouterLink>
    </nav>
  </aside>
</template>
