<template>
    <nav class="relative flex flex-col sm:flex-row items-center justify-between gap-6 px-2 py-4 sm:px-0">
        <!-- Result count (Desktop) -->
        <div class="hidden sm:block order-2 sm:order-1">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">
                Mostrando <span class="text-slate-900">{{ from }}</span> a <span class="text-slate-900">{{ to }}</span> de <span class="text-slate-900">{{ total }}</span>
            </p>
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center gap-2 order-1 sm:order-2">
            <!-- Navigation Dock -->
            <div class="flex items-center gap-1.5 p-1.5 bg-white/70 backdrop-blur-xl rounded-2xl border border-white/80 shadow-xl shadow-slate-200/50">
                
                <!-- Previous Button -->
                <button v-if="currentPage > 1" @click="$emit('page-changed', currentPage - 1)"
                    class="size-10 flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all cursor-pointer active:scale-90">
                    <Icon icon="lucide:chevron-left" class="size-5" />
                </button>
                <div v-else class="size-10 flex items-center justify-center rounded-xl text-slate-200 cursor-not-allowed">
                    <Icon icon="lucide:chevron-left" class="size-5" />
                </div>

                <!-- Page Numbers -->
                <div class="flex items-center gap-1">
                    <template v-for="page in visiblePages" :key="page">
                        <button v-if="page !== '...'" @click="$emit('page-changed', page)"
                            class="min-w-[40px] h-10 px-2 flex items-center justify-center rounded-xl text-sm font-black transition-all cursor-pointer active:scale-95"
                            :class="page === currentPage 
                                ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/10' 
                                : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100'">
                            {{ page }}
                        </button>
                        <div v-else class="size-10 flex items-center justify-center text-slate-300 font-black">
                            <Icon icon="lucide:more-horizontal" class="size-4" />
                        </div>
                    </template>
                </div>

                <!-- Next Button -->
                <button v-if="currentPage < lastPage" @click="$emit('page-changed', currentPage + 1)"
                    class="size-10 flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all cursor-pointer active:scale-90">
                    <Icon icon="lucide:chevron-right" class="size-5" />
                </button>
                <div v-else class="size-10 flex items-center justify-center rounded-xl text-slate-200 cursor-not-allowed">
                    <Icon icon="lucide:chevron-right" class="size-5" />
                </div>
            </div>
        </div>

        <!-- Mobile Info -->
        <div class="sm:hidden order-3 text-center">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                Página {{ currentPage }} de {{ lastPage }}
            </p>
        </div>
    </nav>
</template>

<script setup>
import { computed } from 'vue'
import { Icon } from '@iconify/vue'

// Props
const props = defineProps({
    links: {
        type: Array,
        default: () => []
    },
    currentPage: {
        type: Number,
        required: true
    },
    lastPage: {
        type: Number,
        required: true
    },
    from: {
        type: Number,
        default: 0
    },
    to: {
        type: Number,
        default: 0
    },
    total: {
        type: Number,
        default: 0
    }
})

// Emits
defineEmits(['page-changed'])

// Computed
const visiblePages = computed(() => {
    const pages = []
    const current = props.currentPage
    const last = props.lastPage

    // Smart logic for visible pages
    if (last <= 7) {
        for (let i = 1; i <= last; i++) pages.push(i)
    } else {
        if (current <= 4) {
            for (let i = 1; i <= 5; i++) pages.push(i)
            pages.push('...')
            pages.push(last)
        } else if (current >= last - 3) {
            pages.push(1)
            pages.push('...')
            for (let i = last - 4; i <= last; i++) pages.push(i)
        } else {
            pages.push(1)
            pages.push('...')
            for (let i = current - 1; i <= current + 1; i++) pages.push(i)
            pages.push('...')
            pages.push(last)
        }
    }

    return pages
})
</script>

<style scoped>
/* No additional styles needed, using Tailwind */
</style>