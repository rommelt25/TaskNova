<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Mail } from 'lucide-vue-next'
import AuthShell from '../components/auth/AuthShell.vue'
import PasswordField from '../components/auth/PasswordField.vue'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { login, isLoading, error: authError, clearError } = useAuth()

const email = ref('')
const password = ref('')
const remember = ref(false)
const touched = ref({ email: false, password: false })

const emailError = computed(() => {
  if (!touched.value.email) return ''
  if (!email.value) return 'Ingresa tu correo electrónico.'
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) return 'Ingresa un correo electrónico válido.'
  return ''
})

const passwordError = computed(() => (touched.value.password && !password.value ? 'Ingresa tu contraseña.' : ''))
const formValid = computed(() => Boolean(email.value && password.value && !emailError.value))

onMounted(clearError)

async function handleLogin() {
  touched.value = { email: true, password: true }
  if (!formValid.value) return

  const success = await login(email.value.trim(), password.value, remember.value)
  if (success) router.replace('/dashboard')
}
</script>

<template>
  <AuthShell compact title="Bienvenido" subtitle="Gestiona tus tareas académicas con precisión.">
    <form class="space-y-5" novalidate @submit.prevent="handleLogin">
      <div>
        <label for="login-email" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted">
          Correo electrónico
        </label>
        <div class="relative">
          <Mail class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" aria-hidden="true" />
          <input
            id="login-email"
            v-model.trim="email"
            type="email"
            inputmode="email"
            autocomplete="email"
            placeholder="estudiante@unap.edu.pe"
            class="tn-input pl-11"
            :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/10': emailError }"
            @input="touched.email = true"
            @blur="touched.email = true"
          />
        </div>
        <p v-if="emailError" class="mt-1.5 text-xs text-red-600">{{ emailError }}</p>
      </div>

      <PasswordField
        id="login-password"
        :model-value="password"
        label="Contraseña"
        autocomplete="current-password"
        :error="passwordError"
        @update:model-value="(value) => { password = value; touched.password = true }"
        @blur="touched.password = true"
      />

      <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
        <label class="flex cursor-pointer items-center gap-2 text-brand-muted">
          <input v-model="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
          Recordarme
        </label>
        <router-link to="/forgot-password" class="font-semibold text-primary-700 transition-colors hover:text-secondary-600 hover:underline">
          Olvidé mi contraseña
        </router-link>
      </div>

      <p v-if="authError" role="alert" class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ authError }}
      </p>

      <button type="submit" :disabled="isLoading || !formValid" class="tn-button-primary">
        <span class="relative z-10 flex items-center justify-center gap-2">
          <svg v-if="isLoading" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
          </svg>
          {{ isLoading ? 'Iniciando sesión…' : 'Iniciar sesión' }}
        </span>
      </button>
    </form>

    <p class="mt-6 text-center text-sm text-brand-muted">
      ¿Aún no tienes cuenta?
      <router-link to="/register" class="font-semibold text-primary-700 hover:text-secondary-600 hover:underline">Crear cuenta</router-link>
    </p>
  </AuthShell>
</template>
