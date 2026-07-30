<script setup>
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeft, LoaderCircle } from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import TaskForm from '../components/tasks/TaskForm.vue'
import TaskToast from '../components/tasks/TaskToast.vue'
import { useTasksStore } from '../stores/tasks'
import { useCategoriesStore } from '../stores/categories'

const route = useRoute()
const router = useRouter()
const taskStore = useTasksStore()
const categoriesStore = useCategoriesStore()
const { isLoading, isSaving, error } = storeToRefs(taskStore)
const { categories } = storeToRefs(categoriesStore)
const editing = computed(() => Boolean(route.params.id))
const task = computed(() => taskStore.tasks.find((item) => String(item.id) === String(route.params.id)) || null)

async function save(payload) {
  const saved = editing.value ? await taskStore.updateTask(route.params.id, payload) : await taskStore.createTask(payload)
  if (saved) router.replace(`/tasks/${saved.id}`)
}
onMounted(async () => {
  await categoriesStore.fetchCategories({ per_page: 100 })
  if (editing.value && !task.value) await taskStore.fetchTask(route.params.id)
})
</script>

<template>
  <div class="tn-page min-h-screen pb-10"><header class="tn-header sticky top-0 z-10 border-b backdrop-blur-md"><div class="mx-auto flex max-w-3xl items-center gap-3 px-4 py-4 sm:px-6"><button class="tn-icon-button" aria-label="Volver a tareas" @click="router.push('/tasks')"><ArrowLeft class="h-5 w-5" /></button><div><p class="text-xs font-semibold uppercase tracking-wider text-primary-600">Gestión de tareas</p><h1 class="mt-1 font-display text-xl font-bold text-brand-ink">{{ editing ? 'Editar tarea' : 'Nueva tarea' }}</h1></div></div></header><main class="mx-auto max-w-3xl px-4 py-6 sm:px-6"><div v-if="isLoading" class="tn-card flex items-center justify-center gap-3 rounded-3xl bg-white/90 p-10 text-brand-muted"><LoaderCircle class="h-5 w-5 animate-spin text-primary-600" />Cargando tarea…</div><TaskForm v-else :task="task || {}" :categories="categories" :editing="editing" :loading="isSaving" @submit="save" @cancel="router.push('/tasks')" /></main><TaskToast :message="error" type="error" @close="taskStore.clearMessages()" /></div>
</template>
