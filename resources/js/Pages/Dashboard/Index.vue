<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Header -->
    <header class="bg-white/90 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-40">
      <div class="max-w-4xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div
              class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
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

            <!-- Profile Dropdown -->
            <div class="relative" @keydown.esc="showMenu = false">
              <button
                @click="showMenu = !showMenu"
                class="cursor-pointer w-8 h-8 rounded-full overflow-hidden focus:outline-none focus:ring-2 focus:ring-blue-400"
                aria-label="Abrir menu do perfil"
              >
                <img :src="user.profile_photo_url || '/default-avatar.png'" :alt="user.name"
                  class="w-full h-full object-cover">
              </button>
              <transition name="fade" @click.away="showMenu = false">
                <div
                  v-if="showMenu"
                  class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50"
                  @click.away="showMenu = false"
                >
                  <Link href="/reports" class="flex items-center px-4 py-3 hover:bg-gray-50 text-gray-700">
                    <span class="text-xl mr-2">📊</span> Relatórios
                  </Link>
                  <Link :href="`/u/${user.username}`" class="flex items-center px-4 py-3 hover:bg-gray-50 text-gray-700">
                    <span class="text-xl mr-2">🔗</span> Meu Perfil
                  </Link>
                  <Link href="/challenges" class="flex items-center px-4 py-3 hover:bg-gray-50 text-gray-700">
                    <span class="text-xl mr-2">🎯</span> Desafios
                  </Link>
                  <Link href="/profile/settings" class="flex items-center px-4 py-3 hover:bg-gray-50 text-gray-700">
                    <span class="text-xl mr-2">⚙️</span> Config
                  </Link>
                </div>
              </transition>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6 space-y-6">
      <!-- Se não há desafios ativos -->
      <div v-if="activeChallenges.length === 0" class="text-center py-12">
        <div
          class="w-24 h-24 mx-auto mb-6 bg-gradient-to-r from-blue-100 to-purple-100 rounded-full flex items-center justify-center">
          <span class="text-4xl">🎯</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-3">Pronto para um novo desafio?</h2>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          Escolha um dos nossos desafios populares ou crie o seu próprio para começar sua jornada.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <Link href="/challenges"
            class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
          Ver Desafios
          </Link>
          <Link href="/challenges/create"
            class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors">
          Criar Desafio
          </Link>
        </div>
      </div>

      <!-- challenges carousel -->
      <div v-else class="relative">
        <!-- challenges carousel arrows -->
        <div class="flex items-center justify-center relative py-6">
          <!-- left arrow -->
          <button v-if="activeChallenges.length > 1" @click="prevChallenge" class="cursor-pointer absolute -left-3.5 md:left-0 top-1/2 -translate-y-1/2 z-20 w-8 h-8 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-blue-600 shadow-lg transition text-white
                opacity-60 hover:opacity-100 focus:opacity-100 md:opacity-100 md:hover:opacity-100"
            aria-label="Desafio anterior">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <!-- challenges card -->
          <div class="w-full mx-auto" :class="[activeChallenges.length > 1 ? 'max-w-2xl' : '']"
            @touchstart="onTouchStart" @touchend="onTouchEnd" @mousedown="onMouseDown" @mouseup="onMouseUp"
            @mouseleave="onMouseLeave">
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
                      <span class="text-gray-600">Dia {{ currentDay }} de {{ currentChallenge.challenge.duration_days
                        }}</span>
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
                  <ProgressRing :progress="progressPercentage" :size="80" :stroke-width="8" class="text-blue-600" />
                </div>
              </div>

              <!-- Mobile Progress Bar -->
              <div class="sm:hidden mb-4">
                <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                  <span>Progresso</span>
                  <span>{{ Math.round(progressPercentage) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all duration-500"
                    :style="`width: ${progressPercentage}%`"></div>
                </div>
              </div>

              <!-- Quick Stats -->
              <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-100">
                <div class="text-center">
                  <div class="text-2xl font-bold text-blue-600">{{ currentChallenge.streak_days }}</div>
                  <div class="text-xs text-gray-500">Sequência</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-green-600">{{ Math.round(currentChallenge.completion_rate) }}%
                  </div>
                  <div class="text-xs text-gray-500">Concluído</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-purple-600">{{ daysRemaining }}</div>
                  <div class="text-xs text-gray-500">Restantes</div>
                </div>
              </div>
            </div>
          </div>

          <!-- right arrow -->
          <button v-if="activeChallenges.length > 1" @click="nextChallenge" class="cursor-pointer absolute -right-3.5 md:right-0 top-1/2 -translate-y-1/2 z-20 w-8 h-8 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-blue-600 shadow-lg transition text-white
                opacity-60 hover:opacity-100 focus:opacity-100 md:opacity-100 md:hover:opacity-100"
            aria-label="Próximo desafio">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <!-- Bolinhas logo abaixo do card -->
        <div v-if="activeChallenges.length > 1" class="flex justify-center mt-2 mb-6">
          <span v-for="(challenge, idx) in activeChallenges" :key="challenge.id" class="w-3 h-3 rounded-full mx-1"
            :class="idx === currentIndex ? 'bg-blue-600' : 'bg-gray-300'"></span>
        </div>

        <!-- Espaçamento entre card e bloco de tasks -->
        <div class="mb-6"></div>

        <!-- Bloco de tasks do dia, visual separado -->
        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200 mb-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900 mb-2 sm:mb-0 text-center">
              {{ todayFormatted }}
            </h3>
            <span class="text-sm text-gray-500 text-center">
              {{ completedTasksToday }}/{{ totalTasksToday }} concluídas
            </span>
          </div>

          <!-- Task Cards -->
          <div class="space-y-3">
            <TaskCard v-for="task in todayTasks" :key="`task-${task.id}`" :task="task"
              :user-challenge="currentChallenge" :is-completed="task.is_completed" :checkin="task.checkin"
              @checkin-completed="handleCheckinCompleted" @checkin-removed="handleCheckinRemoved" />
          </div>

          <!-- Botão Compartilhar Card do Dia -->
          <div v-if="todayTasks.some(task => task.checkin)" class="flex justify-center mt-6">
            <button @click="showShareModal = true"
              class="bg-gradient-to-r cursor-pointer from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:from-blue-700 hover:to-purple-700 transition-all flex items-center space-x-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-check" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/>
              </svg>
              <span>Compartilhar meu dia</span>
            </button>
          </div>

          <!-- All Tasks Completed -->
          <div v-if="allTasksCompleted"
            class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 text-center border border-green-200 mt-6">
            <div class="text-4xl mb-3">🎉</div>
            <h4 class="text-lg font-bold text-green-800 mb-2">Parabéns! Desafio concluído!</h4>
            <p class="text-green-700 text-sm">
              Você completou todas as tasks do desafio de hoje. Continue assim!
            </p>
          </div>
        </div>

        <!-- WhatsApp Connection, separado das tasks -->
        <div class="mb-6"></div>
        <WhatsAppConnection :user="user" @connection-updated="handleWhatsAppUpdate" />
      </div>
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

    <!-- Modal de Preview do Card do Dia -->
    <template v-if="showShareModal">
      <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl p-4 md:p-8 max-w-md md:max-w-xl w-full relative" style="max-height:95vh; overflow:auto;">
          <button @click="showShareModal = false" class="cursor-pointer absolute top-3 right-3 text-gray-400 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <h2 class="text-xl font-bold mb-4 text-center">Compartilhar meu dia</h2>
          <div class="w-full flex justify-center items-center" style="min-height: 60vh;">
            <div class="relative" style="aspect-ratio:9/16; height:70vh; max-height:80vh; max-width:calc(80vh*9/16);">
              <div v-if="generatingImage" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 bg-gray-100 rounded">
                <div class="text-4xl mb-2">⏳</div>
                <p class="text-sm">Gerando imagem personalizada...</p>
                <p class="text-xs text-gray-500 mt-1">Aguarde alguns segundos</p>
              </div>
              <img v-else-if="shareCardImageUrl" :src="shareCardImageUrl" alt="Preview do Card do Dia" class="absolute inset-0 w-full h-full object-contain rounded shadow" />
              <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 bg-gray-100 rounded">
                <div class="text-4xl mb-2">❌</div>
                <p class="text-sm">Erro ao gerar imagem</p>
              </div>
            </div>
          </div>
          <button 
            @click="downloadShareCard"
            :disabled="!shareCardImageUrl"
            class="cursor-pointer w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center space-x-2 mt-6"
          >
            <svg v-if="generatingImage" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ generatingImage ? 'Gerando...' : 'Baixar imagem' }}</span>
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import TaskCard from '@/components/TaskCard.vue'
import ProgressRing from '@/components/ProgressRing.vue'
import WhatsAppConnection from '@/components/WhatsAppConnection.vue'
import { useApi } from '@/composables/useApi'
import { useShare } from '@/composables/useShare'

// Props do Inertia
const { props } = usePage()
const user = computed(() => props.auth.user)
const activeChallenges = ref(props.activeChallenges || []) // array de desafios ativos
const currentIndex = ref(0)

// Atualiza o desafio e tasks do dia conforme o índice
const currentChallenge = computed(() => activeChallenges.value[currentIndex.value] || null)
const todayTasks = ref(currentChallenge.value?.today_tasks || [])

// Atualiza tasks do dia ao trocar de desafio
watch(currentIndex, (idx) => {
  todayTasks.value = currentChallenge.value?.today_tasks || []
})

// State
const loading = ref(false)
const showMenu = ref(false)
const showShareModal = ref(false)
const generatingImage = ref(false)
const shareCardImageUrl = ref(null)

// Computed
const currentDay = computed(() => {
  if (!currentChallenge.value) return 0
  // Usa o current_day do backend que já está limitado corretamente
  return currentChallenge.value.current_day || 1
})

const daysRemaining = computed(() => {
  if (!currentChallenge.value) return 0
  return Math.max(0, currentChallenge.value.challenge.duration_days - currentDay.value + 1)
})

const progressPercentage = computed(() => {
  if (!currentChallenge.value) return 0
  // Limita o progresso a 100% máximo
  const progress = (currentDay.value / currentChallenge.value.challenge.duration_days) * 100
  return Math.min(100, Math.max(0, progress))
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

// Navegação do carrossel
const prevChallenge = () => {
  if (currentIndex.value > 0) currentIndex.value--
  else currentIndex.value = activeChallenges.value.length - 1
}
const nextChallenge = () => {
  if (currentIndex.value < activeChallenges.value.length - 1) currentIndex.value++
  else currentIndex.value = 0
}

// Methods
const { post: apiPost, loading: apiLoading } = useApi()
const { shareImage, isSupported: isShareSupported } = useShare()

const atualizarStats = async () => {
  if (!currentChallenge.value) return
  try {
    const data = await apiPost(`/api/user-challenge-recalculate-stats/${currentChallenge.value.id}`)
    currentChallenge.value.total_checkins = data.total_checkins
    currentChallenge.value.completion_rate = data.completion_rate
    currentChallenge.value.streak_days = data.streak_days
    currentChallenge.value.best_streak = data.best_streak
    currentChallenge.value.current_day = data.current_day
  } catch (e) {
    console.error('Erro ao atualizar stats:', e)
  }
}

const handleCheckinCompleted = async (taskId, checkin) => {
  loading.value = true

  // Atualizar state local
  const taskIndex = todayTasks.value.findIndex(t => t.id === taskId)
  if (taskIndex !== -1) {
    todayTasks.value[taskIndex].is_completed = true
    todayTasks.value[taskIndex].checkin = checkin
  }

  await atualizarStats()
  loading.value = false
}

const handleCheckinRemoved = async (taskId) => {
  loading.value = true

  // Atualizar state local
  const taskIndex = todayTasks.value.findIndex(t => t.id === taskId)
  if (taskIndex !== -1) {
    todayTasks.value[taskIndex].is_completed = false
    todayTasks.value[taskIndex].checkin = null
  }

  await atualizarStats()
  loading.value = false
}

const handleWhatsAppUpdate = (session) => {
  // ...
}

watch(showShareModal, async (val) => {
  if (val) {
    await generateShareCard()
  } else {
    shareCardImageUrl.value = null
  }
})

const generateShareCard = async () => {
  generatingImage.value = true
  shareCardImageUrl.value = null
  try {
    if (!currentChallenge.value) return
    const payload = {
      challenge_id: currentChallenge.value.id,
      day: currentDay.value,
      total_days: currentChallenge.value.challenge.duration_days,
      title: currentChallenge.value.challenge.title,
      description: currentChallenge.value.challenge.description,
      tasks: todayTasks.value.map(task => ({
        name: task.name,
        completed: task.is_completed
      }))
    }
    const response = await fetch('/api/share-card', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'image/png'
      },
      body: JSON.stringify(payload)
    })
    if (!response.ok) throw new Error('Erro ao gerar imagem')
    const blob = await response.blob()
    shareCardImageUrl.value = URL.createObjectURL(blob)
  } catch (e) {
    alert('Erro ao gerar imagem')
  } finally {
    generatingImage.value = false
  }
}

const downloadShareCard = async () => {
  if (!shareCardImageUrl.value) return
  
  // Tentar compartilhamento nativo primeiro (se disponível)
  if (isShareSupported()) {
    const success = await shareImage(shareCardImageUrl.value, `DOPA Check - Dia ${currentDay.value}`)
    if (success) {
      return // Compartilhamento nativo funcionou
    }
  }
  
  // Fallback: download tradicional
  const a = document.createElement('a')
  a.href = shareCardImageUrl.value
  a.download = `dopa-check-card-${new Date().toISOString().split('T')[0]}.png`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

// Lifecycle
onMounted(() => {
  // Auto-refresh tasks a cada minuto (caso tenha check-ins pelo WhatsApp)
  setInterval(() => {
    if (currentChallenge.value) {
      fetch(`/api/today-tasks?challenge_id=${currentChallenge.value.id}`)
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

const touchStartX = ref(null)
const mouseStartX = ref(null)

function onTouchStart(e) {
  touchStartX.value = e.touches[0].clientX
}
function onTouchEnd(e) {
  if (touchStartX.value === null) return
  const deltaX = e.changedTouches[0].clientX - touchStartX.value
  if (deltaX > 50) prevChallenge()
  if (deltaX < -50) nextChallenge()
  touchStartX.value = null
}
function onMouseDown(e) {
  mouseStartX.value = e.clientX
}
function onMouseUp(e) {
  if (mouseStartX.value === null) return
  const deltaX = e.clientX - mouseStartX.value
  if (deltaX > 50) prevChallenge()
  if (deltaX < -50) nextChallenge()
  mouseStartX.value = null
}
function onMouseLeave() {
  mouseStartX.value = null
}
</script>

<style scoped>
/* Animações personalizadas */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
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