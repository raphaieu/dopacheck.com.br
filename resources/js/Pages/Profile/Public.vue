<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Header -->
    <DopaHeaderWrapper subtitle="Perfil Público" max-width="4xl" :show-back-button="true" back-link="/challenges" />

    <main class="max-w-4xl mx-auto px-4 py-8">
      <!-- Profile Header -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
          <img 
            :src="profileUser.avatar || '/default-avatar.png'" 
            :alt="profileUser.name"
            class="w-24 h-24 rounded-full object-cover border-4 border-blue-100"
          />
          <div class="flex-1 text-center sm:text-left">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ profileUser.name }}</h2>
            <p class="text-gray-600 mb-4">@{{ profileUser.username || profileUser.id }}</p>
            <div class="flex flex-wrap gap-4 justify-center sm:justify-start">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ stats.total_challenges }}</div>
                <div class="text-xs text-gray-500">Desafios</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ stats.completed_challenges }}</div>
                <div class="text-xs text-gray-500">Completos</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ stats.total_checkins }}</div>
                <div class="text-xs text-gray-500">Check-ins</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-orange-600">{{ stats.best_streak }}</div>
                <div class="text-xs text-gray-500">Melhor Sequência</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Current Challenge -->
      <div v-if="currentChallenge" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Desafio Atual</h3>
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <h4 class="text-xl font-semibold text-gray-900 mb-2">{{ currentChallenge.challenge.title }}</h4>
            <p class="text-gray-600 mb-4">{{ currentChallenge.challenge.description }}</p>
            <div class="flex items-center gap-4 text-sm text-gray-600">
              <span>Dia {{ currentChallenge.current_day }} de {{ currentChallenge.challenge.duration_days }}</span>
              <span>{{ Math.round(currentChallenge.progress_percentage) }}% concluído</span>
            </div>
          </div>
          <div class="w-20 h-20">
            <ProgressRing 
              :progress="currentChallenge.progress_percentage" 
              :size="80" 
              :stroke-width="8" 
              color="blue"
            />
          </div>
        </div>
      </div>

      <!-- Completed Challenges -->
      <div v-if="completedChallenges.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Desafios Completados</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div 
            v-for="userChallenge in completedChallenges" 
            :key="userChallenge.id"
            class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow"
          >
            <h4 class="font-semibold text-gray-900 mb-2">{{ userChallenge.challenge.title }}</h4>
            <p class="text-sm text-gray-600">{{ userChallenge.challenge.duration_days }} dias</p>
          </div>
        </div>
      </div>

      <!-- Recent Check-ins Grid -->
      <div v-if="recentCheckins.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Check-ins Recentes</h3>
        <div class="grid grid-cols-3 gap-2">
          <div 
            v-for="checkin in recentCheckins" 
            :key="checkin.id"
            class="aspect-square rounded-lg overflow-hidden cursor-pointer hover:opacity-80 transition-opacity"
            @click="openImageModal(checkin.image_url)"
          >
            <img 
              :src="checkin.image_url" 
              :alt="checkin.task.name"
              class="w-full h-full object-cover"
            />
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!currentChallenge && completedChallenges.length === 0 && recentCheckins.length === 0" 
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="text-6xl mb-4">📭</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Perfil vazio</h3>
        <p class="text-gray-600">Este usuário ainda não tem desafios ou check-ins públicos.</p>
      </div>
    </main>

    <!-- Image Modal -->
    <div v-if="selectedImage" 
      class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4"
      @click="selectedImage = null"
    >
      <img 
        :src="selectedImage" 
        alt="Check-in"
        class="max-w-full max-h-full object-contain rounded-lg"
        @click.stop
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import DopaHeaderWrapper from '@/components/DopaHeaderWrapper.vue'
import ProgressRing from '@/components/ProgressRing.vue'

const props = defineProps({
  profileUser: Object,
  completedChallenges: Array,
  currentChallenge: Object,
  recentCheckins: Array,
  stats: Object,
})

const selectedImage = ref(null)

const openImageModal = (imageUrl) => {
  selectedImage.value = imageUrl
}
</script>

