import { ref } from 'vue'
import { defineStore } from 'pinia'
import { categoriesApi } from '../api'

export const useCategoriesStore = defineStore('categories', () => {
  const categories = ref([])
  const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
  const isLoading = ref(false)
  const isSaving = ref(false)
  const error = ref(null)
  const successMessage = ref('')
  const lastQuery = ref({})

  function clearMessages() { error.value = null; successMessage.value = '' }

  async function fetchCategories(query = {}) {
    isLoading.value = true
    error.value = null
    lastQuery.value = { ...lastQuery.value, ...query }
    try {
      const response = await categoriesApi.getCategories(lastQuery.value)
      categories.value = response.items
      pagination.value = response.meta
      return response
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally { isLoading.value = false }
  }

  async function saveCategory(category) {
    isSaving.value = true
    clearMessages()
    try {
      const saved = category.id ? await categoriesApi.updateCategory(category.id, category) : await categoriesApi.createCategory(category)
      const index = categories.value.findIndex((item) => String(item.id) === String(saved.id))
      if (index >= 0) categories.value.splice(index, 1, saved)
      else if (!category.id) categories.value.unshift(saved)
      successMessage.value = category.id ? 'Categoría actualizada correctamente.' : 'Categoría creada correctamente.'
      return saved
    } catch (requestError) {
      error.value = requestError.message
      return null
    } finally { isSaving.value = false }
  }

  async function deleteCategory(category) {
    const previous = [...categories.value]
    categories.value = categories.value.filter((item) => String(item.id) !== String(category.id))
    isSaving.value = true
    clearMessages()
    try {
      await categoriesApi.deleteCategory(category.id)
      pagination.value = { ...pagination.value, total: Math.max(0, pagination.value.total - 1) }
      successMessage.value = 'Categoría eliminada correctamente.'
      return true
    } catch (requestError) {
      categories.value = previous
      error.value = requestError.message
      return false
    } finally { isSaving.value = false }
  }

  return { categories, pagination, isLoading, isSaving, error, successMessage, lastQuery, clearMessages, fetchCategories, saveCategory, deleteCategory }
})
