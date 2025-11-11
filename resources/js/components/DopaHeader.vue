<template>
  <header class="bg-white/90 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-40">
    <div :class="['mx-auto px-4 py-4', maxWidthClass]">
      <div class="flex items-center justify-between relative">
        <!-- Left: Back Button or Empty Space -->
        <div class="flex items-center w-10 sm:w-12">
          <slot name="back">
            <Link v-if="showBackButton" :href="backLink" class="text-gray-600 hover:text-gray-900 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </Link>
          </slot>
        </div>

        <!-- Center: Logo & Title -->
        <div class="flex-1 flex items-center justify-start sm:justify-center">
          <Link :href="homeLink" class="flex items-center space-x-2 hover:opacity-80 transition-opacity">
            <div
              class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
              <span class="text-white font-bold text-base sm:text-lg">🧠</span>
            </div>
            <div class="text-left">
              <h1 class="text-lg sm:text-xl font-bold text-gray-900">DOPA Check</h1>
              <p v-if="subtitle" class="text-xs sm:text-sm text-gray-500">{{ subtitle }}</p>
            </div>
          </Link>
        </div>

        <!-- Right: Actions & User Menu -->
        <div class="flex items-center space-x-2 sm:space-x-4">
          <!-- Custom Actions Slot -->
          <slot name="actions"></slot>

          <!-- Profile Dropdown -->
          <div class="relative" ref="dropdownRef" @keydown.esc="closeMenu">
            <button
              @click.stop="toggleMenu"
              class="cursor-pointer relative w-10 h-10 sm:w-12 sm:h-12 rounded-full overflow-hidden focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
              aria-label="Abrir menu do perfil"
            >
              <img 
                :src="user?.profile_photo_url || '/default-avatar.png'" 
                :alt="user?.name || 'Usuário'"
                class="w-full h-full object-cover"
              >
              <!-- PRO Badge inside avatar -->
              <div v-if="user?.is_pro" 
                class="absolute bottom-0 right-0 w-5 h-5 sm:w-6 sm:h-6 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full border-2 border-white flex items-center justify-center shadow-md"
                title="PRO"
              >
                <span class="text-[10px] sm:text-xs text-white font-bold">✨</span>
              </div>
            </button>
            <transition name="fade">
              <div
                v-if="showMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50"
              >
                <Link 
                  href="/reports" 
                  class="flex items-center px-4 py-3 hover:bg-gray-50 text-gray-700 transition-colors"
                  @click="showMenu = false"
                >
                  <span class="text-xl mr-2">📊</span> Relatórios
                </Link>
                <Link 
                  :href="`/u/${user?.username}`" 
                  class="flex items-center px-4 py-3 hover:bg-gray-50 text-gray-700 transition-colors"
                  @click="showMenu = false"
                >
                  <span class="text-xl mr-2">🔗</span> Meu Perfil
                </Link>
                <Link 
                  href="/challenges" 
                  class="flex items-center px-4 py-3 hover:bg-gray-50 text-gray-700 transition-colors"
                  @click="showMenu = false"
                >
                  <span class="text-xl mr-2">🎯</span> Desafios
                </Link>
                <Link 
                  href="/profile/settings" 
                  class="flex items-center px-4 py-3 hover:bg-gray-50 text-gray-700 transition-colors"
                  @click="showMenu = false"
                >
                  <span class="text-xl mr-2">⚙️</span> Config
                </Link>
                <div class="border-t border-gray-100 my-1"></div>
                <button
                  @click="handleLogout"
                  class="w-full flex items-center px-4 py-3 hover:bg-gray-50 text-gray-700 transition-colors text-left"
                >
                  <span class="text-xl mr-2">🚪</span> Sair
                </button>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  subtitle: {
    type: String,
    default: null
  },
  maxWidth: {
    type: String,
    default: '7xl' // 7xl, 4xl, full, etc
  },
  homeLink: {
    type: String,
    default: '/dopa'
  },
  showBackButton: {
    type: Boolean,
    default: false
  },
  backLink: {
    type: String,
    default: '/dopa'
  }
})

const page = usePage()
const user = computed(() => page.props.auth?.user)
const showMenu = ref(false)
const dropdownRef = ref(null)

const maxWidthClass = computed(() => {
  const classes = {
    '7xl': 'max-w-7xl',
    '4xl': 'max-w-4xl',
    'full': 'w-full',
    '6xl': 'max-w-6xl'
  }
  return classes[props.maxWidth] || 'max-w-7xl'
})

function toggleMenu() {
  showMenu.value = !showMenu.value
}

function closeMenu() {
  showMenu.value = false
}

function handleLogout() {
  showMenu.value = false
  router.post('/logout')
}

// Fechar menu ao clicar fora
function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeMenu()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

