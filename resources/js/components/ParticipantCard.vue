<template>
    <div class="group flex items-center gap-4 p-4 rounded-3xl bg-white/70 backdrop-blur-md border border-white/80 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-0.5 transition-all duration-300">
        <!-- Avatar -->
        <div class="shrink-0 relative">
            <div class="absolute -inset-1 bg-gradient-to-tr from-blue-500 to-purple-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity blur-[2px]"></div>
            <img :src="participant.user?.profile_photo_url || participant.user?.avatar || '/default-avatar.png'"
                :alt="participant.user?.name || 'Participante'"
                class="relative w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm transition-transform group-hover:scale-105 duration-300">
        </div>

        <!-- User Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-extrabold text-slate-900 truncate tracking-tight">
                        {{ participant.user?.display_name || participant.user?.name || 'Usuário' }}
                    </h4>
                    <div class="flex items-center gap-2 mt-1">
                        <span :class="getStatusClasses(participant.status)" class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full border">
                            <Icon :icon="getStatusIconSlug(participant.status)" class="size-2.5" />
                            {{ formatStatus(participant.status) }}
                        </span>
                    </div>
                </div>

                <!-- Progress -->
                <div class="shrink-0 text-right">
                    <div class="text-base font-black bg-gradient-to-r from-blue-600 via-violet-600 to-purple-600 bg-clip-text text-transparent leading-none">
                        {{ Math.round(participant.progress_percentage || participant.completion_rate || 0) }}%
                    </div>
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Progresso</div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="flex items-center justify-between mt-3 text-[10px] font-bold text-slate-400">
                <span class="flex items-center gap-1">
                    <Icon icon="lucide:calendar-days" class="size-3" />
                    {{ formatRelativeDate(participant.started_at) }}
                </span>
                <span v-if="participant.streak_days" class="flex items-center gap-1 text-orange-600">
                    <Icon icon="lucide:flame" class="size-3 fill-current" />
                    {{ participant.streak_days }} d
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Icon } from '@iconify/vue'
import { formatUserChallengeStatus } from '@/utils/userChallengeStatus.js'

// Props
defineProps({
    participant: {
        type: Object,
        required: true
    }
})

// Methods
const getStatusIconSlug = (status) => {
    const icons = {
        'active': 'lucide:play-circle',
        'completed': 'lucide:award',
        'paused': 'lucide:pause-circle',
        'abandoned': 'lucide:x-circle',
        'expired': 'lucide:calendar-x',
    }
    return icons[status] || 'lucide:circle'
}

const formatStatus = (status) => formatUserChallengeStatus(status)

const getStatusClasses = (status) => {
    const classes = {
        'active': 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'completed': 'bg-blue-50 text-blue-600 border-blue-100',
        'paused': 'bg-amber-50 text-amber-600 border-amber-100',
        'abandoned': 'bg-rose-50 text-rose-600 border-rose-100',
        'expired': 'bg-slate-50 text-slate-500 border-slate-100'
    }
    return classes[status] || 'bg-slate-50 text-slate-500 border-slate-100'
}

const formatRelativeDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    const now = new Date()
    const diffTime = now.getTime() - date.getTime()
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))

    if (diffDays === 0) return 'hoje'
    if (diffDays === 1) return 'ontem'
    if (diffDays < 7) return `${diffDays} dias atrás`
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} sem atrás`

    return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
}
</script>