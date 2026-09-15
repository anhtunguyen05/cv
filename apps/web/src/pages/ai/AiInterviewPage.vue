<script setup lang="ts">
import { Bot, ArrowLeft } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import SectionHeader from '@/shared/components/molecules/SectionHeader.vue'
import EmptyState from '@/shared/components/molecules/EmptyState.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import Card from '@/shared/components/ui/card/Card.vue'
</script>

<template>
  <div class="space-y-6 max-w-3xl">
    <SectionHeader
      title="AI Interview Prep"
      description="Practice AI-guided technical and behavioral mock interviews customized to your CV and target role."
      level="h1"
    />

    <Card class="p-8">
      <EmptyState
        :icon="Bot"
        title="AI Interview Mode Coming Soon"
        description="This feature is planned for a future update. You'll be able to simulate real interview questions tailored to your match report."
      >
        <template #action>
          <RouterLink :to="ROUTES.DASHBOARD">
            <AppButton variant="outline" size="sm">
              <ArrowLeft :size="14" :stroke-width="1.5" />
              Back to Dashboard
            </AppButton>
          </RouterLink>
        </template>
      </EmptyState>
    </Card>
  </div>
</template>
