import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { tasksApi } from '../api'

export const useTasksStore = defineStore('tasks', () => {
  const tasks = ref([])
  const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
  const isLoading = ref(false)
  const isSaving = ref(false)
  const error = ref(null)
  const successMessage = ref('')
  const lastQuery = ref({})

  const completedCount = computed(() => tasks.value.filter((task) => task.status === 'completed').length)

  function clearMessages() {
    error.value = null
    successMessage.value = ''
  }

  async function fetchTasks(query = {}) {
    isLoading.value = true
    error.value = null
    lastQuery.value = { ...lastQuery.value, ...query }
    try {
      const response = await tasksApi.getTasks(lastQuery.value)
      tasks.value = response.items
      pagination.value = response.meta
      return response
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function fetchTask(id) {
    isLoading.value = true
    error.value = null
    try {
      const task = await tasksApi.getTask(id)
      const index = tasks.value.findIndex((item) => String(item.id) === String(id))
      if (index >= 0) tasks.value.splice(index, 1, task)
      return task
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function createTask(payload) {
    isSaving.value = true
    clearMessages()
    try {
      const task = await tasksApi.createTask(payload)
      successMessage.value = 'Tarea creada correctamente.'
      return task
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function updateTask(id, payload) {
    isSaving.value = true
    clearMessages()
    try {
      const task = await tasksApi.updateTask(id, payload)
      const index = tasks.value.findIndex((item) => String(item.id) === String(id))
      if (index >= 0) tasks.value.splice(index, 1, task)
      successMessage.value = 'Cambios guardados correctamente.'
      return task
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function changeStatus(task, status) {
    const index = tasks.value.findIndex((item) => String(item.id) === String(task.id))
    const previous = { ...task }
    if (index >= 0) tasks.value.splice(index, 1, { ...task, status, completed: status === 'completed' })
    isSaving.value = true
    clearMessages()
    try {
      const updated = await tasksApi.updateTaskStatus(task.id, status)
      if (index >= 0) tasks.value.splice(index, 1, updated)
      successMessage.value = status === 'completed' ? 'Tarea marcada como completada.' : 'Tarea marcada como pendiente.'
      return updated
    } catch (requestError) {
      if (index >= 0) tasks.value.splice(index, 1, previous)
      error.value = requestError.message
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function deleteTask(task) {
    const index = tasks.value.findIndex((item) => String(item.id) === String(task.id))
    const previous = [...tasks.value]
    if (index >= 0) tasks.value.splice(index, 1)
    isSaving.value = true
    clearMessages()
    try {
      await tasksApi.deleteTask(task.id)
      pagination.value = { ...pagination.value, total: Math.max(0, pagination.value.total - 1) }
      successMessage.value = 'Tarea eliminada correctamente.'
      return true
    } catch (requestError) {
      tasks.value = previous
      error.value = requestError.message
      return false
    } finally {
      isSaving.value = false
    }
  }

  return { tasks, pagination, isLoading, isSaving, error, successMessage, completedCount, lastQuery, clearMessages, fetchTasks, fetchTask, createTask, updateTask, changeStatus, deleteTask }
})
