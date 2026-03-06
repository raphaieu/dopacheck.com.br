<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 pb-12 overflow-x-clip pt-24">
        <!-- Header Wrapper -->
        <DopaHeaderWrapper 
            max-width="6xl" 
            :show-back-button="true" 
            back-link="/challenges" 
        />

        <!-- Hero Section -->
        <div class="relative overflow-hidden pt-4 pb-12">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl"></div>
            
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-xs font-bold tracking-wider uppercase mb-4 shadow-sm">
                            <Icon icon="lucide:users" class="size-3.5" />
                            Comunidade
                        </div>
                        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            Participantes do <br>
                            <span class="bg-gradient-to-r from-blue-600 via-violet-600 to-purple-600 bg-clip-text text-transparent">
                                {{ challenge.title }}
                            </span>
                        </h1>
                        <p class="mt-4 text-lg text-slate-600 leading-relaxed max-w-xl">
                            Acompanhe a evolução de quem aceitou o desafio e está construindo consistência dia após dia.
                        </p>
                    </div>

                    <div class="flex gap-4">
                        <div class="bg-white/70 backdrop-blur-md rounded-2xl p-5 shadow-xl shadow-blue-500/5 border border-white/80 min-w-[140px] text-center transform transition hover:scale-105 duration-300">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Participantes</p>
                            <p class="text-3xl font-black text-slate-900">{{ stats.total_participants }}</p>
                        </div>
                        <div class="bg-white/70 backdrop-blur-md rounded-2xl p-5 shadow-xl shadow-purple-500/5 border border-white/80 min-w-[140px] text-center transform transition hover:scale-105 duration-300">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Concluídos</p>
                            <p class="text-3xl font-black text-violet-600">{{ stats.completed_participants }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-8">
                <!-- Participants List -->
                <div class="lg:col-span-8">
                    <div v-if="participants.data.length === 0" 
                        class="bg-white/40 backdrop-blur-sm rounded-3xl border border-white/80 p-12 text-center shadow-xl shadow-slate-200/50">
                        <div class="w-20 h-20 mx-auto mb-6 bg-slate-100 rounded-2xl flex items-center justify-center text-3xl">
                            🎯
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Ainda sem participantes</h3>
                        <p class="text-slate-600">Seja o primeiro a começar este desafio e inspire outros!</p>
                        <Link href="/challenges" class="mt-8 inline-flex items-center text-violet-600 font-bold hover:gap-2 transition-all">
                            Explorar mais desafios <Icon icon="lucide:arrow-right" class="ml-1 size-5" />
                        </Link>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="participant in participants.data" :key="participant.id" 
                            class="group relative bg-white/70 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/80 shadow-sm hover:shadow-xl hover:shadow-blue-500/10 hover:-translate-y-1 transition-all duration-300">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="relative shrink-0">
                                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-400 to-violet-400 rounded-full blur opacity-20 group-hover:opacity-40 transition-opacity"></div>
                                        <img :src="participant.user.profile_photo_url || '/default-avatar.png'" 
                                            :alt="participant.user.display_name"
                                            class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-full border-2 border-white shadow-sm object-cover">
                                        <div v-if="participant.status === 'active'" 
                                            class="absolute bottom-0 right-0 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white shadow-sm"></div>
                                        <div v-else-if="participant.status === 'completed'" 
                                            class="absolute bottom-0 right-0 w-4 h-4 bg-violet-500 rounded-full border-2 border-white shadow-sm flex items-center justify-center">
                                            <Icon icon="lucide:check" class="size-2.5 text-white stroke-[4px]" />
                                        </div>
                                    </div>
                                    
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-slate-900 text-base sm:text-lg truncate tracking-tight group-hover:text-blue-600 transition-colors">
                                            {{ participant.user.display_name }}
                                        </h3>
                                        <div class="flex items-center gap-2 text-xs text-slate-500">
                                            <span class="font-medium">@{{ participant.user.username }}</span>
                                            <span v-if="participant.streak_days" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-orange-50 text-orange-600 font-bold">
                                                <Icon icon="lucide:flame" class="size-3" /> {{ participant.streak_days }}
                                            </span>
                                        </div>
                                        <div class="mt-2 text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest">
                                            Dia {{ parseInt(participant.current_day) || 1 }} de {{ challenge.duration_days }}
                                        </div>
                                    </div>
                                </div>

                                <div class="shrink-0 flex flex-col items-end gap-1">
                                    <div class="text-2xl sm:text-3xl font-black tabular-nums text-slate-900">
                                        {{ Math.round(parseFloat(participant.progress_percentage || 0)) }}<span class="text-sm font-bold text-slate-400">%</span>
                                    </div>
                                    <div class="w-16 sm:w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-blue-500 to-violet-600 transition-all duration-1000" 
                                            :style="{ width: Math.min(100, parseFloat(participant.progress_percentage || 0)) + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="participants.data.length > 0" class="pt-6">
                            <Pagination :links="participants.links" :current-page="participants.current_page"
                                :last-page="participants.last_page" @page-changed="handlePageChange" />
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- About Challenge -->
                    <div class="bg-white/70 backdrop-blur-md rounded-3xl p-6 sm:p-8 border border-white/80 shadow-xl shadow-slate-200/50 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5">
                            <Icon :icon="getCategoryIconSlug(challenge.category)" class="size-32" />
                        </div>
                        
                        <div class="relative z-10">
                            <h3 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                                    <Icon icon="lucide:info" class="size-5" />
                                </span>
                                Detalhes
                            </h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Sobre o desafio</p>
                                    <p class="text-slate-600 leading-relaxed">{{ challenge.description }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Dificuldade</p>
                                        <p :class="getDifficultyTextClasses(challenge.difficulty)" class="text-sm font-bold truncate">
                                            {{ formatDifficulty(challenge.difficulty) }}
                                        </p>
                                    </div>
                                    <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Duração</p>
                                        <p class="text-sm font-bold text-slate-900">{{ challenge.duration_days }} dias</p>
                                    </div>
                                </div>

                                <div class="pt-6 border-t border-slate-100">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-sm font-bold text-slate-900">Taxa de conclusão</span>
                                        <span class="text-sm font-black text-violet-600">{{ stats.completion_rate }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="bg-gradient-to-r from-blue-500 via-violet-500 to-purple-500 h-full rounded-full transition-all duration-1000" 
                                            :style="{ width: Math.min(100, stats.completion_rate) + '%' }"></div>
                                    </div>
                                    <p class="mt-3 text-[11px] text-slate-500 leading-relaxed font-medium">
                                        Baseado no desempenho histórico de todos os participantes que já finalizaram.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Creator info -->
                    <div class="bg-slate-900 rounded-3xl p-6 shadow-2xl shadow-slate-900/20 relative overflow-hidden">
                        <div class="absolute -top-8 -left-8 w-24 h-24 bg-violet-600/20 rounded-full blur-2xl"></div>
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white">
                                <Icon icon="lucide:user-plus" class="size-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Criado por</p>
                                <p class="font-bold text-white text-lg">{{ challenge.creator?.name || 'Administrador' }}</p>
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
import { computed } from 'vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import { Icon } from '@iconify/vue'

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

useSeoMetaTags({
    title: computed(() => props.challenge?.title ? `Participantes - ${props.challenge.title}` : 'Participantes'),
})

// Avatar initials fallback (like hero mock in Welcome.vue)
const getInitials = (name) => {
    return name
        ?.split(' ')
        .map(n => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase() || 'U'
}

// Icon slugs mapping
const getCategoryIconSlug = (category) => {
    const icons = {
        'fitness': 'lucide:dumbbell',
        'mindfulness': 'lucide:brain',
        'productivity': 'lucide:zap',
        'learning': 'lucide:book-open',
        'health': 'lucide:heart-pulse',
        'creativity': 'lucide:palette',
        'social': 'lucide:users',
        'lifestyle': 'lucide:sparkles'
    }
    return icons[category] || 'lucide:target'
}

const formatDifficulty = (difficulty) => {
    const difficultyMap = {
        'beginner': 'Iniciante',
        'intermediate': 'Intermediário',
        'advanced': 'Avançado'
    }
    return difficultyMap[difficulty] || difficulty
}

const getDifficultyTextClasses = (difficulty) => {
    const classes = {
        'beginner': 'text-emerald-600',
        'intermediate': 'text-amber-600',
        'advanced': 'text-rose-600'
    }
    return classes[difficulty] || 'text-slate-600'
}

const handlePageChange = (page) => {
    router.get(route('challenges.participants', { challenge: props.challenge.id }), { page }, {
        preserveState: true,
        preserveScroll: true
    })
}
</script>

<style scoped>
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.8);
}
</style>
 