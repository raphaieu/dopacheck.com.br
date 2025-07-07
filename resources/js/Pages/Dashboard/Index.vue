<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
      <!-- Header -->
      <header class="bg-white/90 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
                <span class="text-white font-bold text-lg">🧠</span>
              </div>
              <div>
                <h1 class="text-xl font-bold text-gray-900">DOPA Check</h1>
                <p class="text-sm text-gray-500" v-if="currentChallenge">
                  Dia {{ currentDay }} de {{ currentChallenge.challenge.duration_days }}
                </p>
              </div>
            </div>
            
            <div class="flex items-center space-x-2">
              <!-- Plan Badge -->
              <span :class="[
                'px-3 py-1 rounded-full text-xs font-medium',
                user.is_pro ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'
              ]">
                {{ user.is_pro ? '✨ PRO' : '🆓 FREE' }}
              </span>
              
              <!-- Profile -->
              <div class="w-8 h-8 rounded-full overflow-hidden">
                <img :src="user.profile_photo_url || '/default-avatar.png'" :alt="user.name" class="w-full h-full object-cover">
              </div>
            </div>
          </div>
        </div>
      </header>
  
      <main class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        <!-- No Active Challenge State -->
        <div v-if="!currentChallenge" class="text-center py-12">
          <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-r from-blue-100 to-purple-100 rounded-full flex items-center justify-center">
            <span class="text-4xl">🎯</span>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-3">Pronto para um novo desafio?</h2>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">
            Escolha um dos nossos desafios populares ou crie o seu próprio para começar sua jornada.
          </p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <Link href="/challenges" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
              Ver Desafios
            </Link>
            <Link href="/challenges/create" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors">
              Criar Desafio
            </Link>
          </div>
        </div>
  
        <!-- Active Challenge Dashboard -->
        <template v-else>
          <!-- Challenge Header -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                  {{ currentChallenge.challenge.title }}
                </h2>
                <p class="text-gray-600 mb-4">{{ currentChallenge.challenge.description }}</p>
                
                <!-- Progress Info -->
                <div class="flex items-center space-x-6 text-sm">
                  <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                    <span class="text-gray-600">Dia {{ currentDay }} de {{ currentChallenge.challenge.duration_days }}</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span class="text-gray-600">{{ currentChallenge.total_checkins }} check-ins</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                    <span class="text-gray-600">{{ currentChallenge.streak_days }} dias seguidos</span>
                  </div>
                </div>
              </div>
              
              <!-- Progress Ring -->
              <div class="hidden sm:block">
                <ProgressRing 
                  :progress="progressPercentage" 
                  :size="80"
                  :stroke-width="8"
                  class="text-blue-600"
                />
              </div>
            </div>
  
            <!-- Mobile Progress Bar -->
            <div class="sm:hidden mb-4">
              <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                <span>Progresso</span>
                <span>{{ Math.round(progressPercentage) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all duration-500"
                  :style="`width: ${progressPercentage}%`"
                ></div>
              </div>
            </div>
  
            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-100">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ currentChallenge.streak_days }}</div>
                <div class="text-xs text-gray-500">Sequência</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ Math.round(currentChallenge.completion_rate) }}%</div>
                <div class="text-xs text-gray-500">Concluído</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ daysRemaining }}</div>
                <div class="text-xs text-gray-500">Restantes</div>
              </div>
            </div>
          </div>
  
          <!-- Today's Tasks -->
          <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
              <h3 class="text-xl font-bold text-gray-900 mb-2 sm:mb-0 text-center">
                {{ todayFormatted }}
              </h3>
              <span class="text-sm text-gray-500 text-center">
                {{ completedTasksToday }}/{{ totalTasksToday }} concluídas
              </span>
            </div>
  
            <!-- Task Cards -->
            <div class="space-y-3">
              <TaskCard
                v-for="task in todayTasks"
                :key="`task-${task.id}`"
                :task="task"
                :user-challenge="currentChallenge"
                :is-completed="task.is_completed"
                :checkin="task.checkin"
                @checkin-completed="handleCheckinCompleted"
                @checkin-removed="handleCheckinRemoved"
              />
            </div>
  
            <!-- All Tasks Completed -->
            <div v-if="allTasksCompleted" class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 text-center border border-green-200">
              <div class="text-4xl mb-3">🎉</div>
              <h4 class="text-lg font-bold text-green-800 mb-2">Parabéns! Dia concluído!</h4>
              <p class="text-green-700 text-sm">
                Você completou todas as tasks de hoje. Continue assim!
              </p>
            </div>
          </div>
  
          <!-- WhatsApp Connection -->
          <WhatsAppConnection :user="user" @connection-updated="handleWhatsAppUpdate" />
  
          <!-- Quick Actions -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h4 class="font-bold text-gray-900 mb-4">Ações Rápidas</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <Link href="/checkins" class="flex flex-col items-center p-4 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
                <span class="text-2xl mb-2">📊</span>
                <span class="text-sm font-medium text-gray-700">Relatórios</span>
              </Link>
              
              <Link :href="`/u/${user.username}`" class="flex flex-col items-center p-4 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
                <span class="text-2xl mb-2">🔗</span>
                <span class="text-sm font-medium text-gray-700">Meu Perfil</span>
              </Link>
              
              <Link href="/challenges" class="flex flex-col items-center p-4 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
                <span class="text-2xl mb-2">🎯</span>
                <span class="text-sm font-medium text-gray-700">Desafios</span>
              </Link>
              
              <Link href="/profile/settings" class="flex flex-col items-center p-4 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
                <span class="text-2xl mb-2">⚙️</span>
                <span class="text-sm font-medium text-gray-700">Config</span>
              </Link>
            </div>
          </div>
        </template>
      </main>
  
      <!-- Loading Overlay -->
      <div v-if="loading" class="fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 shadow-xl">
          <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-2 border-blue-600 border-t-transparent"></div>
            <span class="text-gray-700 font-medium">Carregando...</span>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { computed, onMounted, ref } from 'vue'
  import { Link, usePage } from '@inertiajs/vue3'
  import TaskCard from '@/components/TaskCard.vue'
  import ProgressRing from '@/components/ProgressRing.vue'
  import WhatsAppConnection from '@/components/WhatsAppConnection.vue'
  
  // Props do Inertia
  const { props } = usePage()
  const user = computed(() => props.auth.user)
  const currentChallenge = ref(props.currentChallenge)
  const todayTasks = ref(props.todayTasks || [])
  const whatsappSession = ref(props.whatsappSession)

  // State
  const loading = ref(false)
  
  // Computed
  const currentDay = computed(() => {
    if (!currentChallenge.value) return 0
    const startDate = new Date(currentChallenge.value.started_at)
    const today = new Date()
    const diffTime = today.getTime() - startDate.getTime()
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return Math.max(1, diffDays)
  })
  
  const daysRemaining = computed(() => {
    if (!currentChallenge.value) return 0
    return Math.max(0, currentChallenge.value.challenge.duration_days - currentDay.value + 1)
  })
  
  const progressPercentage = computed(() => {
    if (!currentChallenge.value) return 0
    return (currentDay.value / currentChallenge.value.challenge.duration_days) * 100
  })
  
  const completedTasksToday = computed(() => {
    return todayTasks.value.filter(task => task.is_completed).length
  })
  
  const totalTasksToday = computed(() => {
    return todayTasks.value.length
  })
  
  const allTasksCompleted = computed(() => {
    return totalTasksToday.value > 0 && completedTasksToday.value === totalTasksToday.value
  })
  
  const todayFormatted = computed(() => {
    const today = new Date()
    const options = { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    }
    return today.toLocaleDateString('pt-BR', options)
  })
  
  // Methods
  const handleCheckinCompleted = (taskId, checkin) => {
    loading.value = true
    
    // Atualizar state local
    const taskIndex = todayTasks.value.findIndex(t => t.id === taskId)
    if (taskIndex !== -1) {
      todayTasks.value[taskIndex].is_completed = true
      todayTasks.value[taskIndex].checkin = checkin
    }
    
    // Atualizar stats do challenge
    if (currentChallenge.value) {
      currentChallenge.value.total_checkins += 1
      
      // Recalcular completion rate
      const totalExpected = currentDay.value * currentChallenge.value.challenge.tasks.length
      currentChallenge.value.completion_rate = (currentChallenge.value.total_checkins / totalExpected) * 100
    }
    
    loading.value = false
  }
  
  const handleCheckinRemoved = (taskId) => {
    loading.value = true
    
    // Atualizar state local
    const taskIndex = todayTasks.value.findIndex(t => t.id === taskId)
    if (taskIndex !== -1) {
      todayTasks.value[taskIndex].is_completed = false
      todayTasks.value[taskIndex].checkin = null
    }
    
    // Atualizar stats do challenge
    if (currentChallenge.value) {
      currentChallenge.value.total_checkins = Math.max(0, currentChallenge.value.total_checkins - 1)
      
      // Recalcular completion rate
      const totalExpected = currentDay.value * currentChallenge.value.challenge.tasks.length
      currentChallenge.value.completion_rate = (currentChallenge.value.total_checkins / totalExpected) * 100
    }
    
    loading.value = false
  }
  
  const handleWhatsAppUpdate = (session) => {
    whatsappSession.value = session
  }
  
  // Lifecycle
  onMounted(() => {
    // Auto-refresh tasks a cada minuto (caso tenha check-ins pelo WhatsApp)
    setInterval(() => {
      if (currentChallenge.value) {
        fetch('/api/today-tasks')
          .then(response => response.json())
          .then(data => {
            if (data.tasks) {
              todayTasks.value = data.tasks
            }
          })
          .catch(console.error)
      }
    }, 60000) // 1 minuto
  })
  </script>
  
  <style scoped>
  /* Animações personalizadas */
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
  }
  
  .fade-enter-from, .fade-leave-to {
    opacity: 0;
  }
  
  /* Scrollbar customizada */
  ::-webkit-scrollbar {
    width: 6px;
  }
  
  ::-webkit-scrollbar-track {
    background: #f1f5f9;
  }
  
  ::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
  }
  
  ::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }
  </style>