<script setup>
import { reactive, watch } from 'vue'
import { Palette, Save, X } from 'lucide-vue-next'

const props = defineProps({ category: { type: Object, default: null }, categories: { type: Array, default: () => [] }, loading: Boolean, open: Boolean })
const emit = defineEmits(['submit', 'close'])
const form = reactive({ id: null, name: '', color: '#2864e6', icon: '' })
const errors = reactive({})
const icons = ['🏷️', '📚', '💼', '❤️', '🏠', '✨']

function populate(category) {
  Object.assign(form, { id: category?.id ?? null, name: category?.name ?? '', color: category?.color ?? '#2864e6', icon: category?.icon ?? '' })
  Object.keys(errors).forEach((key) => delete errors[key])
}
watch(() => [props.open, props.category], () => { if (props.open) populate(props.category) }, { immediate: true, deep: true })
function validate() {
  if (!form.name.trim()) errors.name = 'El nombre es obligatorio.'
  else if (form.name.trim().length > 60) errors.name = 'El nombre no puede superar 60 caracteres.'
  else if (props.categories.some((item) => item.id !== form.id && item.name?.trim().toLocaleLowerCase('es') === form.name.trim().toLocaleLowerCase('es'))) errors.name = 'Ya tienes una categoría con este nombre.'
  else delete errors.name
  if (!form.color) errors.color = 'Selecciona un color.'
  else if (!/^#[0-9a-f]{6}$/i.test(form.color)) errors.color = 'Usa un color hexadecimal válido, por ejemplo #2864e6.'
  else delete errors.color
  return !Object.keys(errors).length
}
function submit() { if (validate()) emit('submit', { ...form, name: form.name.trim() }) }
</script>

<template>
  <Teleport to="body"><div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-brand-ink/35 p-4" role="dialog" aria-modal="true"><form class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl" @submit.prevent="submit"><div class="flex items-start justify-between"><div><p class="text-xs font-bold uppercase tracking-wider text-primary-600">Tus categorías</p><h2 class="mt-1 font-display text-xl font-bold text-brand-ink">{{ form.id ? 'Editar categoría' : 'Nueva categoría' }}</h2></div><button type="button" class="tn-icon-button" aria-label="Cerrar" :disabled="loading" @click="$emit('close')"><X class="h-5 w-5" /></button></div><label class="mt-6 block"><span class="tn-label">Nombre</span><input v-model="form.name" class="tn-input" :class="{ 'border-red-400': errors.name }" maxlength="60" placeholder="Ej. Estudios" @input="validate" /><p v-if="errors.name" class="tn-field-error">{{ errors.name }}</p></label><div class="mt-5 grid grid-cols-[auto_1fr] gap-3"><label><span class="tn-label">Color</span><input v-model="form.color" type="color" class="h-12 w-14 cursor-pointer rounded-xl border border-slate-200 bg-white p-1" @change="validate" /></label><label><span class="tn-label">Código de color</span><input v-model="form.color" class="tn-input" maxlength="9" placeholder="#2864e6" @input="validate" /></label></div><p v-if="errors.color" class="tn-field-error">{{ errors.color }}</p><fieldset class="mt-5"><legend class="tn-label">Icono <span class="normal-case tracking-normal">(opcional)</span></legend><div class="flex flex-wrap gap-2"><button v-for="icon in icons" :key="icon" type="button" class="flex h-10 w-10 items-center justify-center rounded-xl border text-lg transition" :class="form.icon === icon ? 'border-primary-500 bg-primary-50' : 'border-slate-200 hover:bg-slate-50'" @click="form.icon = form.icon === icon ? '' : icon">{{ icon }}</button></div></fieldset><div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><button type="button" class="rounded-xl px-4 py-3 text-sm font-semibold text-brand-muted hover:bg-slate-100" :disabled="loading" @click="$emit('close')">Cancelar</button><button type="submit" class="tn-button-primary sm:w-auto sm:px-5" :disabled="loading"><Save class="mr-2 inline h-4 w-4" />{{ loading ? 'Guardando…' : 'Guardar categoría' }}</button></div></form></div></Teleport>
</template>
