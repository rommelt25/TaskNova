import { ref } from 'vue'
import { defineStore } from 'pinia'
import { taskAttachmentsApi } from '../api'

export const useTaskAttachmentsStore = defineStore('task-attachments', () => {
  const attachments = ref([])
  const currentTaskId = ref(null)
  const isLoading = ref(false)
  const isUploading = ref(false)
  const uploadProgress = ref(0)
  const error = ref(null)

  async function fetchAttachments(taskId) {
    currentTaskId.value = taskId
    isLoading.value = true
    error.value = null
    try {
      attachments.value = await taskAttachmentsApi.getAttachments(taskId)
      return attachments.value
    } catch (requestError) {
      error.value = requestError.message
      return []
    } finally {
      isLoading.value = false
    }
  }

  async function upload(taskId, file) {
    isUploading.value = true
    uploadProgress.value = 0
    error.value = null
    try {
      const attachment = await taskAttachmentsApi.uploadAttachment(taskId, file, (event) => {
        if (event.total) uploadProgress.value = Math.round((event.loaded / event.total) * 100)
      })
      if (String(currentTaskId.value) === String(taskId)) attachments.value.unshift(attachment)
      return attachment
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isUploading.value = false
    }
  }

  async function remove(taskId, attachmentId) {
    error.value = null
    try {
      await taskAttachmentsApi.deleteAttachment(taskId, attachmentId)
      attachments.value = attachments.value.filter((attachment) => attachment.id !== attachmentId)
      return true
    } catch (requestError) {
      error.value = requestError.message
      return false
    }
  }

  async function download(taskId, attachment) {
    error.value = null
    try {
      return await taskAttachmentsApi.downloadAttachment(taskId, attachment.id)
    } catch (requestError) {
      error.value = requestError.message
      return null
    }
  }

  return { attachments, currentTaskId, isLoading, isUploading, uploadProgress, error, fetchAttachments, upload, remove, download }
})
