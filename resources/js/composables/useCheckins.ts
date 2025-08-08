import { ref, computed } from 'vue'

export function useCheckins() {
  const submitting = ref(false)
  
  const quickCheckin = async (taskId: number, userChallengeId: number) => {
    submitting.value = true
    
    try {
      const response = await fetch('/api/quick-checkin', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
          task_id: taskId,
          user_challenge_id: userChallengeId,
          source: 'web'
        })
      })
      
      if (!response.ok) {
        const errorData = await response.json()
        if (response.status === 409 && errorData.checkin) {
          return errorData.checkin
        }
        throw new Error(errorData.message || 'Erro ao fazer check-in')
      }
      
      const data = await response.json()
      return data.checkin
      
    } catch (error) {
      console.error('Erro no check-in:', error)
      throw error
    } finally {
      submitting.value = false
    }
  }
  
  const removeCheckin = async (checkinId: number) => {
    submitting.value = true
    
    try {
      const response = await fetch(`/checkins/${checkinId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      })
      
      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Erro ao remover check-in')
      }
      
      return true
      
    } catch (error) {
      console.error('Erro ao remover check-in:', error)
      throw error
    } finally {
      submitting.value = false
    }
  }
  
  const uploadCheckin = async (formData: FormData) => {
    submitting.value = true
    
    try {
      const response = await fetch('/checkins', {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      })
      
      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Erro ao fazer check-in')
      }
      
      const data = await response.json()
      return data.checkin
      
    } catch (error) {
      console.error('Erro no upload:', error)
      throw error
    } finally {
      submitting.value = false
    }
  }
  
  return {
    submitting: computed(() => submitting.value),
    quickCheckin,
    removeCheckin,
    uploadCheckin
  }
}
