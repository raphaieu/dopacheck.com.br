<template>
    <div class="group flex items-center gap-5 p-4 sm:p-5 rounded-3xl bg-white/70 backdrop-blur-md border border-white/80 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1 transition-all duration-300">
        <!-- Task Number & Icon -->
        <div class="flex-shrink-0 relative">
            <div class="absolute -top-2 -left-2 w-6 h-6 bg-slate-900 text-white rounded-lg flex items-center justify-center text-[10px] font-black z-10 shadow-lg">
                {{ index + 1 }}
            </div>

            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl transition-transform group-hover:scale-110 duration-500 shadow-sm border border-white/50"
                :style="`background-color: ${task.color}15; color: ${task.color}`">
                <Icon v-if="task.icon_slug" :icon="task.icon_slug" class="size-8" />
                <span v-else>{{ task.icon || '📋' }}</span>
            </div>
        </div>

        <!-- Task Content -->
        <div class="flex-1 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex-1">
                    <h4 class="text-base font-extrabold text-slate-900 mb-1 leading-tight">{{ task.name }}</h4>
                    <p v-if="task.description" class="text-sm text-slate-500 mb-3 leading-snug line-clamp-2">{{ task.description }}</p>

                    <!-- Hashtag & Required -->
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100/50">
                            #{{ task.hashtag }}
                        </span>

                        <span v-if="task.is_required"
                            class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-50 text-rose-600 border border-rose-100/50">
                            Obrigatória
                        </span>
                        <span v-else
                            class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-500">
                            Opcional
                        </span>
                    </div>
                </div>

                <!-- Example Preview -->
                <div v-if="isParticipating" class="flex-shrink-0 lg:block hidden">
                    <div class="text-right">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">no WhatsApp</div>
                        <div class="text-[11px] font-bold text-slate-600 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100 font-mono italic">
                            Foto + #{{ task.hashtag }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Icon } from '@iconify/vue'

// Props
defineProps({
    task: {
        type: Object,
        required: true
    },
    index: {
        type: Number,
        required: true
    },
    isParticipating: {
        type: Boolean,
        default: false
    }
})

</script>