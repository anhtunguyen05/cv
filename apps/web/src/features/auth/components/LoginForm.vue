<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { loginSchema } from '../schemas/auth.schema'
import { useLoginMutation } from '../api/auth.mutations'
import FormField from '@/shared/components/molecules/FormField.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'

const { handleSubmit, defineField, errors } = useForm({
  validationSchema: toTypedSchema(loginSchema),
})

const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')

const { mutate: login, isPending, error: mutationError } = useLoginMutation()

const onSubmit = handleSubmit((values) => {
  login(values)
})
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold tracking-tight text-[#0f172a]">Sign in</h1>
    <p class="mt-1 text-sm text-[#64748b]">Welcome back. Enter your credentials to continue.</p>

    <form class="mt-8 space-y-5" novalidate @submit="onSubmit">
      <!-- Server error -->
      <div
        v-if="mutationError"
        class="px-4 py-3 rounded-md bg-[#fef2f2] border border-[#fecaca] text-sm text-[#dc2626]"
        role="alert"
      >
        {{ (mutationError as Error).message || 'Sign in failed. Please try again.' }}
      </div>

      <FormField label="Email address" :error="errors.email" html-for="email" required>
        <input
          id="email"
          v-model="email"
          v-bind="emailAttrs"
          type="email"
          autocomplete="email"
          placeholder="you@example.com"
          :class="[
            'w-full h-9 px-3 rounded-md text-sm border transition-colors',
            'focus:outline-none focus:ring-2 focus:ring-offset-0',
            errors.email
              ? 'border-[#ef4444] focus:ring-[#ef4444]/30 bg-[#fef2f2]'
              : 'border-[#e2e8f0] bg-white focus:border-[#6366f1] focus:ring-[#6366f1]/20',
          ]"
        />
      </FormField>

      <FormField label="Password" :error="errors.password" html-for="password" required>
        <input
          id="password"
          v-model="password"
          v-bind="passwordAttrs"
          type="password"
          autocomplete="current-password"
          placeholder="••••••••"
          :class="[
            'w-full h-9 px-3 rounded-md text-sm border transition-colors',
            'focus:outline-none focus:ring-2 focus:ring-offset-0',
            errors.password
              ? 'border-[#ef4444] focus:ring-[#ef4444]/30 bg-[#fef2f2]'
              : 'border-[#e2e8f0] bg-white focus:border-[#6366f1] focus:ring-[#6366f1]/20',
          ]"
        />
      </FormField>

      <AppButton type="submit" class="w-full" :loading="isPending">
        Sign in
      </AppButton>
    </form>

    <p class="mt-6 text-center text-sm text-[#64748b]">
      No account?
      <RouterLink to="/register" class="text-[#6366f1] hover:text-[#4f46e5] font-medium transition-colors">
        Create one
      </RouterLink>
    </p>
  </div>
</template>
