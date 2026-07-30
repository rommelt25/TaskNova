<script setup>
import { computed, onMounted } from 'vue'
import { useTasks } from '../composables/useTasks'
import BottomNav from '../components/navigation/BottomNav.vue'

const { tasks, fetchTasks } = useTasks()

onMounted(fetchTasks)

const completed = computed(() => tasks.value.filter(t => t.completed).length)
const pending = computed(() => tasks.value.filter(t => !t.completed).length)
const total = computed(() => tasks.value.length)
const successRate = computed(() => total.value === 0 ? 0 : Math.round((completed.value / total.value) * 100))
</script>

<template>
  <div class="tn-page min-h-screen pb-24">
    <header class="tn-header sticky top-0 z-10 border-b backdrop-blur-md">
      <div class="mx-auto max-w-3xl px-4 py-4">
        <h2 class="font-display text-xl font-bold text-gray-800">📊 Estadísticas</h2>
      </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-6 space-y-6">
      <div class="grid grid-cols-2 gap-4">
        <div class="tn-card rounded-2xl bg-white/90 p-5 shadow-sm text-center">
          <p class="text-3xl font-bold text-primary-600">{{ total }}</p>
          <p class="text-xs text-gray-500">Total tareas</p>
        </div>
        <div class="tn-card rounded-2xl bg-white/90 p-5 shadow-sm text-center">
          <p class="text-3xl font-bold text-green-600">{{ completed }}</p>
          <p class="text-xs text-gray-500">Completadas</p>
        </div>
        <div class="tn-card rounded-2xl bg-white/90 p-5 shadow-sm text-center">
          <p class="text-3xl font-bold text-yellow-600">{{ pending }}</p>
          <p class="text-xs text-gray-500">Pendientes</p>
        </div>
        <div class="tn-card rounded-2xl bg-white/90 p-5 shadow-sm text-center">
          <p class="text-3xl font-bold text-purple-600">{{ successRate }}%</p>
          <p class="text-xs text-gray-500">Tasa de éxito</p>
        </div>
      </div>

      <div class="tn-card rounded-2xl bg-white/90 p-5 shadow-sm">
        <h4 class="text-sm font-semibold text-gray-700 mb-4">Resumen</h4>
        <div class="space-y-3">
          <div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Completadas</span>
              <span class="font-semibold text-gray-800">{{ completed }}</span>
            </div>
            <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200">
              <div class="h-full rounded-full bg-green-500 transition-all" :style="{ width: `${total ? (completed/total)*100 : 0}%` }" />
            </div>
          </div>
          <div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Pendientes</span>
              <span class="font-semibold text-gray-800">{{ pending }}</span>
            </div>
            <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200">
              <div class="h-full rounded-full bg-yellow-500 transition-all" :style="{ width: `${total ? (pending/total)*100 : 0}%` }" />
            </div>
          </div>
        </div>
      </div>
    </main>

    <BottomNav />
  </div>
</template>
