<script setup>
import { onBeforeUnmount, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

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
</template>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
