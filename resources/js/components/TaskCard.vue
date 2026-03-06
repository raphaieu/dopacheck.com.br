<template>
  <div :class="[
    'rounded-3xl p-5 md:p-6 transition-all duration-500 border relative overflow-hidden group',
    isCompleted 
      ? 'bg-emerald-500/5 border-emerald-500/20 shadow-lg shadow-emerald-500/5' 
      : 'bg-white/60 backdrop-blur-md border-white/80 hover:bg-white/80 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-0.5'
  ]">
    <div class="flex items-start gap-4 md:gap-6 relative z-10">
      <!-- Task Icon & Status -->
      <div class="flex-shrink-0 relative">
        <div :class="[
          'w-14 h-14 rounded-2xl flex items-center justify-center text-3xl transition-all duration-500 shadow-sm border',
          isCompleted 
            ? 'bg-emerald-100 border-emerald-200 text-emerald-600 scale-110 shadow-emerald-200/50' 
            : 'bg-white border-slate-200 shadow-slate-200/50 text-slate-400 group-hover:scale-110 group-hover:border-blue-200 group-hover:text-blue-600'
        ]" :style="!isCompleted ? `background-color: ${task.color}15; color: ${task.color}` : null">
        
          <Icon v-if="isCompleted" icon="lucide:check-circle-2" class="size-8" />
          <template v-else>
            <Icon v-if="task.icon_slug" :icon="task.icon_slug" class="size-8" />
            <span v-else>{{ task.icon || '📋' }}</span>
          </template>
        </div>
        
        <!-- Streak indicator -->
        <transition name="fade">
          <div v-if="isCompleted && streakDays > 1" class="absolute -top-2 -right-2 bg-gradient-to-br from-orange-400 to-red-500 text-white text-[10px] font-black rounded-full size-6 flex items-center justify-center shadow-lg border-2 border-white ring-2 ring-orange-500/20">
            {{ streakDays }}
          </div>
        </transition>
      </div>

      <!-- Task Content -->
      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-4 mb-2">
          <div class="flex-1">
            <h4 :class="[
              'font-black text-lg md:text-xl transition-colors tracking-tight leading-none',
              isCompleted ? 'text-emerald-900' : 'text-slate-900'
            ]">
              {{ task.name }}
            </h4>
            
            <p class="text-slate-500 text-sm mt-2 leading-relaxed line-clamp-2 md:line-clamp-none">
              {{ task.description }}
            </p>
            
            <!-- Tags Row -->
            <div class="mt-4 flex flex-wrap items-center gap-2">
              <span :class="[
                'inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm border',
                isCompleted 
                  ? 'bg-emerald-100 border-emerald-200 text-emerald-700' 
                  : 'bg-blue-50 border-blue-100 text-blue-700'
              ]">
                #{{ task.hashtag }}
              </span>
              
              <!-- Required indicator -->
              <span v-if="task.is_required" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-orange-50 border border-orange-100 text-orange-700 text-[10px] font-bold uppercase tracking-wider shadow-sm">
                <Icon icon="lucide:star" class="size-3 fill-orange-500" />
                Obrigatória
              </span>
            </div>
          </div>

          <!-- Completion Checkmark (Desktop/Tablet) -->
          <div class="hidden sm:block flex-shrink-0">
            <div v-if="isCompleted" class="text-emerald-500 bg-emerald-100 p-2 rounded-full shadow-inner">
              <Icon icon="lucide:check" class="size-6 stroke-[3]" />
            </div>
            <div v-else class="text-slate-200 group-hover:text-slate-300 transition-colors">
              <Icon icon="lucide:circle" class="size-8 stroke-[1.5]" />
            </div>
          </div>
        </div>

        <!-- Checkin Info (Expanded when completed) -->
        <transition name="fade">
          <div v-if="isCompleted && checkin" class="mt-5 p-4 bg-white/50 backdrop-blur-sm rounded-2xl border border-white/80 shadow-inner">
            <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest mb-3">
              <div class="flex items-center gap-2 text-slate-500">
                <Icon :icon="checkin.source === 'whatsapp' ? 'lucide:message-square' : 'lucide:globe'" class="size-4" :class="checkin.source === 'whatsapp' ? 'text-emerald-500' : 'text-blue-500'" />
                <span>
                  {{ checkin.source === 'whatsapp' ? 'WhatsApp' : 'Web' }} 
                  <span class="mx-1 opacity-30">•</span> 
                  {{ formatTime(checkin.checked_at) }}
                </span>
              </div>
              
              <button
                @click="handleRemoveCheckin"
                class="cursor-pointer text-slate-300 hover:text-red-500 hover:scale-110 transition-all p-1"
                title="Desfazer check-in"
              >
                <Icon icon="lucide:trash-2" class="size-4" />
              </button>
            </div>
            
            <!-- Checkin Image -->
            <div v-if="checkin.image_url" class="relative group/img overflow-hidden rounded-xl bg-slate-100 mb-3 border border-slate-200/50 shadow-sm">
              <img 
                :src="checkin.image_url" 
                alt="Check-in" 
                class="w-full h-40 object-cover cursor-pointer group-hover/img:scale-105 transition-transform duration-700"
                @click="showImageModal = true"
              >
              <div class="absolute inset-0 bg-black/20 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                <Icon icon="lucide:maximize" class="size-8 text-white drop-shadow-lg" />
              </div>
            </div>
            
            <!-- Checkin Message -->
            <div v-if="checkin.message" class="text-sm text-slate-700 leading-relaxed italic bg-white/40 p-3 rounded-lg border border-white/50 mb-3">
              "{{ checkin.message }}"
            </div>
            
            <!-- AI Analysis (PRO) -->
            <div v-if="checkin.ai_analysis && user.is_pro" class="p-4 bg-violet-600/5 rounded-2xl border border-violet-600/10 shadow-sm relative overflow-hidden">
              <div class="absolute -top-12 -right-12 size-24 bg-violet-600/5 rounded-full blur-xl"></div>
              <div class="relative z-10">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2 px-2 py-1 rounded-lg bg-violet-100 text-violet-700 text-[10px] font-black uppercase tracking-wider">
                    <Icon icon="lucide:bot" class="size-3.5" />
                    Análise IA
                  </div>
                  <span class="text-[10px] font-bold text-violet-400 uppercase tracking-widest">
                    {{ Math.round(checkin.confidence_score * 100) }}% Precisão
                  </span>
                </div>
                <p class="text-sm text-slate-700 font-medium leading-relaxed">{{ checkin.ai_analysis.summary }}</p>
              </div>
            </div>
          </div>
        </transition>

        <!-- Check-in Actions (Uncompleted) -->
        <div v-if="!isCompleted" class="mt-6 flex flex-col sm:flex-row gap-3">
          <button
            @click="showCheckinModal = true"
            :disabled="submitting"
            class="cursor-pointer flex-1 bg-slate-900 text-white px-6 py-3.5 rounded-2xl font-bold hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-xl shadow-slate-900/10 flex items-center justify-center gap-3 group/btn active:scale-95"
          >
            <Icon icon="lucide:camera" class="size-5 text-blue-400 group-hover/btn:scale-110 transition-transform" />
            <span>{{ submitting ? 'Enviando...' : 'Check-in' }}</span>
          </button>
          
          <button
            @click="handleManualCheckin"
            :disabled="submitting"
            class="cursor-pointer px-6 py-3.5 bg-white border border-slate-200 text-slate-900 rounded-2xl font-bold hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-3 active:scale-95"
          >
            <Icon icon="lucide:check-square" class="size-5 text-emerald-500" />
            <span>Marcar como feito</span>
          </button>
        </div>

        <!-- WhatsApp Tip -->
        <div v-if="!isCompleted && whatsappConnected" class="mt-4 p-4 bg-blue-50/50 backdrop-blur-sm rounded-2xl border border-blue-100/50 flex items-start gap-4">
          <div class="bg-white p-2 rounded-xl shadow-sm border border-blue-100 flex-shrink-0 animate-bounce transition-all duration-1000">
             <Icon icon="lucide:lightbulb" class="size-6 text-blue-600" />
          </div>
          <div class="text-[11px] leading-relaxed">
            <p class="font-black text-blue-900 uppercase tracking-widest mb-1">Dica de mestre</p>
            <p class="text-blue-800 font-medium">Envie uma foto + <code class="bg-blue-100 px-2 py-0.5 rounded-md font-black text-blue-900 shadow-sm border border-blue-200">#{{ task.hashtag }}</code> para o nosso robô completar automaticamente via WhatsApp.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals (Checkin, Image) remains same structure but they will benefit from global styles if any -->
    <CheckinModal
      :show="showCheckinModal"
      :task="task"
      :user-challenge="userChallenge"
      :checked-date="selectedDate"
      @close="showCheckinModal = false"
      @checkin-completed="handleCheckinSuccess"
    />

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
import { Icon } from '@iconify/vue'
import { csrfFetch } from '@/utils/csrf.js'
import CheckinModal from '@/components/CheckinModal.vue'
import ImageModal from '@/components/ImageModal.vue'
  
  // Props
  const props = defineProps({
    task: {
      type: Object,
      required: true
    },
    selectedDate: {
      type: String,
      default: null
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
          checked_date: props.selectedDate || null,
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