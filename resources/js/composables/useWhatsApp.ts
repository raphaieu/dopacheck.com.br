import { ref, computed } from 'vue'
import { csrfFetch } from '@/utils/csrf.js'

export function useWhatsApp() {
  const connecting = ref(false)
  const disconnecting = ref(false)
  
  const connectWhatsApp = async () => {
    connecting.value = true
    
    try {
      const response = await csrfFetch('/whatsapp/connect', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        }
      })
      
      if (!response.ok) {
        throw new Error('Erro ao conectar WhatsApp')
      }
      
      const data = await response.json()
      return data
      
    } catch (error) {
      console.error('Erro na conexão:', error)
      throw error
    } finally {
      connecting.value = false
    }
  }
  
  const disconnectWhatsApp = async () => {
    disconnecting.value = true
    
    try {
      const response = await csrfFetch('/whatsapp/disconnect', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        }
      })
      
      if (!response.ok) {
        throw new Error('Erro ao desconectar WhatsApp')
      }
      
      return true
      
    } catch (error) {
      console.error('Erro na desconexão:', error)
      throw error
    } finally {
      disconnecting.value = false
    }
  }
  
  const getStatus = async () => {
    try {
      const response = await fetch('/api/whatsapp-status')
      
      if (!response.ok) {
        throw new Error('Erro ao buscar status')
      }
      
      const data = await response.json()
      return data
      
    } catch (error) {
      console.error('Erro ao buscar status:', error)
      return null
    }
  }
  
  return {
    connecting: computed(() => connecting.value),
    disconnecting: computed(() => disconnecting.value),
    connectWhatsApp,
    disconnectWhatsApp,
    getStatus
  }
}
