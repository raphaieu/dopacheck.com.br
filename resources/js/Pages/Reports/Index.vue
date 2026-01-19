<template>
  <div class="min-h-screen bg-linear-to-br from-blue-50 via-white to-purple-50">
    <!-- Header -->
    <DopaHeader subtitle="Relatórios" max-width="4xl" home-link="/dopa" :show-back-button="true" back-link="/dopa" />

    <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">
      <!-- Overall Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
          <div class="text-2xl font-bold text-blue-600">{{ overallStats.total_challenges }}</div>
          <div class="text-xs text-gray-500 mt-1">Total Desafios</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
          <div class="text-2xl font-bold text-green-600">{{ overallStats.completed_challenges }}</div>
          <div class="text-xs text-gray-500 mt-1">Completos</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
          <div class="text-2xl font-bold text-purple-600">{{ overallStats.total_checkins }}</div>
          <div class="text-xs text-gray-500 mt-1">Check-ins</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
          <div class="text-2xl font-bold text-orange-600">{{ overallStats.best_streak }}</div>
          <div class="text-xs text-gray-500 mt-1">Melhor Sequência</div>
        </div>
      </div>

      <!-- Challenges List -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gray-900">Meus Desafios</h2>
          <button 
            @click="exportData"
            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
          >
            Exportar Dados
          </button>
        </div>
        
        <div class="space-y-4">
          <div 
            v-for="userChallenge in userChallenges" 
            :key="userChallenge.id"
            class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow cursor-pointer"
            @click="viewChallenge(userChallenge.id)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="font-semibold text-gray-900 mb-2">{{ userChallenge.challenge.title }}</h3>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                  <span>{{ userChallenge.challenge.duration_days }} dias</span>
                  <span>{{ userChallenge.total_checkins }} check-ins</span>
                  <span>{{ Math.round(userChallenge.completion_rate) }}% concluído</span>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  userChallenge.status === 'active' ? 'bg-blue-100 text-blue-700' :
                  userChallenge.status === 'completed' ? 'bg-green-100 text-green-700' :
                  'bg-gray-100 text-gray-600'
                ]">
                  {{ formatStatus(userChallenge.status) }}
                </span>
                <ProgressRing 
                  :progress="userChallenge.progress_percentage" 
                  :size="50" 
                  :stroke-width="6" 
                  color="blue"
                />
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

