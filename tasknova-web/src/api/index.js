import axios from 'axios'
import router from '../router'

const config = {
  APP_NAME: import.meta.env.VITE_APP_NAME || 'TaskNova',
  APP_ENV: import.meta.env.VITE_APP_ENV || 'production',
  API_URL: (import.meta.env.VITE_API_URL || '').replace(/\/$/, ''),
  TIMEOUT: Number(import.meta.env.VITE_API_TIMEOUT || 15000),
}

const http = axios.create({
  baseURL: config.API_URL,
  timeout: config.TIMEOUT,
  headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
})

http.interceptors.request.use((requestConfig) => {
  const token = localStorage.getItem('tasknova_token') || sessionStorage.getItem('tasknova_token')
  if (token) requestConfig.headers.Authorization = `Bearer ${token}`
  return requestConfig
})

http.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status ?? 0
    if (status === 401) {
      localStorage.removeItem('tasknova_token')
      localStorage.removeItem('tasknova_user')
      sessionStorage.removeItem('tasknova_token')
      sessionStorage.removeItem('tasknova_user')
      if (router.currentRoute.value.path !== '/login') {
        router.replace({ path: '/login', query: { expired: '1' } })
      }
    }
    if (typeof window !== 'undefined' && (status === 0 || status >= 500)) {
      window.dispatchEvent(new CustomEvent('tasknova:api-error', { detail: { status } }))
    }
    return Promise.reject(normalizeError(error))
  }
)

function normalizeError(error) {
  if (error.response) {
    const data = error.response.data
    const firstValidationError = data?.errors && Object.values(data.errors).flat()[0]
    const statusMessage = {
      401: 'Tu sesión expiró. Inicia sesión nuevamente.',
      403: 'No tienes permiso para realizar esta acción.',
      404: 'No encontramos el recurso solicitado.',
      500: 'Ocurrió un problema en el servidor. Inténtalo nuevamente en unos minutos.',
    }[error.response.status]
    return new Error(firstValidationError || data?.message || statusMessage || `Error del servidor (${error.response.status})`)
  }
  if (error.code === 'ECONNABORTED') return new Error('El servidor tardó demasiado en responder. Inténtalo nuevamente.')
  if (error.request) return new Error('No se pudo conectar con el servidor. Verifica tu conexión e inténtalo nuevamente.')
  return new Error(error.message || 'Ocurrió un error inesperado.')
}

function unwrap(resource) {
  return resource?.data ?? resource
}

function normalizeTask(task) {
  const resource = unwrap(task) || {}
  const categoryData = resource.category && typeof resource.category === 'object' ? resource.category : null
  return {
    ...resource,
    category: categoryData?.name ?? resource.category ?? resource.subject ?? '',
    category_id: resource.category_id ?? categoryData?.id ?? null,
    category_data: categoryData,
    category_color: categoryData?.color ?? resource.category_color ?? null,
    due_time: resource.due_time ?? resource.time ?? '',
    completed: resource.status === 'completed',
  }
}

function defaultDueDate() {
  return new Date().toISOString().slice(0, 10)
}

function taskPayload(task) {
  const payload = { ...task }
  if (typeof payload.category === 'string' && payload.category) {
    payload.subject = payload.category
  }
  delete payload.category
  delete payload.category_data
  delete payload.category_color
  if (payload.time !== undefined) {
    payload.due_time = payload.time
    delete payload.time
  }
  if (payload.completed !== undefined) {
    payload.status = payload.completed ? 'completed' : 'pending'
    delete payload.completed
  }
  return payload
}

export const authApi = {
  async login(email, password) {
    const { data } = await http.post('/login', { email, password, device_name: 'tasknova-web' })
    const session = unwrap(data)
    return { user: unwrap(session.user), token: session.token }
  },

  async register(name, email, password) {
    const { data } = await http.post('/register', {
      name,
      email,
      password,
      password_confirmation: password,
      device_name: 'tasknova-web',
    })
    const session = unwrap(data)
    return { user: unwrap(session.user), token: session.token }
  },

  async logout() {
    await http.post('/logout')
  },

  async getUser() {
    const { data } = await http.get('/user')
    return unwrap(data)
  },

  async requestPasswordReset(email) {
    await http.post('/forgot-password', { email })
  },
}

export const tasksApi = {
  async getTasks(params = {}) {
    const { data } = await http.get('/tasks', { params: { per_page: 10, ...params } })
    const resource = data?.data?.data ? data.data : data
    const items = Array.isArray(resource?.data) ? resource.data : Array.isArray(resource) ? resource : []
    const meta = resource?.meta ?? data?.meta ?? {}
    return {
      items: items.map(normalizeTask),
      meta: {
        current_page: meta.current_page ?? 1,
        last_page: meta.last_page ?? 1,
        per_page: meta.per_page ?? params.per_page ?? 10,
        total: meta.total ?? items.length,
      },
    }
  },

  async getTask(id) {
    const { data } = await http.get(`/tasks/${id}`)
    return normalizeTask(data)
  },

  async createTask(task) {
    const payload = taskPayload({
      priority: 'medium',
      status: 'pending',
      subject: 'General',
      due_date: defaultDueDate(),
      ...task,
    })
    const { data } = await http.post('/tasks', payload)
    return normalizeTask(unwrap(data))
  },

  async updateTask(id, task) {
    const { data } = await http.put(`/tasks/${id}`, taskPayload(task))
    return normalizeTask(unwrap(data))
  },

  async deleteTask(id) {
    await http.delete(`/tasks/${id}`)
  },

  async updateTaskStatus(id, status) {
    const { data } = await http.patch(`/tasks/${id}/status`, { status })
    return normalizeTask(unwrap(data))
  },
}

export const notificationsApi = {
  async getNotifications(params = {}) {
    const { data } = await http.get('/notifications', { params: { per_page: 20, ...params } })
    return normalizePaginatedCollection(data, params)
  },

  async markAsRead(id) {
    const { data } = await http.patch(`/notifications/${id}/read`)
    return unwrap(data)
  },

  async markAllAsRead() {
    const { data } = await http.patch('/notifications/read-all')
    return unwrap(data)
  },

  async deleteNotification(id) {
    await http.delete(`/notifications/${id}`)
  },
}

export const taskAttachmentsApi = {
  async getAttachments(taskId) {
    const { data } = await http.get(`/tasks/${taskId}/attachments`)
    const resource = unwrap(data)
    return Array.isArray(resource) ? resource : resource?.data || []
  },

  async uploadAttachment(taskId, file, onUploadProgress) {
    const formData = new FormData()
    formData.append('file', file)
    const { data } = await http.post(`/tasks/${taskId}/attachments`, formData, { onUploadProgress })
    return unwrap(data)
  },

  async deleteAttachment(taskId, attachmentId) {
    await http.delete(`/tasks/${taskId}/attachments/${attachmentId}`)
  },

  async downloadAttachment(taskId, attachmentId) {
    const response = await http.get(`/tasks/${taskId}/attachments/${attachmentId}/download`, { responseType: 'blob' })
    return response.data
  },
}

export const profileApi = {
  async getProfile() {
    const { data } = await http.get('/profile')
    return unwrap(data)
  },

  async updateProfile(profile, avatarFile = null) {
    if (avatarFile) {
      const formData = new FormData()
      Object.entries(profile).forEach(([key, value]) => {
        if (value !== null && value !== undefined) formData.append(key, value)
      })
      formData.append('avatar', avatarFile)
      formData.append('_method', 'PUT')
      const { data } = await http.post('/profile', formData)
      return unwrap(data)
    }

    const { data } = await http.put('/profile', profile)
    return unwrap(data)
  },
}

export const dashboardApi = {
  async getSummary() {
    const { data } = await http.get('/dashboard/summary')
    return unwrap(data)
  },

  async getUpcomingTasks() {
    const { data } = await http.get('/dashboard/upcoming-tasks')
    const payload = unwrap(data)
    return Array.isArray(payload) ? payload : payload?.items || []
  },

  async getRecentActivity() {
    const { data } = await http.get('/dashboard/recent-activity')
    const payload = unwrap(data)
    return Array.isArray(payload) ? payload : payload?.items || []
  },
}

function normalizePaginatedCollection(response, params = {}) {
  const resource = response?.data?.data ? response.data : response
  const items = Array.isArray(resource?.data) ? resource.data : Array.isArray(resource) ? resource : []
  const meta = resource?.meta ?? response?.meta ?? {}
  return {
    items,
    meta: {
      current_page: meta.current_page ?? 1,
      last_page: meta.last_page ?? 1,
      per_page: meta.per_page ?? params.per_page ?? 10,
      total: meta.total ?? items.length,
    },
  }
}

export const categoriesApi = {
  async getCategories(params = {}) {
    const { data } = await http.get('/categories', { params: { per_page: 10, ...params } })
    return normalizePaginatedCollection(data, params)
  },

  async createCategory(category) {
    const { data } = await http.post('/categories', category)
    return unwrap(data)
  },

  async updateCategory(id, category) {
    const { data } = await http.put(`/categories/${id}`, category)
    return unwrap(data)
  },

  async deleteCategory(id) {
    await http.delete(`/categories/${id}`)
  },
}

export const calendarApi = {
  async getCalendar(params = {}) {
    const { data } = await http.get('/calendar', { params })
    const resource = data?.data?.data ? data.data : data
    const items = Array.isArray(resource?.data) ? resource.data : Array.isArray(resource) ? resource : []
    return items.map(normalizeTask)
  },
}

export default config
