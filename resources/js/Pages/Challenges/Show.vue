<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <!-- Header -->
        <DopaHeaderWrapper :subtitle="challenge.title" max-width="7xl" :show-back-button="true" back-link="/challenges" />

        <main class="max-w-6xl mx-auto px-4 py-8">
            <!-- Challenge Hero -->
            <section class="mb-8">
                <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100">
                    <!-- Background Pattern -->
                    <div class="relative bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 p-8">
                        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>

                        <div class="relative">
                            <!-- Featured Badge -->
                            <div v-if="challenge.is_featured" class="mb-4">
                                <span
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-md">
                                    ⭐ Desafio em Destaque
                                </span>
                            </div>

                            <div class="grid lg:grid-cols-3 gap-8 items-center">
                                <!-- Challenge Info -->
                                <div class="lg:col-span-2">
                                    <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ challenge.title }}</h1>
                                    <p class="text-lg text-gray-700 leading-relaxed mb-6">{{ challenge.description }}</p>

                                    <!-- Meta Badges -->
                                    <div class="flex flex-wrap gap-3 mb-6">
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            {{ getCategoryIcon(challenge.category) }} {{ formatCategory(challenge.category) }}
                                        </span>
                                        <span :class="getDifficultyClasses(challenge.difficulty)">
                                            {{ getDifficultyIcon(challenge.difficulty) }} {{ formatDifficulty(challenge.difficulty) }}
                                        </span>
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                                            📅 {{ challenge.duration_days }} dias
                                        </span>
                                    </div>

                                    <!-- Creator Info -->
                                    <div v-if="challenge.creator" class="flex items-center space-x-3 text-gray-600">
                                        <img :src="challenge.creator.profile_photo_url || '/default-avatar.png'"
                                            :alt="challenge.creator.name" class="w-8 h-8 rounded-full">
                                        <span class="text-sm">
                                            Criado por <span class="font-medium">{{ challenge.creator.name }}</span>
                                            • {{ formatDate(challenge.created_at) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Challenge Icon/Image -->
                                <div class="lg:col-span-1 flex justify-center lg:justify-end">
                                    <div
                                        class="w-32 h-32 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl flex items-center justify-center shadow-xl">
                                        <span class="text-6xl">{{ getCategoryIcon(challenge.category) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Bar -->
                    <div class="bg-white p-6 border-t border-gray-100">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">{{ formatNumber(stats.total_participants)
                                }}</div>
                                <div class="text-sm text-gray-500">Participantes</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600">{{ stats.completion_rate }}%</div>
                                <div class="text-sm text-gray-500">Taxa de Sucesso</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600">{{ challenge.tasks?.length || 0 }}</div>
                                <div class="text-sm text-gray-500">Tasks Diárias</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-orange-600">{{ stats.active_participants }}</div>
                                <div class="text-sm text-gray-500">Ativos Agora</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Tasks Section -->
                    <section class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Tasks Diárias</h2>

                        <div class="space-y-4">
                            <TaskPreview v-for="(task, index) in challenge.tasks" :key="`task-${task.id}`" :task="task"
                                :index="index" :is-participating="isParticipating" />
                        </div>

                        <div v-if="challenge.tasks?.length === 0" class="text-center py-8 text-gray-500">
                            <div
                                class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <p>Nenhuma task definida para este desafio</p>
                        </div>
                    </section>

                    <!-- Recent Participants -->
                    <section v-if="recentParticipants.length > 0"
                        class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Participantes Recentes</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <ParticipantCard v-for="participant in recentParticipants"
                                :key="`participant-${participant.id}`" :participant="participant" />
                        </div>

                        <div class="mt-6 text-center">
                            <Link :href="`/challenges/${challenge.id}/participants`" 
                                class="cursor-pointer text-blue-600 hover:text-blue-700 font-medium text-sm">
                                Ver todos os participantes
                            </Link>
                        </div>
                    </section>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Action Card -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">
                        <div v-if="!isAuthenticated" class="text-center">
                            <div
                                class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Faça login para participar</h3>
                            <p class="text-gray-600 text-sm mb-4">Entre na sua conta para se juntar a este desafio</p>

                            <div class="space-y-3">
                                <Link href="/login"
                                    class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors block text-center">
                                Fazer Login
                                </Link>
                                <Link href="/register"
                                    class="w-full border border-gray-300 text-gray-700 px-4 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors block text-center">
                                Criar Conta
                                </Link>
                            </div>
                        </div>

                        <div v-else-if="!isParticipating && canJoin" class="text-center">
                            <div
                                class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center">
                                <span class="text-2xl">🎯</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Pronto para o desafio?</h3>
                            <p class="text-gray-600 text-sm mb-4">Junte-se a {{ stats.total_participants }} pessoas que
                                já estão transformando seus hábitos</p>

                            <button @click="handleJoinChallenge" :disabled="joining"
                                class="cursor-pointer w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 disabled:opacity-50 transition-all duration-200 flex items-center justify-center space-x-2">
                                <svg v-if="joining" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>{{ joining ? 'Entrando...' : 'Participar do Desafio' }}</span>
                            </button>

                            <p class="text-xs text-gray-500 mt-3">
                                Você será redirecionado para o dashboard para começar
                            </p>
                        </div>

                        <div v-else-if="isParticipating" class="text-center">
                            <div
                                class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-green-800 mb-2">Você está participando!</h3>
                            <p class="text-green-700 text-sm mb-4">Continue sua jornada no dashboard</p>

                            <!-- User Challenge Stats -->
                            <div v-if="userChallenge" class="bg-green-50 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div>
                                        <div class="text-lg font-bold text-green-600">{{ parseInt(userChallenge.current_day) || 1
                                        }}</div>
                                        <div class="text-xs text-green-600">Dia Atual</div>
                                    </div>
                                    <div>
                                        <div class="text-lg font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">{{
                                            Math.round(userChallenge.progress_percentage || userChallenge.completion_rate || 0) }}%</div>
                                        <div class="text-xs text-green-600">Progresso</div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Link href="/dopa"
                                    class="w-full bg-green-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-green-700 transition-colors block text-center">
                                Ir para Dashboard
                                </Link>
                                <button @click="handleLeaveChallenge"
                                    class="cursor-pointer w-full text-red-600 hover:text-red-700 font-medium text-sm">
                                    Sair do desafio
                                </button>
                            </div>
                        </div>

                        <div v-else class="text-center">
                            <div
                                class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Limite de desafios atingido</h3>
                            <p class="text-gray-600 text-sm mb-4">Você já tem o máximo de desafios ativos. Upgrade para
                                PRO para desafios ilimitados.</p>

                            <Link href="/subscriptions/create"
                                class="w-full bg-purple-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-purple-700 transition-colors block text-center">
                            Upgrade para PRO
                            </Link>
                        </div>
                    </div>

                    <!-- Challenge Details -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes do Desafio</h3>

                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Duração</span>
                                <span class="text-gray-600 font-medium">{{ challenge.duration_days }} dias</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Dificuldade</span>
                                <span :class="getDifficultyTextClasses(challenge.difficulty)">
                                    {{ getDifficultyIcon(challenge.difficulty) }} {{
                                        formatDifficulty(challenge.difficulty) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Categoria</span>
                                <span class="text-gray-600 font-medium">{{ formatCategory(challenge.category) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Criado em</span>
                                <span class="text-gray-600 font-medium">{{ formatDate(challenge.created_at) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Visibilidade</span>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                    🌍 Público
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DopaHeaderWrapper from '@/components/DopaHeaderWrapper.vue'
import TaskPreview from '@/components/TaskPreview.vue'
import ParticipantCard from '@/components/ParticipantCard.vue'
import { useChallenges } from '@/composables/useChallenges'

// Props
const props = defineProps({
    challenge: {
        type: Object,
        required: true
    },
    userChallenge: {
        type: Object,
        default: null
    },
    canJoin: {
        type: Boolean,
        default: false
    },
    stats: {
        type: Object,
        required: true
    },
    recentParticipants: {
        type: Array,
        default: () => []
    },
    isAuthenticated: {
        type: Boolean,
        default: false
    }
})

// Composables
const { loading, joinChallenge, leaveChallenge } = useChallenges()

// State
const joining = ref(false)

// Computed
const isParticipating = computed(() => !!props.userChallenge)

// Methods
const handleJoinChallenge = async () => {
    if (joining.value) return

    joining.value = true
    try {
        await joinChallenge(props.challenge.id)
    } finally {
        joining.value = false
    }
}

const handleLeaveChallenge = async () => {
    if (!confirm('Tem certeza que deseja sair deste desafio? Seu progresso será perdido.')) return

    await leaveChallenge(props.challenge.id)
}

// Helper methods
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
        'beginner': 'inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800',
        'intermediate': 'inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800',
        'advanced': 'inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800'
    }
    return classes[difficulty] || 'inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800'
}

const getDifficultyTextClasses = (difficulty) => {
    const classes = {
        'beginner': 'font-medium text-green-600',
        'intermediate': 'font-medium text-yellow-600',
        'advanced': 'font-medium text-red-600'
    }
    return classes[difficulty] || 'font-medium text-gray-600'
}

const formatNumber = (num) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k'
    }
    return num?.toString() || '0'
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}
</script>

<style scoped>
.bg-grid-pattern {
    background-image: radial-gradient(circle, #e5e7eb 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>