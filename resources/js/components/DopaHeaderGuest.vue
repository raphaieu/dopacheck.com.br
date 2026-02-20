<template>
  <header class="sticky top-0 z-50 border-b border-slate-100 bg-white/80 backdrop-blur-lg">
    <div class="mx-auto max-w-6xl px-5 sm:px-6">
      <div class="flex h-16 items-center justify-between">
        <!-- Logo -->
        <Link :href="homeLink" class="flex items-center gap-2.5 transition-opacity hover:opacity-80">
          <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-linear-to-r from-blue-600 to-purple-600 sm:h-9 sm:w-9">
            <span class="text-base sm:text-lg">🧠</span>
          </div>
          <span class="text-lg font-bold tracking-tight text-slate-900">DOPA Check</span>
        </Link>

        <!-- Desktop -->
        <div class="hidden items-center gap-6 sm:flex">
          <Link
            href="/challenges"
            class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-900"
          >
            Desafios
          </Link>
          <Link
            href="/sobre"
            class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-900"
          >
            Sobre
          </Link>
          <Link
            :href="loginHref"
            class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-900"
          >
            Entrar
          </Link>
          <Button
            :as="Link"
            :href="registerHref"
            size="sm"
            class="rounded-lg bg-violet-600 text-white transition-colors hover:bg-violet-700"
          >
            Começar grátis
          </Button>
        </div>

        <!-- Mobile toggle -->
        <Button
          class="sm:hidden"
          variant="ghost"
          size="icon"
          aria-label="Abrir menu"
          @click="toggleMenu"
        >
          <Icon
            :icon="isMenuOpen ? 'lucide:x' : 'lucide:menu'"
            class="h-5 w-5 text-slate-700"
            aria-hidden="true"
          />
        </Button>
      </div>

      <!-- Mobile menu -->
      <div v-show="isMenuOpen" class="border-t border-slate-100 pb-4 pt-3 sm:hidden">
        <nav class="flex flex-col gap-2">
          <Link
            href="/challenges"
            class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50"
            @click="toggleMenu"
          >
            Desafios
          </Link>
          <Link
            href="/sobre"
            class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50"
            @click="toggleMenu"
          >
            Sobre
          </Link>
          <Link
            :href="loginHref"
            class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50"
            @click="toggleMenu"
          >
            Entrar
          </Link>
          <Button
            :as="Link"
            :href="registerHref"
            class="rounded-lg bg-violet-600 text-white transition-colors hover:bg-violet-700"
            @click="toggleMenu"
          >
            Começar grátis
          </Button>
        </nav>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import Button from '@/components/ui/button/Button.vue'

defineProps({
  subtitle: { type: String, default: null },
  homeLink: { type: String, default: '/' },
  maxWidth: { type: String, default: '7xl' },
  showBackButton: { type: Boolean, default: false },
  backLink: { type: String, default: '/' },
})

const isMenuOpen = ref(false)
const page = usePage()
const isLoggedIn = computed(() => !!page.props.auth?.user)
const loginHref = computed(() => (isLoggedIn.value ? '/dopa' : '/login'))
const registerHref = computed(() => (isLoggedIn.value ? '/dopa' : '/register'))

function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value
}
</script>
