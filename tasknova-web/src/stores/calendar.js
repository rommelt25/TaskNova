import { ref } from 'vue'
import { defineStore } from 'pinia'
import { calendarApi } from '../api'

export const useCalendarStore = defineStore('calendar', () => {
  const tasks = ref([])
  const isLoading = ref(false)
  const error = ref(null)

  async function fetchCalendar(filters = {}) {
    isLoading.value = true
    error.value = null
    try {
      tasks.value = await calendarApi.getCalendar(filters)
      return tasks.value
    } catch (requestError) {
      error.value = requestError.message
      return []
    } finally { isLoading.value = false }
  }

  return { tasks, isLoading, error, fetchCalendar }
})
