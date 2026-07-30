<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Mail, UserRound } from 'lucide-vue-next'
import AuthShell from '../components/auth/AuthShell.vue'
import PasswordField from '../components/auth/PasswordField.vue'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { register, isLoading, error: authError, clearError } = useAuth()

const firstName = ref('')
const lastName = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const touched = ref({ firstName: false, lastName: false, email: false, password: false, passwordConfirmation: false })

const emailError = computed(() => {
  if (!touched.value.email) return ''
  if (!email.value) return 'Ingresa tu correo electrónico.'
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) return 'Ingresa un correo electrónico válido.'
  return ''
})
const firstNameError = computed(() => (touched.value.firstName && !firstName.value.trim() ? 'Ingresa tus nombres.' : ''))
const lastNameError = computed(() => (touched.value.lastName && !lastName.value.trim() ? 'Ingresa tus apellidos.' : ''))
const passwordError = computed(() => {
  if (!touched.value.password) return ''
  if (!password.value) return 'Crea una contraseña.'
  if (password.value.length < 8) return 'Usa al menos 8 caracteres.'
  return ''
})
const passwordConfirmationError = computed(() => {
  if (!touched.value.passwordConfirmation) return ''
  if (!passwordConfirmation.value) return 'Confirma tu contraseña.'
  if (passwordConfirmation.value !== password.value) return 'Las contraseñas no coinciden.'
  return ''
})

const passwordStrength = computed(() => {
  const value = password.value
  const score = [value.length >= 8, /[A-Z]/.test(value), /\d/.test(value), /[^A-Za-z0-9]/.test(value)].filter(Boolean).length
  const labels = ['Muy débil', 'Débil', 'Aceptable', 'Segura', 'Muy segura']
  const colors = ['bg-slate-200', 'bg-red-500', 'bg-amber-500', 'bg-primary-500', 'bg-emerald-500']
  return { score, label: labels[score], color: colors[score] }
})

const formValid = computed(() => Boolean(
  firstName.value.trim() && lastName.value.trim() && email.value && password.value.length >= 8 && password.value === passwordConfirmation.value && !emailError.value
))

onMounted(clearError)

async function handleRegister() {
  touched.value = { firstName: true, lastName: true, email: true, password: true, passwordConfirmation: true }
  if (!formValid.value) return

  const success = await register({
    firstName: firstName.value,
    lastName: lastName.value,
    email: email.value.trim(),
    password: password.value,
  })

  if (success) router.replace({ name: 'complete-profile' })
}
</script>

<template>
  <AuthShell title="Crea tu cuenta" subtitle="Empieza a organizar tu vida académica con TaskNova.">
    <form class="space-y-5" novalidate @submit.prevent="handleRegister">
      <div class="grid gap-5 sm:grid-cols-2">
        <div>
          <label for="first-name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted">Nombres</label>
          <div class="relative">
            <UserRound class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" aria-hidden="true" />
            <input id="first-name" v-model="firstName" type="text" autocomplete="given-name" class="tn-input pl-11" :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/10': firstNameError }" @input="touched.firstName = true" @blur="touched.firstName = true" />
          </div>
          <p v-if="firstNameError" class="mt-1.5 text-xs text-red-600">{{ firstNameError }}</p>
        </div>
        <div>
          <label for="last-name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted">Apellidos</label>
          <input id="last-name" v-model="lastName" type="text" autocomplete="family-name" class="tn-input" :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/10': lastNameError }" @input="touched.lastName = true" @blur="touched.lastName = true" />
          <p v-if="lastNameError" class="mt-1.5 text-xs text-red-600">{{ lastNameError }}</p>
        </div>
      </div>

      <div>
        <label for="register-email" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted">Correo electrónico</label>
        <div class="relative">
          <Mail class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" aria-hidden="true" />
          <input id="register-email" v-model.trim="email" type="email" inputmode="email" autocomplete="email" placeholder="estudiante@unap.edu.pe" class="tn-input pl-11" :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/10': emailError }" @input="touched.email = true" @blur="touched.email = true" />
        </div>
        <p v-if="emailError" class="mt-1.5 text-xs text-red-600">{{ emailError }}</p>
      </div>

      <PasswordField id="register-password" :model-value="password" label="Contraseña" autocomplete="new-password" :error="passwordError" @update:model-value="(value) => { password = value; touched.password = true }" @blur="touched.password = true" />
      <div class="-mt-2" aria-live="polite">
        <div class="mb-1.5 flex items-center justify-between text-xs">
          <span class="text-brand-muted">Fortaleza de contraseña</span>
          <span class="font-semibold text-brand-ink">{{ passwordStrength.label }}</span>
        </div>
        <div class="grid grid-cols-4 gap-1.5">
          <span v-for="index in 4" :key="index" class="h-1.5 rounded-full" :class="index <= passwordStrength.score ? passwordStrength.color : 'bg-slate-200'" />
        </div>
      </div>

      <PasswordField id="register-password-confirmation" :model-value="passwordConfirmation" label="Confirmar contraseña" autocomplete="new-password" :error="passwordConfirmationError" @update:model-value="(value) => { passwordConfirmation = value; touched.passwordConfirmation = true }" @blur="touched.passwordConfirmation = true" />

      <p v-if="authError" role="alert" class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">{{ authError }}</p>

      <button type="submit" :disabled="isLoading || !formValid" class="tn-button-primary">
        <span class="relative z-10 flex items-center justify-center gap-2">
          <svg v-if="isLoading" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
          {{ isLoading ? 'Creando cuenta…' : 'Crear cuenta' }}
        </span>
      </button>
    </form>

    <router-link to="/login" class="mt-6 flex items-center justify-center text-sm font-semibold text-primary-700 hover:text-secondary-600 hover:underline">Volver al inicio de sesión</router-link>
  </AuthShell>
</template>
