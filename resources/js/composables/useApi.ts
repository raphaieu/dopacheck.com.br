import { ref } from 'vue'
import { csrfHeaders } from '@/utils/csrf.js'

export function useApi() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  
  const request = async (url: string, options: RequestInit = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const defaultHeaders = csrfHeaders({
        'Content-Type': 'application/json',
      })
      
      const response = await fetch(url, {
        ...options,
        credentials: 'same-origin',
        headers: {
          ...defaultHeaders,
          ...options.headers
        }
      })
      
      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || `HTTP ${response.status}`)
      }
      
      const data = await response.json()
      return data
      
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro desconhecido'
      throw err
    } finally {
      loading.value = false
    }
  }
  
  const get = (url: string) => request(url, { method: 'GET' })
  const post = (url: string, data?: any) => request(url, {
    method: 'POST',
    body: data ? JSON.stringify(data) : undefined
  })
  const put = (url: string, data?: any) => request(url, {
    method: 'PUT',
    body: data ? JSON.stringify(data) : undefined
  })
  const del = (url: string) => request(url, { method: 'DELETE' })
  
  return {
    loading,
    error,
    request,
    get,
    post,
    put,
    delete: del
  }
}
