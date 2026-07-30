<script setup>
import { computed } from 'vue'

const props = defineProps({
  tasks: { type: Array, required: true }
})

const achievements = computed(() => {
  const list = []
  const completed = props.tasks.filter(t => t.completed)
  if (completed.length >= 5) list.push({ icon: '🏅', label: '5 tareas completadas' })
  if (completed.length >= 10) list.push({ icon: '🥇', label: '10 tareas completadas' })
  if (props.tasks.length >= 10) list.push({ icon: '📋', label: '10 tareas creadas' })
  if (list.length === 0) list.push({ icon: '🌟', label: 'Completa 5 tareas para desbloquear logros' })
  return list.slice(0, 3)
})
</script>

<template>
  <div class="tn-card rounded-2xl bg-white/90 p-5 shadow-sm">
    <h4 class="mb-3 text-sm font-semibold text-gray-700">🏆 Logros recientes</h4>
    <div class="flex flex-wrap gap-3">
      <div
        v-for="ach in achievements"
        :key="ach.label"
        class="flex items-center gap-2 rounded-full bg-primary-50 px-4 py-2 text-sm font-medium text-primary-700"
      >
        <span>{{ ach.icon }}</span>
        {{ ach.label }}
      </div>
    </div>
  </div>
</template>
