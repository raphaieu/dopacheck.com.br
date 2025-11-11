<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <!-- Header -->
        <DopaHeader subtitle="Criar Desafio" max-width="4xl" :show-back-button="true" back-link="/challenges" />

        <main class="max-w-4xl mx-auto px-4 py-8">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-center space-x-4">
                    <div class="flex items-center">
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors',
                            currentStep >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'
                        ]">
                            1
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">Informações Básicas</span>
                    </div>

                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>

                    <div class="flex items-center">
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors',
                            currentStep >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'
                        ]">
                            2
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">Tasks Diárias</span>
                    </div>

                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>

                    <div class="flex items-center">
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors',
                            currentStep >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'
                        ]">
                            3
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">Revisão</span>
                    </div>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-8">
                <!-- Step 1: Basic Information -->
                <div v-show="currentStep === 1" class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Informações Básicas</h2>
                        <p class="text-gray-600">Defina o título, descrição e configurações do seu desafio</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Título do Desafio *
                            </label>
                            <input id="title" v-model="form.title" type="text" maxlength="255" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500"
                                placeholder="Ex: 30 Dias de Leitura" @input="validateField('title')">
                            <div class="flex justify-between mt-1">
                                <span v-if="errors.title" class="text-sm text-red-600">{{ errors.title }}</span>
                                <span class="text-sm text-gray-500">{{ form.title.length }}/255</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição *
                            </label>
                            <textarea id="description" v-model="form.description" rows="4" maxlength="1000" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500 resize-none"
                                placeholder="Descreva qual é o objetivo do desafio e como ele pode transformar a vida dos participantes..."
                                @input="validateField('description')"></textarea>
                            <div class="flex justify-between mt-1">
                                <span v-if="errors.description" class="text-sm text-red-600">{{ errors.description
                                    }}</span>
                                <span class="text-sm text-gray-500">{{ form.description.length }}/1000</span>
                            </div>
                        </div>

                        <!-- Duration & Settings Grid -->
                        <div class="grid md:grid-cols-3 gap-6">
                            <!-- Duration -->
                            <div>
                                <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-2">
                                    Duração (dias) *
                                </label>
                                <input id="duration_days" v-model.number="form.duration_days" type="number" min="1"
                                    max="365" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
                                    @input="validateField('duration_days')">
                                <span v-if="errors.duration_days" class="text-sm text-red-600">{{ errors.duration_days
                                    }}</span>
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoria *
                                </label>
                                <select id="category" v-model="form.category" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
                                    @change="validateField('category')">
                                    <option value="">Selecione...</option>
                                    <option value="fitness">💪 Fitness</option>
                                    <option value="mindfulness">🧘 Mindfulness</option>
                                    <option value="productivity">⚡ Produtividade</option>
                                    <option value="learning">📚 Aprendizado</option>
                                    <option value="health">🏥 Saúde</option>
                                    <option value="creativity">🎨 Criatividade</option>
                                    <option value="social">👥 Social</option>
                                    <option value="lifestyle">🌟 Estilo de Vida</option>
                                </select>
                                <span v-if="errors.category" class="text-sm text-red-600">{{ errors.category }}</span>
                            </div>

                            <!-- Difficulty -->
                            <div>
                                <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                                    Dificuldade *
                                </label>
                                <select id="difficulty" v-model="form.difficulty" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
                                    @change="validateField('difficulty')">
                                    <option value="">Selecione...</option>
                                    <option value="beginner">🟢 Iniciante</option>
                                    <option value="intermediate">🟡 Intermediário</option>
                                    <option value="advanced">🔴 Avançado</option>
                                </select>
                                <span v-if="errors.difficulty" class="text-sm text-red-600">{{ errors.difficulty
                                    }}</span>
                            </div>
                        </div>

                        <!-- Visibility -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="flex items-center space-x-3">
                                <input v-model="form.is_public" type="checkbox"
                                    class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">Tornar desafio público</span>
                                    <p class="text-sm text-gray-600">Outras pessoas poderão ver e participar do seu
                                        desafio</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Tasks -->
                <div v-show="currentStep === 2" class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Tasks Diárias</h2>
                        <p class="text-gray-600">Defina as atividades que os participantes farão todos os dias</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Existing Tasks -->
                        <div v-for="(task, index) in form.tasks" :key="`task-${index}`" class="space-y-4">
                            <div class="border border-gray-200 rounded-lg p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Task {{ index + 1 }}</h3>
                                    <button v-if="form.tasks.length > 1" type="button" @click="removeTask(index)"
                                        class="text-red-600 hover:text-red-700 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4">
                                    <!-- Task Name -->
                                    <div class="md:col-span-2">
                                        <label :for="`task-name-${index}`"
                                            class="block text-sm font-medium text-gray-700 mb-2">
                                            Nome da Task *
                                        </label>
                                        <input :id="`task-name-${index}`" v-model="task.name" type="text"
                                            maxlength="255" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
                                            placeholder="Ex: Ler por 30 minutos">
                                    </div>

                                    <!-- Hashtag -->
                                    <div>
                                        <label :for="`task-hashtag-${index}`"
                                            class="block text-sm font-medium text-gray-700 mb-2">
                                            Hashtag (WhatsApp) *
                                        </label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">#</span>
                                            <input :id="`task-hashtag-${index}`" v-model="task.hashtag" type="text"
                                                maxlength="50" required pattern="^[a-zA-Z0-9_]+$"
                                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
                                                placeholder="leitura" @input="sanitizeHashtag(task, index)">
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Apenas letras, números e _ (será única no
                                            sistema)</p>
                                    </div>

                                    <!-- Icon -->
                                    <div>
                                        <label :for="`task-icon-${index}`"
                                            class="block text-sm font-medium text-gray-700 mb-2">
                                            Ícone (Emoji)
                                        </label>
                                        <input :id="`task-icon-${index}`" v-model="task.icon" type="text" maxlength="10"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
                                            placeholder="📚">
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <label :for="`task-description-${index}`"
                                            class="block text-sm font-medium text-gray-700 mb-2">
                                            Descrição (opcional)
                                        </label>
                                        <textarea :id="`task-description-${index}`" v-model="task.description" rows="2"
                                            maxlength="500"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 resize-none"
                                            placeholder="Detalhe como realizar esta task..."></textarea>
                                    </div>

                                    <!-- Settings -->
                                    <div class="md:col-span-2 flex flex-wrap gap-4">
                                        <label class="flex items-center space-x-2">
                                            <input v-model="task.is_required" type="checkbox"
                                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">Task obrigatória</span>
                                        </label>

                                        <div class="flex items-center space-x-2">
                                            <label :for="`task-color-${index}`"
                                                class="text-sm text-gray-700">Cor:</label>
                                            <input :id="`task-color-${index}`" v-model="task.color" type="color"
                                                class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Task Button -->
                        <div v-if="form.tasks.length < 10" class="text-center">
                            <button type="button" @click="addTask"
                                class="inline-flex items-center px-6 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-400 hover:text-blue-600 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Adicionar Task ({{ form.tasks.length }}/10)
                            </button>
                        </div>

                        <div v-if="form.tasks.length === 0" class="text-center py-8 text-gray-500">
                            <div
                                class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <p class="mb-4">Nenhuma task criada ainda</p>
                            <button type="button" @click="addTask"
                                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Criar Primeira Task
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Review -->
                <div v-show="currentStep === 3" class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Revisão Final</h2>
                        <p class="text-gray-600">Confira os detalhes do seu desafio antes de criar</p>
                    </div>

                    <!-- Challenge Preview -->
                    <div class="space-y-6">
                        <!-- Basic Info Preview -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-gray-600">Título:</span>
                                    <p class="text-gray-800 font-medium">{{ form.title || 'Sem título' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Descrição:</span>
                                    <p class="text-gray-800">{{ form.description || 'Sem descrição' }}</p>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-3">
                                    <div>
                                        <span class="text-sm text-gray-600">Duração:</span>
                                        <p class="text-gray-800 font-medium">{{ form.duration_days }} dias</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-600">Categoria:</span>
                                        <p class="text-gray-800 font-medium">{{ formatCategoryPreview(form.category) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-600">Dificuldade:</span>
                                        <p class="text-gray-800 font-medium">{{ formatDifficultyPreview(form.difficulty) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-600">Visibilidade:</span>
                                        <p class="text-gray-800 font-medium">{{ form.is_public ? '🌍 Público' : '🔒 Privado' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tasks Preview -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tasks Diárias ({{ form.tasks.length }})
                            </h3>
                            <div class="space-y-3">
                                <div v-for="(task, index) in form.tasks" :key="`preview-task-${index}`"
                                    class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg"
                                        :style="`background-color: ${task.color}20; color: ${task.color}`">
                                        {{ task.icon || '📝' }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <h4 class="font-medium text-gray-900">{{ task.name || `Task ${index + 1}` }}
                                            </h4>
                                            <span class="text-sm text-blue-600">#{{ task.hashtag || 'hashtag' }}</span>
                                            <span v-if="task.is_required"
                                                class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded">Obrigatória</span>
                                        </div>
                                        <p v-if="task.description" class="text-sm text-gray-600">{{ task.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center justify-between">
                    <!-- Botão Voltar: apenas nos passos 2 e 3 para navegar entre passos -->
                    <button v-if="currentStep > 1" type="button" @click="previousStep"
                        class="cursor-pointer px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        Voltar
                    </button>
                    <div v-else></div>

                    <!-- Botão de ação: Continuar ou Criar Desafio -->
                    <div class="flex justify-end">
                        <button v-if="currentStep < 3" type="button" @click="nextStep" :disabled="!canProceed"
                            class="cursor-pointer px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            Continuar
                        </button>

                        <button v-else type="submit" :disabled="submitting || !canProceed"
                            class="cursor-pointer px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center space-x-2">
                            <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>{{ submitting ? 'Criando...' : 'Criar Desafio' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import DopaHeader from '@/components/DopaHeader.vue'

// State
const currentStep = ref(1)
const submitting = ref(false)
const errors = reactive({})

// Form data
const form = reactive({
    title: '',
    description: '',
    duration_days: 21,
    category: '',
    difficulty: '',
    is_public: true,
    tasks: []
})

// Computed
const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return form.title && form.description && form.duration_days && form.category && form.difficulty
        case 2:
            return form.tasks.length > 0 && form.tasks.every(task => task.name && task.hashtag)
        case 3:
            return true
        default:
            return false
    }
})

// Methods
const nextStep = () => {
    if (canProceed.value && currentStep.value < 3) {
        currentStep.value++
    }
}

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--
    }
}

const addTask = () => {
    if (form.tasks.length < 10) {
        form.tasks.push({
            name: '',
            hashtag: '',
            description: '',
            is_required: true,
            icon: '📝',
            color: '#3B82F6'
        })
    }
}

const removeTask = (index) => {
    form.tasks.splice(index, 1)
}

const sanitizeHashtag = (task, index) => {
    // Remove caracteres especiais e espaços
    task.hashtag = task.hashtag.toLowerCase().replace(/[^a-z0-9_]/g, '')
}

const validateField = (field) => {
    // Clear previous error
    delete errors[field]

    // Basic validation
    switch (field) {
        case 'title':
            if (!form.title.trim()) {
                errors.title = 'Título é obrigatório'
            } else if (form.title.length < 5) {
                errors.title = 'Título deve ter pelo menos 5 caracteres'
            }
            break
        case 'description':
            if (!form.description.trim()) {
                errors.description = 'Descrição é obrigatória'
            } else if (form.description.length < 20) {
                errors.description = 'Descrição deve ter pelo menos 20 caracteres'
            }
            break
        case 'duration_days':
            if (!form.duration_days || form.duration_days < 1 || form.duration_days > 365) {
                errors.duration_days = 'Duração deve ser entre 1 e 365 dias'
            }
            break
    }
}

const handleSubmit = async () => {
    if (!canProceed.value || submitting.value) return

    submitting.value = true

    try {
        await router.post('/challenges', form, {
            onSuccess: () => {
                // Redirect will be handled by backend
            },
            onError: (serverErrors) => {
                Object.assign(errors, serverErrors)
                // Go back to first step if there are basic info errors
                if (serverErrors.title || serverErrors.description || serverErrors.category || serverErrors.difficulty) {
                    currentStep.value = 1
                } else if (serverErrors.tasks) {
                    currentStep.value = 2
                }
            },
            onFinish: () => {
                submitting.value = false
            }
        })
    } catch (error) {
        console.error('Error creating challenge:', error)
        submitting.value = false
    }
}

const formatCategoryPreview = (category) => {
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

const formatDifficultyPreview = (difficulty) => {
    const difficultyMap = {
        'beginner': '🟢 Iniciante',
        'intermediate': '🟡 Intermediário',
        'advanced': '🔴 Avançado'
    }
    return difficultyMap[difficulty] || difficulty
}

// Initialize with one task
if (form.tasks.length === 0) {
    addTask()
}
</script>

<style scoped>
/* Custom input styles */
input[type="text"],
input[type="number"],
select,
textarea {
    background-color: white !important;
    color: #111827 !important;
}

input[type="text"]::placeholder,
textarea::placeholder {
    color: #6b7280 !important;
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

/* Progress steps animation */
.transition-colors {
    transition: all 0.3s ease;
}

/* Custom color picker */
input[type="color"] {
    -webkit-appearance: none;
    border: none;
    width: 32px;
    height: 32px;
    cursor: pointer;
}

input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
}

input[type="color"]::-webkit-color-swatch {
    border: 1px solid #d1d5db;
    border-radius: 4px;
}
</style>