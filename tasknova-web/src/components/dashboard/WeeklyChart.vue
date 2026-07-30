<script setup>
import { computed } from 'vue'

const props = defineProps({
  tasks: { type: Array, required: true }
})

const days = ['L', 'M', 'M', 'J', 'V', 'S', 'D']
const today = new Date().getDay()
const weekStart = new Date()
weekStart.setDate(weekStart.getDate() - (today === 0 ? 6 : today - 1))

const chartData = computed(() => {
  return days.map((_, idx) => {
    const date = new Date(weekStart)
    date.setDate(date.getDate() + idx)
    return props.tasks.filter(t => {
      const taskDate = new Date(t.created_at)
      return taskDate.toDateString() === date.toDateString()
    }).length
  })
})

const maxValue = computed(() => Math.max(...chartData.value, 1))
</script>

<template>
  <div class="tn-card rounded-2xl bg-white/90 p-5 shadow-sm">
    <h4 class="text-sm font-semibold text-gray-700">Actividad Semanal</h4>
    <div class="mt-4 flex items-end justify-between h-32 gap-1">
      <div
        v-for="(value, index) in chartData"
        :key="index"
        class="flex flex-1 flex-col items-center gap-1"
      >
        <div
          class="w-full rounded-t-md transition-all duration-500"
          :style="{
            height: `${(value / maxValue) * 100}%`,
            backgroundColor: ['#3e7af4', '#8554f5', '#6d9dfb', '#a47cff', '#2864e6', '#7140df', '#9abfff'][index]
          }"
        />
        <span class="text-xs text-gray-500">{{ days[index] }}</span>
        <span class="text-[10px] font-semibold text-gray-400">{{ value }}</span>
      </div>
    </div>
  </div>
</template>
