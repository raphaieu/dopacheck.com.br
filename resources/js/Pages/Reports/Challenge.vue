<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Header -->
    <header class="bg-white/90 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-40">
      <div class="max-w-4xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <Link href="/reports" class="text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </Link>
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
              <span class="text-white font-bold text-lg">🧠</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Relatório Detalhado</h1>
          </div>
          <div class="w-6"></div>
        </div>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">
      <!-- Challenge Info -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ userChallenge.challenge.title }}</h2>
        <p class="text-gray-600 mb-4">{{ userChallenge.challenge.description }}</p>
        <div class="flex flex-wrap gap-4 text-sm">
          <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-medium">
            {{ userChallenge.challenge.duration_days }} dias
          </span>
          <span :class="[
            'px-3 py-1 rounded-full font-medium',
            userChallenge.status === 'active' ? 'bg-blue-100 text-blue-700' :
            userChallenge.status === 'completed' ? 'bg-green-100 text-green-700' :
            'bg-gray-100 text-gray-600'
          ]">
            {{ formatStatus(userChallenge.status) }}
          </span>
          <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-700 font-medium">
            {{ userChallenge.total_checkins }} check-ins
          </span>
          <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-700 font-medium">
            Sequência: {{ userChallenge.streak_days }} dias
          </span>
        </div>
      </div>

      <!-- Progress by Day -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Progresso por Dia</h2>
        <div class="space-y-4">
          <div 
            v-for="day in progressByDay" 
            :key="day.day"
            class="border border-gray-200 rounded-xl p-4"
          >
            <div class="flex items-center justify-between mb-3">
              <div>
                <h3 class="font-semibold text-gray-900">Dia {{ day.day }}</h3>
                <p class="text-sm text-gray-600">{{ formatDate(day.date) }}</p>
              </div>
              <div class="text-right">
                <div class="text-lg font-bold text-blue-600">{{ day.completed_count }}/{{ day.total_count }}</div>
                <div class="text-xs text-gray-500">tarefas</div>
              </div>
            </div>
            <div class="space-y-2">
              <div 
                v-for="taskItem in day.tasks" 
                :key="taskItem.task.id"
                class="flex items-center justify-between p-2 rounded-lg"
                :class="taskItem.completed ? 'bg-green-50' : 'bg-gray-50'"
              >
                <div class="flex items-center gap-3">
                  <span class="text-xl">{{ taskItem.task.icon || '✅' }}</span>
                  <div>
                    <div class="font-medium text-gray-900">{{ taskItem.task.name }}</div>
                    <div class="text-xs text-gray-600">#{{ taskItem.task.hashtag }}</div>
                  </div>
                </div>
                <div v-if="taskItem.completed" class="text-green-600 font-medium">
                  ✓ Concluído
                </div>
                <div v-else class="text-gray-400">
                  ○ Pendente
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
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  userChallenge: Object,
  progressByDay: Array,
})

const formatStatus = (status) => {
  const statusMap = {
    'active': 'Ativo',
    'completed': 'Completo',
    'paused': 'Pausado',
    'abandoned': 'Abandonado',
  }
  return statusMap[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('pt-BR', { 
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>

