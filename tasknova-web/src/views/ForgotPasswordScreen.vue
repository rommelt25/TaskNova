<script setup>
import { computed, onMounted, ref } from 'vue'
import { Mail, CheckCircle2 } from 'lucide-vue-next'
import AuthShell from '../components/auth/AuthShell.vue'
import { useAuth } from '../composables/useAuth'

const { requestPasswordReset, isLoading, error: authError, clearError } = useAuth()
const email = ref('')
const touched = ref(false)
const success = ref(false)

const emailError = computed(() => {
  if (!touched.value) return ''
  if (!email.value) return 'Ingresa tu correo electrónico.'
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) return 'Ingresa un correo electrónico válido.'
  return ''
})

onMounted(clearError)

async function handleSubmit() {
  touched.value = true
  if (emailError.value) return
  success.value = await requestPasswordReset(email.value.trim())
}
</script>

<template>
  <AuthShell compact title="Recupera tu contraseña" subtitle="Te enviaremos un enlace para que puedas crear una nueva contraseña.">
    <div v-if="success" class="space-y-6">
      <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 text-center">
        <CheckCircle2 class="mx-auto h-10 w-10 text-emerald-600" aria-hidden="true" />
        <h2 class="mt-3 font-display text-xl font-bold text-brand-ink">Revisa tu correo</h2>
        <p class="mt-2 text-sm leading-6 text-brand-muted">Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.</p>
      </div>
      <router-link to="/login" class="tn-button-primary flex items-center justify-center">Volver al inicio de sesión</router-link>
    </div>

    <form v-else class="space-y-5" novalidate @submit.prevent="handleSubmit">
      <div>
        <label for="reset-email" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted">Correo electrónico</label>
        <div class="relative">
          <Mail class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" aria-hidden="true" />
          <input id="reset-email" v-model.trim="email" type="email" inputmode="email" autocomplete="email" placeholder="estudiante@unap.edu.pe" class="tn-input pl-11" :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/10': emailError }" @input="touched = true" @blur="touched = true" />
        </div>
        <p v-if="emailError" class="mt-1.5 text-xs text-red-600">{{ emailError }}</p>
      </div>

      <p v-if="authError" role="alert" class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">{{ authError }}</p>

      <button type="submit" :disabled="isLoading || Boolean(emailError) || !email" class="tn-button-primary">
        <span class="relative z-10 flex items-center justify-center gap-2">
          <svg v-if="isLoading" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
          {{ isLoading ? 'Enviando enlace…' : 'Enviar enlace' }}
        </span>
      </button>

      <router-link to="/login" class="flex justify-center text-sm font-semibold text-primary-700 hover:text-secondary-600 hover:underline">Volver al inicio de sesión</router-link>
    </form>
  </AuthShell>
</template>
