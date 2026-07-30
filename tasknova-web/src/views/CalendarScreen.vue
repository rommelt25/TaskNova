<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { CalendarDays, ChevronLeft, ChevronRight, CircleDot, Edit3, Eye, FilterX, LoaderCircle } from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import BottomNav from '../components/navigation/BottomNav.vue'
import CalendarGrid from '../components/calendar/CalendarGrid.vue'
import TaskBadge from '../components/tasks/TaskBadge.vue'
import { useCalendarStore } from '../stores/calendar'
import { useCategoriesStore } from '../stores/categories'

const router = useRouter()
const calendarStore = useCalendarStore()
const categoriesStore = useCategoriesStore()
const { tasks, isLoading, error } = storeToRefs(calendarStore)
const { categories } = storeToRefs(categoriesStore)
const currentDate = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1))
const selectedDate = ref(new Date().toISOString().slice(0, 10))
const filters = reactive({ category_id: '', priority: '', status: '' })

const monthLabel = computed(() => new Intl.DateTimeFormat('es-PE', { month: 'long', year: 'numeric' }).format(currentDate.value))
const selectedLabel = computed(() => selectedDate.value ? new Intl.DateTimeFormat('es-PE', { weekday: 'long', day: 'numeric', month: 'long' }).format(new Date(`${selectedDate.value}T00:00:00`)) : '')
const visibleTasks = computed(() => tasks.value.map((task) => {
  const category = task.category_data || categories.value.find((item) => String(item.id) === String(task.category_id) || item.name === task.subject)
  return { ...task, category_data: category, category_color: task.category_color || category?.color }
}))
const visibleSelectedTasks = computed(() => visibleTasks.value.filter((task) => task.due_date === selectedDate.value))

function monthValue() { return `${currentDate.value.getFullYear()}-${String(currentDate.value.getMonth() + 1).padStart(2, '0')}` }
function load() { return calendarStore.fetchCalendar({ month: monthValue(), category_id: filters.category_id || undefined, priority: filters.priority || undefined, status: filters.status || undefined }) }
function previousMonth() { currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1); selectedDate.value = ''; load() }
function nextMonth() { currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1); selectedDate.value = ''; load() }
function today() { const now = new Date(); currentDate.value = new Date(now.getFullYear(), now.getMonth(), 1); selectedDate.value = now.toISOString().slice(0, 10); load() }
function resetFilters() { Object.assign(filters, { category_id: '', priority: '', status: '' }); load() }
function taskStatus(task) { return task.status !== 'completed' && task.due_date && new Date(`${task.due_date}T23:59:59`) < new Date() ? 'overdue' : task.status }
function color(task) { return task.category_data?.color || task.category_color || '#2864e6' }
watch(filters, load, { deep: true })
onMounted(async () => { await categoriesStore.fetchCategories({ per_page: 100 }); await load() })
</script>

<template>
  <div class="tn-page min-h-screen pb-28"><header class="tn-header sticky top-0 z-10 border-b backdrop-blur-md"><div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6"><div><p class="text-xs font-semibold uppercase tracking-wider text-primary-600">Planifica tu tiempo</p><h1 class="mt-1 flex items-center gap-2 font-display text-2xl font-bold text-brand-ink"><CalendarDays class="h-6 w-6 text-primary-600" />Calendario</h1></div><button type="button" class="rounded-xl border border-primary-200 bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 transition hover:bg-primary-100" @click="today">Hoy</button></div></header><main class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6"><section class="tn-card rounded-3xl bg-white/90 p-4"><div class="grid gap-3 sm:grid-cols-3"><label><span class="tn-label">Categoría</span><select v-model="filters.category_id" class="tn-input py-3"><option value="">Todas</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></label><label><span class="tn-label">Prioridad</span><select v-model="filters.priority" class="tn-input py-3"><option value="">Todas</option><option value="high">Alta</option><option value="medium">Media</option><option value="low">Baja</option></select></label><label><span class="tn-label">Estado</span><select v-model="filters.status" class="tn-input py-3"><option value="">Todos</option><option value="pending">Pendiente</option><option value="completed">Completada</option><option value="overdue">Vencida</option></select></label></div><button type="button" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-primary-700 hover:text-primary-800" @click="resetFilters"><FilterX class="h-4 w-4" />Limpiar filtros</button></section><div v-if="error" class="rounded-2xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">{{ error }}</div><section class="tn-card overflow-hidden rounded-3xl bg-white/90"><div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6"><button type="button" class="tn-icon-button" aria-label="Mes anterior" @click="previousMonth"><ChevronLeft class="h-5 w-5" /></button><h2 class="capitalize font-display text-lg font-bold text-brand-ink sm:text-xl">{{ monthLabel }}</h2><button type="button" class="tn-icon-button" aria-label="Mes siguiente" @click="nextMonth"><ChevronRight class="h-5 w-5" /></button></div><CalendarGrid :current-date="currentDate" :selected-date="selectedDate" :tasks="visibleTasks" :loading="isLoading" @select="selectedDate = $event" /></section><section class="tn-card rounded-3xl bg-white/90 p-5 sm:p-6"><div class="flex items-center justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-wider text-primary-600">Tareas del día</p><h2 class="mt-1 capitalize font-display text-lg font-bold text-brand-ink">{{ selectedLabel || 'Selecciona un día' }}</h2></div><span v-if="selectedDate" class="rounded-xl bg-primary-50 px-3 py-2 text-sm font-semibold text-primary-700">{{ visibleSelectedTasks.length }}</span></div><div v-if="isLoading" class="mt-5 space-y-3"><div v-for="item in 3" :key="item" class="h-16 animate-pulse rounded-2xl bg-slate-100" /></div><div v-else-if="!selectedDate || !visibleSelectedTasks.length" class="py-9 text-center text-sm text-brand-muted"><CircleDot class="mx-auto mb-3 h-7 w-7 text-primary-400" />No hay tareas programadas para este día.</div><div v-else class="mt-5 space-y-3"><article v-for="task in visibleSelectedTasks" :key="task.id" class="flex flex-col gap-3 rounded-2xl border border-slate-100 p-4 sm:flex-row sm:items-center"><span class="h-10 w-1.5 rounded-full" :style="{ backgroundColor: color(task) }" /><div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2"><h3 class="truncate font-semibold text-brand-ink">{{ task.title }}</h3><TaskBadge type="priority" :value="task.priority" /><TaskBadge type="status" :value="taskStatus(task)" /></div><p class="mt-1 text-sm text-brand-muted">{{ task.due_time || 'Sin hora definida' }} · {{ task.category_data?.name || task.category || task.subject || 'Sin categoría' }}</p></div><div class="flex gap-1"><button class="tn-icon-button" title="Ver detalle" @click="router.push(`/tasks/${task.id}`)"><Eye class="h-4 w-4" /></button><button class="tn-icon-button" title="Editar tarea" :disabled="task.can_manage === false" @click="router.push(`/tasks/${task.id}/edit`)"><Edit3 class="h-4 w-4" /></button></div></article></div></section></main><BottomNav /></div>
</template>
