<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ChevronLeft, ChevronRight, FilterX, ListTodo, Plus, Search, SlidersHorizontal } from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import BottomNav from '../components/navigation/BottomNav.vue'
import ConfirmDialog from '../components/tasks/ConfirmDialog.vue'
import TaskTable from '../components/tasks/TaskTable.vue'
import TaskToast from '../components/tasks/TaskToast.vue'
import { useTasksStore } from '../stores/tasks'
import { useCategoriesStore } from '../stores/categories'

const router = useRouter()
const taskStore = useTasksStore()
const categoriesStore = useCategoriesStore()
const { tasks, pagination, isLoading, isSaving, error, successMessage } = storeToRefs(taskStore)
const { categories } = storeToRefs(categoriesStore)
const filters = reactive({ status: 'all', priority: '', category_id: '', search: '', sort: 'due_date', direction: 'asc' })
const taskToDelete = ref(null)
let searchTimer

function isOverdue(task) { return task.status !== 'completed' && task.due_date && new Date(`${task.due_date}T23:59:59`) < new Date() }
function queryFor(page = 1) {
  return {
    page, per_page: 10, scope: 'owned', priority: filters.priority || undefined, category_id: filters.category_id || undefined,
    status: ['pending', 'completed', 'overdue'].includes(filters.status) ? filters.status : undefined,
    search: filters.search || undefined, sort: filters.sort, direction: filters.direction,
  }
}
async function load(page = 1) { await taskStore.fetchTasks(queryFor(page)) }
function applyFilters() { load(1) }
function resetFilters() { Object.assign(filters, { status: 'all', priority: '', category_id: '', search: '', sort: 'due_date', direction: 'asc' }); load(1) }
function onSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(() => load(1), 300) }
const filteredTasks = computed(() => {
  let items = [...tasks.value]
  const needle = filters.search.trim().toLocaleLowerCase('es')
  if (needle) items = items.filter((task) => `${task.title} ${task.description || ''}`.toLocaleLowerCase('es').includes(needle))
  if (filters.status === 'overdue') items = items.filter(isOverdue)
  return items.sort((a, b) => {
    const aValue = filters.sort === 'title' ? a.title : `${a.due_date || ''}${a.due_time || ''}`
    const bValue = filters.sort === 'title' ? b.title : `${b.due_date || ''}${b.due_time || ''}`
    return aValue.localeCompare(bValue, 'es') * (filters.direction === 'asc' ? 1 : -1)
  })
})
function changePage(page) { if (page >= 1 && page <= pagination.value.last_page) load(page) }
function confirmDelete(task) { taskToDelete.value = task }
async function deleteTask() { if (await taskStore.deleteTask(taskToDelete.value)) { taskToDelete.value = null; if (!tasks.value.length && pagination.value.current_page > 1) load(pagination.value.current_page - 1) } }
function toggleTask(task) { taskStore.changeStatus(task, task.status === 'completed' ? 'pending' : 'completed') }
function closeToast() { taskStore.clearMessages() }
watch(successMessage, (message) => { if (message) setTimeout(() => { if (successMessage.value === message) taskStore.clearMessages() }, 3600) })
onMounted(async () => { await categoriesStore.fetchCategories({ per_page: 100 }); await load() })
</script>

<template>
  <div class="tn-page min-h-screen pb-28">
    <header class="tn-header sticky top-0 z-10 border-b backdrop-blur-md"><div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6"><div><p class="text-xs font-semibold uppercase tracking-wider text-primary-600">Organización personal</p><h1 class="mt-1 font-display text-2xl font-bold text-brand-ink">Mis tareas</h1></div><button type="button" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-secondary-500 px-4 py-3 text-sm font-semibold text-white shadow-glow transition hover:-translate-y-0.5" @click="router.push('/tasks/new')"><Plus class="h-4 w-4" />Nueva tarea</button></div></header>
    <main class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6">
      <section class="tn-card rounded-3xl bg-white/90 p-4 sm:p-5"><div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto]"><label class="relative block"><Search class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" /><input v-model="filters.search" class="tn-input pl-10" placeholder="Buscar por título o descripción" @input="onSearch" /></label><div class="flex flex-wrap gap-2"><button v-for="option in [{ value: 'all', label: 'Todas' }, { value: 'pending', label: 'Pendientes' }, { value: 'completed', label: 'Completadas' }, { value: 'overdue', label: 'Vencidas' }]" :key="option.value" type="button" class="rounded-xl px-3 py-2 text-sm font-semibold transition" :class="filters.status === option.value ? 'bg-primary-600 text-white shadow-sm' : 'bg-primary-50 text-primary-700 hover:bg-primary-100'" @click="filters.status = option.value; applyFilters()">{{ option.label }}</button></div></div><div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5"><label><span class="tn-label">Prioridad</span><select v-model="filters.priority" class="tn-input py-3" @change="applyFilters"><option value="">Todas</option><option value="high">Alta</option><option value="medium">Media</option><option value="low">Baja</option></select></label><label><span class="tn-label">Categoría</span><select v-model="filters.category_id" class="tn-input py-3" @change="applyFilters"><option value="">Todas</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></label><label><span class="tn-label">Ordenar por</span><select v-model="filters.sort" class="tn-input py-3" @change="applyFilters"><option value="due_date">Fecha</option><option value="title">Título</option></select></label><label><span class="tn-label">Dirección</span><select v-model="filters.direction" class="tn-input py-3" @change="applyFilters"><option value="asc">Ascendente</option><option value="desc">Descendente</option></select></label><div class="flex items-end"><button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-brand-muted transition hover:bg-slate-50" @click="resetFilters"><FilterX class="h-4 w-4" />Limpiar filtros</button></div></div></section>

      <div v-if="error" class="rounded-2xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">{{ error }}</div>
      <TaskTable v-if="isLoading || filteredTasks.length" :tasks="filteredTasks" :loading="isLoading" @view="router.push(`/tasks/${$event.id}`)" @edit="router.push(`/tasks/${$event.id}/edit`)" @delete="confirmDelete" @toggle="toggleTask" />
      <section v-else class="tn-card rounded-3xl bg-white/90 px-6 py-14 text-center"><span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-700"><ListTodo class="h-7 w-7" /></span><h2 class="mt-5 font-display text-xl font-bold text-brand-ink">No hay tareas para mostrar</h2><p class="mx-auto mt-2 max-w-md text-sm leading-6 text-brand-muted">Crea una tarea o cambia los filtros para organizar tus pendientes.</p><button type="button" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white" @click="router.push('/tasks/new')"><Plus class="h-4 w-4" />Crear tarea</button></section>
      <div v-if="pagination.last_page > 1" class="flex items-center justify-between"><p class="text-sm text-brand-muted">{{ pagination.total }} tareas · Página {{ pagination.current_page }} de {{ pagination.last_page }}</p><div class="flex gap-2"><button class="tn-icon-button border border-slate-200" :disabled="pagination.current_page <= 1 || isLoading" aria-label="Página anterior" @click="changePage(pagination.current_page - 1)"><ChevronLeft class="h-5 w-5" /></button><button class="tn-icon-button border border-slate-200" :disabled="pagination.current_page >= pagination.last_page || isLoading" aria-label="Página siguiente" @click="changePage(pagination.current_page + 1)"><ChevronRight class="h-5 w-5" /></button></div></div>
    </main>
    <ConfirmDialog :open="Boolean(taskToDelete)" :loading="isSaving" :message="`Eliminarás “${taskToDelete?.title || ''}”. Esta acción no se puede deshacer.`" @confirm="deleteTask" @cancel="taskToDelete = null" />
    <TaskToast :message="successMessage || error" :type="error ? 'error' : 'success'" @close="closeToast" />
    <BottomNav />
  </div>
</template>
