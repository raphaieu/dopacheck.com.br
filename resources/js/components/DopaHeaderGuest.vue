<template>
  <header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur-sm supports-backdrop-filter:bg-background/60">
    <div class="container mx-auto px-4">
      <div class="flex h-16 items-center justify-between">
        <!-- Left: Logo & Title -->
        <div class="flex items-center">
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

        <!-- Right: Desktop Buttons / Mobile Menu -->
        <div class="flex items-center space-x-2 sm:space-x-4">
          <!-- Desktop: Login/Register Buttons -->
          <div class="hidden sm:flex space-x-2">
            <Button variant="outline" :as="Link" href="/login" prefetch="mount">
              Entrar
            </Button>
            <Button variant="outline" :as="Link" href="/register" prefetch="mount">
              Cadastrar
            </Button>
          </div>

          <!-- Mobile: Hamburger Menu -->
          <Button 
            class="sm:hidden" 
            variant="ghost" 
            size="icon" 
            aria-label="Toggle menu" 
            @click="toggleMenu"
          >
            <Icon :icon="isMenuOpen ? 'lucide:x' : 'lucide:menu'" class="h-6 w-6" aria-hidden="true" />
          </Button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div v-show="isMenuOpen" class="sm:hidden border-t mt-2 pt-4">
        <nav class="flex flex-row space-x-2">
          <Button
            variant="outline" 
            :as="Link" 
            href="/login" 
            class="flex-1" 
            prefetch="mount"
            @click="toggleMenu"
          >
            Entrar
          </Button>
          <Button
            variant="outline" 
            :as="Link" 
            href="/register" 
            class="flex-1" 
            prefetch="mount"
            @click="toggleMenu"
          >
            Cadastrar
          </Button>
        </nav>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import Button from '@/components/ui/button/Button.vue'

const props = defineProps({
  subtitle: {
    type: String,
    default: null
  },
  homeLink: {
    type: String,
    default: '/'
  },
  maxWidth: {
    type: String,
    default: '7xl'
  },
  showBackButton: {
    type: Boolean,
    default: false
  },
  backLink: {
    type: String,
    default: '/'
  }
})

const isMenuOpen = ref(false)

function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value
}
</script>

