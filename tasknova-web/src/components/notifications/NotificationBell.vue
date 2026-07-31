<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { Bell } from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import NotificationDropdown from './NotificationDropdown.vue'
import { useNotificationsStore } from '../../stores/notifications'

const notificationsStore = useNotificationsStore()
const { notifications, isLoading, isSaving, error, unreadCount } = storeToRefs(notificationsStore)
const open = ref(false)
let refreshTimer

async function refresh() {
  await notificationsStore.fetchNotifications()
}

async function toggle() {
  open.value = !open.value
  if (open.value) await refresh()
}

onMounted(() => {
  refresh()
  refreshTimer = window.setInterval(refresh, 60000)
})

onBeforeUnmount(() => window.clearInterval(refreshTimer))
</script>

<template>
  <div class="relative">
    <button type="button" class="tn-icon-button relative" aria-label="Abrir notificaciones" :aria-expanded="open" @click="toggle"><Bell class="h-5 w-5" /><span v-if="unreadCount" class="absolute -right-1 -top-1 min-w-5 rounded-full bg-red-500 px-1 text-center text-[10px] font-bold leading-5 text-white">{{ unreadCount > 99 ? '99+' : unreadCount }}</span></button>
    <NotificationDropdown v-if="open" :notifications="notifications" :loading="isLoading" :saving="isSaving" :error="error" @mark-read="notificationsStore.markAsRead" @mark-all-read="notificationsStore.markAllAsRead" @delete="notificationsStore.removeNotification" />
  </div>
</template>
