<script setup lang="ts">
import { computed, watch } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { registerSchema } from '../schemas/auth.schema'
import { useRegisterMutation } from '../api/auth.mutations'
import FormField from '@/shared/components/molecules/FormField.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import { User, Mail, Lock, ArrowRight } from 'lucide-vue-next'
import { ApiRequestError } from '@/shared/api/client'

const { handleSubmit, defineField, errors, values, setErrors } = useForm({
  validationSchema: toTypedSchema(registerSchema),
})

const [name, nameAttrs] = defineField('name')
const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')
const [passwordConfirmation, passwordConfirmationAttrs] = defineField('password_confirmation')

const { mutate: register, isPending, error: mutationError } = useRegisterMutation()

const serverMessage = computed(() => {
  if (!mutationError.value) return ''
  return mutationError.value instanceof Error
    ? mutationError.value.message
    : 'Registration failed. Please try again.'
})

const retryMessage = computed(() => {
  if (!(mutationError.value instanceof ApiRequestError)) return ''
  if (mutationError.value.status === 429 && mutationError.value.retryAfter) {
    return `Please try again in ${mutationError.value.retryAfter} seconds.`
  }
  if (mutationError.value.status === 419) {
    return 'Your session expired. Please submit again to refresh your session.'
  }
  return ''
})

watch(mutationError, (error) => {
  if (!(error instanceof ApiRequestError) || !error.details) return
  setErrors(
    Object.fromEntries(
      Object.entries(error.details).map(([field, message]) => [
        field,
        Array.isArray(message) ? message[0] : message,
      ]),
    ),
  )
})

const onSubmit = handleSubmit((vals) => register(vals))

// Password strength computation
const passwordStrength = computed(() => {
  const pw = values.password ?? ''
  if (!pw) return 0
  let score = 0
  if (pw.length >= 6) score++
  if (pw.length >= 12) score++
  if (pw.length >= 18) score++
  if (pw.length >= 24) score++
  return score
})

const strengthColors = ['bg-border', 'bg-danger', 'bg-warning', 'bg-primary', 'bg-success']
const strengthLabels = ['', 'Weak', 'Fair', 'Good', 'Strong']
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">Create your account</h1>
      <p class="mt-1.5 text-sm text-text-muted">
        Get started tailoring your CV for every job application.
      </p>
    </div>

    <form class="space-y-4" novalidate @submit="onSubmit">
      <!-- Server error -->
      <div
        v-if="mutationError"
        class="px-4 py-3 rounded-xl bg-danger-muted border border-danger-border text-sm text-danger-hover"
        role="alert"
      >
        {{ serverMessage }}
        <span v-if="retryMessage" class="block mt-1">{{ retryMessage }}</span>
      </div>

      <FormField label="Full Name" :error="errors.name" html-for="name" required>
        <div class="relative flex items-center">
          <User :size="16" class="absolute left-3.5 text-text-subtle pointer-events-none" />
          <input
            id="name"
            v-model="name"
            v-bind="nameAttrs"
            :aria-invalid="!!errors.name"
            :aria-describedby="errors.name ? 'name-error' : undefined"
            type="text"
            autocomplete="name"
            placeholder="Nguyen Anh Tu"
            :class="[
              'w-full h-10 sm:h-11 pl-10 pr-3.5 rounded-xl text-sm border transition-all text-text focus:outline-none focus:ring-2 focus:ring-offset-0',
              errors.name
                ? 'border-danger focus:ring-danger/20 bg-danger-muted'
                : 'border-border bg-white focus:border-primary focus:ring-primary/20 hover:border-border-hover',
            ]"
          />
        </div>
      </FormField>

      <FormField label="Email Address" :error="errors.email" html-for="reg-email" required>
        <div class="relative flex items-center">
          <Mail :size="16" class="absolute left-3.5 text-text-subtle pointer-events-none" />
          <input
            id="reg-email"
            v-model="email"
            v-bind="emailAttrs"
            :aria-invalid="!!errors.email"
            :aria-describedby="errors.email ? 'reg-email-error' : undefined"
            type="email"
            autocomplete="email"
            placeholder="you@example.com"
            :class="[
              'w-full h-10 sm:h-11 pl-10 pr-3.5 rounded-xl text-sm border transition-all text-text focus:outline-none focus:ring-2 focus:ring-offset-0',
              errors.email
                ? 'border-danger focus:ring-danger/20 bg-danger-muted'
                : 'border-border bg-white focus:border-primary focus:ring-primary/20 hover:border-border-hover',
            ]"
          />
        </div>
      </FormField>

      <FormField label="Password" :error="errors.password" html-for="reg-password" required>
        <div class="relative flex items-center">
          <Lock :size="16" class="absolute left-3.5 text-text-subtle pointer-events-none" />
          <input
            id="reg-password"
            v-model="password"
            v-bind="passwordAttrs"
            :aria-invalid="!!errors.password"
            :aria-describedby="errors.password ? 'reg-password-error' : undefined"
            type="password"
            autocomplete="new-password"
            placeholder="••••••••"
            :class="[
              'w-full h-10 sm:h-11 pl-10 pr-3.5 rounded-xl text-sm border transition-all text-text focus:outline-none focus:ring-2 focus:ring-offset-0',
              errors.password
                ? 'border-danger focus:ring-danger/20 bg-danger-muted'
                : 'border-border bg-white focus:border-primary focus:ring-primary/20 hover:border-border-hover',
            ]"
          />
        </div>
        <!-- Password strength bar -->
        <div v-if="password" class="mt-2.5 flex gap-1.5">
          <div
            v-for="i in 4"
            :key="i"
            :class="[
              'h-1.5 flex-1 rounded-full transition-all duration-300',
              i <= passwordStrength ? strengthColors[passwordStrength] : 'bg-border',
            ]"
          />
        </div>
        <p v-if="password && passwordStrength > 0" class="mt-1 text-xs text-text-muted">
          {{ strengthLabels[passwordStrength] }} password strength
        </p>
      </FormField>

      <FormField
        label="Confirm Password"
        :error="errors.password_confirmation"
        html-for="password-confirm"
        required
      >
        <div class="relative flex items-center">
          <Lock :size="16" class="absolute left-3.5 text-text-subtle pointer-events-none" />
          <input
            id="password-confirm"
            v-model="passwordConfirmation"
            v-bind="passwordConfirmationAttrs"
            :aria-invalid="!!errors.password_confirmation"
            :aria-describedby="errors.password_confirmation ? 'password-confirm-error' : undefined"
            type="password"
            autocomplete="new-password"
            placeholder="••••••••"
            :class="[
              'w-full h-10 sm:h-11 pl-10 pr-3.5 rounded-xl text-sm border transition-all text-text focus:outline-none focus:ring-2 focus:ring-offset-0',
              errors.password_confirmation
                ? 'border-danger focus:ring-danger/20 bg-danger-muted'
                : 'border-border bg-white focus:border-primary focus:ring-primary/20 hover:border-border-hover',
            ]"
          />
        </div>
      </FormField>

      <div class="pt-2">
        <AppButton size="lg" type="submit" class="w-full shadow-xs" :loading="isPending">
          <span>Create account</span>
          <ArrowRight :size="16" />
        </AppButton>
      </div>
    </form>

    <p class="text-center text-sm text-text-muted">
      Already have an account?
      <RouterLink
        to="/login"
        class="text-primary hover:text-primary-hover font-semibold ml-1 transition-colors"
      >
        Sign in
      </RouterLink>
    </p>
  </div>
</template>
