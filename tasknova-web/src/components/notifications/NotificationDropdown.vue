<script setup>
import { computed, ref } from 'vue'
import { BellOff, CheckCheck, Circle, LoaderCircle, Trash2 } from 'lucide-vue-next'

const props = defineProps({ notifications: { type: Array, default: () => [] }, loading: Boolean, saving: Boolean, error: String })
defineEmits(['mark-read', 'mark-all-read', 'delete'])

const filter = ref('all')
const visibleNotifications = computed(() => props.notifications.filter((notification) => {
  if (filter.value === 'unread') return !notification.read_at
  if (filter.value === 'read') return Boolean(notification.read_at)
  return true
}))

function formatDate(value) {
  return value ? new Intl.DateTimeFormat('es-PE', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value)) : ''
}
</script>

<template>
  <section class="absolute right-0 top-full z-30 mt-3 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-xl" aria-label="Centro de notificaciones">
    <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-4"><div><h2 class="font-display text-base font-bold text-brand-ink">Notificaciones</h2><p class="mt-1 text-xs text-brand-muted">Mantente al día con tus tareas.</p></div><button type="button" class="text-xs font-semibold text-primary-700 hover:text-primary-800 disabled:opacity-50" :disabled="saving" @click="$emit('mark-all-read')">Marcar todas como leídas</button></div>
    <div class="flex gap-2 border-b border-slate-100 px-4 py-3"><button v-for="option in [{ value: 'all', label: 'Todas' }, { value: 'unread', label: 'No leídas' }, { value: 'read', label: 'Leídas' }]" :key="option.value" type="button" class="rounded-lg px-2.5 py-1 text-xs font-semibold transition" :class="filter === option.value ? 'bg-primary-600 text-white' : 'bg-primary-50 text-primary-700 hover:bg-primary-100'" @click="filter = option.value">{{ option.label }}</button></div>
    <div v-if="loading" class="space-y-3 p-4"><div v-for="item in 3" :key="item" class="h-16 animate-pulse rounded-xl bg-slate-100" /></div>
    <p v-else-if="error" class="m-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ error }}</p>
    <div v-else-if="visibleNotifications.length" class="max-h-96 divide-y divide-slate-100 overflow-y-auto"><article v-for="notification in visibleNotifications" :key="notification.id" class="flex gap-3 px-4 py-4 transition hover:bg-slate-50" :class="{ 'bg-primary-50/40': !notification.read_at }"><Circle class="mt-1 h-2.5 w-2.5 shrink-0" :class="notification.read_at ? 'fill-slate-300 text-slate-300' : 'fill-primary-600 text-primary-600'" /><div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-3"><h3 class="text-sm font-semibold text-brand-ink">{{ notification.title }}</h3><span class="shrink-0 text-[11px] text-brand-muted">{{ formatDate(notification.created_at) }}</span></div><p class="mt-1 text-sm leading-5 text-brand-muted">{{ notification.message }}</p><div class="mt-2 flex gap-3"><button v-if="!notification.read_at" type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-primary-700 hover:text-primary-800" :disabled="saving" @click="$emit('mark-read', notification)"><CheckCheck class="h-3.5 w-3.5" />Marcar leída</button><button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-muted hover:text-red-600" :disabled="saving" @click="$emit('delete', notification.id)"><Trash2 class="h-3.5 w-3.5" />Eliminar</button></div></div></article></div>
    <div v-else class="px-6 py-10 text-center"><BellOff class="mx-auto h-7 w-7 text-primary-400" /><p class="mt-3 text-sm font-semibold text-brand-ink">No hay notificaciones</p><p class="mt-1 text-xs text-brand-muted">Cuando tengas novedades aparecerán aquí.</p></div>
    <div v-if="saving" class="flex items-center justify-center gap-2 border-t border-slate-100 py-2 text-xs text-brand-muted"><LoaderCircle class="h-3.5 w-3.5 animate-spin" />Actualizando…</div>
  </section>
</template>
