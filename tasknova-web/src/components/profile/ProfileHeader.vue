<script setup>
import { Camera, Trash2, UserRound } from 'lucide-vue-next'

defineProps({
  name: { type: String, default: '' },
  imageUrl: { type: String, default: '' },
})

const emit = defineEmits(['select-image', 'remove-image'])

function selectImage(event) {
  emit('select-image', event.target.files?.[0] || null)
  event.target.value = ''
}
</script>

<template>
  <section class="tn-card rounded-3xl bg-white/90 p-5 sm:p-6">
    <div class="flex flex-col items-center gap-5 sm:flex-row sm:text-left">
      <div class="relative shrink-0">
        <img v-if="imageUrl" :src="imageUrl" alt="Foto de perfil" class="h-28 w-28 rounded-full border-4 border-white object-cover shadow-soft" />
        <div v-else class="flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-white shadow-glow">
          <span v-if="name" class="font-display text-4xl font-bold">{{ name.charAt(0).toUpperCase() }}</span>
          <UserRound v-else class="h-11 w-11" aria-hidden="true" />
        </div>
        <label for="profile-avatar" class="absolute bottom-0 right-0 flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border-2 border-white bg-primary-600 text-white shadow-md transition hover:bg-secondary-500" title="Cambiar foto">
          <Camera class="h-4 w-4" aria-hidden="true" />
          <input id="profile-avatar" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" @change="selectImage" />
        </label>
      </div>
      <div class="min-w-0 flex-1 text-center sm:text-left">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-primary-700">Mi Perfil</p>
        <h1 class="mt-1 font-display text-2xl font-bold text-brand-ink">{{ name || 'Completa tu perfil' }}</h1>
        <p class="mt-1 text-sm leading-6 text-brand-muted">Mantén actualizada tu información personal y académica.</p>
        <div class="mt-4 flex flex-wrap justify-center gap-3 sm:justify-start">
          <label for="profile-avatar" class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-primary-50 px-3 py-2 text-sm font-semibold text-primary-700 transition hover:bg-primary-100">
            <Camera class="h-4 w-4" aria-hidden="true" />
            {{ imageUrl ? 'Cambiar imagen' : 'Seleccionar imagen' }}
          </label>
          <button v-if="imageUrl" type="button" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50" @click="emit('remove-image')">
            <Trash2 class="h-4 w-4" aria-hidden="true" />
            Eliminar imagen
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
