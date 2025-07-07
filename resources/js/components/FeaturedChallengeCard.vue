<template>
    <div
        class="relative bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-500 group">
        <!-- Featured Badge -->
        <div class="absolute top-4 left-4 z-10 flex flex-col space-y-2">
            <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-md">
                ⭐ Destaque
            </span>

            <!-- Private Badge -->
            <span v-if="!challenge.is_public"
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-600 text-white shadow-md">
                🔒 Privado
            </span>
        </div>

        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 opacity-60"></div>

        <!-- Content -->
        <div class="relative p-8">
            <!-- Icon/Image -->
            <div class="flex justify-center mb-6">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <span class="text-4xl">{{ getCategoryIcon(challenge.category) }}</span>
                </div>
            </div>

            <!-- Challenge Info -->
            <div class="text-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors">
                    {{ challenge.title }}
                </h3>
                <p class="text-gray-600 leading-relaxed line-clamp-3">
                    {{ challenge.description }}
                </p>
            </div>

            <!-- Meta Info -->
            <div class="flex justify-center space-x-6 mb-6 text-sm">
                <div class="flex items-center space-x-1 text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ challenge.duration_days }} dias</span>
                </div>

                <div class="flex items-center space-x-1 text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>{{ formatNumber(challenge.participant_count) }} pessoas</span>
                </div>

                <div class="flex items-center space-x-1">
                    <span :class="getDifficultyClasses(challenge.difficulty)">
                        {{ getDifficultyIcon(challenge.difficulty) }}
                    </span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="text-center p-3 bg-white/70 rounded-xl backdrop-blur-sm">
                    <div class="text-xl font-bold text-blue-600">{{ challenge.tasks?.length || 0 }}</div>
                    <div class="text-xs text-gray-600">Tasks</div>
                </div>
                <div class="text-center p-3 bg-white/70 rounded-xl backdrop-blur-sm">
                    <div class="text-xl font-bold text-green-600">{{ getCompletionRate(challenge) }}%</div>
                    <div class="text-xs text-gray-600">Sucesso</div>
                </div>
                <div class="text-center p-3 bg-white/70 rounded-xl backdrop-blur-sm">
                    <div class="text-xl font-bold text-purple-600">{{ getTrendingScore(challenge) }}</div>
                    <div class="text-xs text-gray-600">Trending</div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex space-x-3">
                <button @click="$emit('view', challenge.id)"
                    class="cursor-pointer flex-1 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                    Ver Detalhes
                </button>

                <button v-if="!isParticipating" @click="handleJoin" :disabled="joining"
                    class="cursor-pointer flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 disabled:opacity-50 transform hover:scale-105 transition-all duration-200 shadow-lg flex items-center justify-center space-x-2">
                    <svg v-if="joining" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ joining ? 'Entrando...' : 'Participar Agora' }}</span>
                </button>

                <div v-else
                    class="flex-1 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-semibold flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Participando</span>
                </div>
            </div>

            <!-- Creator Info -->
            <div v-if="challenge.creator" class="flex items-center justify-center space-x-2 mt-6 text-sm text-gray-500">
                <img :src="challenge.creator.profile_photo_url || '/default-avatar.png'" :alt="challenge.creator.name"
                    class="w-6 h-6 rounded-full">
                <span>Criado por {{ challenge.creator.name }}</span>
            </div>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-blue-200/30 to-transparent rounded-bl-full">
        </div>
        <div
            class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-purple-200/30 to-transparent rounded-tr-full">
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

const getDifficultyIcon = (difficulty) => {
    const icons = {
        'beginner': '🟢',
        'intermediate': '🟡',
        'advanced': '🔴'
    }
    return icons[difficulty] || '⚪'
}

const getDifficultyClasses = (difficulty) => {
    const classes = {
        'beginner': 'text-green-600',
        'intermediate': 'text-yellow-600',
        'advanced': 'text-red-600'
    }
    return classes[difficulty] || 'text-gray-600'
}

const formatNumber = (num) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k'
    }
    return num?.toString() || '0'
}

const getCompletionRate = (challenge) => {
    // Mock completion rate - in real app, this would come from API
    return Math.floor(Math.random() * 20) + 75 // 75-95% (featured challenges have higher success)
}

const getTrendingScore = (challenge) => {
    // Mock trending score - in real app, this would come from API
    const scores = ['🔥', '🚀', '⭐', '💎', '🌟']
    return scores[Math.floor(Math.random() * scores.length)]
}
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom gradient animations */
@keyframes gradient-shift {
    0% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}

.group:hover .animated-gradient {
    background-size: 200% 200%;
    animation: gradient-shift 3s ease infinite;
}
</style>