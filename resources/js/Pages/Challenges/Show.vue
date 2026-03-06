<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 pb-12 overflow-x-clip pt-24">
        <!-- Header -->
        <DopaHeaderWrapper max-width="6xl" :show-back-button="true" back-link="/challenges" />

        <!-- Hero Section -->
        <div class="relative overflow-hidden pt-4 pb-12">
            <!-- Decorative blur elements -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl"></div>
            
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid lg:grid-cols-12 gap-12 items-center">
                    <!-- Challenge Info -->
                    <div class="lg:col-span-7">
                        <div v-if="challenge.is_featured" class="mb-6">
                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-bold bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-lg shadow-orange-500/20 animate-float">
                                <Icon icon="lucide:star" class="size-4 fill-current" />
                                Desafio em Destaque
                            </span>
                        </div>
                        
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold tracking-wider uppercase mb-4 shadow-sm">
                            <Icon :icon="getCategoryIconSlug(challenge.category)" class="size-3.5" />
                            {{ formatCategory(challenge.category) }}
                        </div>

                        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight mb-6">
                            {{ challenge.title }}
                        </h1>
                        
                        <p class="text-lg text-slate-600 leading-relaxed mb-8 max-w-2xl">
                            {{ challenge.description }}
                        </p>

                        <!-- Quick Meta -->
                        <div class="flex flex-wrap gap-4 items-center">
                            <div class="flex items-center gap-2 bg-white/60 backdrop-blur-md px-4 py-2 rounded-2xl border border-white/80 shadow-sm">
                                <Icon icon="lucide:trending-up" :class="getDifficultyTextClasses(challenge.difficulty)" class="size-4" />
                                <span class="text-sm font-bold text-slate-700">{{ formatDifficulty(challenge.difficulty) }}</span>
                            </div>
                            <div class="flex items-center gap-2 bg-white/60 backdrop-blur-md px-4 py-2 rounded-2xl border border-white/80 shadow-sm">
                                <Icon icon="lucide:calendar" class="size-4 text-violet-600" />
                                <span class="text-sm font-bold text-slate-700">{{ challenge.duration_days }} dias</span>
                            </div>
                            <div v-if="challenge.creator" class="flex items-center gap-2 px-2 py-1 rounded-2xl hover:bg-white/40 transition-colors">
                                <img :src="challenge.creator.profile_photo_url || '/default-avatar.png'"
                                    :alt="challenge.creator.name" class="w-7 h-7 rounded-full border border-white shadow-sm">
                                <span class="text-sm text-slate-500 leading-tight">
                                    Por <span class="font-bold text-slate-700">{{ challenge.creator.name }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Image / Icon -->
                    <div class="lg:col-span-5 flex justify-center">
                        <div class="relative group">
                            <div class="absolute -inset-4 bg-gradient-to-r from-blue-500 to-purple-600 rounded-[2.5rem] opacity-20 blur-2xl group-hover:opacity-30 transition-opacity"></div>
                            
                            <div v-if="challenge.image_url" class="relative w-full max-w-md aspect-video rounded-[2rem] overflow-hidden shadow-2xl border-4 border-white">
                                <img :src="challenge.image_url" :alt="challenge.title" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700" />
                            </div>
                            <div v-else class="relative w-48 h-48 sm:w-64 sm:h-64 bg-gradient-to-br from-blue-600 to-purple-700 rounded-[2.5rem] flex items-center justify-center shadow-2xl overflow-hidden">
                                <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                                <Icon :icon="getCategoryIconSlug(challenge.category)" class="size-24 sm:size-32 text-white/90 drop-shadow-xl animate-float" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-20">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-white/80 shadow-xl shadow-slate-200/50 flex flex-col items-center text-center transform transition hover:scale-105 duration-300">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                        <Icon icon="lucide:users" class="size-5" />
                    </div>
                    <div class="text-2xl font-black text-slate-900 leading-none">{{ formatNumber(stats.total_participants) }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Participantes</div>
                </div>

                <div class="bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-white/80 shadow-xl shadow-slate-200/50 flex flex-col items-center text-center transform transition hover:scale-105 duration-300">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                        <Icon icon="lucide:check-circle" class="size-5" />
                    </div>
                    <div class="text-2xl font-black text-slate-900 leading-none">{{ stats.completion_rate }}%</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Taxa de Sucesso</div>
                </div>

                <div class="bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-white/80 shadow-xl shadow-slate-200/50 flex flex-col items-center text-center transform transition hover:scale-105 duration-300">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center mb-3">
                        <Icon icon="lucide:list-todo" class="size-5" />
                    </div>
                    <div class="text-2xl font-black text-slate-900 leading-none">{{ challenge.tasks?.length || 0 }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Tasks Diárias</div>
                </div>

                <div class="bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-white/80 shadow-xl shadow-slate-200/50 flex flex-col items-center text-center transform transition hover:scale-105 duration-300">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mb-3">
                        <Icon icon="lucide:flame" class="size-5" />
                    </div>
                    <div class="text-2xl font-black text-slate-900 leading-none">{{ stats.active_participants }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Ativos Agora</div>
                </div>
            </div>
        </div>

        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid lg:grid-cols-12 gap-12">
                <!-- Main Content -->
                <div class="lg:col-span-8 space-y-12">
                    <!-- Tasks Section -->
                    <section>
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-lg shadow-slate-900/10">
                                    <Icon icon="lucide:check-square" class="size-5" />
                                </span>
                                Objetivos do Desafio
                            </h2>
                            <span class="px-3 py-1 bg-white/50 backdrop-blur-sm border border-slate-200 rounded-full text-xs font-bold text-slate-500">
                                {{ challenge.tasks?.length || 0 }} etapas
                            </span>
                        </div>

                        <div v-if="challenge.tasks?.length === 0" class="bg-white/50 backdrop-blur-sm rounded-3xl border border-dashed border-slate-300 p-12 text-center">
                            <Icon icon="lucide:clipboard-list" class="size-12 text-slate-300 mx-auto mb-4" />
                            <p class="text-slate-500 font-medium">Nenhuma task definida para este desafio</p>
                        </div>

                        <div v-else class="space-y-4">
                            <TaskPreview v-for="(task, index) in challenge.tasks" :key="`task-${task.id}`" :task="task"
                                :index="index" :is-participating="isParticipating" />
                        </div>
                    </section>

                    <!-- Recent Participants -->
                    <section v-if="recentParticipants.length > 0">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-white shadow-lg shadow-violet-600/10">
                                    <Icon icon="lucide:users" class="size-5" />
                                </span>
                                Participantes
                            </h2>
                            <Link :href="`/challenges/${challenge.id}/participants`" 
                                class="text-sm font-bold text-violet-600 hover:text-violet-700 flex items-center gap-1">
                                Ver todos <Icon icon="lucide:chevron-right" class="size-4" />
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <ParticipantCard v-for="participant in recentParticipants"
                                :key="`participant-${participant.id}`" :participant="participant" class="glass-card hover:shadow-xl transition-all" />
                        </div>
                    </section>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-4 space-y-8">
                    <!-- Action Card -->
                    <div class="sticky top-24 space-y-8">
                        <div class="bg-slate-900 rounded-[2.5rem] p-8 shadow-2xl shadow-slate-900/30 text-white relative overflow-hidden group">
                            <div class="absolute -top-12 -right-12 w-32 h-32 bg-violet-600/20 rounded-full blur-3xl transition-all group-hover:bg-violet-600/30"></div>
                            <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-blue-600/20 rounded-full blur-3xl transition-all group-hover:bg-blue-600/30"></div>
                            
                            <div class="relative z-10">
                                <!-- Not Authenticated -->
                                <div v-if="!isAuthenticated" class="text-center">
                                    <div class="w-16 h-16 mx-auto mb-6 bg-white/10 rounded-2xl flex items-center justify-center">
                                        <Icon icon="lucide:log-in" class="size-8" />
                                    </div>
                                    <h3 class="text-xl font-black mb-3">Junte-se ao Desafio</h3>
                                    <p class="text-slate-400 text-sm mb-8">Faça login para começar sua jornada de consistência hoje mesmo.</p>

                                    <div class="space-y-4">
                                        <Link href="/login" class="block w-full bg-white text-slate-900 py-4 rounded-2xl font-black text-center hover:scale-[1.02] active:scale-[0.98] transition-all">
                                            Entrar
                                        </Link>
                                        <Link href="/register" class="block w-full bg-white/10 text-white py-4 rounded-2xl font-black text-center hover:bg-white/20 transition-all border border-white/10">
                                            Criar Conta
                                        </Link>
                                    </div>
                                </div>

                                <!-- Expired -->
                                <div v-else-if="!isParticipating && challenge.is_expired" class="text-center">
                                    <div class="w-16 h-16 mx-auto mb-6 bg-rose-500/20 rounded-2xl flex items-center justify-center">
                                        <Icon icon="lucide:timer-off" class="size-8 text-rose-400" />
                                    </div>
                                    <h3 class="text-xl font-black mb-3">Desafio Encerrado</h3>
                                    <p class="text-slate-400 text-sm mb-6">Este desafio já terminou e não aceita novos participantes.</p>
                                    <div class="py-4 px-6 bg-white/5 rounded-2xl border border-white/5 text-slate-500 font-bold">
                                        Inscrições Fechadas
                                    </div>
                                </div>

                                <!-- Team Challenge (Not Member) -->
                                <div v-else-if="!isAuthenticated && challenge.visibility === 'team' && challenge.team?.slug" class="text-center">
                                    <div class="w-16 h-16 mx-auto mb-6 bg-indigo-500/20 rounded-2xl flex items-center justify-center">
                                        <Icon icon="lucide:users-2" class="size-8 text-indigo-400" />
                                    </div>
                                    <h3 class="text-xl font-black mb-3">Grupo Exclusivo</h3>
                                    <p class="text-slate-400 text-sm mb-8">
                                        Este desafio pertence ao grupo <span class="text-white font-bold">{{ challenge.team.name }}</span>.
                                    </p>
                                    <Link :href="`/join/${challenge.team.slug}`" class="block w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-4 rounded-2xl font-black text-center shadow-lg shadow-blue-500/25 hover:scale-[1.02] transition-all">
                                        Acessar Grupo
                                    </Link>
                                </div>

                                <!-- Ready to Join -->
                                <div v-else-if="!isParticipating && canJoin" class="text-center">
                                    <div class="w-16 h-16 mx-auto mb-6 bg-blue-500/20 rounded-2xl flex items-center justify-center text-blue-400">
                                        <Icon icon="lucide:rocket" class="size-8" />
                                    </div>
                                    <h3 class="text-xl font-black mb-3">Vamos Começar?</h3>
                                    <p class="text-slate-400 text-sm mb-8">
                                        Junte-se a {{ stats.total_participants }} pessoas agora mesmo.
                                    </p>

                                    <button @click="handleJoinChallenge" :disabled="joining"
                                        class="cursor-pointer w-full bg-gradient-to-r from-blue-500 via-violet-600 to-purple-600 text-white py-4 rounded-2xl font-black text-center shadow-xl shadow-blue-600/20 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 flex items-center justify-center gap-3">
                                        <Icon v-if="joining" icon="lucide:loader" class="size-6 animate-spin" />
                                        <span>{{ joining ? 'Processando...' : 'Participar Agora' }}</span>
                                    </button>
                                </div>

                                <!-- Already Participating -->
                                <div v-else-if="isParticipating" class="text-center">
                                    <div class="w-16 h-16 mx-auto mb-6 bg-emerald-500/20 rounded-2xl flex items-center justify-center">
                                        <Icon icon="lucide:party-popper" class="size-8 text-emerald-400" />
                                    </div>
                                    <h3 class="text-xl font-black mb-2">Você está dentro!</h3>
                                    <p class="text-slate-400 text-sm mb-8">Seu foco gera consistência.</p>

                                    <div v-if="userChallenge" class="bg-white/5 rounded-2xl p-5 mb-8 border border-white/5 text-left">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Seu Progresso</span>
                                            <span class="text-xs font-black text-emerald-400">Dia {{ parseInt(userChallenge.current_day) || 1 }}</span>
                                        </div>
                                        <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden mb-2">
                                            <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full transition-all duration-1000" 
                                                :style="{ width: Math.round(userChallenge.progress_percentage || userChallenge.completion_rate || 0) + '%' }"></div>
                                        </div>
                                        <div class="text-right text-[10px] font-bold text-slate-500 tabular-nums">
                                            {{ Math.round(userChallenge.progress_percentage || userChallenge.completion_rate || 0) }}%
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <Link href="/dopa" class="block w-full bg-white text-slate-900 py-4 rounded-2xl font-black text-center hover:scale-[1.02] shadow-lg shadow-white/10 transition-all">
                                            Entrar no Painel
                                        </Link>
                                        <button @click="handleLeaveChallenge" class="cursor-pointer w-full text-rose-400 hover:text-rose-300 font-bold text-sm transition-colors">
                                            Abandonar desafio
                                        </button>
                                    </div>
                                </div>

                                <!-- Limit Reached -->
                                <div v-else class="text-center">
                                    <div class="w-16 h-16 mx-auto mb-6 bg-amber-500/20 rounded-2xl flex items-center justify-center">
                                        <Icon icon="lucide:shield-alert" class="size-8 text-amber-400" />
                                    </div>
                                    <h3 class="text-xl font-black mb-3">Limite Atingido</h3>
                                    <p class="text-slate-400 text-sm mb-8">Libere mais desafios com o plano PRO.</p>

                                    <Link href="/subscriptions/create" class="block w-full bg-amber-500 text-slate-900 py-4 rounded-2xl font-black text-center shadow-lg shadow-amber-500/20 hover:scale-[1.02] transition-all">
                                        Seja PRO
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Details Card -->
                        <div class="bg-white/70 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/80 shadow-xl shadow-slate-200/50">
                            <h3 class="text-xl font-extrabold text-slate-900 mb-8 flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-900">
                                    <Icon icon="lucide:settings-2" class="size-5" />
                                </span>
                                Visão Geral
                            </h3>

                            <div class="space-y-6">
                                <div class="flex justify-between items-center group">
                                    <span class="text-sm font-bold text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition-colors">Duração</span>
                                    <span class="text-sm font-black text-slate-900">{{ challenge.duration_days }} dias</span>
                                </div>
                                <div class="flex justify-between items-center group">
                                    <span class="text-sm font-bold text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition-colors">Dificuldade</span>
                                    <span :class="getDifficultyTextClasses(challenge.difficulty)" class="text-sm font-black">
                                        {{ formatDifficulty(challenge.difficulty) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center group">
                                    <span class="text-sm font-bold text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition-colors">Visibilidade</span>
                                    <span class="px-3 py-1 bg-slate-900 text-white rounded-lg text-[10px] font-black uppercase tracking-tighter">
                                        {{ challenge?.visibility === 'private' ? 'Protegido' : (challenge?.visibility === 'team' ? 'Grupo' : 'Aberto') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center group">
                                    <span class="text-sm font-bold text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition-colors">Criado em</span>
                                    <span class="text-sm font-black text-slate-900">{{ formatDate(challenge.created_at) }}</span>
                                </div>
                            </div>

                            <div v-if="canEdit" class="mt-10 pt-8 border-t border-slate-100">
                                <Link :href="`/challenges/${challenge.id}/edit`" class="flex items-center justify-center gap-2 w-full py-4 bg-slate-100 text-slate-700 font-black rounded-2xl hover:bg-slate-200 transition-all">
                                    <Icon icon="lucide:edit-3" class="size-4" />
                                    Editar Desafio
                                </Link>
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
import { Link, router, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import DopaHeaderWrapper from '@/components/DopaHeaderWrapper.vue'
import TaskPreview from '@/components/TaskPreview.vue'
import ParticipantCard from '@/components/ParticipantCard.vue'
import { useChallenges } from '@/composables/useChallenges'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'

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
const canEdit = computed(() => {
    const page = usePage()
    const userId = page?.props?.auth?.user?.id
    // Só o criador pode editar e apenas desafios privados (públicos/grupo não podem ser editados)
    return !!userId && Number(props.challenge?.created_by) === Number(userId) && props.challenge?.visibility === 'private'
})

const origin = typeof window !== 'undefined' ? window.location.origin : 'https://dopacheck.com.br'
const ogImageUrl = computed(() =>
    props.challenge?.image_url ? props.challenge.image_url : `${origin}/images/og.png`
)

useSeoMetaTags({
    title: computed(() => props.challenge?.title ? props.challenge.title : 'Desafio'),
    description: computed(() => props.challenge?.description ? props.challenge.description : undefined),

    ogTitle: computed(() => props.challenge?.title ? `${props.challenge.title} | DOPA Check` : 'DOPA Check'),
    ogDescription: computed(() => props.challenge?.description ? props.challenge.description : undefined),
    ogUrl: computed(() => typeof window !== 'undefined' ? window.location.href : undefined),
    ogType: 'website',
    ogImage: ogImageUrl,

    twitterTitle: computed(() => props.challenge?.title ? `${props.challenge.title} | DOPA Check` : 'DOPA Check'),
    twitterDescription: computed(() => props.challenge?.description ? props.challenge.description : undefined),
    twitterCard: 'summary_large_image',
    twitterImage: ogImageUrl,
})

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
        'beginner': 'text-emerald-500',
        'intermediate': 'text-amber-500',
        'advanced': 'text-rose-500'
    }
    return classes[difficulty] || 'text-slate-500'
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
    background-image: radial-gradient(circle, #000 1px, transparent 1px);
    background-size: 20px 20px;
}

.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.8);
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}
</style>