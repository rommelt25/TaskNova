<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useRoute } from 'vue-router'
import NotificationBell from './components/notifications/NotificationBell.vue'
import TaskAttachments from './components/tasks/TaskAttachments.vue'

const router = useRouter()
const route = useRoute()
const taskId = computed(() => route.path.match(/^\/tasks\/(\d+)$/)?.[1] || null)

function handleApiFailure(event) {
  const status = event.detail?.status
  if (status === 500) router.push('/error')
  if (status === 0) router.push('/offline')
}

onMounted(() => window.addEventListener('tasknova:api-error', handleApiFailure))
onBeforeUnmount(() => window.removeEventListener('tasknova:api-error', handleApiFailure))
</script>

<template>
  <router-view v-slot="{ Component }">
    <transition name="fade" mode="out-in" enter-active-class="animate-fade-in" leave-active-class="animate-fade-out">
      <component :is="Component" />
    </transition>
  </router-view>
  <NotificationBell v-if="route.path === '/tasks'" class="fixed right-24 top-3 z-20 sm:right-40" />
  <div v-if="taskId" class="tn-page px-4 pb-10 sm:px-6"><div class="mx-auto max-w-4xl"><TaskAttachments :task-id="taskId" /></div></div>
</template>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
