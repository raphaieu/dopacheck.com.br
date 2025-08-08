<template>
    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
        <!-- Task Number & Icon -->
        <div class="flex-shrink-0 flex items-center space-x-3">
            <div
                class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-sm font-bold text-gray-500 border border-gray-200">
                {{ index + 1 }}
            </div>

            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl"
                :style="`background-color: ${task.color}20; color: ${task.color}`">
                <span>{{ task.icon || '📝' }}</span>
            </div>
        </div>

        <!-- Task Content -->
        <div class="flex-1 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-1">{{ task.name }}</h4>
                    <p v-if="task.description" class="text-sm text-gray-600 mb-2">{{ task.description }}</p>

                    <!-- Hashtag & Required -->
                    <div class="flex items-center space-x-2">
                        <span
                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            #{{ task.hashtag }}
                        </span>

                        <span v-if="task.is_required"
                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-700">
                            Obrigatória
                        </span>
                        <span v-else
                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                            Opcional
                        </span>
                    </div>
                </div>

                <!-- Example Preview -->
                <div v-if="isParticipating" class="flex-shrink-0 mt-3 sm:mt-0 sm:ml-4">
                    <div class="text-left sm:text-right">
                        <div class="text-sm text-gray-500 mb-1">Exemplo:</div>
                        <div class="text-xs text-gray-600 bg-white px-3 py-2 rounded-lg border border-gray-200 font-mono break-words">
                            Foto + #{{ task.hashtag }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
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