<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ChevronLeft, ChevronRight, Edit3, FolderPlus, Palette, Plus, Search, Tag, Trash2 } from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import BottomNav from '../components/navigation/BottomNav.vue'
import CategoryForm from '../components/categories/CategoryForm.vue'
import ConfirmDialog from '../components/tasks/ConfirmDialog.vue'
import TaskToast from '../components/tasks/TaskToast.vue'
import { useCategoriesStore } from '../stores/categories'

const router = useRouter()
const categoriesStore = useCategoriesStore()
const { categories, pagination, isLoading, isSaving, error, successMessage } = storeToRefs(categoriesStore)
const filter = reactive({ search: '' })
const formOpen = ref(false)
const categoryToEdit = ref(null)
const categoryToDelete = ref(null)
let searchTimer

function load(page = 1) { return categoriesStore.fetchCategories({ page, per_page: 10, search: filter.search || undefined }) }
function search() { clearTimeout(searchTimer); searchTimer = setTimeout(() => load(1), 300) }
function countTasks(category) { return category.tasks_count ?? category.tasks?.length ?? category.task_count ?? 0 }
function openCreate() { categoryToEdit.value = null; formOpen.value = true }
function openEdit(category) { categoryToEdit.value = category; formOpen.value = true }
async function save(category) { if (await categoriesStore.saveCategory(category)) formOpen.value = false }
async function remove() { if (await categoriesStore.deleteCategory(categoryToDelete.value)) categoryToDelete.value = null }
function changePage(page) { if (page >= 1 && page <= pagination.value.last_page) load(page) }
watch(successMessage, (message) => { if (message) setTimeout(() => { if (successMessage.value === message) categoriesStore.clearMessages() }, 3600) })
onMounted(() => load())
</script>

<template>
  <div class="tn-page min-h-screen pb-28"><header class="tn-header sticky top-0 z-10 border-b backdrop-blur-md"><div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6"><div><p class="text-xs font-semibold uppercase tracking-wider text-primary-600">Organiza mejor</p><h1 class="mt-1 font-display text-2xl font-bold text-brand-ink">Categorías</h1></div><button type="button" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-secondary-500 px-4 py-3 text-sm font-semibold text-white shadow-glow transition hover:-translate-y-0.5" @click="openCreate"><Plus class="h-4 w-4" />Nueva categoría</button></div></header><main class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6"><section class="tn-card rounded-3xl bg-white/90 p-4"><label class="relative block"><Search class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" /><input v-model="filter.search" class="tn-input pl-10" placeholder="Buscar categorías" @input="search" /></label></section><div v-if="error" class="rounded-2xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">{{ error }}</div><section v-if="isLoading" class="grid gap-3 sm:grid-cols-2"><div v-for="item in 6" :key="item" class="tn-card h-28 animate-pulse rounded-3xl bg-white/90" /></section><section v-else-if="categories.length" class="grid gap-3 sm:grid-cols-2"><article v-for="category in categories" :key="category.id" class="tn-card flex min-h-28 items-center gap-4 rounded-3xl bg-white/90 p-4"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-xl shadow-sm" :style="{ backgroundColor: `${category.color}20`, color: category.color }">{{ category.icon || '🏷️' }}</span><div class="min-w-0 flex-1"><h2 class="truncate font-display text-lg font-bold text-brand-ink">{{ category.name }}</h2><p class="mt-1 text-sm text-brand-muted">{{ countTasks(category) }} {{ countTasks(category) === 1 ? 'tarea asociada' : 'tareas asociadas' }}</p><div class="mt-2 flex items-center gap-2 text-xs font-semibold" :style="{ color: category.color }"><span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: category.color }" />{{ category.color }}</div></div><div class="flex flex-col gap-1"><button class="tn-icon-button" title="Editar categoría" @click="openEdit(category)"><Edit3 class="h-4 w-4" /></button><button class="tn-icon-button text-red-600" title="Eliminar categoría" @click="categoryToDelete = category"><Trash2 class="h-4 w-4" /></button></div></article></section><section v-else class="tn-card rounded-3xl bg-white/90 px-6 py-14 text-center"><span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-700"><Tag class="h-7 w-7" /></span><h2 class="mt-5 font-display text-xl font-bold text-brand-ink">Aún no tienes categorías</h2><p class="mx-auto mt-2 max-w-md text-sm leading-6 text-brand-muted">Crea categorías personalizadas para identificar tus tareas con rapidez.</p><button type="button" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white" @click="openCreate"><FolderPlus class="h-4 w-4" />Crear categoría</button></section><div v-if="pagination.last_page > 1" class="flex items-center justify-between"><p class="text-sm text-brand-muted">{{ pagination.total }} categorías · Página {{ pagination.current_page }} de {{ pagination.last_page }}</p><div class="flex gap-2"><button class="tn-icon-button border border-slate-200" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)"><ChevronLeft class="h-5 w-5" /></button><button class="tn-icon-button border border-slate-200" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)"><ChevronRight class="h-5 w-5" /></button></div></div></main><CategoryForm :open="formOpen" :category="categoryToEdit" :categories="categories" :loading="isSaving" @submit="save" @close="formOpen = false" /><ConfirmDialog :open="Boolean(categoryToDelete)" :loading="isSaving" title="¿Eliminar categoría?" :message="`Eliminarás “${categoryToDelete?.name || ''}”. Esta acción no se puede deshacer.`" @confirm="remove" @cancel="categoryToDelete = null" /><TaskToast :message="successMessage || error" :type="error ? 'error' : 'success'" @close="categoriesStore.clearMessages()" /><BottomNav /></div>
</template>
