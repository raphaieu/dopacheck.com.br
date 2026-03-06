<template>
    <div
        class="group relative bg-white/70 backdrop-blur-xl rounded-[2.5rem] p-7 border border-white/80 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:shadow-blue-500/10 hover:-translate-y-2 transition-all duration-500 overflow-hidden">
        
        <!-- Hover highlight -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-400/10 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

        <!-- Challenge Header -->
        <div class="flex items-start justify-between mb-6 relative z-10">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100/50">
                        <Icon :icon="getCategoryIconSlug(challenge.category)" class="mr-1.5 size-3" />
                        {{ formatCategory(challenge.category) }}
                    </span>
                    <span :class="getDifficultyClasses(challenge.difficulty)">
                        {{ formatDifficulty(challenge.difficulty) }}
                    </span>
                </div>

                <h3 class="text-xl font-extrabold text-slate-900 mb-2 leading-tight tracking-tight line-clamp-2 group-hover:text-blue-600 transition-colors">
                    {{ challenge.title }}
                </h3>
            </div>

            <!-- Challenge Image/Icon -->
            <div class="shrink-0 ml-4 group-hover:scale-110 transition-transform duration-500">
                <div v-if="challenge.image_url" class="size-16 rounded-2xl overflow-hidden border-2 border-white shadow-md">
                    <img :src="challenge.image_url" :alt="challenge.title" class="w-full h-full object-cover">
                </div>
                <div v-else
                    class="size-16 rounded-2xl flex items-center justify-center text-3xl shadow-inner shadow-slate-900/5"
                    :style="`background-color: ${getCategoryColor(challenge.category)}15; color: ${getCategoryColor(challenge.category)}`">
                    <Icon :icon="getCategoryIconSlug(challenge.category)" class="size-8" />
                </div>
            </div>
        </div>

        <!-- Description -->
        <p class="text-slate-500 text-sm font-medium line-clamp-2 mb-6 leading-relaxed relative z-10">
            {{ challenge.description }}
        </p>

        <!-- Stats Grid -->
        <div class="grid grid-cols-3 gap-3 mb-6 p-4 bg-slate-50/50 rounded-2xl border border-slate-100/50 relative z-10">
            <div class="text-center">
                <div class="text-base font-black text-slate-900">{{ formatNumber(challenge.participant_count) }}</div>
                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Participantes</div>
            </div>
            <div class="text-center border-x border-slate-200/50 px-2">
                <div class="text-base font-black text-slate-900">{{ challenge.tasks?.length || 0 }}</div>
                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Tarefas</div>
            </div>
            <div class="text-center">
                <div class="text-base font-black text-blue-600">{{ getCompletionRate(challenge) }}%</div>
                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Concluído</div>
            </div>
        </div>

        <!-- Tags & Meta -->
        <div class="flex flex-wrap items-center gap-2 mb-6 relative z-10">
            <!-- Period Status -->
            <div
              v-if="challenge.is_expired"
              class="flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200"
            >
              <Icon icon="lucide:calendar-off" class="size-3" />
              Encerrado
            </div>
            <div
              v-else-if="challenge.is_future"
              class="flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-600 border border-amber-100/50"
            >
              <Icon icon="lucide:calendar" class="size-3" />
              Em breve
            </div>
            <div
              v-else-if="challenge.is_active"
              class="flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-600 border border-emerald-100/50"
            >
              <Icon icon="lucide:check-circle" class="size-3" />
              Ativo
            </div>

            <!-- Duration -->
            <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-slate-50 text-slate-600 border border-slate-100">
                <Icon icon="lucide:timer" class="size-3" />
                {{ challenge.duration_days }}d
            </div>

            <!-- Visibility -->
            <div
              v-if="challenge.visibility === 'private'"
              class="flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-purple-50 text-purple-600 border border-purple-100/50"
            >
              <Icon icon="lucide:lock" class="size-3" />
              Privado
            </div>
        </div>

        <!-- Creator Info -->
        <div v-if="challenge.creator" class="flex items-center justify-between mb-6 relative z-10">
            <div class="flex items-center gap-2">
                <img :src="challenge.creator.profile_photo_url || '/default-avatar.png'" :alt="challenge.creator.name"
                    class="size-6 rounded-full ring-2 ring-white shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 tracking-tight">Por <span class="text-slate-600">{{ challenge.creator.name }}</span></span>
            </div>
            <span class="text-[10px] font-bold text-slate-300">{{ formatDate(challenge.created_at) }}</span>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 relative z-10">
            <button @click="$emit('view', challenge.id)"
                class="cursor-pointer flex-1 bg-slate-50 text-slate-700 px-4 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-100 transition-all border border-slate-100 active:scale-95">
                Detalhes
            </button>

            <!-- Visitante + desafio de time: link para landing do grupo -->
            <Link
                v-if="!user && challenge.visibility === 'team' && challenge.team?.slug && !challenge.is_expired"
                :href="`/join/${challenge.team.slug}`"
                class="cursor-pointer flex-1 bg-slate-900 text-white px-4 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/10 text-center active:scale-95"
            >
                Participar
            </Link>

            <button v-else-if="!isParticipating" @click="handleJoin" :disabled="joining"
                class="cursor-pointer flex-1 px-4 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest disabled:opacity-50 transition-all flex items-center justify-center gap-2 shadow-lg active:scale-95"
                :class="challenge.is_expired ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none' : 'bg-blue-600 text-white hover:bg-blue-700 shadow-blue-500/20'"
            >
                <Icon v-if="joining" icon="lucide:loader-2" class="size-4 animate-spin" />
                <span>{{ challenge.is_expired ? 'Finalizado' : (joining ? 'Entrando...' : 'Participar') }}</span>
            </button>

            <div v-else
                class="flex-1 bg-emerald-500 text-white px-4 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20">
                <Icon icon="lucide:check" class="size-4" />
                <span>Membro</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

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
    return props.challenge.user_is_participating || false
})

// Methods
const handleJoin = async () => {
    if (joining.value) return
    if (props.challenge?.is_expired) return

    joining.value = true
    try {
        emit('join', props.challenge.id)
    } finally {
        joining.value = false
    }
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

const getCategoryColor = (category) => {
    const colors = {
        'fitness': '#3b82f6', // blue
        'mindfulness': '#8b5cf6', // violet
        'productivity': '#f59e0b', // amber
        'learning': '#10b981', // emerald
        'health': '#ef4444', // red
        'creativity': '#ec4899', // pink
        'social': '#6366f1', // indigo
        'lifestyle': '#f97316' // orange
    }
    return colors[category] || '#3b82f6'
}

const formatCategory = (category) => {
    const categoryMap = {
        'fitness': 'Fitness',
        'mindfulness': 'Mind',
        'productivity': 'Foco',
        'learning': 'Educa',
        'health': 'Saúde',
        'creativity': 'Criat',
        'social': 'Social',
        'lifestyle': 'Life'
    }
    return categoryMap[category] || category
}

const formatDifficulty = (difficulty) => {
    const difficultyMap = {
        'beginner': 'Iniciante',
        'intermediate': 'Médio',
        'advanced': 'Pró'
    }
    return difficultyMap[difficulty] || difficulty
}

const getDifficultyClasses = (difficulty) => {
    const classes = {
        'beginner': 'inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-600 border border-emerald-100/50',
        'intermediate': 'inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-600 border border-amber-100/50',
        'advanced': 'inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-rose-50 text-rose-600 border border-rose-100/50'
    }
    return classes[difficulty] || 'inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-slate-50 text-slate-600 border border-slate-100'
}

const formatNumber = (num) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k'
    }
    return num?.toString() || '0'
}

const getCompletionRate = (challenge) => {
    return challenge.completion_rate ?? 0
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
</style>