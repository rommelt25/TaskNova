<script setup>
import { CalendarClock, Flag } from 'lucide-vue-next'

defineProps({ tasks: { type: Array, default: () => [] } })

function priorityClass(priority) {
  return { high: 'bg-red-50 text-red-600', medium: 'bg-amber-50 text-amber-700', low: 'bg-emerald-50 text-emerald-700' }[priority] || 'bg-slate-100 text-slate-600'
}

function priorityLabel(priority) {
  return { high: 'Alta', medium: 'Media', low: 'Baja' }[priority] || 'Sin prioridad'
}

function formatDate(task) {
  if (!task.due_date) return 'Sin fecha programada'
  const date = new Date(`${task.due_date}T${task.due_time || '00:00:00'}`)
  return new Intl.DateTimeFormat('es-PE', { day: 'numeric', month: 'short', ...(task.due_time ? { hour: '2-digit', minute: '2-digit' } : {}) }).format(date)
}
</script>

<template>
  <section id="upcoming-tasks" class="tn-card rounded-3xl bg-white/90 p-5 sm:p-6">
    <div class="mb-5 flex items-center justify-between gap-3"><div><h2 class="font-display text-lg font-bold text-brand-ink">Próximas tareas</h2><p class="mt-1 text-sm text-brand-muted">Lo que sigue en tu agenda.</p></div><CalendarClock class="h-5 w-5 text-primary-600" aria-hidden="true" /></div>
    <div v-if="!tasks.length" class="rounded-2xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-brand-muted">No tienes tareas programadas.</div>
    <ul v-else class="space-y-3"><li v-for="task in tasks" :key="task.id" class="rounded-2xl border border-slate-100 bg-white/70 p-4"><div class="flex flex-wrap items-start justify-between gap-3"><div class="min-w-0"><p class="truncate font-semibold text-brand-ink">{{ task.title }}</p><p class="mt-1 text-sm text-brand-muted">{{ formatDate(task) }}</p></div><span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="priorityClass(task.priority)"><Flag class="h-3.5 w-3.5" aria-hidden="true" />{{ priorityLabel(task.priority) }}</span></div></li></ul>
  </section>
</template>
