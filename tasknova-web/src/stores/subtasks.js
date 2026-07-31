import { ref } from 'vue'
import { defineStore } from 'pinia'
import { subtasksApi } from '../api'

export const useSubtasksStore = defineStore('subtasks', () => {
  const subtasks = ref([])
  const currentTaskId = ref(null)
  const isLoading = ref(false)
  const isSaving = ref(false)
  const error = ref(null)

  async function fetchSubtasks(taskId) {
    currentTaskId.value = taskId
    isLoading.value = true
    error.value = null
    try {
      subtasks.value = await subtasksApi.getSubtasks(taskId)
      return subtasks.value
    } catch (requestError) {
      error.value = requestError.message
      return []
    } finally {
      isLoading.value = false
    }
  }

  async function create(taskId, payload) {
    isSaving.value = true
    error.value = null
    try {
      const subtask = await subtasksApi.createSubtask(taskId, payload)
      if (String(currentTaskId.value) === String(taskId)) {
        subtasks.value.push(subtask)
        sortSubtasks()
      }
      return subtask
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function update(taskId, subtaskId, payload) {
    isSaving.value = true
    error.value = null
    try {
      const subtask = await subtasksApi.updateSubtask(taskId, subtaskId, payload)
      replace(subtask)
      return subtask
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function updateStatus(taskId, subtask, completed) {
    isSaving.value = true
    error.value = null
    try {
      const updated = await subtasksApi.updateSubtaskStatus(taskId, subtask.id, completed)
      replace(updated)
      return updated
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function remove(taskId, subtaskId) {
    isSaving.value = true
    error.value = null
    try {
      await subtasksApi.deleteSubtask(taskId, subtaskId)
      subtasks.value = subtasks.value.filter((subtask) => subtask.id !== subtaskId)
      return true
    } catch (requestError) {
      error.value = requestError.message
      return false
    } finally {
      isSaving.value = false
    }
  }

  function replace(subtask) {
    const index = subtasks.value.findIndex((item) => item.id === subtask.id)
    if (index >= 0) subtasks.value.splice(index, 1, subtask)
    sortSubtasks()
  }

  function sortSubtasks() {
    subtasks.value.sort((a, b) => a.position - b.position || a.id - b.id)
  }

  return { subtasks, currentTaskId, isLoading, isSaving, error, fetchSubtasks, create, update, updateStatus, remove }
})
