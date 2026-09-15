<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { registerSchema } from '../schemas/auth.schema'
import { useRegisterMutation } from '../api/auth.mutations'
import FormField from '@/shared/components/molecules/FormField.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'

const { handleSubmit, defineField, errors, values } = useForm({
  validationSchema: toTypedSchema(registerSchema),
})

const [name, nameAttrs] = defineField('name')
const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')
const [passwordConfirmation, passwordConfirmationAttrs] = defineField('password_confirmation')

const { mutate: register, isPending, error: mutationError } = useRegisterMutation()

const onSubmit = handleSubmit((vals) => register(vals))

// Password strength
const passwordStrength = computed(() => {
  const pw = values.password ?? ''
  if (!pw) return 0
  let score = 0
  if (pw.length >= 8) score++
  if (/[A-Z]/.test(pw)) score++
  if (/[0-9]/.test(pw)) score++
  if (/[^A-Za-z0-9]/.test(pw)) score++
  return score
})

const strengthColors = ['bg-[#e2e8f0]', 'bg-[#ef4444]', 'bg-[#f59e0b]', 'bg-[#6366f1]', 'bg-[#10b981]']
const strengthLabels = ['', 'Weak', 'Fair', 'Good', 'Strong']
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold tracking-tight text-[#0f172a]">Create account</h1>
    <p class="mt-1 text-sm text-[#64748b]">Start tailoring your CV to every job.</p>

    <form class="mt-8 space-y-5" novalidate @submit="onSubmit">
      <div v-if="mutationError" class="px-4 py-3 rounded-md bg-[#fef2f2] border border-[#fecaca] text-sm text-[#dc2626]" role="alert">
        {{ (mutationError as Error).message || 'Registration failed. Please try again.' }}
      </div>

      <FormField label="Full name" :error="errors.name" html-for="name" required>
        <input id="name" v-model="name" v-bind="nameAttrs" type="text" autocomplete="name" placeholder="Nguyen Anh Tu"
          :class="['w-full h-9 px-3 rounded-md text-sm border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0', errors.name ? 'border-[#ef4444] focus:ring-[#ef4444]/30 bg-[#fef2f2]' : 'border-[#e2e8f0] bg-white focus:border-[#6366f1] focus:ring-[#6366f1]/20']"
        />
      </FormField>

      <FormField label="Email address" :error="errors.email" html-for="reg-email" required>
        <input id="reg-email" v-model="email" v-bind="emailAttrs" type="email" autocomplete="email" placeholder="you@example.com"
          :class="['w-full h-9 px-3 rounded-md text-sm border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0', errors.email ? 'border-[#ef4444] focus:ring-[#ef4444]/30 bg-[#fef2f2]' : 'border-[#e2e8f0] bg-white focus:border-[#6366f1] focus:ring-[#6366f1]/20']"
        />
      </FormField>

      <FormField label="Password" :error="errors.password" html-for="reg-password" required>
        <input id="reg-password" v-model="password" v-bind="passwordAttrs" type="password" autocomplete="new-password" placeholder="••••••••"
          :class="['w-full h-9 px-3 rounded-md text-sm border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0', errors.password ? 'border-[#ef4444] focus:ring-[#ef4444]/30 bg-[#fef2f2]' : 'border-[#e2e8f0] bg-white focus:border-[#6366f1] focus:ring-[#6366f1]/20']"
        />
        <!-- Password strength meter -->
        <div v-if="password" class="mt-2 flex gap-1">
          <div
            v-for="i in 4"
            :key="i"
            :class="['h-1 flex-1 rounded-full transition-colors duration-300', i <= passwordStrength ? strengthColors[passwordStrength] : 'bg-[#e2e8f0]']"
          />
        </div>
        <p v-if="password && passwordStrength > 0" class="mt-1 text-xs text-[#64748b]">
          {{ strengthLabels[passwordStrength] }} password
        </p>
      </FormField>

      <FormField label="Confirm password" :error="errors.password_confirmation" html-for="password-confirm" required>
        <input id="password-confirm" v-model="passwordConfirmation" v-bind="passwordConfirmationAttrs" type="password" autocomplete="new-password" placeholder="••••••••"
          :class="['w-full h-9 px-3 rounded-md text-sm border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0', errors.password_confirmation ? 'border-[#ef4444] focus:ring-[#ef4444]/30 bg-[#fef2f2]' : 'border-[#e2e8f0] bg-white focus:border-[#6366f1] focus:ring-[#6366f1]/20']"
        />
      </FormField>

      <AppButton type="submit" class="w-full" :loading="isPending">
        Create account
      </AppButton>
    </form>

    <p class="mt-6 text-center text-sm text-[#64748b]">
      Already have an account?
      <RouterLink to="/login" class="text-[#6366f1] hover:text-[#4f46e5] font-medium transition-colors">
        Sign in
      </RouterLink>
    </p>
  </div>
</template>
