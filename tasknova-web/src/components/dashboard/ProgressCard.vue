<script setup>
import { computed } from 'vue'

const props = defineProps({ summary: { type: Object, default: null } })

const completed = computed(() => props.summary?.completed_tasks ?? props.summary?.completed ?? 0)
const total = computed(() => props.summary?.total_tasks ?? props.summary?.total ?? 0)
const percentage = computed(() => {
  const fromApi = props.summary?.completion_percentage
  if (typeof fromApi === 'number') return Math.min(100, Math.max(0, Math.round(fromApi)))
  return total.value ? Math.round((completed.value / total.value) * 100) : 0
})
</script>

<template>
  <section class="tn-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-600 to-secondary-600 p-5 text-white shadow-glow sm:p-6">
    <div class="absolute -right-12 -top-12 h-36 w-36 rounded-full bg-white/10 blur-2xl" />
    <div class="absolute -bottom-12 -left-12 h-36 w-36 rounded-full bg-white/10 blur-2xl" />
    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
      <div><p class="text-xs font-semibold uppercase tracking-wider text-white/80">Progreso general</p><h2 class="mt-2 font-display text-2xl font-bold">{{ percentage === 100 && total ? '¡Objetivo completado!' : 'Sigue construyendo tu ritmo.' }}</h2><p class="mt-2 text-sm text-white/85">{{ completed }} de {{ total }} tareas completadas.</p></div>
      <div class="text-left sm:text-right"><span class="font-display text-4xl font-bold">{{ percentage }}%</span><p class="text-xs font-semibold uppercase tracking-wider text-white/70">Completado</p></div>
    </div>
    <div class="relative mt-5 h-2.5 overflow-hidden rounded-full bg-white/20"><div class="h-full rounded-full bg-white transition-all duration-700" :style="{ width: `${percentage}%` }" /></div>
  </section>
</template>
