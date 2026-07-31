import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { notificationsApi } from '../api'

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref([])
  const pagination = ref({ current_page: 1, last_page: 1, per_page: 20, total: 0 })
  const isLoading = ref(false)
  const isSaving = ref(false)
  const error = ref(null)
  const unreadCount = computed(() => notifications.value.filter((notification) => !notification.read_at).length)

  async function fetchNotifications(params = {}) {
    isLoading.value = true
    error.value = null
    try {
      const response = await notificationsApi.getNotifications(params)
      notifications.value = response.items
      pagination.value = response.meta
      return response
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function markAsRead(notification) {
    if (!notification || notification.read_at) return notification

    isSaving.value = true
    error.value = null
    try {
      const updated = await notificationsApi.markAsRead(notification.id)
      const index = notifications.value.findIndex((item) => item.id === notification.id)
      if (index >= 0) notifications.value.splice(index, 1, updated)
      return updated
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function markAllAsRead() {
    isSaving.value = true
    error.value = null
    try {
      await notificationsApi.markAllAsRead()
      const readAt = new Date().toISOString()
      notifications.value = notifications.value.map((notification) => ({ ...notification, read_at: notification.read_at || readAt }))
      return true
    } catch (requestError) {
      error.value = requestError.message
      return false
    } finally {
      isSaving.value = false
    }
  }

  async function removeNotification(id) {
    isSaving.value = true
    error.value = null
    try {
      await notificationsApi.deleteNotification(id)
      notifications.value = notifications.value.filter((notification) => notification.id !== id)
      pagination.value = { ...pagination.value, total: Math.max(0, pagination.value.total - 1) }
      return true
    } catch (requestError) {
      error.value = requestError.message
      return false
    } finally {
      isSaving.value = false
    }
  }

  return { notifications, pagination, isLoading, isSaving, error, unreadCount, fetchNotifications, markAsRead, markAllAsRead, removeNotification }
})
