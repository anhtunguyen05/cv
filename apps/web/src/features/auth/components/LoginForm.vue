<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { loginSchema } from '../schemas/auth.schema'
import { useLoginMutation } from '../api/auth.mutations'
import FormField from '@/shared/components/molecules/FormField.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import { Sparkles, ArrowRight, Lock, Mail } from 'lucide-vue-next'

const { handleSubmit, defineField, errors, setValues } = useForm({
  validationSchema: toTypedSchema(loginSchema),
})

const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')

const { mutate: login, isPending, error: mutationError } = useLoginMutation()

const onSubmit = handleSubmit((values) => {
  login(values)
})

function fillDemo() {
  setValues({
    email: 'tu@example.com',
    password: 'password123',
  })
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">
        Sign in to CareerFitCV
      </h1>
      <p class="mt-1.5 text-sm text-text-muted">
        Access your tailored CV profiles and job analyses.
      </p>
    </div>

    <!-- Quick Demo Credential Fill Button -->
    <button
      type="button"
      class="w-full flex items-center justify-between p-3.5 rounded-xl bg-primary-muted border border-primary-border/60 text-sm text-primary-dark hover:bg-primary-tint transition-colors cursor-pointer"
      @click="fillDemo"
    >
      <div class="flex items-center gap-2.5">
        <Sparkles :size="16" class="text-primary" />
        <span class="font-medium">Fill Demo Account (tu@example.com)</span>
      </div>
      <span
        class="font-bold text-xs uppercase tracking-wider bg-white/80 px-2 py-0.5 rounded-md border border-primary-border"
        >Auto-fill</span
      >
    </button>

    <form class="space-y-4" novalidate @submit="onSubmit">
      <!-- Server error -->
      <div
        v-if="mutationError"
        class="px-4 py-3 rounded-xl bg-danger-muted border border-danger-border text-sm text-danger-hover"
        role="alert"
      >
        {{ (mutationError as Error).message || 'Sign in failed. Please check your credentials.' }}
      </div>

      <FormField label="Email address" :error="errors.email" html-for="email" required>
        <div class="relative flex items-center">
          <Mail :size="16" class="absolute left-3.5 text-text-subtle pointer-events-none" />
          <input
            id="email"
            v-model="email"
            v-bind="emailAttrs"
            type="email"
            autocomplete="email"
            placeholder="you@example.com"
            :class="[
              'w-full h-10 sm:h-11 pl-10 pr-3.5 rounded-xl text-sm border transition-all text-text',
              'focus:outline-none focus:ring-2 focus:ring-offset-0',
              errors.email
                ? 'border-danger focus:ring-danger/20 bg-danger-muted'
                : 'border-border bg-white focus:border-primary focus:ring-primary/20 hover:border-border-hover',
            ]"
          />
        </div>
      </FormField>

      <FormField label="Password" :error="errors.password" html-for="password" required>
        <div class="relative flex items-center">
          <Lock :size="16" class="absolute left-3.5 text-text-subtle pointer-events-none" />
          <input
            id="password"
            v-model="password"
            v-bind="passwordAttrs"
            type="password"
            autocomplete="current-password"
            placeholder="••••••••"
            :class="[
              'w-full h-10 sm:h-11 pl-10 pr-3.5 rounded-xl text-sm border transition-all text-text',
              'focus:outline-none focus:ring-2 focus:ring-offset-0',
              errors.password
                ? 'border-danger focus:ring-danger/20 bg-danger-muted'
                : 'border-border bg-white focus:border-primary focus:ring-primary/20 hover:border-border-hover',
            ]"
          />
        </div>
      </FormField>

      <div class="pt-2">
        <AppButton size="lg" type="submit" class="w-full shadow-xs" :loading="isPending">
          <span>Sign in</span>
          <ArrowRight :size="16" />
        </AppButton>
      </div>
    </form>

    <p class="text-center text-sm text-text-muted">
      Don't have an account yet?
      <RouterLink
        to="/register"
        class="text-primary hover:text-primary-hover font-semibold ml-1 transition-colors"
      >
        Create one free
      </RouterLink>
    </p>
  </div>
</template>
