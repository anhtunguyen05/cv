<script setup lang="ts">
import {
  TooltipProvider,
  TooltipRoot,
  TooltipTrigger,
  TooltipPortal,
  TooltipContent,
  TooltipArrow,
} from 'reka-ui'

interface Props {
  content: string
  side?: 'top' | 'right' | 'bottom' | 'left'
  delayDuration?: number
}

withDefaults(defineProps<Props>(), {
  side: 'top',
  delayDuration: 200,
})
</script>

<template>
  <TooltipProvider :delay-duration="delayDuration">
    <TooltipRoot>
      <TooltipTrigger as-child>
        <slot />
      </TooltipTrigger>
      <TooltipPortal>
        <TooltipContent
          :side="side"
          :side-offset="5"
          class="z-50 overflow-hidden rounded-md bg-dark-surface px-3 py-1.5 text-xs text-white shadow-md animate-in fade-in-50"
        >
          {{ content }}
          <TooltipArrow class="fill-dark-surface" />
        </TooltipContent>
      </TooltipPortal>
    </TooltipRoot>
  </TooltipProvider>
</template>
