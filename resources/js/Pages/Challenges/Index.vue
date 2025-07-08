<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <!-- Header -->
        <header class="bg-white/90 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <Link href="/dopa" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
                            <span class="text-white font-bold text-lg">🧠</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">DOPA Check</h1>
                            <p class="text-sm text-gray-500">Desafios</p>
                        </div>
                        </Link>
                    </div>

                    <div class="flex items-center space-x-4">
                        <Link href="/challenges/create"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Criar Desafio</span>
                        </Link>

                        <Link href="/dopa" class="text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 pt-8">
            <!-- Hero Section - Featured Challenges -->
            <section v-if="featuredChallenges.length > 0" class="mb-12 hidden">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Desafios em Destaque</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Desafios populares escolhidos pela nossa comunidade para transformar sua rotina
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <FeaturedChallengeCard v-for="challenge in featuredChallenges" :key="`featured-${challenge.id}`"
                        :challenge="challenge" @view="handleViewChallenge" @join="handleJoinChallenge" />
                </div>
            </section>

            <!-- Filters & Search -->
            <section class="mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <!-- Search -->
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input v-model="searchQuery" type="text" placeholder="Buscar desafios..."
                                    class="dopa-input w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500"
                                    @input="handleSearch">
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="flex flex-wrap gap-3">
                            <!-- Category Filter -->
                            <select v-model="selectedCategory" @change="handleFilter"
                                class="dopa-select px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900">
                                <option value="" class="text-gray-500">Todas as categorias</option>
                                <option v-for="category in categories" :key="category" :value="category"
                                    class="text-gray-900">
                                    {{ formatCategory(category) }}
                                </option>
                            </select>

                            <!-- Difficulty Filter -->
                            <select v-model="selectedDifficulty" @change="handleFilter"
                                class="dopa-select px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900">
                                <option value="" class="text-gray-500">Todas as dificuldades</option>
                                <option value="beginner" class="text-gray-900">🟢 Iniciante</option>
                                <option value="intermediate" class="text-gray-900">🟡 Intermediário</option>
                                <option value="advanced" class="text-gray-900">🔴 Avançado</option>
                            </select>

                            <!-- Sort -->
                            <select v-model="selectedSort" @change="handleFilter"
                                class="dopa-select px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900">
                                <option value="popular" class="text-gray-900">Mais populares</option>
                                <option value="newest" class="text-gray-900">Mais recentes</option>
                                <option value="featured" class="text-gray-900">Em destaque</option>
                            </select>

                            <!-- Show Private Challenges -->
                            <label v-if="user" class="flex items-center space-x-2 px-3 py-2 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 cursor-pointer transition-colors">
                                <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors" 
                                     :class="showPrivateChallenges ? 'bg-blue-600' : 'bg-gray-200'">
                                    <input v-model="showPrivateChallenges" type="checkbox" @change="handleFilter"
                                        class="sr-only">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                          :class="showPrivateChallenges ? 'translate-x-6' : 'translate-x-1'"></span>
                                </div>
                                <span class="text-sm text-gray-700 font-medium">Incluir Privados</span>
                            </label>
                        </div>
                    </div>

                    <!-- Active Filters -->
                    <div v-if="hasActiveFilters" class="mt-4 flex flex-wrap gap-2">
                        <span class="text-sm text-gray-600">Filtros ativos:</span>

                        <button v-if="selectedCategory" @click="clearFilter('category')"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                            {{ formatCategory(selectedCategory) }}
                            <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <button v-if="selectedDifficulty" @click="clearFilter('difficulty')"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                            {{ formatDifficulty(selectedDifficulty) }}
                            <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <button v-if="showPrivateChallenges" @click="clearFilter('private')"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 hover:bg-purple-200 transition-colors">
                            🔒 Desafio Privado
                            <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <button @click="clearAllFilters" class="text-xs text-gray-500 hover:text-gray-700 underline">
                            Limpar todos
                        </button>
                    </div>
                </div>
            </section>

            <!-- Challenges Grid -->
            <section>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">
                        Todos os Desafios
                        <span class="text-lg font-normal text-gray-500 ml-2">
                            ({{ challenges.total }} encontrados)
                        </span>
                    </h3>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="i in 6" :key="`skeleton-${i}`" class="animate-pulse">
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                            <div class="h-3 bg-gray-200 rounded w-full mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-2/3 mb-4"></div>
                            <div class="flex space-x-2 mb-4">
                                <div class="h-6 bg-gray-200 rounded-full w-16"></div>
                                <div class="h-6 bg-gray-200 rounded-full w-20"></div>
                            </div>
                            <div class="h-10 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="challenges.data.length === 0" class="text-center py-12">
                    <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Nenhum desafio encontrado</h3>
                    <p class="text-gray-600 mb-6">
                        Tente ajustar os filtros ou criar um novo desafio
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button @click="clearAllFilters"
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                            Limpar Filtros
                        </button>
                        <Link href="/challenges/create"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        Criar Desafio
                        </Link>
                    </div>
                </div>

                <!-- Challenges List -->
                <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ChallengeCard v-for="challenge in challenges.data" :key="`challenge-${challenge.id}`"
                        :challenge="challenge" @join="handleJoinChallenge" @view="handleViewChallenge" />
                </div>

                <!-- Pagination -->
                <div v-if="challenges.last_page > 1" class="mt-8">
                    <Pagination
                    :links="challenges.links"
                    :current-page="challenges.current_page"
                    :last-page="challenges.last_page"
                    :total="challenges.total"
                    :from="challenges.from"
                    :to="challenges.to"
                    @page-changed="handlePageChange" />
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import ChallengeCard from '@/components/ChallengeCard.vue'
import FeaturedChallengeCard from '@/components/FeaturedChallengeCard.vue'
import Pagination from '@/components/Pagination.vue'
import { useChallenges } from '@/composables/useChallenges'

// Props
const props = defineProps({
    challenges: {
        type: Object,
        required: true
    },
    featuredChallenges: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    auth: {
        type: Object,
        default: () => null
    }
})
console.log(props.challenges);
const user = computed(() => props.auth.user)
  
// Composables
const { loading, joinChallenge } = useChallenges()

// State
const searchQuery = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category || '')
const selectedDifficulty = ref(props.filters.difficulty || '')
const selectedSort = ref(props.filters.sort || 'newest')
const showPrivateChallenges = ref(props.filters.show_private !== undefined ? props.filters.show_private : true)
const searchTimeout = ref(null)

// Computed
const hasActiveFilters = computed(() => {
    return selectedCategory.value || selectedDifficulty.value || searchQuery.value || showPrivateChallenges.value
})

// Methods
const handleSearch = () => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        handleFilter()
    }, 500)
}

const handleFilter = () => {
    const params = {
        search: searchQuery.value || undefined,
        category: selectedCategory.value || undefined,
        difficulty: selectedDifficulty.value || undefined,
        sort: selectedSort.value || undefined,
        show_private: showPrivateChallenges.value || undefined,
    }

    Object.keys(params).forEach(key => {
        if (params[key] === undefined) {
            delete params[key]
        }
    })

    router.get('/challenges', params, {
        preserveState: true,
        preserveScroll: true
    })
}

const handlePageChange = (page) => {
    const params = {
        page,
        search: searchQuery.value || undefined,
        category: selectedCategory.value || undefined,
        difficulty: selectedDifficulty.value || undefined,
        sort: selectedSort.value || undefined,
        show_private: showPrivateChallenges.value || undefined,
    }

    router.get('/challenges', params, {
        preserveState: true
    })
}

const clearFilter = (filterType) => {
    switch (filterType) {
        case 'category':
            selectedCategory.value = ''
            break
        case 'difficulty':
            selectedDifficulty.value = ''
            break
        case 'search':
            searchQuery.value = ''
            break
        case 'private':
            showPrivateChallenges.value = true
            break
    }
    handleFilter()
}

const clearAllFilters = () => {
    searchQuery.value = ''
    selectedCategory.value = ''
    selectedDifficulty.value = ''
    selectedSort.value = 'newest'
    showPrivateChallenges.value = true
    handleFilter()
}

const handleJoinChallenge = async (challengeId) => {
    await joinChallenge(challengeId)
}

const handleViewChallenge = (challengeId) => {
    router.visit(`/challenges/${challengeId}`)
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

const formatDifficulty = (difficulty) => {
    const difficultyMap = {
        'beginner': '🟢 Iniciante',
        'intermediate': '🟡 Intermediário',
        'advanced': '🔴 Avançado'
    }
    return difficultyMap[difficulty] || difficulty
}

// Watch for route changes to update filters
watch(() => props.filters, (newFilters) => {
    searchQuery.value = newFilters.search || ''
    selectedCategory.value = newFilters.category || ''
    selectedDifficulty.value = newFilters.difficulty || ''
    selectedSort.value = newFilters.sort || 'newest'
    showPrivateChallenges.value = newFilters.show_private !== undefined ? newFilters.show_private : true
}, { immediate: true })
</script>

<style scoped>
/* Force input/select styles */
.dopa-input {
    background-color: white !important;
    color: #111827 !important;
}

.dopa-input::placeholder {
    color: #6b7280 !important;
}

.dopa-select {
    background-color: white !important;
    color: #111827 !important;
}

.dopa-select option {
    background-color: white !important;
    color: #111827 !important;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Smooth transitions */
.transition-all {
    transition: all 0.3s ease;
}

/* Hover effects */
.hover-scale {
    transition: transform 0.2s ease;
}

.hover-scale:hover {
    transform: scale(1.02);
}
</style>