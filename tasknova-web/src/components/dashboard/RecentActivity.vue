<script setup>
import { Activity, CheckCircle2, Pencil, PlusCircle } from 'lucide-vue-next'

defineProps({
  items: { type: Array, default: () => [] },
})

function activityIcon(type) {
  return { created: PlusCircle, updated: Pencil, completed: CheckCircle2 }[type] || Activity
}

function activityTone(type) {
  return { created: 'bg-primary-50 text-primary-700', updated: 'bg-amber-50 text-amber-600', completed: 'bg-emerald-50 text-emerald-600' }[type] || 'bg-slate-100 text-slate-600'
}
</script>

<template>
  <section class="tn-card rounded-3xl bg-white/90 p-5 sm:p-6">
    <div class="mb-5 flex items-center justify-between gap-3">
      <div><h2 class="font-display text-lg font-bold text-brand-ink">Actividad reciente</h2><p class="mt-1 text-sm text-brand-muted">Tus últimos movimientos.</p></div>
      <Activity class="h-5 w-5 text-primary-600" aria-hidden="true" />
    </div>
    <div v-if="!items.length" class="rounded-2xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-brand-muted">Aún no hay actividad reciente.</div>
    <ol v-else class="space-y-4">
      <li v-for="item in items" :key="item.id || `${item.type}-${item.created_at}`" class="flex gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" :class="activityTone(item.type)"><component :is="activityIcon(item.type)" class="h-4 w-4" aria-hidden="true" /></span>
        <div class="min-w-0"><p class="text-sm font-semibold text-brand-ink">{{ item.title || item.description }}</p><p v-if="item.created_at" class="mt-0.5 text-xs text-brand-muted">{{ new Date(item.created_at).toLocaleString('es-PE', { dateStyle: 'medium', timeStyle: 'short' }) }}</p></div>
      </li>
    </ol>
  </section>
</template>
