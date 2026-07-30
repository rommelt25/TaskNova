<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, CloudOff, Home, RefreshCw, ServerCrash, TriangleAlert } from 'lucide-vue-next'
import { useAuth } from '../composables/useAuth'

const props = defineProps({ code: { type: String, default: '404' } })
const router = useRouter()
const { isAuthenticated } = useAuth()

const content = computed(() => ({
  '404': { icon: TriangleAlert, title: 'No encontramos esta página', message: 'La dirección puede haber cambiado o ya no estar disponible.' },
  '500': { icon: ServerCrash, title: 'Ocurrió un problema inesperado', message: 'No pudimos completar la operación. Inténtalo nuevamente en unos minutos.' },
  offline: { icon: CloudOff, title: 'No hay conexión con el servidor', message: 'Revisa tu conexión a internet e inténtalo de nuevo.' },
}[props.code] || { icon: TriangleAlert, title: 'Ocurrió un error', message: 'No pudimos completar la operación.' }))

function goBack() { router.back() }
function goHome() { router.push(isAuthenticated.value ? '/dashboard' : '/login') }
function retry() { window.location.reload() }
</script>

<template>
  <main class="tn-page flex min-h-screen items-center justify-center px-4 py-10">
    <section class="tn-card w-full max-w-lg rounded-3xl bg-white/90 p-7 text-center sm:p-10">
      <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-50 text-primary-700"><component :is="content.icon" class="h-8 w-8" /></span>
      <p class="mt-6 text-sm font-bold uppercase tracking-wider text-primary-600">Error {{ code }}</p>
      <h1 class="mt-2 font-display text-2xl font-bold text-brand-ink">{{ content.title }}</h1>
      <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-brand-muted">{{ content.message }}</p>
      <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
        <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-brand-muted transition hover:bg-slate-100" @click="goBack"><ArrowLeft class="h-4 w-4" />Volver</button>
        <button v-if="code !== '404'" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-primary-200 bg-primary-50 px-5 py-3 text-sm font-semibold text-primary-700 transition hover:bg-primary-100" @click="retry"><RefreshCw class="h-4 w-4" />Reintentar</button>
        <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white shadow-glow" @click="goHome"><Home class="h-4 w-4" />{{ isAuthenticated ? 'Ir al Dashboard' : 'Ir al inicio de sesión' }}</button>
      </div>
    </section>
  </main>
</template>
