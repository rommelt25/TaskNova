<script setup>
import { computed, ref } from 'vue'
import { Eye, EyeOff } from 'lucide-vue-next'

const props = defineProps({
  modelValue: { type: String, default: '' },
  id: { type: String, required: true },
  label: { type: String, required: true },
  autocomplete: { type: String, default: 'current-password' },
  error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'blur'])
const visible = ref(false)
const inputType = computed(() => (visible.value ? 'text' : 'password'))
</script>

<template>
  <div>
    <label :for="id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand-muted">{{ label }}</label>
    <div class="relative">
      <input
        :id="id"
        :value="modelValue"
        :type="inputType"
        :autocomplete="autocomplete"
        class="tn-input pr-12"
        :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/10': error }"
        @input="emit('update:modelValue', $event.target.value)"
        @blur="emit('blur')"
      />
      <button
        type="button"
        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-brand-muted transition-colors hover:bg-primary-50 hover:text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
        :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
        @click="visible = !visible"
      >
        <EyeOff v-if="visible" class="h-5 w-5" aria-hidden="true" />
        <Eye v-else class="h-5 w-5" aria-hidden="true" />
      </button>
    </div>
    <p v-if="error" class="mt-1.5 text-xs text-red-600">{{ error }}</p>
  </div>
</template>
