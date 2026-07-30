import { ref } from 'vue'
import { defineStore } from 'pinia'
import { dashboardApi } from '../api'

export const useDashboardStore = defineStore('dashboard', () => {
  const summary = ref(null)
  const upcomingTasks = ref([])
  const recentActivity = ref([])
  const isLoading = ref(false)
  const error = ref(null)

  async function fetchSummary() {
    summary.value = await dashboardApi.getSummary()
    return summary.value
  }

  async function fetchUpcomingTasks() {
    upcomingTasks.value = await dashboardApi.getUpcomingTasks()
    return upcomingTasks.value
  }

  async function fetchRecentActivity() {
    recentActivity.value = await dashboardApi.getRecentActivity()
    return recentActivity.value
  }

  async function fetchDashboard() {
    isLoading.value = true
    error.value = null
    try {
      await Promise.all([fetchSummary(), fetchUpcomingTasks(), fetchRecentActivity()])
      return true
    } catch (requestError) {
      error.value = requestError.message
      return false
    } finally {
      isLoading.value = false
    }
  }

  return { summary, upcomingTasks, recentActivity, isLoading, error, fetchSummary, fetchUpcomingTasks, fetchRecentActivity, fetchDashboard }
})
