import { ref } from 'vue'
import { defineStore } from 'pinia'
import { profileApi } from '../api'

export const useProfileStore = defineStore('profile', () => {
  const profile = ref(null)
  const isLoading = ref(false)
  const isSaving = ref(false)
  const error = ref(null)
  const successMessage = ref('')

  function clearMessages() {
    error.value = null
    successMessage.value = ''
  }

  async function fetchProfile() {
    isLoading.value = true
    error.value = null
    try {
      profile.value = await profileApi.getProfile()
      return profile.value
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function updateProfile(payload, avatarFile = null) {
    isSaving.value = true
    clearMessages()
    try {
      profile.value = await profileApi.updateProfile(payload, avatarFile)
      successMessage.value = 'Tus datos se guardaron correctamente.'
      return profile.value
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally {
      isSaving.value = false
    }
  }

  return { profile, isLoading, isSaving, error, successMessage, fetchProfile, updateProfile, clearMessages }
})
