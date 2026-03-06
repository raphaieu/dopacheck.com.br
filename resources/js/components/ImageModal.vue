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
      <div 
        v-if="show && imageUrl" 
        class="fixed inset-0 bg-slate-900/90 backdrop-blur-md z-[120] flex items-center justify-center p-4 md:p-8"
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
            class="relative max-w-5xl w-full h-full flex items-center justify-center select-none"
            @click.stop
          >
            <!-- Close Button (Top Right) -->
            <button
              @click="handleClose"
              class="cursor-pointer absolute -top-2 -right-2 md:-top-6 md:-right-6 z-50 p-3 rounded-full bg-white/10 hover:bg-white/20 text-white backdrop-blur-md transition-all active:scale-90 border border-white/10"
              title="Fechar"
            >
              <Icon icon="lucide:x" class="size-6 md:size-8" />
            </button>

            <!-- Image Container -->
            <div class="relative bg-black/40 rounded-[2rem] overflow-hidden shadow-2xl border border-white/10 w-full h-full flex items-center justify-center p-2 backdrop-blur-sm">
              <img 
                :src="imageUrl" 
                :alt="title || 'Imagem'"
                class="max-w-full max-h-full object-contain rounded-xl shadow-2xl"
                @load="handleImageLoad"
                @error="handleImageError"
              >
              
              <!-- Loading State -->
              <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-4 text-white">
                  <div class="size-12 rounded-full border-4 border-white/10 border-t-blue-500 animate-spin"></div>
                  <span class="font-bold tracking-tight">Carregando imagem...</span>
                </div>
              </div>
              
              <!-- Error State -->
              <div v-if="error" class="absolute inset-0 flex items-center justify-center bg-red-900/20 backdrop-blur-md">
                <div class="text-center text-white p-8">
                  <Icon icon="lucide:alert-circle" class="size-16 mx-auto mb-4 text-red-400" />
                  <p class="text-xl font-black tracking-tight">Erro ao carregar imagem</p>
                  <p class="text-sm text-white/60 mt-2 font-medium">Verifique sua conexão e tente novamente</p>
                </div>
              </div>

              <!-- Controls Overlay (Bottom) -->
              <div v-if="!loading && !error" class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-3 px-6 py-3 bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl">
                <h3 v-if="title" class="text-sm font-black text-white/90 mr-4 tracking-tight">{{ title }}</h3>
                <div class="h-4 w-px bg-white/20 mr-2"></div>
                
                <!-- Download Button -->
                <button
                  @click="handleDownload"
                  class="cursor-pointer p-2 rounded-xl bg-white/10 hover:bg-white text-white hover:text-slate-900 transition-all flex items-center gap-2 group"
                  title="Baixar imagem"
                >
                  <Icon icon="lucide:download" class="size-5" />
                  <span class="text-xs font-bold uppercase tracking-widest hidden sm:inline-block">Baixar</span>
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
  import { ref, watch } from 'vue'
  import { Icon } from '@iconify/vue'
  
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