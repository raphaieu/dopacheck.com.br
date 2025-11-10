<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Header -->
    <header class="bg-white/90 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-40">
      <div class="max-w-4xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <Link href="/dopa" class="text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </Link>
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
              <span class="text-white font-bold text-lg">🧠</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Estatísticas</h1>
          </div>
          <div class="w-6"></div>
        </div>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">
      <!-- Stats Overview -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
          <div class="text-2xl font-bold text-blue-600">{{ stats.total_challenges }}</div>
          <div class="text-xs text-gray-500 mt-1">Total Desafios</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
          <div class="text-2xl font-bold text-green-600">{{ stats.completed_challenges }}</div>
          <div class="text-xs text-gray-500 mt-1">Completos</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
          <div class="text-2xl font-bold text-purple-600">{{ stats.total_checkins }}</div>
          <div class="text-xs text-gray-500 mt-1">Check-ins</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
          <div class="text-2xl font-bold text-orange-600">{{ stats.current_streak }}</div>
          <div class="text-xs text-gray-500 mt-1">Sequência Atual</div>
        </div>
      </div>

      <!-- Challenges Breakdown -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Desafios por Status</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="(count, status) in challengesBreakdown" :key="status" class="text-center">
            <div class="text-3xl font-bold text-blue-600">{{ count }}</div>
            <div class="text-sm text-gray-600 capitalize">{{ status }}</div>
          </div>
        </div>
      </div>

      <!-- Monthly Check-ins Chart -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Check-ins por Mês</h2>
        <div class="space-y-2">
          <div 
            v-for="(count, month) in monthlyCheckins" 
            :key="month"
            class="flex items-center gap-4"
          >
            <div class="w-24 text-sm text-gray-600">{{ formatMonth(month) }}</div>
            <div class="flex-1 bg-gray-200 rounded-full h-6 relative">
              <div 
                class="bg-blue-600 h-6 rounded-full flex items-center justify-end pr-2"
                :style="`width: ${(count / maxMonthlyCheckins) * 100}%`"
              >
                <span class="text-xs text-white font-medium">{{ count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tasks Performance -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Performance por Task</h2>
        <div class="space-y-3">
          <div 
            v-for="task in tasksPerformance" 
            :key="task.task.id"
            class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
          >
            <div class="flex items-center gap-3">
              <span class="text-2xl">{{ task.task.icon || '✅' }}</span>
              <div>
                <div class="font-medium text-gray-900">{{ task.task.name }}</div>
                <div class="text-sm text-gray-600">#{{ task.task.hashtag }}</div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-lg font-bold text-blue-600">{{ task.total_checkins }}</div>
              <div class="text-xs text-gray-500">check-ins</div>
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

const props = defineProps({
  stats: Object,
  challengesBreakdown: Object,
  monthlyCheckins: Object,
  tasksPerformance: Array,
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

