<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <DopaHeaderWrapper 
            :subtitle="`${challenge.title} - Participantes`" 
            max-width="7xl" 
            :show-back-button="true" 
            back-link="/challenges" 
        />

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Participants List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200">
                        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-white to-gray-50">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
                                        <span class="mr-2">👥</span>
                                        Todos os Participantes
                                    </h2>
                                    <p class="text-gray-600 mt-1 text-sm sm:text-base font-medium">Veja quem está participando deste desafio</p>
                                </div>
                                <div class="text-right bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl px-4 py-3 border border-blue-200 shadow-sm self-start sm:self-auto">
                                    <div class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">{{ stats.total_participants }}</div>
                                    <div class="text-xs text-gray-600 font-medium">Participantes</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div v-if="participants.data.length === 0" class="text-center py-12">
                                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum participante ainda</h3>
                                <p class="text-gray-500">Seja o primeiro a participar deste desafio!</p>
                            </div>

                            <div v-else class="space-y-3">
                                <div v-for="participant in participants.data" :key="participant.id" 
                                    class="flex items-center justify-between p-5 bg-gradient-to-r from-white to-gray-50 rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-center space-x-4 flex-1">
                                        <div class="relative">
                                            <img :src="participant.user.profile_photo_url || '/default-avatar.png'" 
                                                :alt="participant.user.name"
                                                class="w-14 h-14 rounded-full border-2 border-white shadow-md object-cover">
                                            <div v-if="participant.status === 'active'" 
                                                class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                                            <div v-else-if="participant.status === 'completed'" 
                                                class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 text-lg">{{ participant.user.name }}</h3>
                                            <p class="text-sm text-gray-500">@{{ participant.user.username }}</p>
                                            <div class="flex items-center space-x-3 mt-2">
                                                <span class="text-xs text-gray-600">
                                                    {{ parseInt(participant.current_day) || 1 }} / {{ challenge.duration_days }} dias
                                                </span>
                                                <span v-if="participant.streak_days" class="text-xs text-orange-600 font-medium flex items-center">
                                                    🔥 {{ participant.streak_days }} dias
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4">
                                        <div class="text-right">
                                            <div class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                                {{ Math.round(parseFloat(participant.progress_percentage || 0)) }}%
                                            </div>
                                            <div class="text-xs text-gray-500 font-medium">Progresso</div>
                                            <div class="mt-2 w-20 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-blue-500 to-purple-600 rounded-full transition-all duration-300" 
                                                    :style="{ width: Math.min(100, parseFloat(participant.progress_percentage || 0)) + '%' }"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <span v-if="participant.status === 'completed'" 
                                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200">
                                                ✅ Concluído
                                            </span>
                                            <span v-else-if="participant.status === 'active'" 
                                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-800 border border-blue-200">
                                                🔄 Ativo
                                            </span>
                                            <span v-else 
                                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                                ⏸️ Pausado
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div v-if="participants.data.length > 0" class="mt-8">
                                <Pagination :links="participants.links" :current-page="participants.current_page"
                                    :last-page="participants.last_page" @page-changed="handlePageChange" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Challenge Info -->
                    <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl p-6 shadow-lg border border-blue-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="mr-2 text-xl">{{ getCategoryIcon(challenge.category) }}</span>
                            Sobre o Desafio
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-blue-100">
                                <h4 class="font-semibold text-gray-900 mb-2">{{ challenge.title }}</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">{{ challenge.description }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 pt-4">
                                <div class="text-center bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-4 border border-blue-200">
                                    <div class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ stats.active_participants }}</div>
                                    <div class="text-xs font-medium text-gray-600 mt-1">Ativos</div>
                                </div>
                                <div class="text-center bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-200">
                                    <div class="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">{{ stats.completed_participants }}</div>
                                    <div class="text-xs font-medium text-gray-600 mt-1">Concluídos</div>
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t border-blue-200">
                                <div class="flex justify-between items-center text-sm mb-2">
                                    <span class="text-gray-700 font-medium">Taxa de conclusão</span>
                                    <span class="font-bold text-gray-900">{{ stats.completion_rate }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                                    <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 h-3 rounded-full transition-all duration-500 shadow-sm" 
                                        :style="{ width: Math.min(100, stats.completion_rate) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Challenge Details -->
                    <div class="bg-gradient-to-br from-white to-purple-50 rounded-2xl p-6 shadow-lg border border-purple-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="mr-2">📋</span>
                            Detalhes
                        </h3>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center bg-white/60 backdrop-blur-sm rounded-lg p-3 border border-purple-100">
                                <span class="text-gray-600 font-medium">Duração</span>
                                <span class="text-gray-900 font-bold">{{ challenge.duration_days }} dias</span>
                            </div>
                            <div class="flex justify-between items-center bg-white/60 backdrop-blur-sm rounded-lg p-3 border border-purple-100">
                                <span class="text-gray-600 font-medium">Dificuldade</span>
                                <span :class="getDifficultyTextClasses(challenge.difficulty)" class="font-bold">
                                    {{ formatDifficulty(challenge.difficulty) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center bg-white/60 backdrop-blur-sm rounded-lg p-3 border border-purple-100">
                                <span class="text-gray-600 font-medium">Categoria</span>
                                <span class="text-gray-900 font-bold">{{ formatCategory(challenge.category) }}</span>
                            </div>
                            <div class="flex justify-between items-center bg-white/60 backdrop-blur-sm rounded-lg p-3 border border-purple-100">
                                <span class="text-gray-600 font-medium">Criado por</span>
                                <span class="text-gray-900 font-bold">{{ challenge.creator?.name || 'Anônimo' }}</span>
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
import DopaHeaderWrapper from '@/components/DopaHeaderWrapper.vue'
import Pagination from '@/components/Pagination.vue'

// Props
const props = defineProps({
    challenge: {
        type: Object,
        required: true
    },
    participants: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        required: true
    }
})

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
        'fitness': '💪 Fitness',
        'mindfulness': '🧘 Mindfulness',
        'productivity': '⚡ Produtividade',
        'learning': '📚 Aprendizado',
        'health': '🏥 Saúde',
        'creativity': '🎨 Criatividade',
        'social': '👥 Social',
        'lifestyle': '🌟 Estilo de Vida'
    }
    return categoryMap[category] || category
}


const getDifficultyTextClasses = (difficulty) => {
    const classes = {
        'beginner': 'text-green-600 font-medium',
        'intermediate': 'text-yellow-600 font-medium',
        'advanced': 'text-red-600 font-medium'
    }
    return classes[difficulty] || 'text-gray-600 font-medium'
}

const formatDifficulty = (difficulty) => {
    const difficultyMap = {
        'beginner': '🟢 Iniciante',
        'intermediate': '🟡 Intermediário',
        'advanced': '🔴 Avançado'
    }
    return difficultyMap[difficulty] || difficulty
}


const handlePageChange = (page) => {
    router.get(route('challenges.participants', { challenge: props.challenge.id }), { page }, {
        preserveState: true,
        preserveScroll: true
    })
}
</script> 