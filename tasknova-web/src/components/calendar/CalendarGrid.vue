<script setup>
import { computed } from 'vue'

const props = defineProps({ currentDate: { type: Date, required: true }, selectedDate: { type: String, default: '' }, tasks: { type: Array, default: () => [] }, loading: Boolean })
defineEmits(['select'])

const weekdays = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']
const days = computed(() => {
  const year = props.currentDate.getFullYear()
  const month = props.currentDate.getMonth()
  const first = new Date(year, month, 1)
  const startOffset = (first.getDay() + 6) % 7
  const count = new Date(year, month + 1, 0).getDate()
  const result = Array.from({ length: startOffset }, () => null)
  for (let day = 1; day <= count; day += 1) result.push(new Date(year, month, day))
  while (result.length % 7) result.push(null)
  return result
})
function dateKey(date) { return date.toISOString().slice(0, 10) }
function dayTasks(date) { const key = dateKey(date); return props.tasks.filter((task) => task.due_date === key) }
function isToday(date) { return dateKey(date) === dateKey(new Date()) }
function taskColor(task) { return task.category?.color || task.category_color || task.color || '#2864e6' }
</script>

<template>
  <div class="overflow-x-auto">
    <div class="min-w-[640px]">
      <div class="grid grid-cols-7 border-b border-slate-100">
        <div v-for="day in weekdays" :key="day" class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider text-brand-muted">{{ day }}</div>
      </div>
      <div v-if="loading" class="grid grid-cols-7">
        <div v-for="cell in 35" :key="cell" class="min-h-28 border-b border-r border-slate-100 p-2"><div class="h-4 w-5 animate-pulse rounded bg-slate-100" /><div class="mt-3 h-5 animate-pulse rounded bg-slate-100" /></div>
      </div>
      <div v-else class="grid grid-cols-7">
        <div v-for="(date, index) in days" :key="date ? dateKey(date) : `empty-${index}`" class="min-h-28 border-b border-r border-slate-100 p-2" :class="{ 'bg-primary-50/35': date && dateKey(date) === selectedDate }">
          <template v-if="date">
            <button type="button" class="flex h-7 w-7 items-center justify-center rounded-lg text-sm font-semibold transition hover:bg-primary-100" :class="isToday(date) ? 'bg-primary-600 text-white hover:bg-primary-700' : 'text-brand-ink'" @click="$emit('select', dateKey(date))">{{ date.getDate() }}</button>
            <div class="mt-2 space-y-1">
              <button v-for="task in dayTasks(date).slice(0, 3)" :key="task.id" type="button" class="block w-full truncate rounded-md px-1.5 py-1 text-left text-[11px] font-semibold text-white" :style="{ backgroundColor: taskColor(task) }" :title="task.title" @click="$emit('select', dateKey(date))">{{ task.title }}</button>
              <p v-if="dayTasks(date).length > 3" class="px-1 text-[11px] font-semibold text-primary-700">+{{ dayTasks(date).length - 3 }} más</p>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>
