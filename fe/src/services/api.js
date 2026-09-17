const apiBaseUrl = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'

async function request(path, options = {}) {
  const token = localStorage.getItem('panodoro.authToken')

  const response = await fetch(`${apiBaseUrl}${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options.headers,
    },
    ...options,
  })

  const data = await response.json().catch(() => ({}))

  if (!response.ok) {
    const validationErrors = data.errors
      ? Object.values(data.errors).flat().join(' ')
      : ''
    throw new Error(validationErrors || data.message || 'Request failed.')
  }

  return data
}

export function registerUser(payload) {
  return request('/register', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function loginUser(payload) {
  return request('/login', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function requestPasswordReset(payload) {
  return request('/forgot-password', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function resetPassword(payload) {
  return request('/reset-password', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function logoutUser() {
  return request('/logout', { method: 'POST' })
}

export function getCurrentUser() {
  return request('/user')
}

export function getTimerSettings() {
  return request('/settings')
}

export function updateTimerSettings(payload) {
  return request('/settings', {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
}

export function addStudyTime(payload) {
  return request('/study-logs', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function getAnalytics() {
  return request('/analytics')
}

export function getHistory(page = 1, perPage = 20) {
  return request(`/history?page=${page}&per_page=${perPage}`)
}

export function getNotes() {
  return request('/notes')
}

export function createNote(payload) {
  return request('/notes', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function updateNote(noteId, payload) {
  return request(`/notes/${noteId}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
}

export function deleteNote(noteId) {
  return request(`/notes/${noteId}`, { method: 'DELETE' })
}

export function getTasks() {
  return request('/tasks')
}

export function createTask(payload) {
  return request('/tasks', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function updateTask(taskId, payload) {
  return request(`/tasks/${taskId}`, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export function deleteTask(taskId) {
  return request(`/tasks/${taskId}`, { method: 'DELETE' })
}
