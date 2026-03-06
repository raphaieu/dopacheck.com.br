<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 overflow-x-clip pt-28">
    <!-- Header -->
    <DopaHeader 
      :subtitle="currentChallenge ? `Dia ${subtitleDay} de ${currentChallenge.challenge.duration_days}` : null"
      max-width="4xl"
    />

    <main class="max-w-4xl mx-auto px-4 pb-6 space-y-6">
      <!-- CTA de Upgrade (FREE -> PRO) -->
      <div
        v-if="user && !user.is_pro"
        class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-sm border border-white/80 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative overflow-hidden"
      >
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-purple-400/10 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex-1">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-xs font-bold tracking-wider uppercase mb-3 shadow-sm">
            <Icon icon="lucide:sparkles" class="size-3.5" />
            Premium
          </div>
          <h3 class="text-xl font-bold text-slate-900 tracking-tight">
            Desbloqueie limites maiores e recursos exclusivos
          </h3>
          <p class="text-slate-600 text-sm mt-1 max-w-lg">
            Aumente sua produtividade com análise de IA, check-ins ilimitados e estatísticas avançadas.
          </p>
        </div>
        <Link
          href="/subscriptions/create"
          class="relative z-10 bg-gradient-to-r from-blue-600 to-violet-600 text-white px-8 py-3.5 rounded-xl font-bold hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-300 text-center flex items-center justify-center gap-2 group"
        >
          <span>Virar PRO</span>
          <Icon icon="lucide:arrow-right" class="size-4 group-hover:translate-x-1 transition-transform" />
        </Link>
      </div>

      <!-- Se não há desafios ativos -->
      <div v-if="activeChallenges.length === 0" class="py-16 px-6 text-center bg-white/40 backdrop-blur-sm rounded-3xl border border-white/60 shadow-sm relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-400/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-purple-400/5 rounded-full blur-3xl"></div>
        
        <div class="relative z-10">
          <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-blue-500/10 to-purple-500/10 rounded-2xl flex items-center justify-center border border-white/50 shadow-inner">
            <Icon icon="lucide:target" class="size-10 text-blue-600" />
          </div>
          <h2 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Pronto para um novo desafio?</h2>
          <p class="text-slate-600 mb-8 max-w-sm mx-auto text-lg leading-relaxed">
            Escolha um dos nossos desafios populares ou crie o seu próprio para começar sua jornada hoje.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link href="/challenges"
              class="bg-slate-900 text-white px-8 py-4 rounded-xl font-bold hover:bg-slate-800 transition-all shadow-xl shadow-slate-900/10 flex items-center justify-center gap-2">
              <Icon icon="lucide:search" class="size-5" />
              Explorar Desafios
            </Link>
            <Link href="/challenges/create"
              class="bg-white text-slate-900 border border-slate-200 px-8 py-4 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
              <Icon icon="lucide:plus" class="size-5" />
              Criar Desafio
            </Link>
          </div>
        </div>
      </div>

      <!-- challenges carousel -->
      <div v-else class="relative">
        <!-- challenges carousel arrows -->
        <div class="flex items-center justify-center relative py-6">
          <!-- left arrow -->
          <button v-if="activeChallenges.length > 1" @click="prevChallenge" class="cursor-pointer absolute -left-4 md:-left-6 top-1/2 -translate-y-1/2 z-20 w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-xl bg-white/80 backdrop-blur-md border border-white/80 shadow-lg transition-all duration-300 text-slate-900 hover:scale-110 active:scale-95 group"
            aria-label="Desafio anterior">
            <Icon icon="lucide:chevron-left" class="size-6 group-hover:-translate-x-0.5 transition-transform" />
          </button>

          <!-- challenges card -->
          <div class="w-full mx-auto" :class="[activeChallenges.length > 1 ? 'max-w-2xl' : '']"
            @touchstart="onTouchStart" @touchend="onTouchEnd" @mousedown="onMouseDown" @mouseup="onMouseUp"
            @mouseleave="onMouseLeave">
            <div class="bg-white/70 backdrop-blur-xl rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-500/5 border border-white/80 relative overflow-hidden group/card">
              <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-400/10 rounded-full blur-3xl group-hover/card:bg-blue-400/20 transition-colors duration-500"></div>
              
              <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8">
                <div class="flex-1">
                  <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-bold tracking-wider uppercase mb-4 shadow-sm">
                    <Icon icon="lucide:rocket" class="size-3" />
                    Desafio Ativo
                  </div>
                  <h2 class="text-2xl font-black text-slate-900 mb-3 tracking-tight leading-tight">
                    {{ currentChallenge.challenge.title }}
                  </h2>
                  <p class="text-slate-600 mb-6 text-sm leading-relaxed">{{ currentChallenge.challenge.description }}</p>

                  <!-- Progress Info Labels -->
                  <div class="flex flex-wrap items-center gap-y-3 gap-x-3">
                    <div class="flex items-center gap-2 bg-slate-100/50 px-3 py-1.5 rounded-lg border border-slate-200/50">
                      <Icon icon="lucide:calendar" class="size-4 text-blue-600" />
                      <span class="text-sm font-bold text-slate-700">Dia {{ currentDay }} de {{ currentChallenge.challenge.duration_days }}</span>
                    </div>
                    <div class="flex items-center gap-2 bg-slate-100/50 px-3 py-1.5 rounded-lg border border-slate-200/50">
                      <Icon icon="lucide:check-circle" class="size-4 text-emerald-600" />
                      <span class="text-sm font-bold text-slate-700">{{ currentChallenge.total_checkins }} check-ins</span>
                    </div>
                    <div class="flex items-center gap-2 bg-slate-100/50 px-3 py-1.5 rounded-lg border border-slate-200/50">
                      <Icon icon="lucide:flame" class="size-4 text-orange-600" />
                      <span class="text-sm font-bold text-slate-700">{{ currentChallenge.streak_days }} dias seguidos</span>
                    </div>
                  </div>
                </div>

                <!-- Progress Ring -->
                <div class="flex justify-center md:flex-none">
                  <div class="relative p-2 rounded-2xl bg-white/50">
                    <ProgressRing :progress="progressPercentage" :size="100" :stroke-width="10" color="blue" />
                  </div>
                </div>
              </div>

              <!-- Mobile/Tablet Progress Bar (if ring is hidden or small) -->
              <div class="md:hidden mt-2 mb-8">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">
                  <span>Progresso Geral</span>
                  <span>{{ Math.round(progressPercentage) }}%</span>
                </div>
                <div class="w-full bg-slate-200/50 rounded-full h-2.5 p-0.5 border border-slate-200/30 shadow-inner">
                  <div class="bg-gradient-to-r from-blue-600 via-violet-600 to-purple-600 h-1.5 rounded-full transition-all duration-1000 ease-out shadow-lg shadow-blue-500/20"
                    :style="`width: ${progressPercentage}%`"></div>
                </div>
              </div>

              <!-- Quick Stats Grid -->
              <div class="grid grid-cols-3 gap-4 p-4 rounded-2xl bg-slate-50/50 border border-slate-200/50">
                <div class="text-center group/stat">
                  <div class="text-2xl font-black text-blue-600 group-hover/stat:scale-110 transition-transform duration-300">{{ currentChallenge.streak_days }}</div>
                  <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Sequência</div>
                </div>
                <div class="text-center group/stat">
                  <div class="text-2xl font-black text-emerald-600 group-hover/stat:scale-110 transition-transform duration-300 text-nowrap">
                    {{ todayCompletionRate }}%
                  </div>
                  <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Hoje</div>
                </div>
                <div class="text-center group/stat">
                  <div class="text-2xl font-black text-purple-600 group-hover/stat:scale-110 transition-transform duration-300">{{ daysRemaining }}</div>
                  <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1 text-nowrap">Restantes</div>
                </div>
              </div>
            </div>
          </div>

          <!-- right arrow -->
          <button v-if="activeChallenges.length > 1" @click="nextChallenge" class="cursor-pointer absolute -right-4 md:-right-6 top-1/2 -translate-y-1/2 z-20 w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-xl bg-white/80 backdrop-blur-md border border-white/80 shadow-lg transition-all duration-300 text-slate-900 hover:scale-110 active:scale-95 group"
            aria-label="Próximo desafio">
            <Icon icon="lucide:chevron-right" class="size-6 group-hover:translate-x-0.5 transition-transform" />
          </button>
        </div>

        <!-- Indicators Dots -->
        <div v-if="activeChallenges.length > 1" class="flex justify-center gap-2 mt-4 mb-8">
          <button 
            v-for="(challenge, idx) in activeChallenges" 
            :key="challenge.id" 
            @click="currentIndex = idx"
            class="transition-all duration-300 rounded-full cursor-pointer"
            :class="idx === currentIndex ? 'w-8 h-2 bg-blue-600' : 'w-2 h-2 bg-slate-300 hover:bg-slate-400'"
            :aria-label="`Ir para desafio ${idx + 1}`"
          ></button>
        </div>

        <!-- Espaçamento entre card e bloco de tasks -->
        <div class="mb-6"></div>

        <!-- Bloco de tasks do dia, visual separado -->
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl p-6 md:p-8 border border-white/80 shadow-xl shadow-blue-500/5 mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div class="text-center sm:text-left">
              <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                {{ todayFormatted }}
              </h3>
              <div class="flex items-center justify-center sm:justify-start gap-2 mt-1">
                <div class="flex -space-x-1">
                  <div v-for="n in totalTasksToday" :key="n" 
                    class="size-2 rounded-full border border-white"
                    :class="n <= completedTasksToday ? 'bg-emerald-500' : 'bg-slate-200'"
                  ></div>
                </div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">
                  {{ completedTasksToday }}/{{ totalTasksToday }} Concluídas
                </span>
              </div>
            </div>
            
            <div class="flex items-center justify-center sm:justify-end gap-3 text-sm text-slate-600 bg-slate-100/50 px-4 py-2 rounded-xl border border-slate-200/50">
              <Icon icon="lucide:hash" class="size-4 text-blue-600" />
              <span>Dia <span class="font-black text-slate-900">{{ subtitleDay }}</span> de {{ currentChallenge.challenge.duration_days }}</span>
            </div>
          </div>

          <!-- Ir para data -->
          <div v-if="currentChallenge?.challenge?.start_date" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 p-4 rounded-2xl bg-slate-50/50 border border-slate-200/50">
            <div>
              <label for="goto-date" class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">
                <Icon icon="lucide:calendar-search" class="size-3.5 text-blue-600" />
                Ir para data
              </label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                  <Icon icon="lucide:calendar" class="size-5" />
                </div>
                <input
                  id="goto-date"
                  v-model="selectedDate"
                  type="date"
                  :min="dateBounds.min"
                  :max="dateBounds.max"
                  class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 bg-white text-slate-900 transition-all font-medium"
                />
              </div>
            </div>
            <div class="flex items-center">
              <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100/50 w-full">
                <Icon icon="lucide:info" class="size-5 text-blue-600 mt-0.5" />
                <p class="text-xs text-blue-900 leading-relaxed">
                  Mostrando registros de <strong>{{ formatDateBR(dateBounds.min) }}</strong> até <strong>{{ formatDateBR(dateBounds.max) }}</strong>.
                </p>
              </div>
            </div>
          </div>

          <!-- Task Cards -->
          <div class="space-y-4">
            <TaskCard v-for="task in todayTasks" :key="`task-${task.id}`" :task="task"
              :user-challenge="currentChallenge" :is-completed="task.is_completed" :checkin="task.checkin"
              :selected-date="selectedDate"
              @checkin-completed="handleCheckinCompleted" @checkin-removed="handleCheckinRemoved" />
          </div>

          <!-- Botão Compartilhar Card do Dia -->
          <div v-if="todayTasks.some(task => task.checkin)" class="flex justify-center mt-10">
            <button @click="showShareModal = true"
              class="cursor-pointer bg-slate-900 text-white px-8 py-4 rounded-2xl font-bold shadow-xl shadow-slate-900/10 hover:bg-slate-800 hover:-translate-y-1 active:translate-y-0 transition-all flex items-center gap-3 group">
              <Icon icon="lucide:share-2" class="size-5 text-blue-400 group-hover:scale-110 transition-transform" />
              <span>Compartilhar meu dia</span>
            </button>
          </div>

          <!-- All Tasks Completed -->
          <transition name="fade">
            <div v-if="allTasksCompleted"
              class="bg-emerald-500/10 backdrop-blur-sm rounded-3xl p-8 text-center border border-emerald-500/20 mt-10 relative overflow-hidden">
              <div class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl"></div>
              <div class="relative z-10">
                <div class="w-16 h-16 mx-auto mb-4 bg-emerald-100 rounded-2xl flex items-center justify-center border border-emerald-200 shadow-sm">
                  <Icon icon="lucide:party-popper" class="size-8 text-emerald-600" />
                </div>
                <h4 class="text-xl font-black text-emerald-900 mb-2 tracking-tight">Dia Concluído!</h4>
                <p class="text-emerald-700 font-medium max-w-xs mx-auto text-sm leading-relaxed">
                  Você completou todas as suas metas de hoje. Mantenha essa energia!
                </p>
              </div>
            </div>
          </transition>
        </div>

        <!-- WhatsApp Connection Wrapper -->
        <WhatsAppConnection 
          :user="user" 
          :current-challenge="currentChallenge"
          @connection-updated="handleWhatsAppUpdate" 
        />
      </div>
    </main>

    <!-- Loading Overlay (Teleported) -->
    <Teleport to="body">
      <transition name="fade">
        <div v-if="loading" class="fixed inset-0 bg-slate-900/40 backdrop-blur-md flex items-center justify-center z-[150]">
          <div class="bg-white/90 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/50 flex flex-col items-center gap-4">
            <div class="relative">
              <div class="size-12 rounded-full border-4 border-slate-100 border-t-blue-600 animate-spin"></div>
              <Icon icon="lucide:loader-2" class="size-6 text-blue-600 absolute inset-0 m-auto animate-pulse" />
            </div>
            <span class="text-slate-900 font-bold tracking-tight">Processando...</span>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- Modal de Preview do Card do Dia (Teleported) -->
    <Teleport to="body">
      <template v-if="showShareModal">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[110] flex items-center justify-center p-4">
          <div class="bg-white/95 backdrop-blur-2xl rounded-[2.5rem] shadow-2xl p-6 md:p-10 max-w-lg w-full relative border border-white/50 animate-in zoom-in-95 duration-300" style="max-height:95vh; overflow:auto;">
            <button @click="showShareModal = false" class="cursor-pointer absolute top-6 right-6 text-slate-400 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-all">
              <Icon icon="lucide:x" class="size-6" />
            </button>
            
            <div class="text-center mb-8">
              <h2 class="text-xl font-black text-slate-900 tracking-tight">Compartilhar meu dia</h2>
              <p class="text-slate-500 text-sm font-medium mt-1">Gere uma imagem personalizada do seu progresso</p>
            </div>

            <div class="w-full flex justify-center items-center bg-slate-100/50 rounded-3xl border border-slate-200/50 p-4 min-h-[50vh]">
              <div class="relative w-full max-w-[300px]" style="aspect-ratio:9/16;">
                <div v-if="generatingImage" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 bg-white/50 backdrop-blur-md rounded-2xl border border-white/80">
                  <div class="relative mb-4">
                    <Icon icon="lucide:image" class="size-12 text-slate-200 animate-pulse" />
                    <Icon icon="lucide:refresh-cw" class="size-6 text-blue-600 absolute -bottom-1 -right-1 animate-spin" />
                  </div>
                  <p class="font-bold text-slate-900 tracking-tight">Gerando imagem...</p>
                  <p class="text-xs text-slate-500 mt-1">Aguarde alguns segundos</p>
                </div>
                <img v-else-if="shareCardImageUrl" :src="shareCardImageUrl" alt="Preview do Card do Dia" class="absolute inset-0 w-full h-full object-contain rounded-2xl shadow-2xl ring-1 ring-black/5" />
                <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-red-400 bg-red-50/50 backdrop-blur-md rounded-2xl border border-red-100/50">
                  <Icon icon="lucide:alert-circle" class="size-12 mb-2" />
                  <p class="font-bold">Ops! Algo deu errado</p>
                  <p class="text-xs">Tente novamente em instantes</p>
                </div>
              </div>
            </div>

            <div class="mt-8 flex flex-col gap-3">
              <button 
                @click="downloadShareCard"
                :disabled="!shareCardImageUrl || generatingImage"
                class="cursor-pointer w-full bg-slate-900 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-[11px] hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-3 shadow-xl shadow-slate-900/10 active:scale-[0.98]"
              >
                <Icon v-if="generatingImage" icon="lucide:loader-2" class="size-5 animate-spin" />
                <Icon v-else icon="lucide:download" class="size-5 text-blue-400" />
                <span>{{ generatingImage ? 'Gerando...' : 'Baixar ou Compartilhar' }}</span>
              </button>
              <p class="text-center text-[10px] text-slate-400 font-bold uppercase tracking-widest px-4">
                Essa imagem contém o resumo do seu dia e estatísticas atuais do desafio.
              </p>
            </div>
          </div>
        </div>
      </template>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import DopaHeader from '@/components/DopaHeader.vue'
import TaskCard from '@/components/TaskCard.vue'
import ProgressRing from '@/components/ProgressRing.vue'
import WhatsAppConnection from '@/components/WhatsAppConnection.vue'
import { useApi } from '@/composables/useApi'
import { useShare } from '@/composables/useShare'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import { csrfFetch } from '@/utils/csrf.js'

// Props do Inertia
const { props } = usePage()
const user = computed(() => props.auth.user)
const activeChallenges = ref(props.activeChallenges || []) // array de desafios ativos
const currentIndex = ref(0)

useSeoMetaTags({
  title: 'Dashboard',
})

// Atualiza o desafio e tasks do dia conforme o índice
const currentChallenge = computed(() => activeChallenges.value[currentIndex.value] || null)
const todayTasks = ref(currentChallenge.value?.today_tasks || [])

// Atualiza tasks do dia ao trocar de desafio
watch(currentIndex, (idx) => {
  todayTasks.value = currentChallenge.value?.today_tasks || []

  // Ajusta data selecionada ao trocar de desafio (clamp nos bounds)
  const { min, max } = dateBounds.value
  const desired = todayIso()
  if (!min) {
    selectedDate.value = desired
  } else {
    selectedDate.value = desired < min ? min : (desired > max ? max : desired)
  }
  selectedDay.value = null
})

// State
const loading = ref(false)
const showShareModal = ref(false)
const generatingImage = ref(false)
const shareCardImageUrl = ref(null)

// Computed
const currentDay = computed(() => {
  if (!currentChallenge.value) return 0
  // Usa o current_day do backend que já está limitado corretamente
  return currentChallenge.value.current_day || 1
})

// ===== Ir para data (data selecionada dentro do período) =====
const selectedDate = ref(null) // ISO yyyy-mm-dd
const selectedDay = ref(null) // number (dia do desafio relativo ao start_date global)

const toLocalIsoDate = (dateObj) => {
  const d = new Date(dateObj.getTime() - dateObj.getTimezoneOffset() * 60000)
  return d.toISOString().slice(0, 10)
}

const todayIso = () => toLocalIsoDate(new Date())

/** Formata data ISO (YYYY-MM-DD) para DD/MM/AAAA (Brasil) */
const formatDateBR = (iso) => {
  if (!iso) return ''
  const [y, m, d] = String(iso).split('-')
  return `${d}/${m}/${y}`
}

const dateBounds = computed(() => {
  const ch = currentChallenge.value?.challenge
  const min = ch?.start_date || null
  const end = ch?.end_date || null

  // past_only: max = min(end_date, hoje)
  const max = (() => {
    const t = todayIso()
    if (!end) return t
    return end < t ? end : t
  })()

  return { min, max }
})

const selectedDateFormatted = computed(() => {
  const iso = selectedDate.value
  if (!iso) return ''
  const date = new Date(`${iso}T00:00:00`)
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  return date.toLocaleDateString('pt-BR', options)
})

const subtitleDay = computed(() => {
  if (!currentChallenge.value) return 0
  return selectedDay.value ?? currentDay.value
})

const daysRemaining = computed(() => {
  if (!currentChallenge.value) return 0
  
  // Se o backend retornou days_remaining, usa ele (já considera se hoje está completo)
  if (currentChallenge.value.days_remaining !== undefined) {
    return currentChallenge.value.days_remaining
  }
  
  // Fallback: calcula localmente se o backend não retornou
  // Se o dia atual está 100% completo (todas tarefas obrigatórias), considera que esse dia já foi "consumido"
  const todayIsComplete = todayCompletionRate.value === 100 && totalRequiredTasksToday.value > 0
  
  // Se o dia atual está completo, não conta ele nos dias restantes
  const baseRemaining = currentChallenge.value.challenge.duration_days - currentDay.value + 1
  const adjustedRemaining = todayIsComplete ? baseRemaining - 1 : baseRemaining
  
  return Math.max(0, adjustedRemaining)
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

const completedRequiredTasksToday = computed(() => {
  return todayTasks.value.filter(task => task.is_required && task.is_completed).length
})

const totalRequiredTasksToday = computed(() => {
  return todayTasks.value.filter(task => task.is_required).length
})

const todayCompletionRate = computed(() => {
  if (totalRequiredTasksToday.value === 0) return 0
  return Math.round((completedRequiredTasksToday.value / totalRequiredTasksToday.value) * 100)
})

const allTasksCompleted = computed(() => {
  return totalTasksToday.value > 0 && completedTasksToday.value === totalTasksToday.value
})

const todayFormatted = computed(() => selectedDateFormatted.value || '')

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

const fetchTasksForDate = async () => {
  if (!currentChallenge.value) return
  const iso = selectedDate.value || todayIso()
  try {
    const response = await csrfFetch(`/api/today-tasks?challenge_id=${currentChallenge.value.id}&date=${encodeURIComponent(iso)}`, {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
    })
    const data = response.ok ? await response.json() : null
    if (data?.tasks) {
      todayTasks.value = data.tasks
    }
    if (data?.selected_day !== undefined) {
      selectedDay.value = data.selected_day
    }
    if (data?.selected_date) {
      selectedDate.value = data.selected_date
    }
  } catch (e) {
    console.error('Erro ao carregar tasks por data:', e)
  }
}

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

watch(selectedDate, async (val) => {
  if (!val) return
  await fetchTasksForDate()
})

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
      day: subtitleDay.value,
      total_days: currentChallenge.value.challenge.duration_days,
      title: currentChallenge.value.challenge.title,
      description: currentChallenge.value.challenge.description,
      tasks: todayTasks.value.map(task => ({
        name: task.name,
        completed: task.is_completed
      }))
    }
    const response = await csrfFetch('/api/share-card', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
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
  // Init: seleciona uma data válida (clamp no período)
  const { min, max } = dateBounds.value
  const desired = todayIso()
  if (!min) {
    selectedDate.value = desired
  } else {
    selectedDate.value = desired < min ? min : (desired > max ? max : desired)
  }

  // Auto-refresh tasks a cada minuto (caso tenha check-ins pelo WhatsApp)
  setInterval(() => {
    if (currentChallenge.value) {
      const iso = selectedDate.value || todayIso()
      csrfFetch(`/api/today-tasks?challenge_id=${currentChallenge.value.id}&date=${encodeURIComponent(iso)}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      })
        .then(response => response.ok ? response.json() : null)
        .then(data => {
          if (data.tasks) {
            todayTasks.value = data.tasks
          }
          if (data?.selected_day !== undefined) {
            selectedDay.value = data.selected_day
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