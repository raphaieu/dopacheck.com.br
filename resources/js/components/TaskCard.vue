<template>
    <div :class="[
      'bg-white rounded-2xl p-6 shadow-sm border transition-all duration-300',
      isCompleted ? 'border-green-200 bg-green-50/30' : 'border-gray-200 hover:border-gray-300'
    ]">
      <div class="flex items-start space-x-4">
        <!-- Task Icon & Status -->
        <div class="flex-shrink-0 relative">
          <div :class="[
            'w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-all duration-300',
            isCompleted 
              ? 'bg-green-100 text-green-600' 
              : `bg-${task.color?.replace('#', '')}-100 text-${task.color?.replace('#', '')}-600`
          ]" :style="!isCompleted ? `background-color: ${task.color}20; color: ${task.color}` : null">
            <span v-if="isCompleted">✅</span>
            <span v-else>{{ task.icon || '📝' }}</span>
          </div>
          
          <!-- Streak indicator -->
          <div v-if="isCompleted && streakDays > 1" class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
            {{ streakDays }}
          </div>
        </div>
  
        <!-- Task Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between mb-2">
            <div class="flex-1">
              <h4 :class="[
                'font-semibold text-lg transition-colors',
                isCompleted ? 'text-green-800' : 'text-gray-900'
              ]">
                {{ task.name }}
              </h4>
              
              <p class="text-gray-600 text-sm mt-1">
                {{ task.description }}
              </p>
              
              <!-- Hashtag -->
              <div class="mt-2">
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  isCompleted 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-blue-100 text-blue-800'
                ]">
                  #{{ task.hashtag }}
                </span>
                
                <!-- Required indicator -->
                <span v-if="task.is_required" class="ml-2 text-xs text-orange-600 font-medium">
                  Obrigatória
                </span>
              </div>
            </div>
  
            <!-- Completion Status -->
            <div class="flex-shrink-0 ml-4">
              <div v-if="isCompleted" class="text-green-600">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <div v-else class="text-gray-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10" stroke-width="2"/>
                </svg>
              </div>
            </div>
          </div>
  
          <!-- Checkin Info -->
          <div v-if="isCompleted && checkin" class="mt-3 p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between text-sm">
              <div class="flex items-center space-x-2 text-gray-600">
                <span class="w-2 h-2 rounded-full" :class="checkin.source === 'whatsapp' ? 'bg-green-500' : 'bg-blue-500'"></span>
                <span>
                  {{ checkin.source === 'whatsapp' ? 'WhatsApp' : 'Web' }} • 
                  {{ formatTime(checkin.checked_at) }}
                </span>
              </div>
              
              <button
                @click="handleRemoveCheckin"
                class="cursor-pointer text-gray-400 hover:text-red-500 transition-colors"
                title="Desfazer check-in"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <!-- Checkin Image -->
            <div v-if="checkin.image_url" class="mt-2">
              <img 
                :src="checkin.image_url" 
                alt="Check-in" 
                class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                @click="showImageModal = true"
              >
            </div>
            
            <!-- Checkin Message -->
            <div v-if="checkin.message" class="mt-2 text-sm text-gray-700">
              {{ checkin.message }}
            </div>
            
            <!-- AI Analysis (PRO) -->
            <div v-if="checkin.ai_analysis && user.is_pro" class="mt-2 p-2 bg-purple-50 rounded border border-purple-200">
              <div class="flex items-center space-x-2 mb-1">
                <span class="text-purple-600 text-xs font-medium">🤖 Análise IA</span>
                <span class="text-xs text-purple-500">({{ Math.round(checkin.confidence_score * 100) }}% confiança)</span>
              </div>
              <p class="text-xs text-purple-700">{{ checkin.ai_analysis.summary }}</p>
            </div>
          </div>
  
          <!-- Check-in Actions -->
          <div v-if="!isCompleted" class="mt-4 flex flex-col sm:flex-row gap-3">
            <!-- Web Check-in Button -->
            <button
              @click="showCheckinModal = true"
              :disabled="submitting"
              class="cursor-pointer flex-1 bg-blue-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>{{ submitting ? 'Enviando...' : 'Check-in' }}</span>
            </button>
            
            <!-- Manual Check-in Button -->
            <button
              @click="handleManualCheckin"
              :disabled="submitting"
              class="cursor-pointer px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Marcar Feito</span>
            </button>
          </div>
  
          <!-- WhatsApp Tip -->
          <div v-if="!isCompleted && whatsappConnected" class="mt-3 p-3 bg-green-50 rounded-lg border border-green-200">
            <div class="flex items-start space-x-2">
              <span class="text-green-600 text-sm">💡</span>
              <div class="text-sm text-green-700">
                <p class="font-medium mb-1">Dica: Use o WhatsApp!</p>
                <p>Envie uma foto + <code class="bg-green-100 px-1 rounded">#{{ task.hashtag }}</code> para check-in automático</p>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Check-in Modal -->
      <CheckinModal
        :show="showCheckinModal"
        :task="task"
        :user-challenge="userChallenge"
        @close="showCheckinModal = false"
        @checkin-completed="handleCheckinSuccess"
      />
  
      <!-- Image Modal -->
      <ImageModal
        :show="showImageModal"
        :image-url="checkin?.image_url"
        :title="`Check-in: ${task.name}`"
        @close="showImageModal = false"
      />
    </div>
  </template>
  
  <script setup>
  import { computed, ref } from 'vue'
  import { router, usePage } from '@inertiajs/vue3'
  import { csrfFetch } from '@/utils/csrf.js'
  import CheckinModal from '@/components/CheckinModal.vue'
  import ImageModal from '@/components/ImageModal.vue'
  
  // Props
  const props = defineProps({
    task: {
      type: Object,
      required: true
    },
    userChallenge: {
      type: Object,
      required: true
    },
    isCompleted: {
      type: Boolean,
      default: false
    },
    checkin: {
      type: Object,
      default: null
    }
  })
  
  // Emits
  const emit = defineEmits(['checkin-completed', 'checkin-removed'])
  
  // Page data
  const { props: pageProps } = usePage()
  const user = computed(() => pageProps.auth.user)
  const whatsappConnected = computed(() => pageProps.whatsappSession?.is_active)
  
  // State
  const showCheckinModal = ref(false)
  const showImageModal = ref(false)
  const submitting = ref(false)
  
  // Computed
  const streakDays = computed(() => {
    // Usar streak_days do userChallenge (calculado no backend)
    // O streak é do desafio inteiro, não da task específica
    // Futuramente pode ser calculado por task específica
    if (!props.isCompleted) {
      return 0
    }
    // Retorna o streak do desafio se a task está completa hoje
    return props.userChallenge?.streak_days ?? 0
  })
  
  // Methods
  const handleManualCheckin = async () => {
    if (submitting.value) return
    
    submitting.value = true
    
    try {
      const response = await csrfFetch('/api/quick-checkin', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          task_id: props.task.id,
          user_challenge_id: props.userChallenge.id,
          source: 'web'
        })
      })
      
      if (response.ok) {
        const data = await response.json()
        emit('checkin-completed', props.task.id, data.checkin)
      } else {
        const errorData = await response.json()
        
        // Se já existe check-in, atualizar o estado local
        if (response.status === 409 && errorData.checkin) {
          emit('checkin-completed', props.task.id, errorData.checkin)
          return
        }
        
        throw new Error(errorData.message || 'Erro ao fazer check-in')
      }
    } catch (error) {
      console.error('Erro:', error)
      alert('Erro ao fazer check-in: ' + error.message)
    } finally {
      submitting.value = false
    }
  }
  
  const handleRemoveCheckin = async () => {
    if (!confirm('Tem certeza que deseja desfazer este check-in?')) return
    
    submitting.value = true
    
    try {
      const response = await csrfFetch(`/checkins/${props.checkin.id}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json'
        }
      })
      
      if (response.ok) {
        emit('checkin-removed', props.task.id)
      } else {
        throw new Error('Erro ao remover check-in')
      }
    } catch (error) {
      console.error('Erro:', error)
      alert('Erro ao remover check-in. Tente novamente.')
    } finally {
      submitting.value = false
    }
  }
  
  const handleCheckinSuccess = (checkin) => {
    showCheckinModal.value = false
    emit('checkin-completed', props.task.id, checkin)
  }
  
  const formatTime = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleTimeString('pt-BR', { 
      hour: '2-digit', 
      minute: '2-digit' 
    })
  }
  </script>