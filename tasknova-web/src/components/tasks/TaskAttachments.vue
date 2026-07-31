<script setup>
import { computed, ref, watch } from 'vue'
import { Download, FileText, LoaderCircle, Paperclip, Trash2, UploadCloud } from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import ConfirmDialog from './ConfirmDialog.vue'
import { useTaskAttachmentsStore } from '../../stores/taskAttachments'

const props = defineProps({ taskId: { type: [Number, String], required: true } })
const attachmentStore = useTaskAttachmentsStore()
const { attachments, isLoading, isUploading, uploadProgress, error } = storeToRefs(attachmentStore)
const input = ref(null)
const attachmentToDelete = ref(null)
const localError = ref('')
const allowedExtensions = new Set(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp', 'zip'])

const visibleError = computed(() => localError.value || error.value)

function formatSize(size) {
  if (!Number.isFinite(size)) return '—'
  if (size < 1024 * 1024) return `${Math.max(1, Math.round(size / 1024))} KB`
  return `${(size / (1024 * 1024)).toFixed(1)} MB`
}

function formatDate(value) {
  return value ? new Intl.DateTimeFormat('es-PE', { dateStyle: 'medium' }).format(new Date(value)) : '—'
}

async function load() {
  if (props.taskId) await attachmentStore.fetchAttachments(props.taskId)
}

function chooseFile() {
  localError.value = ''
  input.value?.click()
}

async function upload(event) {
  const file = event.target.files?.[0]
  event.target.value = ''
  if (!file) return

  const extension = file.name.split('.').pop()?.toLowerCase()
  if (!allowedExtensions.has(extension)) {
    localError.value = 'Selecciona un PDF, documento Office, imagen, archivo ZIP o formato permitido.'
    return
  }
  if (file.size > 20 * 1024 * 1024) {
    localError.value = 'El archivo no puede superar los 20 MB.'
    return
  }

  localError.value = ''
  await attachmentStore.upload(props.taskId, file)
}

async function download(attachment) {
  const blob = await attachmentStore.download(props.taskId, attachment)
  if (!blob) return
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = attachment.original_name
  link.click()
  URL.revokeObjectURL(url)
}

async function remove() {
  if (attachmentToDelete.value && await attachmentStore.remove(props.taskId, attachmentToDelete.value.id)) attachmentToDelete.value = null
}

watch(() => props.taskId, load, { immediate: true })
</script>

<template>
  <section class="tn-card rounded-3xl bg-white/90 p-5 sm:p-6"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="flex items-center gap-2 text-sm font-semibold text-brand-ink"><Paperclip class="h-4 w-4 text-primary-600" />Archivos adjuntos</p><p class="mt-1 text-sm text-brand-muted">PDF, Office, imágenes y ZIP de hasta 20 MB.</p></div><button type="button" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 disabled:opacity-60" :disabled="isUploading" @click="chooseFile"><UploadCloud class="h-4 w-4" />{{ isUploading ? 'Subiendo…' : 'Adjuntar archivo' }}</button><input ref="input" class="hidden" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.zip" @change="upload" /></div>
    <div v-if="isUploading" class="mt-5"><div class="mb-2 flex justify-between text-xs font-semibold text-brand-muted"><span>Subiendo archivo</span><span>{{ uploadProgress }}%</span></div><div class="h-2 overflow-hidden rounded-full bg-primary-50"><div class="h-full rounded-full bg-primary-600 transition-all" :style="{ width: `${uploadProgress}%` }" /></div></div>
    <p v-if="visibleError" class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ visibleError }}</p>
    <div v-if="isLoading" class="mt-5 space-y-3"><div v-for="item in 2" :key="item" class="h-16 animate-pulse rounded-2xl bg-slate-100" /></div>
    <div v-else-if="attachments.length" class="mt-5 divide-y divide-slate-100 rounded-2xl border border-slate-100"><article v-for="attachment in attachments" :key="attachment.id" class="flex items-center gap-3 p-3 sm:p-4"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-700"><FileText class="h-5 w-5" /></span><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-brand-ink">{{ attachment.original_name }}</p><p class="mt-1 text-xs text-brand-muted">{{ attachment.extension?.toUpperCase() }} · {{ formatSize(attachment.size) }} · {{ formatDate(attachment.created_at) }}</p></div><div class="flex shrink-0 gap-1"><button type="button" class="tn-icon-button" :disabled="isUploading" :title="`Descargar ${attachment.original_name}`" @click="download(attachment)"><Download class="h-4 w-4" /></button><button type="button" class="tn-icon-button text-red-600" :disabled="isUploading" :title="`Eliminar ${attachment.original_name}`" @click="attachmentToDelete = attachment"><Trash2 class="h-4 w-4" /></button></div></article></div>
    <div v-else class="mt-5 rounded-2xl border border-dashed border-primary-200 bg-primary-50/50 px-5 py-9 text-center"><Paperclip class="mx-auto h-7 w-7 text-primary-400" /><p class="mt-3 text-sm font-semibold text-brand-ink">Aún no hay archivos adjuntos</p><p class="mt-1 text-sm text-brand-muted">Adjunta material útil para completar esta tarea.</p></div>
    <ConfirmDialog :open="Boolean(attachmentToDelete)" :loading="isUploading" title="¿Eliminar archivo?" :message="`Eliminarás “${attachmentToDelete?.original_name || ''}”. Esta acción no se puede deshacer.`" @confirm="remove" @cancel="attachmentToDelete = null" />
  </section>
</template>
