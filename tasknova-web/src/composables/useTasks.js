import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useTasksStore } from '../stores/tasks'

// Adaptador de compatibilidad para vistas existentes. El estado real vive en Pinia.
export function useTasks() {
  const store = useTasksStore()
  const { tasks, isLoading, error } = storeToRefs(store)
  const completedCount = computed(() => tasks.value.filter((task) => task.status === 'completed').length)
  const pendingCount = computed(() => tasks.value.filter((task) => task.status !== 'completed').length)
  const progress = computed(() => tasks.value.length ? completedCount.value / tasks.value.length : 0)

  async function addTask(payload) {
    return store.createTask(payload)
  }

  async function toggleTask(task) {
    return store.changeStatus(task, task.status === 'completed' ? 'pending' : 'completed')
  }

  return {
    tasks,
    isLoading,
    error,
    completedCount,
    pendingCount,
    progress,
    fetchTasks: store.fetchTasks,
    addTask,
    toggleTask,
    updateTask: store.updateTask,
    removeTask: store.deleteTask,
  }
}
