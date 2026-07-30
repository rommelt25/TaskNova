<script setup>
import { AlertTriangle, X } from 'lucide-vue-next'

defineProps({ open: Boolean, title: { type: String, default: '¿Eliminar tarea?' }, message: { type: String, default: 'Esta acción no se puede deshacer.' }, loading: Boolean })
defineEmits(['confirm', 'cancel'])
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-brand-ink/35 p-4" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
      <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4"><span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-600"><AlertTriangle class="h-5 w-5" /></span><button type="button" class="tn-icon-button" aria-label="Cerrar" :disabled="loading" @click="$emit('cancel')"><X class="h-5 w-5" /></button></div>
        <h2 id="confirm-title" class="mt-4 font-display text-xl font-bold text-brand-ink">{{ title }}</h2>
        <p class="mt-2 text-sm leading-6 text-brand-muted">{{ message }}</p>
        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><button type="button" class="rounded-xl px-4 py-3 text-sm font-semibold text-brand-muted hover:bg-slate-100" :disabled="loading" @click="$emit('cancel')">Cancelar</button><button type="button" class="rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-60" :disabled="loading" @click="$emit('confirm')">{{ loading ? 'Eliminando…' : 'Eliminar tarea' }}</button></div>
      </div>
    </div>
  </Teleport>
</template>
