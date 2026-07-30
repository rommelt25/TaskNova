import { storeToRefs } from 'pinia'
import { useAuthStore } from '../stores/auth'

export function useAuth() {
  const auth = useAuthStore()

  return {
    ...storeToRefs(auth),
    login: auth.login,
    register: auth.register,
    requestPasswordReset: auth.requestPasswordReset,
    logout: auth.logout,
    fetchCurrentUser: auth.fetchCurrentUser,
    clearError: auth.clearError,
  }
}
