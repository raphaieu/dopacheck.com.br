<template>
    <div class="min-h-screen bg-linear-to-br from-blue-50 via-white to-purple-50 relative overflow-x-clip pt-28">
        <!-- Decorative blur elements -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/5 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -left-24 w-96 h-96 bg-purple-400/5 rounded-full blur-3xl"></div>
        
        <!-- Header -->
        <DopaHeaderWrapper subtitle="Explorar" max-width="7xl" />

        <main class="max-w-7xl mx-auto px-4 pb-24 relative z-10">
            <!-- Hero Header -->
            <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight mb-4">
                        Desafios da Comunidade
                    </h2>
                    <p class="text-base sm:text-lg text-slate-600 font-medium leading-relaxed">
                        Encontre o desafio perfeito para transformar sua rotina e junte-se a milhares de pessoas em busca de progresso constante.
                    </p>
                </div>
                
                <div v-if="user" class="shrink-0">
                    <Link href="/challenges/create"
                        class="group bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-slate-800 transition-all hover:scale-105 active:scale-95 shadow-xl shadow-slate-900/10 flex items-center gap-3">
                        <Icon icon="lucide:plus" class="size-5 group-hover:rotate-90 transition-transform duration-300" />
                        <span>Criar Desafio</span>
                    </Link>
                </div>
            </div>

            <!-- Filters & Search -->
            <section class="mb-12">
                <div class="relative group">
                    <!-- Accent Gradient Border -->
                    <div class="absolute -inset-[1px] bg-gradient-to-r from-blue-600/20 via-violet-600/20 to-purple-600/20 rounded-[2.5rem] blur-sm opacity-50 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="relative bg-white/90 backdrop-blur-2xl rounded-[2.5rem] p-6 sm:p-8 shadow-2xl shadow-blue-500/10 border border-white/80 overflow-hidden">
                        <!-- Top Accent Line -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-600 via-violet-600 to-purple-600 opacity-80"></div>

                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                            <!-- Search -->
                            <div class="flex-1 max-w-full lg:max-w-md">
                                <div class="relative group/search">
                                    <Icon icon="lucide:search" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 size-5 group-focus-within/search:text-blue-600 group-focus-within/search:scale-110 transition-all duration-300" />
                                    <input v-model="searchQuery" type="text" placeholder="Buscar desafios..."
                                        class="w-full pl-12 pr-4 py-4 bg-slate-50/80 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-bold text-slate-900 placeholder-slate-400"
                                        @input="handleSearch">
                                </div>
                            </div>

                            <!-- Filters -->
                            <div class="flex flex-wrap items-center gap-3">
                                <!-- Category Filter -->
                                <div class="relative flex-1 sm:flex-initial min-w-[160px]">
                                    <Icon icon="lucide:layout-grid" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 size-4 pointer-events-none transition-colors group-focus-within:text-blue-600" />
                                    <select v-model="selectedCategory" @change="handleFilter"
                                        class="w-full pl-10 pr-10 py-3.5 bg-slate-50/80 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-black uppercase tracking-widest text-slate-700 text-[10px] appearance-none cursor-pointer">
                                        <option value="">Categorias</option>
                                        <option v-for="category in categories" :key="category" :value="category">
                                            {{ formatCategoryLabel(category) }}
                                        </option>
                                    </select>
                                    <Icon icon="lucide:chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 size-4 pointer-events-none" />
                                </div>

                                <!-- Difficulty Filter -->
                                <div class="relative flex-1 sm:flex-initial min-w-[160px]">
                                    <Icon icon="lucide:bar-chart" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 size-4 pointer-events-none transition-colors group-focus-within:text-emerald-600" />
                                    <select v-model="selectedDifficulty" @change="handleFilter"
                                        class="w-full pl-10 pr-10 py-3.5 bg-slate-50/80 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-black uppercase tracking-widest text-slate-700 text-[10px] appearance-none cursor-pointer">
                                        <option value="">Dificuldade</option>
                                        <option value="beginner">Iniciante</option>
                                        <option value="intermediate">Intermediário</option>
                                        <option value="advanced">Avançado</option>
                                    </select>
                                    <Icon icon="lucide:chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 size-4 pointer-events-none" />
                                </div>

                                <!-- Sort -->
                                <div class="relative flex-1 sm:flex-initial min-w-[160px]">
                                    <Icon icon="lucide:arrow-up-down" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 size-4 pointer-events-none" />
                                    <select v-model="selectedSort" @change="handleFilter"
                                        class="w-full pl-10 pr-10 py-3.5 bg-slate-50/80 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-black uppercase tracking-widest text-slate-700 text-[10px] appearance-none cursor-pointer">
                                        <option value="newest">Mais recentes</option>
                                        <option value="popular">Mais populares</option>
                                        <option value="featured">Destaques</option>
                                    </select>
                                    <Icon icon="lucide:chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 size-4 pointer-events-none" />
                                </div>
                            </div>
                        </div>

                        <!-- Toggle Private & Active Filters -->
                        <div class="mt-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6 pt-6 border-t border-slate-100">
                            <div v-if="hasActiveFilters" class="flex flex-wrap items-center gap-3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mr-1">Filtrado por:</span>

                                <button v-if="selectedCategory" @click="clearFilter('category')"
                                    class="inline-flex items-center px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-tighter bg-blue-600 text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all gap-2 cursor-pointer active:scale-95">
                                    <Icon :icon="getCategoryIconSlug(selectedCategory)" class="size-3.5" />
                                    {{ formatCategory(selectedCategory) }}
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>

                                <button v-if="selectedDifficulty" @click="clearFilter('difficulty')"
                                    class="inline-flex items-center px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-tighter bg-emerald-600 text-white shadow-lg shadow-emerald-500/20 hover:bg-emerald-700 transition-all gap-2 cursor-pointer active:scale-95">
                                    <Icon icon="lucide:bar-chart-3" class="size-3.5" />
                                    {{ formatDifficulty(selectedDifficulty) }}
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>

                                <button v-if="user && showPrivateChallenges" @click="clearFilter('private')"
                                    class="inline-flex items-center px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-tighter bg-purple-600 text-white shadow-lg shadow-purple-500/20 hover:bg-purple-700 transition-all gap-2 cursor-pointer active:scale-95">
                                    <Icon icon="lucide:lock" class="size-3.5" />
                                    Privados
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>

                                <button v-if="searchQuery" @click="clearFilter('search')"
                                    class="inline-flex items-center px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-tighter bg-slate-900 text-white shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all gap-2 cursor-pointer active:scale-95">
                                    "{{ searchQuery }}"
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>

                                <button @click="clearAllFilters" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 underline underline-offset-4 decoration-slate-200 hover:decoration-slate-900 transition-all cursor-pointer">
                                    Limpar tudo
                                </button>
                            </div>
                            <div v-else></div>

                            <label v-if="user" class="relative inline-flex items-center cursor-pointer group px-5 py-3 rounded-2xl bg-white border border-slate-100 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-500/5 transition-all">
                                <input v-model="showPrivateChallenges" type="checkbox" @change="handleFilter" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[15px] after:left-[23px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ml-3 text-[11px] font-black uppercase tracking-widest text-slate-600 group-hover:text-slate-900">Incluir Desafios Privados</span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Challenges Grid -->
            <section>
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div class="size-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                            <Icon icon="lucide:list" class="size-6" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                            Resultados
                            <span class="text-sm font-bold text-slate-400 ml-2 uppercase tracking-widest">
                                {{ challenges.total }} encontrados
                            </span>
                        </h3>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="i in 6" :key="`skeleton-${i}`" class="animate-pulse bg-white/50 rounded-[2.5rem] p-8 border border-white/80 h-80">
                        <div class="flex justify-between mb-6">
                            <div class="h-8 bg-slate-200 rounded-xl w-3/4"></div>
                            <div class="size-16 bg-slate-200 rounded-2xl"></div>
                        </div>
                        <div class="space-y-3 mb-8">
                            <div class="h-4 bg-slate-200 rounded-lg w-full"></div>
                            <div class="h-4 bg-slate-200 rounded-lg w-5/6"></div>
                        </div>
                        <div class="h-12 bg-slate-200 rounded-2xl w-full mt-auto"></div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="challenges.data.length === 0" class="bg-white/70 backdrop-blur-xl rounded-[2.5rem] p-12 sm:p-24 text-center border border-white/80 shadow-xl shadow-slate-200/50">
                    <div class="w-32 h-32 mx-auto mb-8 bg-slate-50 rounded-[2.5rem] flex items-center justify-center relative">
                        <Icon icon="lucide:search-x" class="size-14 text-slate-300" />
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-rose-500 rounded-full flex items-center justify-center text-white border-4 border-white">
                            <Icon icon="lucide:alert-circle" class="size-4" />
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Nenhum desafio encontrado</h3>
                    <p class="text-slate-500 font-medium mb-10 max-w-sm mx-auto">
                        Não encontramos resultados para sua busca atual. Tente ajustar os filtros ou crie algo novo!
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button @click="clearAllFilters"
                            class="px-8 py-4 border-2 border-slate-100 text-slate-700 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-slate-50 hover:border-slate-200 transition-all active:scale-95 cursor-pointer">
                            Limpar filtros
                        </button>
                        <Link href="/challenges/create"
                            class="px-8 py-4 bg-blue-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-600/20 active:scale-95">
                            Criar Novo Desafio
                        </Link>
                    </div>
                </div>

                <!-- Challenges List -->
                <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <ChallengeCard v-for="challenge in challenges.data" :key="`challenge-${challenge.id}`"
                        :challenge="challenge" @join="handleJoinChallenge" @view="handleViewChallenge" />
                </div>

                <!-- Pagination -->
                <div v-if="challenges.last_page > 1" class="mt-16">
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
import { Icon } from '@iconify/vue'
import DopaHeaderWrapper from '@/components/DopaHeaderWrapper.vue'
import ChallengeCard from '@/components/ChallengeCard.vue'
import Pagination from '@/components/Pagination.vue'
import { useChallenges } from '@/composables/useChallenges'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'

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

const user = computed(() => props.auth.user)

useSeoMetaTags({
    title: 'Explorar Desafios',
    description: 'Encontre desafios incríveis para transformar sua rotina.'
})
  
// Composables
const { loading, joinChallenge } = useChallenges()

// State
const searchQuery = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category || '')
const selectedDifficulty = ref(props.filters.difficulty || '')
const selectedSort = ref(props.filters.sort || 'newest')
const showPrivateChallenges = ref(user.value ? (props.filters.show_private !== undefined ? (props.filters.show_private === 'true' || props.filters.show_private === true) : true) : false)
const searchTimeout = ref(null)

// Computed
const hasActiveFilters = computed(() => {
    return selectedCategory.value || selectedDifficulty.value || searchQuery.value || (user.value && showPrivateChallenges.value)
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
        show_private: user.value ? showPrivateChallenges.value : undefined,
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
        show_private: user.value ? showPrivateChallenges.value : undefined,
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
            showPrivateChallenges.value = false
            break
    }
    handleFilter()
}

const clearAllFilters = () => {
    searchQuery.value = ''
    selectedCategory.value = ''
    selectedDifficulty.value = ''
    selectedSort.value = 'newest'
    showPrivateChallenges.value = !!user.value
    handleFilter()
}

const handleJoinChallenge = async (challengeId) => {
    await joinChallenge(challengeId)
}

const handleViewChallenge = (challengeId) => {
    router.visit(`/challenges/${challengeId}`)
}

const getCategoryIconSlug = (category) => {
    const icons = {
        'fitness': 'lucide:dumbbell',
        'mindfulness': 'lucide:brain-circuit',
        'productivity': 'lucide:zap',
        'learning': 'lucide:book-open',
        'health': 'lucide:heart-pulse',
        'creativity': 'lucide:palette',
        'social': 'lucide:users-2',
        'lifestyle': 'lucide:sparkles'
    }
    return icons[category] || 'lucide:target'
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

const formatCategoryLabel = (category) => {
    const labels = {
        'fitness': '💪 Fitness',
        'mindfulness': '🧘 Mindfulness',
        'productivity': '⚡ Produtividade',
        'learning': '📚 Aprendizado',
        'health': '🏥 Saúde',
        'creativity': '🎨 Criatividade',
        'social': '👥 Social',
        'lifestyle': '🌟 Estilo de Vida'
    }
    return labels[category] || category
}

const formatDifficulty = (difficulty) => {
    const difficultyMap = {
        'beginner': 'Iniciante',
        'intermediate': 'Intermediário',
        'advanced': 'Avançado'
    }
    return difficultyMap[difficulty] || difficulty
}

// Watch for route changes to update filters
watch(() => props.filters, (newFilters) => {
    searchQuery.value = newFilters.search || ''
    selectedCategory.value = newFilters.category || ''
    selectedDifficulty.value = newFilters.difficulty || ''
    selectedSort.value = newFilters.sort || 'newest'
    showPrivateChallenges.value = user.value ? (newFilters.show_private !== undefined ? (newFilters.show_private === 'true' || newFilters.show_private === true) : true) : false
}, { immediate: true })
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}
</style>