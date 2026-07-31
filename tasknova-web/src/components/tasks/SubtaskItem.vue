<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { Check, Edit3, Save, Trash2, X } from 'lucide-vue-next'

const props = defineProps({ subtask: { type: Object, required: true }, saving: Boolean })
const emit = defineEmits(['update', 'toggle', 'remove'])
const editing = ref(false)
const form = reactive({ title: '', description: '', position: 0 })
const error = ref('')

const completed = computed(() => props.subtask.completed)

function fillForm() {
  form.title = props.subtask.title || ''
  form.description = props.subtask.description || ''
  form.position = props.subtask.position ?? 0
}

function startEditing() {
  fillForm()
  error.value = ''
  editing.value = true
}

function cancelEditing() {
  editing.value = false
  error.value = ''
}

function save() {
  if (!form.title.trim()) {
    error.value = 'El título es obligatorio.'
    return
  }
  emit('update', props.subtask.id, { title: form.title.trim(), description: form.description.trim() || null, position: Number(form.position) || 0 })
  editing.value = false
}

watch(() => props.subtask, fillForm, { immediate: true, deep: true })
</script>

<template>
  <article class="flex gap-3 rounded-2xl border border-slate-100 p-3 transition sm:p-4" :class="completed ? 'bg-emerald-50/50' : 'bg-white'">
    <button type="button" class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-md border transition" :class="completed ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-transparent hover:border-primary-500'" :disabled="saving" :aria-label="completed ? 'Marcar subtarea como pendiente' : 'Marcar subtarea como completada'" @click="$emit('toggle', subtask, !completed)"><Check class="h-3.5 w-3.5" /></button>
    <div class="min-w-0 flex-1"><template v-if="editing"><label class="sr-only">Título de subtarea</label><input v-model="form.title" class="tn-input py-2" maxlength="150" @keyup.enter="save" /><label class="sr-only">Descripción de subtarea</label><input v-model="form.description" class="tn-input mt-2 py-2" placeholder="Descripción opcional" @keyup.enter="save" /><p v-if="error" class="tn-field-error">{{ error }}</p><div class="mt-2 flex items-center gap-2"><label class="text-xs text-brand-muted">Posición <input v-model.number="form.position" class="ml-1 w-16 rounded-lg border border-slate-200 px-2 py-1 text-sm" type="number" min="0" /></label><button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-primary-700" :disabled="saving" @click="save"><Save class="h-3.5 w-3.5" />Guardar</button><button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-muted" :disabled="saving" @click="cancelEditing"><X class="h-3.5 w-3.5" />Cancelar</button></div></template><template v-else><p class="text-sm font-semibold" :class="completed ? 'text-emerald-800 line-through' : 'text-brand-ink'">{{ subtask.title }}</p><p v-if="subtask.description" class="mt-1 text-sm text-brand-muted">{{ subtask.description }}</p></template></div>
    <div v-if="!editing" class="flex shrink-0 gap-1"><button type="button" class="tn-icon-button" :disabled="saving" title="Editar subtarea" @click="startEditing"><Edit3 class="h-4 w-4" /></button><button type="button" class="tn-icon-button text-red-600" :disabled="saving" title="Eliminar subtarea" @click="$emit('remove', subtask)"><Trash2 class="h-4 w-4" /></button></div>
  </article>
</template>
