<script setup>
import { computed } from 'vue'

const props = defineProps({ type: { type: String, required: true }, value: { type: String, required: true } })

const options = {
  status: {
    pending: ['Pendiente', 'bg-amber-50 text-amber-700 ring-amber-200'],
    completed: ['Completada', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
    overdue: ['Vencida', 'bg-red-50 text-red-700 ring-red-200'],
    in_progress: ['En progreso', 'bg-blue-50 text-blue-700 ring-blue-200'],
  },
  priority: {
    high: ['Alta', 'bg-red-50 text-red-700 ring-red-200'],
    medium: ['Media', 'bg-amber-50 text-amber-700 ring-amber-200'],
    low: ['Baja', 'bg-sky-50 text-sky-700 ring-sky-200'],
  },
}

const badge = computed(() => options[props.type]?.[props.value] || [props.value, 'bg-slate-100 text-slate-700 ring-slate-200'])
</script>

<template>
  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset" :class="badge[1]">{{ badge[0] }}</span>
</template>
