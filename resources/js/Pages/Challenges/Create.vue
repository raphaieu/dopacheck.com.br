<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <!-- Header -->
        <DopaHeader :subtitle="isEditMode ? 'Editar Desafio' : 'Criar Desafio'" max-width="4xl" :show-back-button="true" back-link="/challenges" />

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

                        <!-- Período do desafio -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data de início *
                                </label>
                                <input
                                    id="start_date"
                                    v-model="form.start_date"
                                    type="date"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
                                    @input="validateField('start_date')"
                                >
                                <span v-if="errors.start_date" class="text-sm text-red-600">{{ errors.start_date }}</span>
                                <p class="mt-1 text-xs text-gray-500">
                                    Use para criar desafios que já estão em andamento.
                                </p>
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data fim *
                                </label>
                                <input
                                    id="end_date"
                                    v-model="form.end_date"
                                    type="date"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
                                    @input="validateField('end_date')"
                                >
                                <span v-if="errors.end_date" class="text-sm text-red-600">{{ errors.end_date }}</span>
                                <p class="mt-1 text-xs text-gray-500">
                                    Ao alterar, a duração será recalculada automaticamente.
                                </p>
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

                        <!-- Visibility / Sharing -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="flex items-center space-x-3">
                                <input v-model="shareEnabled" type="checkbox"
                                    class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">Compartilhar desafio</span>
                                    <p class="text-sm text-gray-600">
                                        Compartilhe globalmente ou com um time específico.
                                    </p>
                                </div>
                            </label>

                            <div class="mt-4 grid gap-2 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label for="share_scope" class="block text-sm font-medium text-gray-700 mb-2">
                                        Onde compartilhar
                                    </label>
                                    <select
                                      id="share_scope"
                                      v-model="shareScope"
                                      :disabled="!shareEnabled"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                                    >
                                      <option value="global">🌍 Global (qualquer pessoa pode participar)</option>
                                      <optgroup v-if="teamOptions.length" label="Times">
                                        <option v-for="team in teamOptions" :key="team.id" :value="String(team.id)">
                                          👥 {{ team.name }}
                                        </option>
                                      </optgroup>
                                    </select>
                                    <span v-if="errors.team_id" class="text-sm text-red-600">{{ errors.team_id }}</span>
                                    <p v-if="!shareEnabled" class="mt-2 text-xs text-gray-500">
                                      Desmarcado = privado (só você vê e participa).
                                    </p>
                                </div>
                            </div>
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
                                    <div class="md:col-span-1">
                                        <span class="text-sm text-gray-600">Início:</span>
                                        <p class="text-gray-800 font-medium">{{ form.start_date }}</p>
                                    </div>
                                    <div class="md:col-span-1">
                                        <span class="text-sm text-gray-600">Fim:</span>
                                        <p class="text-gray-800 font-medium">{{ form.end_date }}</p>
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
                                        <p class="text-gray-800 font-medium">{{ visibilityPreview }}</p>
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

                    <!-- Aviso ao editar: alterações sensíveis zeram o progresso -->
                    <div v-if="isEditMode && hasSensitiveChanges" class="mt-6 p-4 rounded-xl border-2 border-amber-200 bg-amber-50">
                        <p class="font-semibold text-amber-800 mb-2">⚠️ Atenção</p>
                        <p class="text-sm text-amber-800 mb-4">
                            Você alterou a <strong>data de início/fim</strong> ou <strong>adicionou novas tasks</strong>. Ao salvar, todo o progresso já feito (check-ins, sequência de dias) será perdido e o desafio recomeçará do zero, para manter os relatórios consistentes.
                        </p>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="confirmResetProgress" type="checkbox" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-sm font-medium text-amber-900">Confirmo que entendo que todo o progresso será perdido ao salvar</span>
                        </label>
                        <p v-if="errors.confirm_reset_progress" class="mt-2 text-sm text-red-600">{{ errors.confirm_reset_progress }}</p>
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
                            <span>{{ submitting ? (isEditMode ? 'Salvando...' : 'Criando...') : (isEditMode ? 'Salvar Alterações' : 'Criar Desafio') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, reactive, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import DopaHeader from '@/components/DopaHeader.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'

// State
const currentStep = ref(1)
const submitting = ref(false)
const errors = reactive({})

useSeoMetaTags({
    title: 'Desafio',
})

// Form data
const form = reactive({
    title: '',
    description: '',
    duration_days: 21,
    start_date: '',
    end_date: '',
    category: '',
    difficulty: '',
    visibility: 'global',
    team_id: null,
    tasks: [],
})

const props = defineProps({
    teams: {
        type: Array,
        default: () => [],
    },
    challenge: {
        type: Object,
        default: null,
    },
})

const isEditMode = computed(() => !!props.challenge?.id)

const teamOptions = computed(() => (props.teams || []).filter(t => !t.personal_team))

// Sharing UI state
const shareEnabled = ref(true)
const shareScope = ref('global') // 'global' | teamId (string)

const visibility = computed(() => {
    if (!shareEnabled.value) return 'private'
    if (shareScope.value === 'global') return 'global'
    return 'team'
})

const visibilityPreview = computed(() => {
    if (visibility.value === 'private') return '🔒 Privado (só você)'
    if (visibility.value === 'global') return '🌍 Global'
    const team = teamOptions.value.find(t => String(t.id) === String(shareScope.value))
    return `👥 Time: ${team?.name ?? 'Selecionado'}`
})

// Edição: confirmação de perda de progresso quando há alterações sensíveis
const confirmResetProgress = ref(false)

const hasSensitiveChanges = computed(() => {
    if (!isEditMode.value || !props.challenge) return false
    const ch = props.challenge
    const dateChanged = form.start_date !== (ch.start_date ?? '') || form.end_date !== (ch.end_date ?? '') || Number(form.duration_days) !== Number(ch.duration_days ?? 0)
    const originalTaskCount = Array.isArray(ch.tasks) ? ch.tasks.length : 0
    const newTasksAdded = form.tasks.length > originalTaskCount || form.tasks.some(t => !t.id)
    return dateChanged || newTasksAdded
})

// Computed
const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return form.title && form.description && form.duration_days && form.start_date && form.end_date && form.category && form.difficulty
        case 2:
            return form.tasks.length > 0 && form.tasks.every(task => task.name && task.hashtag)
        case 3:
            if (isEditMode.value && hasSensitiveChanges.value && !confirmResetProgress.value) return false
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
        case 'start_date':
            if (!form.start_date) {
                errors.start_date = 'Data de início é obrigatória'
            }
            break
        case 'end_date':
            if (!form.end_date) {
                errors.end_date = 'Data fim é obrigatória'
            } else if (form.start_date && form.end_date < form.start_date) {
                errors.end_date = 'Data fim deve ser maior ou igual à data de início'
            }
            break
    }
}

// Helpers de data (evita UTC shift do toISOString)
const toLocalIsoDate = (dateObj) => {
    const d = new Date(dateObj.getTime() - dateObj.getTimezoneOffset() * 60000)
    return d.toISOString().slice(0, 10)
}
const parseIsoDate = (iso) => new Date(`${iso}T00:00:00`)
const addDaysIso = (iso, days) => {
    const d = parseIsoDate(iso)
    d.setDate(d.getDate() + days)
    return toLocalIsoDate(d)
}
const diffDaysIso = (startIso, endIso) => {
    const start = parseIsoDate(startIso)
    const end = parseIsoDate(endIso)
    const ms = end.getTime() - start.getTime()
    return Math.floor(ms / (1000 * 60 * 60 * 24))
}

let syncingDates = false
watch(
    () => [form.start_date, form.duration_days],
    () => {
        if (syncingDates) return
        if (!form.start_date || !form.duration_days) return
        syncingDates = true
        form.end_date = addDaysIso(form.start_date, Number(form.duration_days) - 1)
        syncingDates = false
    },
    { deep: false }
)

watch(
    () => form.end_date,
    () => {
        if (syncingDates) return
        if (!form.start_date || !form.end_date) return
        if (form.end_date < form.start_date) return
        const days = diffDaysIso(form.start_date, form.end_date) + 1
        if (days < 1 || days > 365) return
        syncingDates = true
        form.duration_days = days
        syncingDates = false
    }
)

const showServerErrorsToast = (serverErrors = {}) => {
    // Prioriza mensagens "globais" mais úteis pro usuário
    const priorityKeys = ['message', 'confirm_reset_progress', 'tasks', 'title', 'description', 'category', 'difficulty', 'duration_days']
    for (const key of priorityKeys) {
        const value = serverErrors?.[key]
        if (typeof value === 'string' && value.trim()) {
            toast.error(value)
            return
        }
    }

    // Se vierem chaves tipo "tasks.0.hashtag", pega a primeira mensagem string
    const firstString = Object.values(serverErrors).find(v => typeof v === 'string' && v.trim())
    if (firstString) {
        toast.error(firstString)
        return
    }

    // Fallback (quando só recebemos um objeto vazio ou formatos inesperados)
    if (Object.keys(serverErrors || {}).length > 0) {
        toast.error('Não foi possível salvar. Revise os campos destacados e tente novamente.')
    } else {
        toast.error('Não foi possível salvar. Tente novamente.')
    }
}

const handleSubmit = async () => {
    if (!canProceed.value || submitting.value) return

    submitting.value = true

    try {
        // Monta payload com regras de visibilidade
        const payload = {
            ...form,
            visibility: visibility.value,
            team_id: visibility.value === 'team' ? Number(shareScope.value) : null,
        }
        if (isEditMode.value && hasSensitiveChanges.value) {
            payload.confirm_reset_progress = confirmResetProgress.value
        }

        // Limpa erros do server antigos relacionados à visibilidade
        delete errors.team_id
        delete errors.visibility
        delete errors.confirm_reset_progress

        const url = isEditMode.value ? `/challenges/${props.challenge.id}` : '/challenges'
        const method = isEditMode.value ? 'put' : 'post'

        router[method](url, payload, {
            onSuccess: () => {
                // Redirect/flash normalmente é tratado globalmente,
                // mas deixamos um feedback mínimo caso não haja mensagem.
                toast.success(isEditMode.value ? 'Salvando alterações...' : 'Salvando... redirecionando')
            },
            onError: (serverErrors) => {
                Object.assign(errors, serverErrors)
                showServerErrorsToast(serverErrors)
                // Go to step with error
                if (serverErrors.title || serverErrors.description || serverErrors.category || serverErrors.difficulty) {
                    currentStep.value = 1
                } else if (serverErrors.tasks && !serverErrors.confirm_reset_progress) {
                    currentStep.value = 2
                } else if (serverErrors.confirm_reset_progress) {
                    currentStep.value = 3
                }
            },
            onFinish: () => {
                submitting.value = false
            }
        })
    } catch (error) {
        console.error('Error saving challenge:', error)
        toast.error(isEditMode.value ? 'Erro inesperado ao salvar desafio. Tente novamente.' : 'Erro inesperado ao criar desafio. Tente novamente.')
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
if (isEditMode.value) {
    form.title = props.challenge?.title ?? ''
    form.description = props.challenge?.description ?? ''
    form.duration_days = props.challenge?.duration_days ?? 21
    form.start_date = props.challenge?.start_date ?? toLocalIsoDate(new Date())
    form.end_date = props.challenge?.end_date ?? addDaysIso(form.start_date, Number(form.duration_days) - 1)
    form.category = props.challenge?.category ?? ''
    form.difficulty = props.challenge?.difficulty ?? ''
    form.visibility = props.challenge?.visibility ?? 'global'
    form.team_id = props.challenge?.team_id ?? null

    // Preenche UI de sharing (checkbox + select) a partir do enum
    shareEnabled.value = (form.visibility !== 'private')
    shareScope.value = form.visibility === 'team' ? String(form.team_id ?? '') : 'global'

    const tasks = Array.isArray(props.challenge?.tasks) ? props.challenge.tasks : []
    form.tasks = tasks
        .slice()
        .sort((a, b) => (a?.order ?? 0) - (b?.order ?? 0))
        .map(t => ({
            id: t.id,
            name: t.name ?? '',
            hashtag: t.hashtag ?? '',
            description: t.description ?? '',
            is_required: (t.is_required ?? true),
            icon: t.icon ?? '📝',
            color: t.color ?? '#3B82F6',
        }))
} else if (form.tasks.length === 0) {
    // Defaults de datas no create
    form.start_date = toLocalIsoDate(new Date())
    form.end_date = addDaysIso(form.start_date, Number(form.duration_days) - 1)
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
    appearance: none;
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