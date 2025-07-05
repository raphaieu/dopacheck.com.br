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
      <div 
        v-if="show && imageUrl" 
        class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        @click="handleBackdropClick"
      >
        <!-- Modal Content -->
        <Transition
          enter-active-class="transition-all duration-300"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition-all duration-200"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div 
            v-if="show" 
            class="relative max-w-4xl max-h-[90vh] w-full"
            @click.stop
          >
            <!-- Header -->
            <div class="absolute top-0 left-0 right-0 z-10 bg-gradient-to-b from-black/50 to-transparent p-4">
              <div class="flex items-center justify-between text-white">
                <h3 v-if="title" class="text-lg font-semibold">{{ title }}</h3>
                <div class="flex items-center space-x-2">
                  <!-- Download Button -->
                  <button
                    @click="handleDownload"
                    class="p-2 rounded-full bg-white/20 hover:bg-white/30 transition-colors"
                    title="Baixar imagem"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </button>
                  
                  <!-- Close Button -->
                  <button
                    @click="handleClose"
                    class="p-2 rounded-full bg-white/20 hover:bg-white/30 transition-colors"
                    title="Fechar"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
  
            <!-- Image Container -->
            <div class="relative bg-white rounded-2xl overflow-hidden shadow-2xl">
              <img 
                :src="imageUrl" 
                :alt="title || 'Imagem'"
                class="w-full h-auto max-h-[80vh] object-contain"
                @load="handleImageLoad"
                @error="handleImageError"
              >
              
              <!-- Loading State -->
              <div v-if="loading" class="absolute inset-0 bg-gray-100 flex items-center justify-center">
                <div class="flex items-center space-x-3 text-gray-600">
                  <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>Carregando imagem...</span>
                </div>
              </div>
              
              <!-- Error State -->
              <div v-if="error" class="absolute inset-0 bg-gray-100 flex items-center justify-center">
                <div class="text-center text-gray-600">
                  <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <p class="font-medium">Erro ao carregar imagem</p>
                  <p class="text-sm text-gray-500 mt-1">Verifique sua conexão e tente novamente</p>
                </div>
              </div>
            </div>
  
            <!-- Footer Info -->
            <div v-if="metadata" class="absolute bottom-0 left-0 right-0 z-10 bg-gradient-to-t from-black/50 to-transparent p-4">
              <div class="text-white text-sm space-y-1">
                <div v-if="metadata.date" class="flex items-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>{{ formatDate(metadata.date) }}</span>
                </div>
                
                <div v-if="metadata.source" class="flex items-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>Enviado via {{ metadata.source === 'whatsapp' ? 'WhatsApp' : 'Web' }}</span>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </template>
  
  <script setup>
  import { ref, watch } from 'vue'
  
  // Props
  const props = defineProps({
    show: {
      type: Boolean,
      default: false
    },
    imageUrl: {
      type: String,
      default: ''
    },
    title: {
      type: String,
      default: ''
    },
    metadata: {
      type: Object,
      default: null
    }
  })
  
  // Emits
  const emit = defineEmits(['close'])
  
  // State
  const loading = ref(false)
  const error = ref(false)
  
  // Methods
  const handleClose = () => {
    emit('close')
  }
  
  const handleBackdropClick = (e) => {
    if (e.target === e.currentTarget) {
      handleClose()
    }
  }
  
  const handleImageLoad = () => {
    loading.value = false
    error.value = false
  }
  
  const handleImageError = () => {
    loading.value = false
    error.value = true
  }
  
  const handleDownload = async () => {
    if (!props.imageUrl) return
    
    try {
      const response = await fetch(props.imageUrl)
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      
      const link = document.createElement('a')
      link.href = url
      link.download = `dopa-check-${Date.now()}.jpg`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      
      window.URL.revokeObjectURL(url)
    } catch (error) {
      console.error('Erro ao baixar imagem:', error)
      alert('Erro ao baixar imagem. Tente novamente.')
    }
  }
  
  const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
  
  // Watchers
  watch(() => props.show, (newValue) => {
    if (newValue) {
      loading.value = true
      error.value = false
      // Adicionar listener para ESC
      document.addEventListener('keydown', handleEscKey)
    } else {
      // Remover listener para ESC
      document.removeEventListener('keydown', handleEscKey)
    }
  })
  
  const handleEscKey = (e) => {
    if (e.key === 'Escape') {
      handleClose()
    }
  }
  </script>