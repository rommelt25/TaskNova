<script setup>
import { computed } from 'vue'
import { Check, CheckCircle2, ChevronRight, Eye, Pencil, Trash2 } from 'lucide-vue-next'
import TaskBadge from './TaskBadge.vue'

const props = defineProps({ tasks: { type: Array, default: () => [] }, loading: Boolean })
defineEmits(['view', 'edit', 'delete', 'toggle'])

function isOverdue(task) {
  return task.status !== 'completed' && task.due_date && new Date(`${task.due_date}T23:59:59`) < new Date()
}
function taskStatus(task) { return isOverdue(task) ? 'overdue' : task.status }
function formatDate(date) { return date ? new Intl.DateTimeFormat('es-PE', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(`${date}T00:00:00`)) : '—' }
const skeletonRows = computed(() => Array.from({ length: 5 }, (_, index) => index))
</script>

<template>
  <div class="tn-card overflow-hidden rounded-3xl bg-white/90">
    <div v-if="loading" class="space-y-3 p-5"><div v-for="row in skeletonRows" :key="row" class="h-14 animate-pulse rounded-xl bg-slate-100" /></div>
    <template v-else>
      <div class="hidden overflow-x-auto md:block"><table class="w-full min-w-[840px] text-left text-sm"><thead class="border-b border-slate-100 bg-slate-50/70 text-xs uppercase tracking-wider text-brand-muted"><tr><th class="px-5 py-4">Estado</th><th class="px-5 py-4">Título</th><th class="px-5 py-4">Categoría</th><th class="px-5 py-4">Prioridad</th><th class="px-5 py-4">Fecha</th><th class="px-5 py-4">Hora</th><th class="px-5 py-4 text-right">Acciones</th></tr></thead><tbody class="divide-y divide-slate-100"><tr v-for="task in tasks" :key="task.id" class="transition hover:bg-primary-50/35"><td class="px-5 py-4"><TaskBadge type="status" :value="taskStatus(task)" /></td><td class="max-w-xs px-5 py-4"><p class="truncate font-semibold text-brand-ink">{{ task.title }}</p><p v-if="task.description" class="mt-1 truncate text-xs text-brand-muted">{{ task.description }}</p></td><td class="px-5 py-4 text-brand-muted">{{ task.category || task.subject }}</td><td class="px-5 py-4"><TaskBadge type="priority" :value="task.priority" /></td><td class="whitespace-nowrap px-5 py-4 text-brand-muted">{{ formatDate(task.due_date) }}</td><td class="px-5 py-4 text-brand-muted">{{ task.due_time || '—' }}</td><td class="px-5 py-4"><div class="flex justify-end gap-1"><button class="tn-icon-button" title="Ver detalle" @click="$emit('view', task)"><Eye class="h-4 w-4" /></button><button class="tn-icon-button" title="Editar tarea" :disabled="task.can_manage === false" @click="$emit('edit', task)"><Pencil class="h-4 w-4" /></button><button class="tn-icon-button text-emerald-600" :title="task.status === 'completed' ? 'Marcar como pendiente' : 'Completar tarea'" :disabled="task.can_manage === false" @click="$emit('toggle', task)"><CheckCircle2 class="h-4 w-4" /></button><button class="tn-icon-button text-red-600" title="Eliminar tarea" :disabled="task.can_manage === false" @click="$emit('delete', task)"><Trash2 class="h-4 w-4" /></button></div></td></tr></tbody></table></div>
      <div class="divide-y divide-slate-100 md:hidden"><article v-for="task in tasks" :key="task.id" class="p-4"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="truncate font-semibold text-brand-ink">{{ task.title }}</p><p class="mt-1 text-xs text-brand-muted">{{ task.category || task.subject }} · {{ formatDate(task.due_date) }}{{ task.due_time ? ` · ${task.due_time}` : '' }}</p></div><TaskBadge type="status" :value="taskStatus(task)" /></div><div class="mt-3 flex items-center justify-between"><TaskBadge type="priority" :value="task.priority" /><div class="flex gap-1"><button class="tn-icon-button" title="Ver detalle" @click="$emit('view', task)"><ChevronRight class="h-4 w-4" /></button><button class="tn-icon-button text-emerald-600" :disabled="task.can_manage === false" @click="$emit('toggle', task)"><Check class="h-4 w-4" /></button><button class="tn-icon-button text-red-600" :disabled="task.can_manage === false" @click="$emit('delete', task)"><Trash2 class="h-4 w-4" /></button></div></div></article></div>
    </template>
  </div>
</template>
