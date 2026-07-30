<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { CheckCircle2, CircleDashed, ClipboardList, Clock3, Plus, CalendarDays, UserRound, LoaderCircle, AlertCircle, ListTodo } from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import BottomNav from '../components/navigation/BottomNav.vue'
import ProgressCard from '../components/dashboard/ProgressCard.vue'
import PriorityTasks from '../components/dashboard/PriorityTasks.vue'
import RecentActivity from '../components/dashboard/RecentActivity.vue'
import StatCard from '../components/dashboard/StatCard.vue'
import { useAuth } from '../composables/useAuth'
import { useDashboardStore } from '../stores/dashboard'

const router = useRouter()
const { user } = useAuth()
const dashboardStore = useDashboardStore()
const { summary, upcomingTasks, recentActivity, isLoading, error } = storeToRefs(dashboardStore)

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Buenos días'
  if (hour < 19) return 'Buenas tardes'
  return 'Buenas noches'
})

const currentDate = computed(() => new Intl.DateTimeFormat('es-PE', { weekday: 'long', day: 'numeric', month: 'long' }).format(new Date()))
const firstName = computed(() => user.value?.name?.trim().split(/\s+/)[0] || 'estudiante')
const totalTasks = computed(() => summary.value?.total_tasks ?? summary.value?.total ?? null)
const isEmpty = computed(() => Number(totalTasks.value) === 0)

function summaryValue(...keys) {
  if (!summary.value) return '—'
  return keys.map((key) => summary.value[key]).find((value) => value !== undefined && value !== null) ?? '—'
}

function openProfile() {
  router.push('/profile')
}

function openCalendar() {
  router.push('/calendar')
}

function openTasks() {
  router.push('/tasks')
}

function createTask() {
  router.push('/tasks/new')
}

onMounted(() => dashboardStore.fetchDashboard())
</script>

<template>
  <div class="tn-page min-h-screen pb-28">
    <header class="tn-header sticky top-0 z-10 border-b backdrop-blur-md">
      <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
        <div class="min-w-0"><p class="capitalize text-xs font-semibold text-brand-muted">{{ currentDate }}</p><h1 class="mt-1 truncate font-display text-xl font-bold text-brand-ink sm:text-2xl">{{ greeting }}, {{ firstName }} <span aria-hidden="true">👋</span></h1><p class="mt-1 hidden text-sm text-brand-muted sm:block">Organiza tu tiempo, cumple tus objetivos.</p></div>
        <button type="button" class="flex shrink-0 items-center gap-3 rounded-2xl p-1.5 pr-3 text-left transition hover:bg-primary-50" @click="openProfile">
          <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white shadow-soft"><img v-if="user?.avatar_url" :src="user.avatar_url" alt="Avatar de usuario" class="h-full w-full object-cover" /><span v-else class="font-display text-lg font-bold">{{ firstName.charAt(0).toUpperCase() }}</span></div>
          <span class="hidden text-sm font-semibold text-primary-700 sm:block">Editar perfil</span>
        </button>
      </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">
      <div v-if="error" role="alert" class="flex gap-3 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm text-red-700"><AlertCircle class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true" /><span>{{ error }}</span></div>

      <div v-if="isLoading" class="tn-card flex items-center justify-center gap-3 rounded-3xl bg-white/90 p-10 text-brand-muted"><LoaderCircle class="h-5 w-5 animate-spin text-primary-600" aria-hidden="true" />Cargando tu resumen…</div>

      <template v-else>
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <StatCard :icon="ClipboardList" title="Total de tareas" :value="summaryValue('total_tasks', 'total')" tone="primary" />
          <StatCard :icon="CircleDashed" title="Tareas pendientes" :value="summaryValue('pending_tasks', 'pending')" tone="warning" />
          <StatCard :icon="CheckCircle2" title="Tareas completadas" :value="summaryValue('completed_tasks', 'completed')" tone="success" />
          <StatCard :icon="Clock3" title="Tareas vencidas" :value="summaryValue('overdue_tasks', 'overdue')" tone="danger" />
        </section>

        <section v-if="isEmpty" class="tn-card rounded-3xl bg-white/90 px-6 py-10 text-center sm:px-10">
          <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-700"><ListTodo class="h-7 w-7" aria-hidden="true" /></span>
          <h2 class="mt-5 font-display text-2xl font-bold text-brand-ink">Aún no has creado tareas.</h2>
          <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-brand-muted">Cuando tengas tu primera tarea, aquí encontrarás tu progreso, próximos compromisos y actividad reciente.</p>
          <button type="button" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white shadow-glow transition hover:-translate-y-0.5" @click="createTask"><Plus class="h-4 w-4" aria-hidden="true" />Crear primera tarea</button>
        </section>

        <template v-else>
          <ProgressCard :summary="summary" />

          <section aria-label="Accesos rápidos">
            <div class="mb-3 flex items-center justify-between"><h2 class="font-display text-lg font-bold text-brand-ink">Accesos rápidos</h2><span class="text-sm text-brand-muted">Atajos de tu espacio</span></div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
              <button type="button" class="tn-card flex flex-col items-start gap-3 rounded-2xl bg-white/75 p-4 text-left transition hover:-translate-y-0.5" @click="createTask"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-50 text-primary-700"><Plus class="h-5 w-5" aria-hidden="true" /></span><span class="text-sm font-semibold text-brand-ink">Nueva tarea</span></button>
              <button type="button" class="tn-card flex flex-col items-start gap-3 rounded-2xl bg-white/75 p-4 text-left transition hover:-translate-y-0.5" @click="openTasks"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><ClipboardList class="h-5 w-5" aria-hidden="true" /></span><span class="text-sm font-semibold text-brand-ink">Ver todas las tareas</span></button>
              <button type="button" class="tn-card flex flex-col items-start gap-3 rounded-2xl bg-white/75 p-4 text-left transition hover:-translate-y-0.5" @click="openProfile"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-secondary-50 text-secondary-600"><UserRound class="h-5 w-5" aria-hidden="true" /></span><span class="text-sm font-semibold text-brand-ink">Mi Perfil</span></button>
              <button type="button" class="tn-card flex flex-col items-start gap-3 rounded-2xl bg-white/75 p-4 text-left transition hover:-translate-y-0.5" @click="openCalendar"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><CalendarDays class="h-5 w-5" aria-hidden="true" /></span><span class="text-sm font-semibold text-brand-ink">Calendario</span></button>
            </div>
          </section>

          <div class="grid gap-6 lg:grid-cols-2"><PriorityTasks :tasks="upcomingTasks" /><RecentActivity :items="recentActivity" /></div>
        </template>
      </template>
    </main>

    <BottomNav />
  </div>
</template>
