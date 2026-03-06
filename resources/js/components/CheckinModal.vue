<template>
  <Teleport to="body">
    <!-- Modal Backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[110] flex items-center justify-center p-4">
        <!-- Modal Content -->
        <Transition
          enter-active-class="transition-all duration-300"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition-all duration-200"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div v-if="show" class="bg-white/95 backdrop-blur-2xl rounded-[2.5rem] shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto relative border border-white/50 animate-in zoom-in-95 duration-300">
            <!-- Header -->
            <div class="px-8 py-4 border-b border-slate-100/50">
              <div class="flex items-center justify-between">
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">Check-in</h3>
                <button
                  @click="handleClose"
                  class="cursor-pointer text-slate-400 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-all"
                >
                  <Icon icon="lucide:x" class="size-6" />
                </button>
              </div>
            </div>
  
            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="p-4 space-y-8">
              <!-- Task Info -->
              <div class="flex items-center gap-4 p-5 bg-slate-50 rounded-3xl border border-slate-100">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl shadow-sm border border-white/50" 
                     :style="`background-color: ${task.color}15; color: ${task.color}`">
                  <Icon v-if="task.icon_slug" :icon="task.icon_slug" class="size-8" />
                  <span v-else>{{ task.icon || '📋' }}</span>
                </div>
                <div class="flex-1">
                  <h4 class="text-xl font-black text-slate-900 tracking-tight leading-none">{{ task.name }}</h4>
                  <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1">#{{ task.hashtag }}</p>
                </div>
              </div>
  
              <!-- Image Upload -->
              <div class="space-y-3">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">
                  Foto (opcional)
                </label>
                
                <!-- Drop zone -->
                <div
                  @drop="handleDrop"
                  @dragover="handleDragOver"
                  @dragenter="handleDragEnter"
                  @dragleave="handleDragLeave"
                  :class="[
                    'border-2 border-dashed rounded-3xl p-8 text-center transition-all cursor-pointer group',
                    isDragging 
                      ? 'border-blue-500 bg-blue-50/50' 
                      : 'border-slate-200 hover:border-blue-400 hover:bg-blue-50/20'
                  ]"
                  @click="$refs.fileInput.click()"
                >
                  <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    @change="handleFileSelect"
                    class="hidden"
                    :disabled="submitting"
                  >
                  
                  <div v-if="!previewUrl" class="space-y-3">
                    <div class="size-16 mx-auto bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                      <Icon icon="lucide:camera" class="size-8" />
                    </div>
                    <div class="text-sm">
                      <span class="font-black text-blue-600">Clique para selecionar</span>
                      <p class="text-slate-500 font-medium mt-1">ou arraste uma imagem aqui</p>
                    </div>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">PNG, JPG até 5MB</p>
                  </div>
                  
                  <!-- Preview -->
                  <div v-else class="relative group/preview">
                    <img 
                      :src="previewUrl" 
                      alt="Preview" 
                      class="w-full h-56 object-cover rounded-2xl shadow-xl border border-white/50"
                    >
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover/preview:opacity-100 transition-opacity rounded-2xl flex items-center justify-center pointer-events-none">
                      <Icon icon="lucide:refresh-cw" class="size-8 text-white drop-shadow-lg" />
                    </div>
                    <button
                      type="button"
                      @click.stop="removeImage"
                      class="absolute top-4 right-4 bg-red-500 text-white rounded-full size-8 flex items-center justify-center hover:bg-red-600 shadow-lg transition-all active:scale-95"
                    >
                      <Icon icon="lucide:x" class="size-5" />
                    </button>
                  </div>
                </div>
                
                <!-- File size error -->
                <p v-if="fileSizeError" class="text-sm font-bold text-red-500 flex items-center gap-2 mt-2 px-1">
                  <Icon icon="lucide:alert-circle" class="size-4" />
                  {{ fileSizeError }}
                </p>
              </div>
  
              <!-- Message -->
              <div class="space-y-3">
                <label for="message" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">
                  Mensagem (opcional)
                </label>
                <div class="relative group">
                  <div class="absolute top-4 left-4 text-slate-400 group-focus-within:text-blue-600 transition-colors">
                    <Icon icon="lucide:message-square" class="size-5" />
                  </div>
                  <textarea
                    id="message"
                    v-model="form.message"
                    rows="3"
                    :disabled="submitting"
                    class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 text-slate-900 rounded-3xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 disabled:opacity-50 transition-all font-medium placeholder:text-slate-400"
                    placeholder="Conte como foi essa conquista..."
                  />
                </div>
              </div>
  
              <!-- AI Analysis (PRO) -->
              <div v-if="user.is_pro && selectedFile" class="p-5 bg-violet-600/5 rounded-3xl border border-violet-600/10 relative overflow-hidden group/ai">
                <div class="absolute -top-12 -right-12 size-32 bg-violet-600/10 rounded-full blur-2xl group-hover/ai:bg-violet-600/20 transition-colors duration-500"></div>
                <div class="relative z-10">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2 px-2.5 py-1 rounded-lg bg-violet-100 text-violet-700 text-[10px] font-black uppercase tracking-wider">
                      <Icon icon="lucide:bot" class="size-3.5" />
                      Análise IA
                    </div>
                  </div>
                  <label class="flex items-start gap-3 cursor-pointer select-none">
                    <div class="relative flex items-center mt-1">
                      <input
                        type="checkbox"
                        v-model="form.use_ai_analysis"
                        disabled="disabled"
                        class="peer size-5 opacity-0 absolute cursor-pointer"
                      >
                      <div class="size-5 border-2 border-violet-200 rounded-lg group-hover/ai:border-violet-400 transition-all peer-checked:bg-violet-600 peer-checked:border-violet-600 flex items-center justify-center">
                        <Icon icon="lucide:check" class="size-3.5 text-white scale-0 peer-checked:scale-100 transition-transform stroke-[4]" />
                      </div>
                    </div>
                    <span class="text-sm text-slate-700 font-bold leading-relaxed">
                      Extrair métricas automáticas da imagem para meus relatórios detalhados
                    </span>
                  </label>
                </div>
              </div>
  
              <!-- Actions -->
              <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button
                  type="button"
                  @click="handleClose"
                  :disabled="submitting"
                  class="cursor-pointer flex-1 px-8 py-5 border border-slate-200 text-slate-500 font-black uppercase tracking-widest text-[10px] rounded-2xl hover:bg-slate-50 hover:text-slate-900 disabled:opacity-50 transition-all active:scale-95"
                >
                  Cancelar
                </button>
                
                <button
                  type="submit"
                  :disabled="submitting"
                  class="cursor-pointer flex-1 bg-slate-900 text-white px-8 py-5 rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-slate-800 disabled:opacity-50 transition-all flex items-center justify-center gap-3 shadow-xl shadow-slate-900/10 active:scale-95"
                >
                  <Icon v-if="submitting" icon="lucide:loader-2" class="size-5 animate-spin" />
                  <Icon v-else icon="lucide:zap" class="size-5 text-blue-400" />
                  <span>{{ submitting ? 'Enviando...' : 'Fazer Check-in' }}</span>
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
  import { reactive, ref, computed } from 'vue'
  import { usePage } from '@inertiajs/vue3'
  import { csrfFetch } from '@/utils/csrf.js'
  import { Icon } from '@iconify/vue'
  
  // Props
  const props = defineProps({
    show: {
      type: Boolean,
      default: false
    },
    task: {
      type: Object,
      required: true
    },
    userChallenge: {
      type: Object,
      required: true
    },
    checkedDate: {
      type: String,
      default: null
    }
  })
  
  // Emits
  const emit = defineEmits(['close', 'checkin-completed'])
  
  // Page data
  const { props: pageProps } = usePage()
  const user = computed(() => pageProps.auth.user)
  
  // State
  const submitting = ref(false)
  const isDragging = ref(false)
  const selectedFile = ref(null)
  const previewUrl = ref('')
  const fileSizeError = ref('')
  
  // Form
  const form = reactive({
    message: '',
    use_ai_analysis: false
  })
  
  // Methods
  const handleClose = () => {
    if (!submitting.value) {
      resetForm()
      emit('close')
    }
  }
  
  const handleDragOver = (e) => {
    e.preventDefault()
  }
  
  const handleDragEnter = (e) => {
    e.preventDefault()
    isDragging.value = true
  }
  
  const handleDragLeave = (e) => {
    e.preventDefault()
    if (!e.currentTarget.contains(e.relatedTarget)) {
      isDragging.value = false
    }
  }
  
  const handleDrop = (e) => {
    e.preventDefault()
    isDragging.value = false
    
    const files = e.dataTransfer.files
    if (files.length > 0) {
      processFile(files[0])
    }
  }
  
  const handleFileSelect = (e) => {
    const file = e.target.files[0]
    if (file) {
      processFile(file)
    }
  }
  
  const processFile = (file) => {
    fileSizeError.value = ''
    
    // Validar tipo
    if (!file.type.startsWith('image/')) {
      fileSizeError.value = 'Por favor, selecione apenas arquivos de imagem.'
      return
    }
    
    // Validar tamanho (5MB)
    if (file.size > 5 * 1024 * 1024) {
      fileSizeError.value = 'O arquivo deve ter no máximo 5MB.'
      return
    }
    
    selectedFile.value = file
    
    // Criar preview
    const reader = new FileReader()
    reader.onload = (e) => {
      previewUrl.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
  
  const removeImage = () => {
    selectedFile.value = null
    previewUrl.value = ''
    fileSizeError.value = ''
  }
  
  const handleSubmit = async () => {
    if (submitting.value) return
    
    submitting.value = true
    
    try {
      const formData = new FormData()
      formData.append('task_id', props.task.id)
      formData.append('user_challenge_id', props.userChallenge.id)
      if (props.checkedDate) {
        formData.append('checked_date', props.checkedDate)
      }
      formData.append('source', 'web')
      
      if (form.message.trim()) {
        formData.append('message', form.message.trim())
      }
      
      if (selectedFile.value) {
        formData.append('image', selectedFile.value)
        
        if (user.value.is_pro && form.use_ai_analysis) {
          formData.append('use_ai_analysis', '1')
        }
      }
      
      const response = await csrfFetch('/checkins', {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json'
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        emit('checkin-completed', data.checkin)
        resetForm()
      } else {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Erro ao fazer check-in')
      }
    } catch (error) {
      console.error('Erro:', error)
      alert('Erro ao fazer check-in: ' + error.message)
    } finally {
      submitting.value = false
    }
  }
  
  const resetForm = () => {
    form.message = ''
    form.use_ai_analysis = true
    removeImage()
  }
  </script>