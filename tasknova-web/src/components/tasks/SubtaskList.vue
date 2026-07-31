<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { CheckCircle2, CircleDashed, ListChecks, LoaderCircle, Plus } from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import ConfirmDialog from './ConfirmDialog.vue'
import SubtaskItem from './SubtaskItem.vue'
import { useSubtasksStore } from '../../stores/subtasks'

const props = defineProps({ taskId: { type: [Number, String], required: true } })
const subtasksStore = useSubtasksStore()
const { subtasks, isLoading, isSaving, error } = storeToRefs(subtasksStore)
const form = reactive({ title: '', description: '' })
const formError = ref('')
const subtaskToDelete = ref(null)
const completedCount = computed(() => subtasks.value.filter((subtask) => subtask.completed).length)
const totalCount = computed(() => subtasks.value.length)
const progress = computed(() => totalCount.value ? Math.round((completedCount.value / totalCount.value) * 100) : 0)

async function load() {
  if (props.taskId) await subtasksStore.fetchSubtasks(props.taskId)
}

async function create() {
  if (!form.title.trim()) {
    formError.value = 'Escribe un título para la subtarea.'
    return
  }
  const position = subtasks.value.reduce((maximum, subtask) => Math.max(maximum, subtask.position || 0), -1) + 1
  const created = await subtasksStore.create(props.taskId, { title: form.title.trim(), description: form.description.trim() || null, position })
  if (created) {
    form.title = ''
    form.description = ''
    formError.value = ''
  }
}

async function update(id, payload) {
  await subtasksStore.update(props.taskId, id, payload)
}

async function toggle(subtask, completed) {
  await subtasksStore.updateStatus(props.taskId, subtask, completed)
}

async function remove() {
  if (subtaskToDelete.value && await subtasksStore.remove(props.taskId, subtaskToDelete.value.id)) subtaskToDelete.value = null
}

watch(() => props.taskId, load, { immediate: true })
</script>

<template>
  <section class="tn-card rounded-3xl bg-white/90 p-5 sm:p-6"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="flex items-center gap-2 text-sm font-semibold text-brand-ink"><ListChecks class="h-4 w-4 text-primary-600" />Subtareas</p><p class="mt-1 text-sm text-brand-muted">Divide la tarea en pasos concretos.</p></div><div class="rounded-xl bg-primary-50 px-3 py-2 text-right"><p class="text-xs font-semibold text-primary-700">{{ completedCount }} / {{ totalCount }} completadas</p><p class="mt-0.5 text-sm font-bold text-primary-800">{{ progress }} %</p></div></div>
    <div class="mt-5"><div class="flex items-center justify-between text-xs font-semibold text-brand-muted"><span>Progreso</span><span>{{ progress }} %</span></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-primary-50"><div class="h-full rounded-full bg-gradient-to-r from-primary-600 to-secondary-500 transition-all" :style="{ width: `${progress}%` }" /></div></div>
    <form class="mt-5 rounded-2xl border border-slate-100 bg-slate-50/70 p-3" @submit.prevent="create"><div class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto]"><label class="sr-only" for="subtask-title">Nueva subtarea</label><input id="subtask-title" v-model="form.title" class="tn-input py-2.5" maxlength="150" placeholder="Añadir una subtarea" /><button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 disabled:opacity-60" :disabled="isSaving"><Plus class="h-4 w-4" />Añadir</button></div><label class="sr-only" for="subtask-description">Descripción de la subtarea</label><input id="subtask-description" v-model="form.description" class="tn-input mt-2 py-2.5" placeholder="Descripción opcional" /><p v-if="formError" class="tn-field-error">{{ formError }}</p></form>
    <p v-if="error" class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ error }}</p>
    <div v-if="isLoading" class="mt-5 space-y-3"><div v-for="item in 3" :key="item" class="h-16 animate-pulse rounded-2xl bg-slate-100" /></div>
    <div v-else-if="subtasks.length" class="mt-5 space-y-2"><SubtaskItem v-for="subtask in subtasks" :key="subtask.id" :subtask="subtask" :saving="isSaving" @update="update" @toggle="toggle" @remove="subtaskToDelete = $event" /></div>
    <div v-else class="mt-5 rounded-2xl border border-dashed border-primary-200 bg-primary-50/50 px-5 py-9 text-center"><CircleDashed class="mx-auto h-7 w-7 text-primary-400" /><p class="mt-3 text-sm font-semibold text-brand-ink">Aún no hay subtareas</p><p class="mt-1 text-sm text-brand-muted">Añade pasos para avanzar con mayor claridad.</p></div>
    <ConfirmDialog :open="Boolean(subtaskToDelete)" :loading="isSaving" title="¿Eliminar subtarea?" :message="`Eliminarás “${subtaskToDelete?.title || ''}”. Esta acción no se puede deshacer.`" @confirm="remove" @cancel="subtaskToDelete = null" />
  </section>
</template>
