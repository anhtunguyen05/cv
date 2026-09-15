<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, RouterView } from 'vue-router'
import MarketingLayout from '@/app/layouts/MarketingLayout.vue'
import AuthLayout from '@/app/layouts/AuthLayout.vue'
import AppLayout from '@/app/layouts/AppLayout.vue'
import PreviewLayout from '@/app/layouts/PreviewLayout.vue'

const route = useRoute()

const layoutComponents = {
  MarketingLayout,
  AuthLayout,
  AppLayout,
  PreviewLayout,
} as const

type LayoutName = keyof typeof layoutComponents

const layout = computed<LayoutName>(
  () => (route.meta.layout as LayoutName) ?? 'AppLayout',
)
</script>

<template>
  <component :is="layoutComponents[layout]">
    <RouterView />
  </component>
</template>
