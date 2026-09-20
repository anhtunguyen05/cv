<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import {
  LayoutDashboard,
  FileText,
  Briefcase,
  BarChart2,
  PanelLeft,
  LayoutTemplate,
  Sparkles,
} from 'lucide-vue-next'
import { ROUTES } from '@/shared/constants/routes'

interface Props {
  collapsed?: boolean
}

const props = withDefaults(defineProps<Props>(), { collapsed: false })
const emit = defineEmits<{ 'update:collapsed': [value: boolean] }>()

const route = useRoute()
const isCollapsed = ref(props.collapsed)

function toggle() {
  isCollapsed.value = !isCollapsed.value
  emit('update:collapsed', isCollapsed.value)
}

const navItems = [
  { label: 'Dashboard', to: ROUTES.DASHBOARD, icon: LayoutDashboard },
  { label: 'CV Editor', to: ROUTES.CV_EDIT('new'), icon: FileText },
  { label: 'Job Analysis', to: ROUTES.JD_NEW, icon: Briefcase },
  { label: 'Match Reports', to: '/match/1', icon: BarChart2 },
  { label: 'Templates', to: ROUTES.TEMPLATES, icon: LayoutTemplate },
]
</script>

<template>
  <aside
    :class="[
      'flex flex-col border-r border-border/80 bg-surface transition-all duration-200 ease-out select-none',
      isCollapsed ? 'w-18' : 'w-64',
    ]"
  >
    <!-- Top toolbar / collapse toggle -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-border/80">
      <span
        v-if="!isCollapsed"
        class="text-xs font-bold tracking-wider text-text-subtle uppercase pl-1"
      >
        Workspace
      </span>
      <button
        class="p-1.5 rounded-lg text-text-muted hover:bg-white hover:text-text border border-transparent hover:border-border transition-colors ml-auto cursor-pointer"
        :aria-label="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        :title="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        @click="toggle"
      >
        <PanelLeft :size="16" :stroke-width="1.5" />
      </button>
    </div>

    <!-- Navigation List -->
    <nav class="flex-1 px-2.5 py-3 space-y-1 overflow-y-auto">
      <RouterLink
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        :title="isCollapsed ? item.label : undefined"
        :class="[
          'flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs sm:text-[13px] font-medium transition-all duration-150 group relative',
          route.path === item.to || (item.to !== ROUTES.DASHBOARD && route.path.startsWith(item.to))
            ? 'bg-white text-primary-dark shadow-card-quiet border border-border font-semibold'
            : 'text-text-muted hover:bg-white/70 hover:text-text',
        ]"
      >
        <component
          :is="item.icon"
          :size="17"
          :stroke-width="1.5"
          :class="[
            'flex-shrink-0 transition-colors',
            route.path === item.to || (item.to !== ROUTES.DASHBOARD && route.path.startsWith(item.to))
              ? 'text-primary'
              : 'text-text-subtle group-hover:text-text-quiet',
          ]"
        />
        <span v-if="!isCollapsed" class="truncate">{{ item.label }}</span>

        <!-- Active indicator pill on left -->
        <span
          v-if="route.path === item.to || (item.to !== ROUTES.DASHBOARD && route.path.startsWith(item.to))"
          class="absolute left-0 top-1.5 bottom-1.5 w-1 bg-primary rounded-r-full"
        />
      </RouterLink>
    </nav>

    <!-- Bottom summary status card -->
    <div v-if="!isCollapsed" class="p-3 border-t border-border/80">
      <div class="p-3 rounded-xl bg-white border border-border shadow-2xs space-y-1.5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-text flex items-center gap-1.5">
            <Sparkles :size="13" class="text-primary" />
            Match AI Engine
          </span>
          <span class="text-xs font-medium text-success bg-success-muted px-1.5 py-0.5 rounded-full">v1.0</span>
        </div>
        <p class="text-xs text-text-muted leading-relaxed">Tailor your CV to beat ATS filters with grounded evidence citations.</p>
      </div>
    </div>
  </aside>
</template>
