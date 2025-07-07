<template>
    <div
        class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 hover-scale">
        <!-- Challenge Header -->
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                    {{ challenge.title }}
                </h3>
                <p class="text-gray-600 text-sm line-clamp-3 mb-3">
                    {{ challenge.description }}
                </p>
            </div>

            <!-- Challenge Image/Icon -->
            <div class="flex-shrink-0 ml-4">
                <div v-if="challenge.image_url" class="w-16 h-16 rounded-lg overflow-hidden">
                    <img :src="challenge.image_url" :alt="challenge.title" class="w-full h-full object-cover">
                </div>
                <div v-else
                    class="w-16 h-16 bg-gradient-to-br from-blue-100 to-purple-100 rounded-lg flex items-center justify-center">
                    <span class="text-2xl">{{ getCategoryIcon(challenge.category) }}</span>
                </div>
            </div>
        </div>

        <!-- Challenge Meta -->
        <div class="flex flex-wrap gap-2 mb-4">
            <!-- Category -->
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ getCategoryIcon(challenge.category) }} {{ formatCategory(challenge.category) }}
            </span>

            <!-- Difficulty -->
            <span :class="getDifficultyClasses(challenge.difficulty)">
                {{ getDifficultyIcon(challenge.difficulty) }} {{ formatDifficulty(challenge.difficulty) }}
            </span>

            <!-- Duration -->
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                📅 {{ challenge.duration_days }} dias
            </span>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
            <div class="text-center">
                <div class="text-lg font-bold text-blue-600">{{ formatNumber(challenge.participant_count) }}</div>
                <div class="text-xs text-gray-500">Participantes</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-green-600">{{ challenge.tasks?.length || 0 }}</div>
                <div class="text-xs text-gray-500">Tasks</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-purple-600">{{ getCompletionRate(challenge) }}%</div>
                <div class="text-xs text-gray-500">Conclusão</div>
            </div>
        </div>

        <!-- Creator Info -->
        <div v-if="challenge.creator" class="flex items-center space-x-2 mb-4 text-xs text-gray-500">
            <img :src="challenge.creator.profile_photo_url || '/default-avatar.png'" :alt="challenge.creator.name"
                class="w-5 h-5 rounded-full">
            <span>Por {{ challenge.creator.name }}</span>
            <span>•</span>
            <span>{{ formatDate(challenge.created_at) }}</span>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
            <button @click="$emit('view', challenge.id)"
                class="cursor-pointer flex-1 border border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                Ver Detalhes
            </button>

            <button v-if="!isParticipating" @click="handleJoin" :disabled="joining"
                class="cursor-pointer flex-1 bg-blue-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors flex items-center justify-center space-x-2">
                <svg v-if="joining" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span>{{ joining ? 'Entrando...' : 'Participar' }}</span>
            </button>

            <div v-else
                class="flex-1 bg-green-100 text-green-800 px-4 py-2.5 rounded-lg font-medium flex items-center justify-center space-x-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span>Participando</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

// Props
const props = defineProps({
    challenge: {
        type: Object,
        required: true
    }
})

// Emits
const emit = defineEmits(['join', 'view'])

// State
const joining = ref(false)

// Page data
const { props: pageProps } = usePage()
const user = computed(() => pageProps.auth?.user)

// Computed
const isParticipating = computed(() => {
    // Check if user is participating in this challenge
    return props.challenge.user_is_participating || false
})

// Methods
const handleJoin = async () => {
    if (joining.value) return

    joining.value = true
    try {
        emit('join', props.challenge.id)
    } finally {
        joining.value = false
    }
}

const getCategoryIcon = (category) => {
    const icons = {
        'fitness': '💪',
        'mindfulness': '🧘',
        'productivity': '⚡',
        'learning': '📚',
        'health': '🏥',
        'creativity': '🎨',
        'social': '👥',
        'lifestyle': '🌟'
    }
    return icons[category] || '🎯'
}

const formatCategory = (category) => {
    const categoryMap = {
        'fitness': 'Fitness',
        'mindfulness': 'Mindfulness',
        'productivity': 'Produtividade',
        'learning': 'Aprendizado',
        'health': 'Saúde',
        'creativity': 'Criatividade',
        'social': 'Social',
        'lifestyle': 'Estilo de Vida'
    }
    return categoryMap[category] || category
}

const getDifficultyIcon = (difficulty) => {
    const icons = {
        'beginner': '🟢',
        'intermediate': '🟡',
        'advanced': '🔴'
    }
    return icons[difficulty] || '⚪'
}

const formatDifficulty = (difficulty) => {
    const difficultyMap = {
        'beginner': 'Iniciante',
        'intermediate': 'Intermediário',
        'advanced': 'Avançado'
    }
    return difficultyMap[difficulty] || difficulty
}

const getDifficultyClasses = (difficulty) => {
    const classes = {
        'beginner': 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
        'intermediate': 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
        'advanced': 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'
    }
    return classes[difficulty] || 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800'
}

const formatNumber = (num) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k'
    }
    return num?.toString() || '0'
}

const getCompletionRate = (challenge) => {
    // Mock completion rate - in real app, this would come from API
    return Math.floor(Math.random() * 40) + 60 // 60-100%
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: 'short'
    })
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.hover-scale {
    transition: transform 0.2s ease;
}

.hover-scale:hover {
    transform: translateY(-2px);
}
</style>