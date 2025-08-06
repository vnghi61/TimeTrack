export function useApi() {
  const request = async (url, options = {}) => {
    const config = {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        ...options.headers
      },
      credentials: 'same-origin',
      ...options
    }

    if (options.body && typeof options.body === 'object') {
      config.headers['Content-Type'] = 'application/json'
      config.body = JSON.stringify(options.body)
    }

    const response = await fetch(url, config)
    
    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Network error' }))
      throw error
    }
    
    const text = await response.text()
    if (!text) {
      return {}
    }
    
    try {
      return JSON.parse(text)
    } catch (e) {
      console.error('Invalid JSON response:', text)
      return {}
    }
  }

  return {
    get: (url) => request(url),
    post: (url, data) => request(url, { method: 'POST', body: data }),
    put: (url, data) => request(url, { method: 'PUT', body: data }),
    delete: (url) => request(url, { method: 'DELETE' })
  }
}