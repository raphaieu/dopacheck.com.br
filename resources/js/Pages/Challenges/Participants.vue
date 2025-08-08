<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4">
                        <Link href="/challenges" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">{{ challenge.title }}</h1>
                            <p class="text-sm text-gray-500">Participantes</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900">{{ stats.total_participants }}</div>
                            <div class="text-sm text-gray-500">Participantes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Participants List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="p-6 border-b border-gray-100">
                            <h2 class="text-2xl font-bold text-gray-900">Todos os Participantes</h2>
                            <p class="text-gray-600 mt-1">Veja quem está participando deste desafio</p>
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

                            <div v-else class="space-y-4">
                                <div v-for="participant in participants.data" :key="participant.id" 
                                    class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center space-x-4">
                                        <img :src="participant.user.profile_photo_url || '/default-avatar.png'" 
                                            :alt="participant.user.name"
                                            class="w-12 h-12 rounded-full">
                                        <div>
                                            <h3 class="font-medium text-gray-900">{{ participant.user.name }}</h3>
                                            <p class="text-sm text-gray-500">@{{ participant.user.username }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4">
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ parseInt(participant.current_day) || 1 }} / {{ challenge.duration_days }} dias
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ parseFloat(participant.completion_rate || 0) }}% completo
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <span v-if="participant.status === 'completed'" 
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ✅ Concluído
                                            </span>
                                            <span v-else-if="participant.status === 'active'" 
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                🔄 Ativo
                                            </span>
                                            <span v-else 
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
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
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sobre o Desafio</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-purple-100 rounded-lg flex items-center justify-center">
                                    <span class="text-lg">{{ getCategoryIcon(challenge.category) }}</span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ challenge.title }}</h4>
                                    <p class="text-sm text-gray-500">{{ challenge.description }}</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ stats.active_participants }}</div>
                                    <div class="text-xs text-gray-500">Ativos</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ stats.completed_participants }}</div>
                                    <div class="text-xs text-gray-500">Concluídos</div>
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t border-gray-100">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Taxa de conclusão</span>
                                    <span class="font-medium text-gray-900">{{ stats.completion_rate }}%</span>
                                </div>
                                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full" 
                                        :style="{ width: stats.completion_rate + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Challenge Details -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes</h3>

                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Duração</span>
                                <span class="text-gray-900 font-medium">{{ challenge.duration_days }} dias</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Dificuldade</span>
                                <span :class="getDifficultyTextClasses(challenge.difficulty)">
                                    {{ formatDifficulty(challenge.difficulty) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Categoria</span>
                                <span class="text-gray-900 font-medium">{{ formatCategory(challenge.category) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Criado por</span>
                                <span class="text-gray-900 font-medium">{{ challenge.creator?.name || 'Anônimo' }}</span>
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