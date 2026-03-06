<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 relative overflow-x-clip pt-28">
        <!-- Decorative atmospheric blurs -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl animate-pulse pointer-events-none"></div>
        <div class="absolute top-1/2 -left-24 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl animate-pulse pointer-events-none" style="animation-delay: 2s"></div>
        <div class="absolute -bottom-24 right-1/4 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl animate-pulse pointer-events-none" style="animation-delay: 4s"></div>
        
        <!-- Header Wrapper -->
        <DopaHeaderWrapper :subtitle="isEditMode ? 'Editar Desafio' : 'Criar Desafio'" max-width="4xl" />

        <main class="max-w-4xl mx-auto px-4 pb-24 relative z-10">
            <!-- Progress Steps (Premium Stepper) -->
            <div class="mb-12">
                <div class="flex items-center justify-between max-w-2xl mx-auto relative px-4">
                    <!-- Progress Line Background -->
                    <div class="absolute top-5 left-12 right-12 h-[2px] bg-slate-100 -z-10"></div>
                    <!-- Progress Line Active -->
                    <div class="absolute top-5 left-12 h-[2px] bg-gradient-to-r from-blue-600 to-violet-600 -z-10 transition-all duration-500" 
                        :style="{ width: currentStep === 1 ? '0%' : currentStep === 2 ? '50%' : '100%' }"></div>

                    <!-- Step 1 -->
                    <div class="flex flex-col items-center gap-3">
                        <div :class="[
                            'size-10 rounded-2xl flex items-center justify-center text-sm font-black transition-all duration-500 shadow-xl',
                            currentStep >= 1 ? 'bg-slate-900 text-white shadow-slate-900/10' : 'bg-white text-slate-400'
                        ]">
                            <Icon v-if="currentStep > 1" icon="lucide:check" class="size-5" />
                            <span v-else>01</span>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Informações</span>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex flex-col items-center gap-3">
                        <div :class="[
                            'size-10 rounded-2xl flex items-center justify-center text-sm font-black transition-all duration-500 shadow-xl',
                            currentStep >= 2 ? 'bg-slate-900 text-white shadow-slate-900/10' : 'bg-white text-slate-400'
                        ]">
                            <Icon v-if="currentStep > 2" icon="lucide:check" class="size-5" />
                            <span v-else>02</span>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Tarefas Diárias</span>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex flex-col items-center gap-3">
                        <div :class="[
                            'size-10 rounded-2xl flex items-center justify-center text-sm font-black transition-all duration-500 shadow-xl',
                            currentStep >= 3 ? 'bg-slate-900 text-white shadow-slate-900/10' : 'bg-white text-slate-400'
                        ]">
                            <Icon v-if="currentStep === 3 && submitting" icon="lucide:loader-2" class="size-5 animate-spin" />
                            <span v-else>03</span>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Revisão Final</span>
                    </div>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-8">
                <!-- Step 1: Basic Information -->
                <div v-show="currentStep === 1" class="relative group">
                    <div class="absolute -inset-[1px] bg-gradient-to-r from-blue-600/10 via-violet-600/10 to-purple-600/10 rounded-[2.5rem] blur-sm opacity-50"></div>
                    <div class="relative bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 sm:p-12 shadow-2xl shadow-slate-200/50 border border-white/80 overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-600 via-violet-600 to-purple-600 opacity-80"></div>
                        
                        <div class="text-center mb-12">
                            <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic mb-2">Informações Básicas</h2>
                            <p class="text-xs font-black uppercase tracking-widest text-slate-400">Defina o DNA do seu desafio</p>
                        </div>

                        <div class="space-y-10">
                            <!-- Title -->
                            <div class="group/input relative">
                                <label for="title" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">
                                    Título do Desafio *
                                </label>
                                <div class="relative">
                                    <Icon icon="lucide:type" class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 size-5 group-focus-within/input:text-blue-600 transition-colors" />
                                    <input id="title" v-model="form.title" type="text" maxlength="255" required
                                        class="w-full pl-14 pr-16 py-5 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-bold text-slate-900 placeholder-slate-300"
                                        placeholder="Ex: 30 Dias de Alta Performance" @input="validateField('title')">
                                    <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-300">{{ form.title.length }}/255</span>
                                </div>
                                <p v-if="errors.title" class="mt-2 text-[10px] font-black text-rose-600 uppercase tracking-widest">{{ errors.title }}</p>
                            </div>

                            <!-- Description -->
                            <div class="group/input relative">
                                <label for="description" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">
                                    Descrição do Desafio *
                                </label>
                                <textarea id="description" v-model="form.description" rows="4" maxlength="1000" required
                                    class="w-full px-6 py-5 bg-slate-50/50 border border-slate-100 rounded-3xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-medium text-slate-900 placeholder-slate-300 resize-none leading-relaxed"
                                    placeholder="Descreva o objetivo e como ele vai transformar vidas..."
                                    @input="validateField('description')"></textarea>
                                <div class="flex justify-between mt-2 px-1">
                                    <p v-if="errors.description" class="text-[10px] font-black text-rose-600 uppercase tracking-widest">{{ errors.description }}</p>
                                    <span class="text-[10px] font-black text-slate-300 ml-auto">{{ form.description.length }}/1000</span>
                                </div>
                            </div>

                            <!-- Período -->
                            <div class="grid md:grid-cols-2 gap-8">
                                <div class="group/input relative">
                                    <label for="start_date" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Data de Início *</label>
                                    <div class="relative">
                                        <Icon icon="lucide:calendar-play" class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 size-5 group-focus-within/input:text-blue-600 transition-colors" />
                                        <input id="start_date" v-model="form.start_date" type="date" required
                                            class="w-full pl-14 pr-5 py-5 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-bold text-slate-900"
                                            @input="validateField('start_date')">
                                    </div>
                                    <p v-if="errors.start_date" class="mt-2 text-[10px] font-black text-rose-600 uppercase tracking-widest">{{ errors.start_date }}</p>
                                </div>

                                <div class="group/input relative">
                                    <label for="end_date" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Data de Término *</label>
                                    <div class="relative">
                                        <Icon icon="lucide:calendar-check" class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 size-5 group-focus-within/input:text-blue-600 transition-colors" />
                                        <input id="end_date" v-model="form.end_date" type="date" required
                                            class="w-full pl-14 pr-5 py-5 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-bold text-slate-900"
                                            @input="validateField('end_date')">
                                    </div>
                                    <p v-if="errors.end_date" class="mt-2 text-[10px] font-black text-rose-600 uppercase tracking-widest">{{ errors.end_date }}</p>
                                </div>
                            </div>

                            <!-- Duration & Settings Grid -->
                            <div class="grid md:grid-cols-3 gap-8">
                                <div class="group/input relative">
                                    <label for="duration_days" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Duração (dias)</label>
                                    <div class="relative">
                                        <Icon icon="lucide:clock" class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 size-5 group-focus-within/input:text-blue-600 transition-colors" />
                                        <input id="duration_days" v-model.number="form.duration_days" type="number" min="1" max="365" required
                                            class="w-full pl-14 pr-5 py-5 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-bold text-slate-900"
                                            @input="validateField('duration_days')">
                                    </div>
                                    <p v-if="errors.duration_days" class="mt-2 text-[10px] font-black text-rose-600 uppercase tracking-widest">{{ errors.duration_days }}</p>
                                </div>

                                <div class="group/input relative">
                                    <label for="category" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Categoria *</label>
                                    <div class="relative">
                                        <Icon icon="lucide:layout-grid" class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 size-5 pointer-events-none group-focus-within/input:text-blue-600 transition-colors" />
                                        <select id="category" v-model="form.category" required
                                            class="w-full pl-14 pr-10 py-5 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-black uppercase tracking-widest text-slate-700 text-[10px] appearance-none cursor-pointer">
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
                                        <Icon icon="lucide:chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 size-4 pointer-events-none" />
                                    </div>
                                    <p v-if="errors.category" class="mt-2 text-[10px] font-black text-rose-600 uppercase tracking-widest">{{ errors.category }}</p>
                                </div>

                                <div class="group/input relative">
                                    <label for="difficulty" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Dificuldade *</label>
                                    <div class="relative">
                                        <Icon icon="lucide:bar-chart-3" class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 size-5 pointer-events-none group-focus-within/input:text-blue-600 transition-colors" />
                                        <select id="difficulty" v-model="form.difficulty" required
                                            class="w-full pl-14 pr-10 py-5 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-black uppercase tracking-widest text-slate-700 text-[10px] appearance-none cursor-pointer">
                                            <option value="">Selecione...</option>
                                            <option value="beginner">🟢 Iniciante</option>
                                            <option value="intermediate">🟡 Intermediário</option>
                                            <option value="advanced">🔴 Avançado</option>
                                        </select>
                                        <Icon icon="lucide:chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 size-4 pointer-events-none" />
                                    </div>
                                    <p v-if="errors.difficulty" class="mt-2 text-[10px] font-black text-rose-600 uppercase tracking-widest">{{ errors.difficulty }}</p>
                                </div>
                            </div>

                            <!-- Visibility / Sharing -->
                            <div class="bg-slate-50/50 border border-slate-100 rounded-[2rem] p-8 space-y-8">
                                <label class="flex items-center space-x-5 cursor-pointer group/check">
                                    <div class="relative flex items-center">
                                        <input v-model="shareEnabled" type="checkbox"
                                            class="size-6 text-blue-600 border-slate-200 rounded-lg focus:ring-blue-500 focus:ring-offset-0 transition-all cursor-pointer">
                                    </div>
                                    <div>
                                        <span class="text-[11px] font-black uppercase tracking-widest text-slate-900 group-hover/check:text-blue-600 transition-colors">Compartilhar desafio</span>
                                        <p class="text-[10px] font-medium text-slate-500 mt-1 uppercase tracking-tighter">Torne este desafio visível para outras pessoas</p>
                                    </div>
                                </label>

                                <div class="grid gap-6 md:grid-cols-2 animate-in fade-in slide-in-from-top-2 duration-500" v-if="shareEnabled">
                                    <div class="md:col-span-2">
                                        <label for="share_scope" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Escopo de Compartilhamento</label>
                                        <div class="relative">
                                            <Icon icon="lucide:globe" class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 size-5" />
                                            <select
                                              id="share_scope"
                                              v-model="shareScope"
                                              class="w-full pl-14 pr-10 py-5 bg-white border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-200 transition-all font-black uppercase tracking-widest text-slate-700 text-[10px] appearance-none cursor-pointer"
                                            >
                                              <option value="global">🌍 Público Global</option>
                                              <optgroup v-if="teamOptions.length" label="Times">
                                                <option v-for="team in teamOptions" :key="team.id" :value="String(team.id)">
                                                  👥 {{ team.name }}
                                                </option>
                                              </optgroup>
                                            </select>
                                            <Icon icon="lucide:chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 size-4" />
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-[10px] font-black uppercase tracking-widest text-slate-400 text-center italic">
                                    🔒 Desafio Privado (Apenas você terá acesso)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Tasks -->
                <div v-show="currentStep === 2" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="text-center mb-12">
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic mb-2">Tasks Diárias</h2>
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400 px-4">O que precisa ser feito para manter o progresso?</p>
                    </div>

                    <div class="grid gap-8">
                        <div v-for="(task, index) in form.tasks" :key="`task-${index}`" class="relative group">
                            <!-- Premium Task Card -->
                            <div class="absolute -inset-[1px] bg-gradient-to-r from-blue-600/5 via-violet-600/5 to-purple-600/5 rounded-[2.5rem] blur-sm opacity-50"></div>
                            <div class="relative bg-white/90 backdrop-blur-xl rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 border border-white/80 overflow-hidden">
                                <div class="absolute top-0 left-0 w-2 h-full" :style="{ backgroundColor: task.color }"></div>
                                
                                <div class="flex items-center justify-between mb-8">
                                    <div class="flex items-center gap-4">
                                        <div class="size-12 rounded-2xl flex items-center justify-center text-xl shadow-lg" :style="{ backgroundColor: task.color + '15', color: task.color }">
                                            {{ task.icon || '📝' }}
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Task {{ String(index + 1).padStart(2, '0') }}</h3>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Configuração diária</p>
                                        </div>
                                    </div>
                                    
                                    <button v-if="form.tasks.length > 1" type="button" @click="removeTask(index)"
                                        class="size-10 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all duration-300 flex items-center justify-center group/del active:scale-90">
                                        <Icon icon="lucide:trash-2" class="size-5 group-hover/del:scale-110" />
                                    </button>
                                </div>

                                <div class="grid md:grid-cols-2 gap-8">
                                    <!-- Task Name -->
                                    <div class="md:col-span-2 group/input">
                                        <label :for="`task-name-${index}`" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Nome da Task *</label>
                                        <input :id="`task-name-${index}`" v-model="task.name" type="text" maxlength="255" required
                                            class="w-full px-6 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-bold text-slate-900 placeholder-slate-300"
                                            placeholder="Ex: Beber 3L de água">
                                    </div>

                                    <!-- Hashtag -->
                                    <div class="group/input">
                                        <label :for="`task-hashtag-${index}`" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Hashtag (WhatsApp) *</label>
                                        <div class="relative">
                                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">#</span>
                                            <input :id="`task-hashtag-${index}`" v-model="task.hashtag" type="text" maxlength="50" required pattern="^[a-zA-Z0-9_]+$"
                                                class="w-full pl-10 pr-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-bold text-slate-900 placeholder-slate-300"
                                                placeholder="agua" @input="sanitizeHashtag(task, index)">
                                        </div>
                                    </div>

                                    <!-- Icon Picker & Color -->
                                    <div class="flex gap-4">
                                        <div class="flex-1 group/input">
                                            <label :for="`task-icon-${index}`" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Emoji</label>
                                            <input :id="`task-icon-${index}`" v-model="task.icon" type="text" maxlength="10"
                                                class="w-full px-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all text-center text-xl"
                                                placeholder="📝">
                                        </div>
                                        <div class="group/input">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Cor</label>
                                            <div class="relative size-[60px] rounded-2xl border border-slate-100 bg-white overflow-hidden p-1 shadow-sm">
                                                <input v-model="task.color" type="color" class="absolute inset-0 size-full opacity-0 cursor-pointer z-10">
                                                <div class="size-full rounded-xl" :style="{ backgroundColor: task.color }"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2 group/input">
                                        <label :for="`task-description-${index}`" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Descrição (opcional)</label>
                                        <textarea :id="`task-description-${index}`" v-model="task.description" rows="2" maxlength="500"
                                            class="w-full px-6 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-200 transition-all font-medium text-slate-900 placeholder-slate-300 resize-none leading-relaxed"
                                            placeholder="Dicas ou regras para esta tarefa..."></textarea>
                                    </div>

                                    <!-- Required Toggle -->
                                    <div class="md:col-span-2 flex items-center justify-between pt-4 border-t border-slate-50">
                                        <label class="flex items-center space-x-3 cursor-pointer group/req">
                                            <input v-model="task.is_required" type="checkbox"
                                                class="size-5 text-blue-600 border-slate-200 rounded focus:ring-blue-500 transition-all cursor-pointer">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600 group-hover/req:text-slate-900">Task Obrigatória</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Task Button -->
                        <div v-if="form.tasks.length < 10" class="pt-4 px-4">
                            <button type="button" @click="addTask"
                                class="w-full py-8 border-2 border-dashed border-slate-200 rounded-[2.5rem] bg-white/50 text-slate-400 font-black uppercase tracking-[0.2em] text-[10px] hover:bg-white hover:border-blue-400 hover:text-blue-600 hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-500 flex flex-col items-center gap-3 group">
                                <div class="size-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center group-hover:bg-blue-50 group-hover:border-blue-100 transition-colors">
                                    <Icon icon="lucide:plus" class="size-6" />
                                </div>
                                <span>Adicionar Nova Task ({{ form.tasks.length }}/10)</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Review -->
                <div v-show="currentStep === 3" class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="text-center mb-12">
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic mb-2">Revisão Final</h2>
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400 px-4">Quase lá! Confira se tudo está no seu devido lugar.</p>
                    </div>

                    <div class="relative group">
                        <div class="absolute -inset-[1px] bg-gradient-to-r from-blue-600/10 via-violet-600/10 to-purple-600/10 rounded-[2.5rem] blur-sm opacity-50"></div>
                        <div class="relative bg-white/90 backdrop-blur-xl rounded-[2.5rem] p-10 shadow-2xl shadow-slate-200/50 border border-white/80 space-y-12">
                            
                            <!-- Header Detail -->
                            <div class="flex items-center gap-6 pb-10 border-b border-slate-100">
                                <div class="size-20 rounded-3xl bg-slate-900 flex items-center justify-center text-3xl shadow-2xl rotate-3">
                                    <Icon icon="lucide:clipboard-check" class="size-10 text-white" />
                                </div>
                                <div>
                                    <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tighter">{{ form.title || 'Desafio sem Título' }}</h3>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-400 truncate max-w-md">{{ form.description || 'Nenhuma descrição fornecida.' }}</p>
                                </div>
                            </div>

                            <!-- Meta Info Grid -->
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-10">
                                <div class="space-y-2">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <Icon icon="lucide:calendar" class="size-3.5" /> Início / Fim
                                    </span>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-900">{{ form.start_date }} a {{ form.end_date }}</p>
                                </div>
                                <div class="space-y-2">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <Icon icon="lucide:clock" class="size-3.5" /> Duração
                                    </span>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-900">{{ form.duration_days }} Dias</p>
                                </div>
                                <div class="space-y-2">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <Icon icon="lucide:layout-grid" class="size-3.5" /> Categoria
                                    </span>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-900">{{ formatCategoryPreview(form.category) }}</p>
                                </div>
                                <div class="space-y-2">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <Icon icon="lucide:bar-chart-3" class="size-3.5" /> Dificuldade
                                    </span>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-900">{{ formatDifficultyPreview(form.difficulty) }}</p>
                                </div>
                                <div class="space-y-2">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <Icon icon="lucide:shield-check" class="size-3.5" /> Visibilidade
                                    </span>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-900">{{ visibilityPreview }}</p>
                                </div>
                                <div class="space-y-2">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <Icon icon="lucide:list-todo" class="size-3.5" /> Total de Tasks
                                    </span>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-900 text-blue-600">{{ form.tasks.length }} Ativas</p>
                                </div>
                            </div>

                            <!-- Tasks Summary -->
                            <div class="space-y-4 pt-6 border-t border-slate-100">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Listagem de Atividades Diárias</h4>
                                <div class="grid gap-3">
                                    <div v-for="(task, index) in form.tasks" :key="`preview-${index}`" 
                                        class="flex items-center gap-4 p-4 bg-slate-50/50 border border-slate-100 rounded-2xl group/prev transition-all hover:bg-white hover:shadow-xl hover:shadow-slate-200/50">
                                        <div class="size-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-xl shadow-sm group-hover/prev:scale-110 transition-transform">
                                            {{ task.icon || '📝' }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <span class="text-xs font-black uppercase tracking-tighter text-slate-900">{{ task.name }}</span>
                                                <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100/50">#{{ task.hashtag }}</span>
                                                <span v-if="task.is_required" class="text-[8px] font-black uppercase tracking-widest bg-rose-50 text-rose-500 px-1.5 py-0.5 rounded border border-rose-100/50">Obrigatória</span>
                                            </div>
                                        </div>
                                        <div class="size-3 rounded-full shadow-sm" :style="{ backgroundColor: task.color }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aviso ao editar -->
                    <div v-if="isEditMode && hasSensitiveChanges" class="relative overflow-hidden group">
                        <div class="absolute inset-0 bg-amber-500/5 border border-amber-500/20 rounded-[2.5rem] blur-sm"></div>
                        <div class="relative bg-amber-50/80 backdrop-blur-xl rounded-[2.5rem] p-8 border border-amber-100/50 space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="size-12 rounded-2xl bg-amber-500 flex items-center justify-center text-white shadow-xl shadow-amber-500/20">
                                    <Icon icon="lucide:alert-triangle" class="size-6" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-amber-900 uppercase tracking-widest">Atenção Crítica</h3>
                                    <p class="text-[10px] font-bold text-amber-700 uppercase tracking-tighter">Impacto nas métricas do desafio</p>
                                </div>
                            </div>
                            
                            <p class="text-xs font-medium text-amber-800 leading-relaxed uppercase tracking-tighter">
                                Você alterou datas ou adicionou novas tasks. Ao salvar, todo o progresso atual será **REINICIADO** para garantir a consistência dos novos relatórios.
                            </p>

                            <label class="flex items-center gap-4 cursor-pointer p-4 bg-white/50 rounded-2xl border border-amber-200 transition-all hover:bg-white active:scale-[0.98]">
                                <input v-model="confirmResetProgress" type="checkbox" class="size-5 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                <span class="text-[10px] font-black uppercase tracking-widest text-amber-900">Estou ciente e desejo reiniciar o progresso</span>
                            </label>

                            <p v-if="errors.confirm_reset_progress" class="text-[10px] font-black text-rose-600 uppercase tracking-widest px-2">{{ errors.confirm_reset_progress }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-10">
                    <Button v-if="currentStep > 1" type="button" variant="outline" @click="previousStep"
                        class="cursor-pointer h-16 w-full sm:w-auto px-10 rounded-2xl border-slate-200 text-slate-400 font-black uppercase tracking-[0.2em] text-[11px] hover:bg-white hover:text-slate-900 hover:border-slate-400 transition-all active:scale-95">
                        <Icon icon="lucide:arrow-left" class="mr-2 size-4" />
                        Voltar
                    </Button>
                    <div v-else class="hidden sm:block"></div>

                    <div class="w-full sm:w-auto flex flex-col sm:flex-row gap-4">
                        <Button v-if="currentStep < 3" type="button" @click="nextStep" :disabled="!canProceed"
                            class="cursor-pointer h-16 w-full sm:w-auto px-12 rounded-2xl bg-slate-900 text-white font-black uppercase tracking-[0.2em] text-[11px] shadow-xl shadow-slate-900/10 hover:shadow-2xl hover:bg-slate-800 transition-all active:scale-95 disabled:opacity-50">
                            Próximo Passo
                            <Icon icon="lucide:arrow-right" class="ml-2 size-4" />
                        </Button>

                        <button v-else type="submit" :disabled="submitting || !canProceed"
                            class="cursor-pointer h-16 w-full sm:w-auto px-12 rounded-2xl bg-gradient-to-r from-blue-600 via-violet-600 to-purple-600 text-white font-black uppercase tracking-[0.2em] text-[11px] shadow-xl shadow-blue-600/20 hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 active:scale-110 disabled:opacity-50 flex items-center justify-center cursor-pointer">
                            <Icon v-if="submitting" icon="lucide:loader-2" class="mr-2 size-4 animate-spin" />
                            <span>{{ submitting ? (isEditMode ? 'Salvando...' : 'Criando...') : (isEditMode ? 'Salvar Desafio' : 'Lançar Desafio') }}</span>
                            <Icon v-if="!submitting" icon="lucide:rocket" class="ml-2 size-4" />
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
import { Icon } from '@iconify/vue'
import DopaHeaderWrapper from '@/components/DopaHeaderWrapper.vue'
import Button from '@/components/ui/button/Button.vue'
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
/* Custom transitions */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Force modern inputs */
input[type="text"],
input[type="number"],
input[type="date"],
select,
textarea {
    border-color: #f1f5f9;
}

/* Custom color picker */
input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
}
input[type="color"]::-webkit-color-swatch {
    border: none;
    border-radius: 12px;
}

@keyframes pulse-soft {
    0%, 100% { transform: scale(1); opacity: 0.1; }
    50% { transform: scale(1.1); opacity: 0.15; }
}

.animate-pulse {
    animation: pulse-soft 8s infinite ease-in-out;
}
</style>