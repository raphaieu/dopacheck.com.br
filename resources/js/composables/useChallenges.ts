import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export function useChallenges() {
  const loading = ref(false)
  
  const joinChallenge = async (challengeId: number) => {
    loading.value = true
    
    try {
      await router.post(`/challenges/${challengeId}/join`, {}, {
        onSuccess: () => {
          // Redirect para dashboard
          router.visit('/dopa')
        },
        onError: (errors) => {
          console.error('Erro ao participar do desafio:', errors)
        },
        onFinish: () => {
          loading.value = false
        }
      })
    } catch (error) {
      console.error('Erro:', error)
      loading.value = false
    }
  }
  
  const leaveChallenge = async (challengeId: number) => {
    
    loading.value = true
    
    try {
      await router.post(`/challenges/${challengeId}/leave`, {}, {
        onSuccess: () => {
          router.visit('/dopa')
        },
        onError: (errors) => {
          console.error('Erro ao sair do desafio:', errors)
        },
        onFinish: () => {
          loading.value = false
        }
      })
    } catch (error) {
      console.error('Erro:', error)
      loading.value = false
    }
  }
  
  return {
    loading: computed(() => loading.value),
    joinChallenge,
    leaveChallenge
  }
}
