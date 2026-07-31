<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeft, CalendarDays, CheckCircle2, Clock3, Edit3, LoaderCircle, Tag, Trash2 } from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import ConfirmDialog from '../components/tasks/ConfirmDialog.vue'
import TaskAttachments from '../components/tasks/TaskAttachments.vue'
import TaskBadge from '../components/tasks/TaskBadge.vue'
import SubtaskList from '../components/tasks/SubtaskList.vue'
import TaskToast from '../components/tasks/TaskToast.vue'
import { useTasksStore } from '../stores/tasks'

const route = useRoute()
const router = useRouter()
const taskStore = useTasksStore()
const { isLoading, isSaving, error, successMessage } = storeToRefs(taskStore)
const confirmDelete = ref(false)
const task = computed(() => taskStore.tasks.find((item) => String(item.id) === String(route.params.id)) || null)

function formatDate(value, withTime = false) {
  return value ? new Intl.DateTimeFormat('es-PE', withTime ? { dateStyle: 'medium', timeStyle: 'short' } : { dateStyle: 'long' }).format(new Date(value)) : '—'
}

function isOverdue(item) {
  return item?.status !== 'completed' && item?.due_date && new Date(`${item.due_date}T23:59:59`) < new Date()
}

async function toggleStatus() {
  if (task.value) await taskStore.changeStatus(task.value, task.value.status === 'completed' ? 'pending' : 'completed')
}

async function remove() {
  if (task.value && await taskStore.deleteTask(task.value)) router.replace('/tasks')
}

onMounted(async () => {
  if (!task.value) await taskStore.fetchTask(route.params.id)
})
</script>

<template>
  <div class="tn-page min-h-screen pb-10">
    <header class="tn-header sticky top-0 z-10 border-b backdrop-blur-md">
      <div class="mx-auto flex max-w-4xl items-center gap-3 px-4 py-4 sm:px-6">
        <button class="tn-icon-button" aria-label="Volver a tareas" @click="router.push('/tasks')"><ArrowLeft class="h-5 w-5" /></button>
        <div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-wider text-primary-600">Detalle de tarea</p><h1 class="mt-1 truncate font-display text-xl font-bold text-brand-ink">{{ task?.title || 'Tarea' }}</h1></div>
      </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-6 sm:px-6">
      <div v-if="isLoading" class="tn-card flex items-center justify-center gap-3 rounded-3xl bg-white/90 p-10 text-brand-muted"><LoaderCircle class="h-5 w-5 animate-spin text-primary-600" />Cargando tarea…</div>
      <div v-else-if="task" class="space-y-5">
        <section class="tn-card rounded-3xl bg-white/90 p-6 sm:p-8">
          <div class="flex flex-col justify-between gap-5 sm:flex-row">
            <div>
              <div class="flex flex-wrap gap-2"><TaskBadge type="status" :value="isOverdue(task) ? 'overdue' : task.status" /><TaskBadge type="priority" :value="task.priority" /></div>
              <h2 class="mt-5 font-display text-2xl font-bold text-brand-ink">{{ task.title }}</h2>
              <p class="mt-3 whitespace-pre-line leading-7 text-brand-muted">{{ task.description }}</p>
            </div>
            <div v-if="task.can_manage !== false" class="flex shrink-0 items-start gap-2"><button class="tn-icon-button" title="Editar tarea" @click="router.push(`/tasks/${task.id}/edit`)"><Edit3 class="h-5 w-5" /></button><button class="tn-icon-button text-red-600" title="Eliminar tarea" @click="confirmDelete = true"><Trash2 class="h-5 w-5" /></button></div>
          </div>
          <button v-if="task.can_manage !== false" type="button" class="mt-7 inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold transition" :class="task.status === 'completed' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-600 text-white hover:bg-emerald-700'" :disabled="isSaving" @click="toggleStatus"><CheckCircle2 class="h-4 w-4" />{{ task.status === 'completed' ? 'Marcar como pendiente' : 'Marcar como completada' }}</button>
        </section>

        <section class="tn-card grid rounded-3xl bg-white/90 p-5 sm:grid-cols-2">
          <div class="border-b border-slate-100 p-4 sm:border-b-0 sm:border-r"><p class="flex items-center gap-2 text-sm font-semibold text-brand-ink"><Tag class="h-4 w-4 text-primary-600" />Categoría</p><p class="mt-2 text-sm text-brand-muted">{{ task.category || task.subject }}</p></div>
          <div class="p-4"><p class="flex items-center gap-2 text-sm font-semibold text-brand-ink"><CalendarDays class="h-4 w-4 text-primary-600" />Fecha y hora</p><p class="mt-2 text-sm text-brand-muted">{{ formatDate(task.due_date) }}{{ task.due_time ? ` · ${task.due_time}` : '' }}</p></div>
          <div class="border-t border-slate-100 p-4 sm:border-r"><p class="flex items-center gap-2 text-sm font-semibold text-brand-ink"><Clock3 class="h-4 w-4 text-primary-600" />Creada</p><p class="mt-2 text-sm text-brand-muted">{{ formatDate(task.created_at, true) }}</p></div>
          <div class="border-t border-slate-100 p-4"><p class="flex items-center gap-2 text-sm font-semibold text-brand-ink"><Clock3 class="h-4 w-4 text-primary-600" />Última actualización</p><p class="mt-2 text-sm text-brand-muted">{{ formatDate(task.updated_at, true) }}</p></div>
        </section>

        <SubtaskList :task-id="task.id" />
        <TaskAttachments :task-id="task.id" />
      </div>

      <section v-else class="tn-card rounded-3xl bg-white/90 p-10 text-center"><h2 class="font-display text-xl font-bold text-brand-ink">No pudimos encontrar esta tarea</h2><button class="mt-5 rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white" @click="router.push('/tasks')">Volver al listado</button></section>
    </main>

    <ConfirmDialog :open="confirmDelete" :loading="isSaving" :message="`Eliminarás “${task?.title || ''}”. Esta acción no se puede deshacer.`" @confirm="remove" @cancel="confirmDelete = false" />
    <TaskToast :message="successMessage || error" :type="error ? 'error' : 'success'" @close="taskStore.clearMessages()" />
  </div>
</template>
