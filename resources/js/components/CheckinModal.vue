<template>
    <!-- Modal Backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <!-- Modal Content -->
        <Transition
          enter-active-class="transition-all duration-300"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition-all duration-200"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div v-if="show" class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Check-in</h3>
                <button
                  @click="handleClose"
                  class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
  
            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
              <!-- Task Info -->
              <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" 
                     :style="`background-color: ${task.color}20; color: ${task.color}`">
                  <span class="text-lg">{{ task.icon || '📝' }}</span>
                </div>
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-900">{{ task.name }}</h4>
                  <p class="text-sm text-gray-600">#{{ task.hashtag }}</p>
                </div>
              </div>
  
              <!-- Image Upload -->
              <div class="space-y-3">
                <label class="block text-sm font-medium text-gray-700">
                  Foto (opcional)
                </label>
                
                <!-- Drop zone -->
                <div
                  @drop="handleDrop"
                  @dragover="handleDragOver"
                  @dragenter="handleDragEnter"
                  @dragleave="handleDragLeave"
                  :class="[
                    'border-2 border-dashed rounded-xl p-6 text-center transition-colors cursor-pointer',
                    isDragging 
                      ? 'border-blue-400 bg-blue-50' 
                      : 'border-gray-300 hover:border-gray-400'
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
                  
                  <div v-if="!previewUrl" class="space-y-2">
                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <div class="text-sm text-gray-600">
                      <span class="font-medium text-blue-600">Clique para selecionar</span>
                      ou arraste uma imagem aqui
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG até 5MB</p>
                  </div>
                  
                  <!-- Preview -->
                  <div v-else class="relative">
                    <img 
                      :src="previewUrl" 
                      alt="Preview" 
                      class="w-full h-48 object-cover rounded-lg"
                    >
                    <button
                      type="button"
                      @click.stop="removeImage"
                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <!-- File size error -->
                <p v-if="fileSizeError" class="text-sm text-red-600">
                  {{ fileSizeError }}
                </p>
              </div>
  
              <!-- Message -->
              <div class="space-y-2">
                <label for="message" class="block text-sm font-medium text-gray-700">
                  Mensagem (opcional)
                </label>
                <textarea
                  id="message"
                  v-model="form.message"
                  rows="3"
                  :disabled="submitting"
                  class="w-full px-3 py-2 border border-gray-300 text-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50"
                  placeholder="Conte como foi..."
                />
              </div>
  
              <!-- AI Analysis (PRO) -->
              <div v-if="user.is_pro && selectedFile" class="p-4 bg-purple-50 rounded-xl border border-purple-200">
                <div class="flex items-center space-x-2 mb-2">
                  <span class="text-purple-600">🤖</span>
                  <span class="text-sm font-medium text-purple-800">Análise com IA</span>
                  <span class="px-2 py-0.5 bg-purple-200 text-purple-700 rounded text-xs">PRO</span>
                </div>
                <label class="flex items-center space-x-2">
                  <input
                    type="checkbox"
                    v-model="form.use_ai_analysis"
                    :disabled="submitting"
                    class="rounded border-purple-300 text-purple-600 focus:ring-purple-500"
                  >
                  <span class="text-sm text-purple-700">
                    Analisar imagem automaticamente com IA
                  </span>
                </label>
              </div>
  
              <!-- Actions -->
              <div class="flex space-x-3 pt-4">
                <button
                  type="button"
                  @click="handleClose"
                  :disabled="submitting"
                  class="cursor-pointer flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 disabled:opacity-50 transition-colors"
                >
                  Cancelar
                </button>
                
                <button
                  type="submit"
                  :disabled="submitting"
                  class="cursor-pointer flex-1 bg-blue-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors flex items-center justify-center space-x-2"
                >
                  <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ submitting ? 'Enviando...' : 'Fazer Check-in' }}</span>
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>
  </template>
  
  <script setup>
  import { reactive, ref, computed } from 'vue'
  import { usePage } from '@inertiajs/vue3'
  import { csrfFetch } from '@/utils/csrf.js'
  
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
    use_ai_analysis: true
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