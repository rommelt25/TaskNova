<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { fetchCurrentUser, isAuthenticated } = useAuth()
const progress = ref(0)

onMounted(async () => {
  await fetchCurrentUser()
  const interval = setInterval(() => {
    progress.value += 2
    if (progress.value >= 100) {
      clearInterval(interval)
      setTimeout(() => router.replace(isAuthenticated.value ? '/dashboard' : '/login'), 400)
    }
  }, 40)
})
</script>

<template>
  <div class="tn-auth-page flex min-h-screen flex-col items-center justify-center p-8">
    <div class="text-center">
      <div class="mb-8 flex justify-center animate-bounce-in">
        <img src="/brand/tasknova-logo.png" alt="TaskNova" class="tn-logo h-48 w-80 drop-shadow-xl" />
      </div>
      
      <div class="mx-auto mt-10 w-72">
        <div class="h-1.5 overflow-hidden rounded-full bg-primary-100/80 backdrop-blur-sm">
          <div
            class="h-full rounded-full bg-gradient-to-r from-primary-600 to-secondary-500 transition-all duration-300 ease-out shadow-lg"
            :style="{ width: `${progress}%` }"
          />
        </div>
        <p class="mt-3 text-xs font-medium uppercase tracking-[0.2em] text-primary-700/80">
          Cargando entorno académico
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes bounce-in {
  0% { transform: scale(0.5); opacity: 0; }
  60% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(1); }
}
.animate-bounce-in {
  animation: bounce-in 0.8s ease-out;
}
@keyframes slide-up {
  0% { transform: translateY(30px); opacity: 0; }
  100% { transform: translateY(0); opacity: 1; }
}
.animate-slide-up {
  animation: slide-up 0.6s ease-out 0.2s both;
}
.animate-slide-up-delay {
  animation: slide-up 0.6s ease-out 0.4s both;
}
</style>
