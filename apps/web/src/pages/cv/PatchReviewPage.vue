<script setup lang="ts">
import { GitCompare, ArrowLeft } from 'lucide-vue-next'
import { RouterLink, useRoute } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import SectionHeader from '@/shared/components/molecules/SectionHeader.vue'
import EmptyState from '@/shared/components/molecules/EmptyState.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import Card from '@/shared/components/ui/card/Card.vue'

const route = useRoute()
const cvId = (route.params.id as string) ?? '1'
</script>

<template>
  <div class="space-y-6 max-w-3xl">
    <SectionHeader
      title="CV Patch Review"
      description="Review and selectively accept AI-generated targeted suggestions and diffs for your CV."
      level="h1"
    />

    <Card class="p-8">
      <EmptyState
        :icon="GitCompare"
        title="Patch Review In Progress"
        description="Selective AI diff patches will appear here once suggestions are generated against a job description."
      >
        <template #action>
          <RouterLink :to="ROUTES.CV_EDIT(cvId)">
            <AppButton variant="outline" size="sm">
              <ArrowLeft :size="14" :stroke-width="1.5" />
              Return to Editor
            </AppButton>
          </RouterLink>
        </template>
      </EmptyState>
    </Card>
  </div>
</template>
