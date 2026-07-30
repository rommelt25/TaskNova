<script setup>
import { CheckCircle2, AlertCircle, X } from 'lucide-vue-next'

defineProps({ message: { type: String, default: '' }, type: { type: String, default: 'success' } })
defineEmits(['close'])
</script>

<template>
  <Transition name="fade">
    <div v-if="message" class="fixed right-4 top-4 z-[60] flex max-w-sm items-start gap-3 rounded-2xl border bg-white p-4 shadow-xl" :class="type === 'error' ? 'border-red-100 text-red-700' : 'border-emerald-100 text-emerald-700'" role="status">
      <AlertCircle v-if="type === 'error'" class="mt-0.5 h-5 w-5 shrink-0" />
      <CheckCircle2 v-else class="mt-0.5 h-5 w-5 shrink-0" />
      <p class="pr-2 text-sm font-medium">{{ message }}</p>
      <button type="button" class="-mr-1 rounded-lg p-1 hover:bg-slate-100" aria-label="Cerrar mensaje" @click="$emit('close')"><X class="h-4 w-4" /></button>
    </div>
  </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease, transform .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
