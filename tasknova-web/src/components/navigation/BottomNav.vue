<script setup>
import { CalendarDays, ChartNoAxesCombined, House, Shapes, UserRound } from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()
const tabs = [
  { icon: House, label: 'Inicio', route: '/dashboard' },
  { icon: CalendarDays, label: 'Calendario', route: '/calendar' },
  { icon: Shapes, label: 'Categorías', route: '/categories' },
  { icon: ChartNoAxesCombined, label: 'Estadísticas', route: '/statistics' },
  { icon: UserRound, label: 'Mi Perfil', route: '/profile' },
]
</script>

<template>
  <nav class="tn-bottom-nav fixed bottom-0 left-0 right-0 z-50 border-t backdrop-blur-md" aria-label="Navegación principal">
    <div class="mx-auto flex max-w-xl items-center justify-around py-2">
      <button v-for="tab in tabs" :key="tab.route" class="flex min-w-0 flex-col items-center gap-0.5 px-1.5 py-1 transition-all" :class="route.path === tab.route || (tab.route === '/profile' && route.path === '/complete-profile') ? 'scale-105 text-primary-600' : 'text-gray-400 hover:text-gray-600'" @click="router.push(tab.route)">
        <component :is="tab.icon" class="h-5 w-5" aria-hidden="true" />
        <span class="text-[9px] font-medium sm:text-[10px]">{{ tab.label }}</span>
      </button>
    </div>
  </nav>
</template>
