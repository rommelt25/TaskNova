<script setup>
import { computed, reactive, watch } from 'vue'
import { CalendarDays, Clock3, Save } from 'lucide-vue-next'

const props = defineProps({ task: { type: Object, default: () => ({}) }, categories: { type: Array, default: () => [] }, loading: Boolean, editing: Boolean })
const emit = defineEmits(['submit', 'cancel'])
const priorities = [{ value: 'low', label: 'Baja' }, { value: 'medium', label: 'Media' }, { value: 'high', label: 'Alta' }]
const statuses = [{ value: 'pending', label: 'Pendiente' }, { value: 'completed', label: 'Completada' }]
const form = reactive({ title: '', description: '', category_id: '', priority: '', due_date: '', due_time: '', status: 'pending' })
const errors = reactive({})

function fillForm(task = {}) {
  Object.assign(form, { title: task.title || '', description: task.description || '', category_id: task.category_id || '', priority: task.priority || '', due_date: task.due_date || '', due_time: task.due_time || '', status: task.status || 'pending' })
}
watch(() => props.task, fillForm, { immediate: true, deep: true })
const fields = computed(() => [
  ['title', 'El título es obligatorio y puede tener hasta 150 caracteres.', form.title.trim().length > 0 && form.title.trim().length <= 150],
  ['description', 'La descripción es obligatoria.', form.description.trim().length > 0],
  ['category_id', 'Selecciona una categoría.', Boolean(form.category_id)],
  ['priority', 'Selecciona una prioridad.', Boolean(form.priority)],
  ['due_date', 'Selecciona una fecha.', Boolean(form.due_date)],
  ['due_time', 'Selecciona una hora.', Boolean(form.due_time)],
])
function validateField(key) { const field = fields.value.find(([name]) => name === key); if (!field) return true; const [, message, valid] = field; if (valid) delete errors[key]; else errors[key] = message; return valid }
function validate() { return fields.value.every(([key]) => validateField(key)) }
function submit() { if (validate()) emit('submit', { ...form, title: form.title.trim(), description: form.description.trim() }) }
</script>

<template>
  <form class="tn-card rounded-3xl bg-white/90 p-5 sm:p-7" novalidate @submit.prevent="submit">
    <div class="grid gap-5 sm:grid-cols-2">
      <label class="sm:col-span-2"><span class="tn-label">Título</span><input v-model="form.title" class="tn-input" :class="{ 'border-red-400': errors.title }" maxlength="150" placeholder="Ej. Preparar la exposición" @blur="validateField('title')" @input="validateField('title')" /><p v-if="errors.title" class="tn-field-error">{{ errors.title }}</p></label>
      <label class="sm:col-span-2"><span class="tn-label">Descripción</span><textarea v-model="form.description" class="tn-input min-h-28 resize-y" :class="{ 'border-red-400': errors.description }" placeholder="Describe lo que necesitas realizar" @blur="validateField('description')" @input="validateField('description')" /><p v-if="errors.description" class="tn-field-error">{{ errors.description }}</p></label>
      <label><span class="tn-label">Categoría</span><select v-model="form.category_id" class="tn-input" :class="{ 'border-red-400': errors.category_id }" @change="validateField('category_id')"><option value="" disabled>{{ categories.length ? 'Selecciona una categoría' : 'Crea una categoría primero' }}</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select><p v-if="errors.category_id" class="tn-field-error">{{ errors.category_id }}</p></label>
      <label><span class="tn-label">Prioridad</span><select v-model="form.priority" class="tn-input" :class="{ 'border-red-400': errors.priority }" @change="validateField('priority')"><option value="" disabled>Selecciona una prioridad</option><option v-for="priority in priorities" :key="priority.value" :value="priority.value">{{ priority.label }}</option></select><p v-if="errors.priority" class="tn-field-error">{{ errors.priority }}</p></label>
      <label><span class="tn-label">Fecha</span><span class="relative block"><CalendarDays class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" /><input v-model="form.due_date" type="date" class="tn-input pl-10" :class="{ 'border-red-400': errors.due_date }" @change="validateField('due_date')" /></span><p v-if="errors.due_date" class="tn-field-error">{{ errors.due_date }}</p></label>
      <label><span class="tn-label">Hora</span><span class="relative block"><Clock3 class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" /><input v-model="form.due_time" type="time" class="tn-input pl-10" :class="{ 'border-red-400': errors.due_time }" @change="validateField('due_time')" /></span><p v-if="errors.due_time" class="tn-field-error">{{ errors.due_time }}</p></label>
      <label v-if="editing"><span class="tn-label">Estado</span><select v-model="form.status" class="tn-input"><option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option></select></label>
    </div>
    <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><button type="button" class="rounded-xl px-5 py-3 text-sm font-semibold text-brand-muted transition hover:bg-slate-100" :disabled="loading" @click="$emit('cancel')">Cancelar</button><button type="submit" class="tn-button-primary sm:w-auto sm:px-6" :disabled="loading"><Save class="mr-2 inline h-4 w-4" />{{ loading ? 'Guardando…' : editing ? 'Guardar cambios' : 'Crear tarea' }}</button></div>
  </form>
</template>
