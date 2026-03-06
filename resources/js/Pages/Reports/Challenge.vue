<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 pb-20 overflow-x-clip pt-24">
    <!-- Header -->
    <DopaHeader subtitle="Relatório Detalhado" max-width="4xl" home-link="/dopa" :show-back-button="true" back-link="/reports" />

    <main class="max-w-4xl mx-auto px-4 py-8 relative">
      <!-- Decorative Blurs -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl -z-10"></div>
      
      <!-- Challenge Info Card -->
      <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-white/80 p-6 sm:p-8 mb-8 overflow-hidden relative group">
        <div class="absolute -top-12 -right-12 opacity-[0.03] group-hover:opacity-10 transition-opacity">
            <Icon icon="lucide:clipboard-list" class="size-48" />
        </div>

        <div class="relative z-10">
          <h2 class="text-3xl font-black text-slate-900 mb-2 leading-tight">{{ userChallenge.challenge.title }}</h2>
          <p class="text-slate-600 mb-8 leading-relaxed max-w-2xl">{{ userChallenge.challenge.description }}</p>
          
          <div class="flex flex-wrap gap-3">
            <div class="px-4 py-2 rounded-2xl bg-blue-50 text-blue-700 font-black text-[10px] uppercase tracking-widest flex items-center gap-2 border border-blue-100/50">
              <Icon icon="lucide:calendar" class="size-3" />
              {{ userChallenge.challenge.duration_days }} dias
            </div>
            <div :class="[
              'px-4 py-2 rounded-2xl font-black text-[10px] uppercase tracking-widest flex items-center gap-2 border',
              userChallenge.status === 'active' ? 'bg-blue-50 text-blue-700 border-blue-100/50' :
              userChallenge.status === 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-100/50' :
              'bg-slate-100 text-slate-500 border-slate-200/50'
            ]">
              <Icon :icon="userChallenge.status === 'completed' ? 'lucide:check-circle' : 'lucide:clock'" class="size-3" />
              {{ formatStatus(userChallenge.status) }}
            </div>
            <div class="px-4 py-2 rounded-2xl bg-purple-50 text-purple-700 font-black text-[10px] uppercase tracking-widest flex items-center gap-2 border border-purple-100/50">
              <Icon icon="lucide:check-square" class="size-3" />
              {{ userChallenge.total_checkins }} check-ins
            </div>
            <div class="px-4 py-2 rounded-2xl bg-orange-50 text-orange-700 font-black text-[10px] uppercase tracking-widest flex items-center gap-2 border border-orange-100/50">
              <Icon icon="lucide:flame" class="size-3" />
              Sequência: {{ userChallenge.streak_days }} dias
            </div>
          </div>
        </div>
      </div>

      <!-- Progress by Day -->
      <div class="space-y-6">
        <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest px-4 flex items-center gap-2">
            <Icon icon="lucide:trending-up" class="size-4 text-blue-500" />
            Evolução Diária
        </h2>
        
        <div 
          v-for="day in progressByDay" 
          :key="day.day"
          class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 shadow-xl shadow-slate-200/50 border border-white/80 hover:shadow-blue-500/5 transition-all group"
        >
          <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100/50">
            <div>
              <h3 class="text-xl font-black text-slate-900 flex items-center gap-2">
                  Dia {{ day.day }}
                  <span v-if="day.completed_count === day.total_count && day.total_count > 0" class="text-emerald-500">
                      <Icon icon="lucide:check-circle-2" class="size-5" />
                  </span>
              </h3>
              <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-1">{{ formatDate(day.date) }}</p>
            </div>
            <div class="text-right">
              <div class="text-2xl font-black text-blue-600 tabular-nums">
                {{ day.completed_count }}<span class="text-slate-300 text-sm mx-1">/</span><span class="text-slate-400 text-lg">{{ day.total_count }}</span>
              </div>
              <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">tarefas</div>
            </div>
          </div>

          <div class="grid sm:grid-cols-2 gap-3">
            <div 
              v-for="taskItem in day.tasks" 
              :key="taskItem.task.id"
              class="flex items-center justify-between p-4 rounded-2xl transition-all border"
              :class="taskItem.completed 
                ? 'bg-emerald-50/30 border-emerald-100/50' 
                : 'bg-slate-50/50 border-slate-100'
              "
            >
              <div class="flex items-center gap-4">
                <div class="size-10 rounded-xl flex items-center justify-center text-xl shadow-sm"
                  :class="taskItem.completed ? 'bg-white text-emerald-600' : 'bg-white text-slate-400'"
                >
                  <Icon v-if="taskItem.task.icon_slug" :icon="taskItem.task.icon_slug" class="size-5" />
                  <span v-else>{{ taskItem.task.icon || '✅' }}</span>
                </div>
                <div>
                  <div class="font-black text-sm transition-colors" :class="taskItem.completed ? 'text-slate-900' : 'text-slate-400'">
                    {{ taskItem.task.name }}
                  </div>
                  <div class="text-[10px] font-bold uppercase tracking-widest" :class="taskItem.completed ? 'text-emerald-600' : 'text-slate-300'">
                    #{{ taskItem.task.hashtag }}
                  </div>
                </div>
              </div>

              <div v-if="taskItem.completed" class="bg-emerald-500 p-1.5 rounded-full text-white shadow-lg shadow-emerald-500/20">
                <Icon icon="lucide:check" class="size-3" />
              </div>
              <div v-else class="size-6 rounded-full border-2 border-slate-200"></div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DopaHeader from '@/components/DopaHeader.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import { formatUserChallengeStatus } from '@/utils/userChallengeStatus.js'
import { Icon } from '@iconify/vue'

const props = defineProps({
  userChallenge: Object,
  progressByDay: Array,
})

useSeoMetaTags({
  title: computed(() => props.userChallenge?.challenge?.title ? `Relatório - ${props.userChallenge.challenge.title}` : 'Relatório Detalhado'),
})
const formatStatus = (status) => formatUserChallengeStatus(status)

const formatDate = (dateString) => {
  if (!dateString) return ''
  // Parse a data como YYYY-MM-DD e criar Date no timezone local
  // Isso evita problemas de timezone ao interpretar a string
  const [year, month, day] = dateString.split('-').map(Number)
  const date = new Date(year, month - 1, day) // month é 0-indexed no JS
  return date.toLocaleDateString('pt-BR', { 
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>

