<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 pb-20 overflow-x-clip pt-28">
    <!-- Header -->
    <DopaHeader subtitle="Relatórios" max-width="4xl" home-link="/dopa" :show-back-button="true" back-link="/dopa" />

    <main class="max-w-4xl mx-auto px-4 py-8 relative">
      <!-- Decorative Blurs -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl -z-10"></div>
      <div class="absolute top-1/2 -left-24 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl -z-10"></div>

      <!-- Overall Stats Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-white/80 group hover:shadow-blue-500/10 transition-all duration-300">
          <div class="size-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Icon icon="lucide:target" class="size-5" />
          </div>
          <div class="text-3xl font-black text-slate-900 tabular-nums">{{ overallStats.total_challenges }}</div>
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Total Desafios</div>
        </div>
        
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-white/80 group hover:shadow-emerald-500/10 transition-all duration-300">
          <div class="size-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Icon icon="lucide:award" class="size-5" />
          </div>
          <div class="text-3xl font-black text-emerald-600 tabular-nums">{{ overallStats.completed_challenges }}</div>
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Completos</div>
        </div>

        <div class="bg-white/70 backdrop-blur-xl rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-white/80 group hover:shadow-purple-500/10 transition-all duration-300">
          <div class="size-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Icon icon="lucide:check-circle" class="size-5" />
          </div>
          <div class="text-3xl font-black text-purple-600 tabular-nums">{{ overallStats.total_checkins }}</div>
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Check-ins</div>
        </div>

        <div class="bg-slate-900 rounded-3xl p-6 shadow-2xl shadow-slate-900/20 group hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
          <div class="absolute -top-4 -right-4 size-24 bg-orange-500/10 rounded-full blur-2xl"></div>
          <div class="size-10 rounded-xl bg-white/10 text-orange-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Icon icon="lucide:flame" class="size-5" />
          </div>
          <div class="text-3xl font-black text-white tabular-nums">{{ overallStats.best_streak }}</div>
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Melhor Sequência</div>
        </div>
      </div>

      <!-- Challenges List Card -->
      <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-white/80 overflow-hidden">
        <div class="p-6 sm:p-8 flex items-center justify-between border-b border-slate-100/50">
          <h2 class="text-xl font-black text-slate-900 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <Icon icon="lucide:book-open" class="size-5" />
            </span>
            Meus Desafios
          </h2>
          <button 
            @click="exportData"
            class="text-xs font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest px-4 py-2 rounded-xl hover:bg-blue-50 transition-all active:scale-95 border border-blue-100"
          >
            Exportar Dados
          </button>
        </div>
        
        <div class="">
          <div 
            v-for="userChallenge in userChallenges" 
            :key="userChallenge.id"
            class="p-6 hover:bg-white transition-all cursor-pointer border-b border-slate-100 last:border-0 group"
            @click="viewChallenge(userChallenge.id)"
          >
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
              <div class="flex-1 text-center sm:text-left">
                <h3 class="text-lg font-black text-slate-900 mb-2 group-hover:text-blue-600 transition-colors leading-tight">
                    {{ userChallenge.challenge.title }}
                </h3>
                <div class="flex flex-wrap justify-center sm:justify-start gap-4">
                  <div class="flex items-center gap-1.5 text-xs text-slate-400 font-bold uppercase tracking-wider">
                    <Icon icon="lucide:calendar" class="size-3" />
                    {{ userChallenge.challenge.duration_days }} dias
                  </div>
                  <div class="flex items-center gap-1.5 text-xs text-slate-400 font-bold uppercase tracking-wider">
                    <Icon icon="lucide:check-square" class="size-3" />
                    {{ userChallenge.total_checkins }} check-ins
                  </div>
                  <div class="flex items-center gap-1.5 text-xs text-blue-600 font-black uppercase tracking-widest">
                    <Icon icon="lucide:trending-up" class="size-3" />
                    {{ Math.round(userChallenge.progress_percentage) }}%
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-6">
                <span :class="[
                  'px-3.5 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm',
                  userChallenge.status === 'active' ? 'bg-blue-50 text-blue-600' :
                  userChallenge.status === 'completed' ? 'bg-emerald-50 text-emerald-600' :
                  'bg-slate-100 text-slate-500'
                ]">
                  {{ formatStatus(userChallenge.status) }}
                </span>

                <div class="relative group-hover:scale-110 transition-transform">
                  <div class="absolute -inset-2 bg-blue-400/10 rounded-full blur-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                  <ProgressRing 
                    :progress="userChallenge.today_progress_percentage ?? 0" 
                    :size="56" 
                    :stroke-width="6" 
                    color="blue"
                    class="relative z-10"
                  />
                  <div class="inset-0 flex items-center justify-center text-[10px] font-black text-slate-400 uppercase tracking-tighter">
                    Hoje
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import DopaHeader from '@/components/DopaHeader.vue'
import ProgressRing from '@/components/ProgressRing.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import { formatUserChallengeStatus } from '@/utils/userChallengeStatus.js'
import { Icon } from '@iconify/vue'

const props = defineProps({
  userChallenges: Array,
  overallStats: Object,
})

useSeoMetaTags({
  title: 'Relatórios',
})

const viewChallenge = (userChallengeId) => {
  router.visit(`/reports/challenge/${userChallengeId}`)
}

const exportData = () => {
  // TODO: Implement export
  alert('Funcionalidade de exportação em breve!')
}

const formatStatus = (status) => formatUserChallengeStatus(status)
</script>

