<template>
    <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
        <!-- Avatar -->
        <div class="flex-shrink-0">
            <img :src="participant.user?.profile_photo_url || participant.user?.avatar || '/default-avatar.png'"
                :alt="participant.user?.name || 'Participante'"
                class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm">
        </div>

        <!-- User Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h4 class="font-medium text-gray-900 truncate">
                        {{ participant.user?.name || 'Usuário' }}
                    </h4>
                    <div class="flex items-center space-x-2 mt-1">
                        <span :class="getStatusClasses(participant.status)">
                            {{ getStatusIcon(participant.status) }} {{ formatStatus(participant.status) }}
                        </span>
                    </div>
                </div>

                <!-- Progress -->
                <div class="flex-shrink-0 text-right ml-4">
                    <div class="text-lg font-bold text-blue-600">{{ Math.round(participant.completion_rate || 0) }}%
                    </div>
                    <div class="text-xs text-gray-500">Progresso</div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                <span>Iniciou {{ formatRelativeDate(participant.started_at) }}</span>
                <span v-if="participant.streak_days" class="text-orange-600 font-medium">
                    🔥 {{ participant.streak_days }} dias
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
// Props
defineProps({
    participant: {
        type: Object,
        required: true
    }
})

// Methods
const getStatusIcon = (status) => {
    const icons = {
        'active': '🟢',
        'completed': '✅',
        'paused': '⏸️',
        'abandoned': '❌'
    }
    return icons[status] || '⚪'
}

const formatStatus = (status) => {
    const statusMap = {
        'active': 'Ativo',
        'completed': 'Concluído',
        'paused': 'Pausado',
        'abandoned': 'Abandonado'
    }
    return statusMap[status] || status
}

const getStatusClasses = (status) => {
    const classes = {
        'active': 'inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-700',
        'completed': 'inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700',
        'paused': 'inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-700',
        'abandoned': 'inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-700'
    }
    return classes[status] || 'inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700'
}

const formatRelativeDate = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffTime = now.getTime() - date.getTime()
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))

    if (diffDays === 0) return 'hoje'
    if (diffDays === 1) return 'ontem'
    if (diffDays < 7) return `${diffDays} dias atrás`
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} semanas atrás`

    return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
}
</script>