<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 pb-20 overflow-x-clip pt-28">
    <!-- Header -->
    <DopaHeader subtitle="Estatísticas" max-width="4xl" home-link="/dopa" :show-back-button="true" back-link="/dopa" />

    <main class="max-w-4xl mx-auto px-4 py-8 relative">
      <!-- Decorative Blurs -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl -z-10"></div>
      <div class="absolute top-1/2 -left-24 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl -z-10"></div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-white/80 group hover:shadow-blue-500/10 transition-all duration-300">
          <div class="size-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Icon icon="lucide:target" class="size-5" />
          </div>
          <div class="text-3xl font-black text-slate-900 tabular-nums">{{ stats.total_challenges }}</div>
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Total Desafios</div>
        </div>
        
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-white/80 group hover:shadow-emerald-500/10 transition-all duration-300">
          <div class="size-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Icon icon="lucide:award" class="size-5" />
          </div>
          <div class="text-3xl font-black text-emerald-600 tabular-nums">{{ stats.completed_challenges }}</div>
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Completos</div>
        </div>

        <div class="bg-white/70 backdrop-blur-xl rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-white/80 group hover:shadow-purple-500/10 transition-all duration-300">
          <div class="size-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Icon icon="lucide:check-circle" class="size-5" />
          </div>
          <div class="text-3xl font-black text-purple-600 tabular-nums">{{ stats.total_checkins }}</div>
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Check-ins</div>
        </div>

        <div class="bg-slate-900 rounded-3xl p-6 shadow-2xl shadow-slate-900/20 group hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
          <div class="absolute -top-4 -right-4 size-24 bg-orange-500/10 rounded-full blur-2xl"></div>
          <div class="size-10 rounded-xl bg-white/10 text-orange-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Icon icon="lucide:flame" class="size-5" />
          </div>
          <div class="text-3xl font-black text-white tabular-nums">{{ stats.current_streak }}</div>
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Sequência Atual</div>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-8">
        <!-- Challenges Breakdown -->
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-white/80 p-6 sm:p-8">
          <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
            <Icon icon="lucide:pie-chart" class="size-4 text-blue-500" />
            Desafios por Status
          </h2>
          <div class="grid grid-cols-2 gap-6">
            <div v-for="(count, status) in challengesBreakdown" :key="status" class="p-4 rounded-2xl bg-slate-50/50 border border-slate-100 hover:border-blue-200 transition-colors group">
              <div class="text-3xl font-black text-slate-900 tabular-nums group-hover:text-blue-600 transition-colors">{{ count }}</div>
              <div class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">{{ formatStatus(status) }}</div>
            </div>
          </div>
        </div>

        <!-- Monthly Check-ins Chart -->
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-white/80 p-6 sm:p-8">
          <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
            <Icon icon="lucide:bar-chart-2" class="size-4 text-purple-500" />
            Check-ins por Mês
          </h2>
          <div class="space-y-4">
            <div 
              v-for="(count, month) in monthlyCheckins" 
              :key="month"
              class="group"
            >
              <div class="flex items-center justify-between mb-2">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ formatMonth(month) }}</div>
                <div class="text-xs font-black text-slate-900">{{ count }}</div>
              </div>
              <div class="relative w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                <div 
                  class="absolute top-0 left-0 bg-gradient-to-r from-blue-500 to-purple-600 h-full rounded-full transition-all duration-1000 shadow-sm"
                  :style="`width: ${(count / maxMonthlyCheckins) * 100}%`"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tasks Performance -->
        <div class="md:col-span-2 bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-white/80 p-6 sm:p-8">
          <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
            <Icon icon="lucide:activity" class="size-4 text-emerald-500" />
            Performance por Task
          </h2>
          <div class="grid sm:grid-cols-2 gap-4">
            <div 
              v-for="task in tasksPerformance" 
              :key="task.task.id"
              class="flex items-center justify-between p-4 bg-white/50 border border-slate-100 rounded-2xl hover:border-blue-200 hover:shadow-lg hover:shadow-blue-500/5 transition-all group"
            >
              <div class="flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-600 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all duration-300">
                  <Icon v-if="task.task.icon_slug" :icon="task.task.icon_slug" class="size-6" />
                  <span v-else class="text-2xl">{{ task.task.icon || '✅' }}</span>
                </div>
                <div>
                  <div class="font-black text-slate-900 group-hover:text-blue-600 transition-colors">{{ task.task.name }}</div>
                  <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">#{{ task.task.hashtag }}</div>
                </div>
              </div>
              <div class="text-right">
                <div class="text-2xl font-black text-slate-900 tabular-nums group-hover:text-blue-600 transition-colors">{{ task.total_checkins }}</div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">check-ins</div>
              </div>
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
import { Icon } from '@iconify/vue'

const props = defineProps({
  stats: Object,
  challengesBreakdown: Object,
  monthlyCheckins: Object,
  tasksPerformance: Array,
})

useSeoMetaTags({
  title: 'Estatísticas',
})

const maxMonthlyCheckins = computed(() => {
  return Math.max(...Object.values(props.monthlyCheckins || {}), 1)
})

const formatMonth = (monthString) => {
  if (!monthString) return ''
  const [year, month] = monthString.split('-')
  const date = new Date(year, month - 1)
  return date.toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' })
}
</script>

