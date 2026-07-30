import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { authApi } from '../api'

function readSession() {
  const token = localStorage.getItem('tasknova_token') || sessionStorage.getItem('tasknova_token')
  const savedUser = localStorage.getItem('tasknova_user') || sessionStorage.getItem('tasknova_user')

  try {
    return { token, user: savedUser ? JSON.parse(savedUser) : null }
  } catch {
    return { token: null, user: null }
  }
}

function persistSession(user, token, remember) {
  const activeStorage = remember ? localStorage : sessionStorage
  const inactiveStorage = remember ? sessionStorage : localStorage

  inactiveStorage.removeItem('tasknova_token')
  inactiveStorage.removeItem('tasknova_user')
  activeStorage.setItem('tasknova_token', token)
  activeStorage.setItem('tasknova_user', JSON.stringify(user))
}

function clearSession() {
  for (const storage of [localStorage, sessionStorage]) {
    storage.removeItem('tasknova_token')
    storage.removeItem('tasknova_user')
  }
}

export const useAuthStore = defineStore('auth', () => {
  const session = readSession()
  const user = ref(session.user)
  const token = ref(session.token)
  const isLoading = ref(false)
  const error = ref(null)
  const isAuthenticated = computed(() => Boolean(token.value))

  function saveSession(userData, accessToken, remember = true) {
    user.value = userData
    token.value = accessToken
    persistSession(userData, accessToken, remember)
  }

  function clearError() {
    error.value = null
  }

  async function login(email, password, remember = false) {
    isLoading.value = true
    error.value = null
    try {
      const { user: userData, token: accessToken } = await authApi.login(email, password)
      saveSession(userData, accessToken, remember)
      return true
    } catch (requestError) {
      error.value = requestError.message
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function register({ firstName, lastName, email, password }) {
    isLoading.value = true
    error.value = null
    try {
      const name = `${firstName.trim()} ${lastName.trim()}`.trim()
      const { user: userData, token: accessToken } = await authApi.register(name, email, password)
      saveSession(userData, accessToken, true)
      return true
    } catch (requestError) {
      error.value = requestError.message
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function requestPasswordReset(email) {
    isLoading.value = true
    error.value = null
    try {
      await authApi.requestPasswordReset(email)
      return true
    } catch (requestError) {
      error.value = requestError.message
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    isLoading.value = true
    try {
      await authApi.logout()
    } catch {
      // The local session must always be removed, even if the API is unavailable.
    } finally {
      user.value = null
      token.value = null
      clearSession()
      isLoading.value = false
    }
  }

  async function fetchCurrentUser() {
    if (!token.value) return false
    try {
      const userData = await authApi.getUser()
      user.value = userData
      const remember = Boolean(localStorage.getItem('tasknova_token'))
      persistSession(userData, token.value, remember)
      return true
    } catch {
      user.value = null
      token.value = null
      clearSession()
      return false
    }
  }

  return { user, token, isLoading, error, isAuthenticated, login, register, requestPasswordReset, logout, fetchCurrentUser, clearError }
})
