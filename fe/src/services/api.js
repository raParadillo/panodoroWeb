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
