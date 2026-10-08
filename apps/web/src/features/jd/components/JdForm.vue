<script setup lang="ts">
import Button from '@/shared/components/ui/button/Button.vue'
import FormField from '@/shared/components/molecules/FormField.vue'
import type { JdEditorController } from '../composables/useJdEditorController'
import JdDeleteConfirmation from './JdDeleteConfirmation.vue'
defineProps<{ controller: JdEditorController }>()
</script>
<template>
  <form class="space-y-5" @submit.prevent="controller.submit">
    <div
      v-if="controller.localError.value || controller.errorMessage.value"
      :ref="(element) => controller.setErrorSummary(element as HTMLElement | null)"
      role="alert"
      tabindex="-1"
      class="p-3 rounded-xl bg-danger-muted border border-danger-border text-sm text-danger-hover"
    >
      <p>{{ controller.localError.value || controller.errorMessage.value }}</p>
      <Button
        v-if="controller.saveConflict.value"
        type="button"
        variant="outline"
        class="mt-3"
        @click="controller.reloadCurrentRevision"
        >Reload current revision</Button
      >
      <Button
        v-if="controller.saveRetryable.value"
        type="button"
        variant="outline"
        class="mt-3"
        @click="controller.retrySave"
        >Retry save</Button
      >
      <Button
        v-if="controller.deleteConflict.value"
        type="button"
        variant="outline"
        class="mt-3"
        @click="controller.reloadCurrentRevision"
        >Reload current revision</Button
      >
      <Button
        v-if="controller.deleteRetryable.value"
        type="button"
        variant="outline"
        class="mt-3"
        @click="controller.retryDelete"
        >Retry delete</Button
      >
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <FormField
        v-slot="{ controlProps }"
        label="Company"
        hint="Optional"
        html-for="company"
        :error="controller.fieldError('company')"
      >
        <input
          v-bind="controlProps"
          :value="controller.company.value"
          class="h-10 px-3 rounded-lg border border-border bg-white font-normal focus:outline-none focus:ring-2 focus:ring-primary/20"
          @input="controller.setCompany(($event.target as HTMLInputElement).value)"
        />
      </FormField>
      <FormField
        v-slot="{ controlProps }"
        label="Role"
        hint="Optional"
        html-for="role"
        :error="controller.fieldError('role')"
      >
        <input
          v-bind="controlProps"
          :value="controller.role.value"
          class="h-10 px-3 rounded-lg border border-border bg-white font-normal focus:outline-none focus:ring-2 focus:ring-primary/20"
          @input="controller.setRole(($event.target as HTMLInputElement).value)"
        />
      </FormField>
    </div>
    <FormField
      v-slot="{ controlProps }"
      label="Job Description source"
      html-for="jd-text"
      :error="controller.fieldError('raw_text')"
      required
    >
      <textarea
        v-bind="controlProps"
        :value="controller.rawText.value"
        rows="16"
        :aria-describedby="controller.fieldError('raw_text') ? 'jd-help jd-text-error' : 'jd-help'"
        class="w-full p-4 rounded-xl text-sm font-mono border border-border bg-white text-text focus:outline-none focus:ring-2 focus:ring-primary/20 leading-relaxed resize-y"
        @input="controller.setRawText(($event.target as HTMLTextAreaElement).value)"
      />
      <div id="jd-help" class="flex justify-between text-xs text-text-muted">
        <span>{{ controller.charCount.value }} / 50,000 Unicode code points</span
        ><span>Raw source is preserved after decoding.</span>
      </div>
    </FormField>
    <div class="flex flex-wrap gap-3">
      <Button
        type="submit"
        :loading="controller.saveMutation.isPending.value"
        :disabled="controller.saveMutation.isPending.value"
        >{{ controller.current.value ? 'Save new revision' : 'Save Job Description' }}</Button
      >
      <Button
        v-if="controller.current.value"
        type="button"
        variant="outline"
        :loading="controller.analysisMutation.isPending.value"
        :disabled="
          controller.analysisMutation.isPending.value || controller.hasUnsavedChanges.value
        "
        @click="controller.analyze"
        >Analyze revision</Button
      >
      <JdDeleteConfirmation
        v-if="controller.current.value"
        :loading="controller.deleteMutation.isPending.value"
        @confirm="controller.confirmDelete"
      />
    </div>
  </form>
</template>
