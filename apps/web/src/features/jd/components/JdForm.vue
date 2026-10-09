<script setup lang="ts">
import Button from '@/shared/components/ui/button/Button.vue'
import FormField from '@/shared/components/molecules/FormField.vue'
import type { JdFormContract } from '../composables/useJdEditorController'
import JdDeleteConfirmation from './JdDeleteConfirmation.vue'
defineProps<{ form: JdFormContract }>()
</script>
<template>
  <form class="space-y-5" @submit.prevent="form.submit">
    <div
      v-if="form.localError.value || form.errorMessage.value"
      :ref="(element) => form.setErrorSummary(element as HTMLElement | null)"
      role="alert"
      tabindex="-1"
      class="p-3 rounded-xl bg-danger-muted border border-danger-border text-sm text-danger-hover"
    >
      <p>{{ form.localError.value || form.errorMessage.value }}</p>
      <Button
        v-if="form.saveConflict.value"
        type="button"
        variant="outline"
        class="mt-3"
        @click="form.reloadCurrentRevision"
        >Reload current revision</Button
      >
      <Button
        v-if="form.saveRetryable.value"
        type="button"
        variant="outline"
        class="mt-3"
        @click="form.retrySave"
        >Retry save</Button
      >
      <Button
        v-if="form.deleteConflict.value"
        type="button"
        variant="outline"
        class="mt-3"
        @click="form.reloadCurrentRevision"
        >Reload current revision</Button
      >
      <Button
        v-if="form.deleteRetryable.value"
        type="button"
        variant="outline"
        class="mt-3"
        @click="form.retryDelete"
        >Retry delete</Button
      >
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <FormField
        v-slot="{ controlProps }"
        label="Company"
        hint="Optional"
        html-for="company"
        :error="form.fieldError('company')"
      >
        <input
          v-bind="controlProps"
          :value="form.company.value"
          class="h-10 px-3 rounded-lg border border-border bg-white font-normal focus:outline-none focus:ring-2 focus:ring-primary/20"
          @input="form.setCompany(($event.target as HTMLInputElement).value)"
        />
      </FormField>
      <FormField
        v-slot="{ controlProps }"
        label="Role"
        hint="Optional"
        html-for="role"
        :error="form.fieldError('role')"
      >
        <input
          v-bind="controlProps"
          :value="form.role.value"
          class="h-10 px-3 rounded-lg border border-border bg-white font-normal focus:outline-none focus:ring-2 focus:ring-primary/20"
          @input="form.setRole(($event.target as HTMLInputElement).value)"
        />
      </FormField>
    </div>
    <FormField
      v-slot="{ controlProps }"
      label="Job Description source"
      html-for="jd-text"
      :error="form.fieldError('raw_text')"
      required
    >
      <textarea
        v-bind="controlProps"
        :value="form.rawText.value"
        rows="16"
        :aria-describedby="form.fieldError('raw_text') ? 'jd-help jd-text-error' : 'jd-help'"
        class="w-full p-4 rounded-xl text-sm font-mono border border-border bg-white text-text focus:outline-none focus:ring-2 focus:ring-primary/20 leading-relaxed resize-y"
        @input="form.setRawText(($event.target as HTMLTextAreaElement).value)"
      />
      <div id="jd-help" class="flex justify-between text-xs text-text-muted">
        <span>{{ form.charCount.value }} / 50,000 Unicode code points</span
        ><span>Raw source is preserved after decoding.</span>
      </div>
    </FormField>
    <div class="flex flex-wrap gap-3">
      <Button
        type="submit"
        :loading="form.saveMutation.isPending.value"
        :disabled="form.saveMutation.isPending.value"
        >{{ form.current.value ? 'Save new revision' : 'Save Job Description' }}</Button
      >
      <Button
        v-if="form.current.value"
        type="button"
        variant="outline"
        :loading="form.analysisMutation.isPending.value"
        :disabled="form.analysisMutation.isPending.value || form.hasUnsavedChanges.value"
        @click="form.analyze"
        >Analyze revision</Button
      >
      <JdDeleteConfirmation
        v-if="form.current.value"
        :loading="form.deleteMutation.isPending.value"
        @confirm="form.confirmDelete"
      />
    </div>
  </form>
</template>
