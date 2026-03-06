<template>
  <header class="fixed top-0 inset-x-0 z-50 border-b border-white/80 bg-white/70 backdrop-blur-xl transition-all duration-300">
    <div class="mx-auto max-w-6xl px-5 sm:px-6">
      <div class="flex h-16 items-center justify-between">
        <!-- Logo -->
        <Link :href="homeLink" class="flex items-center gap-3 transition-all group">
          <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 via-violet-600 to-purple-600 shadow-lg shadow-blue-500/20 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
            <span class="text-lg">🧠</span>
          </div>
          <span class="text-lg font-black tracking-tight text-slate-900 group-hover:text-blue-600 transition-colors">DOPA Check</span>
        </Link>

        <!-- Desktop -->
        <div class="hidden items-center gap-8 sm:flex">
          <Link
            href="/challenges"
            class="text-sm font-bold text-slate-500 transition-all hover:text-slate-900 hover:scale-105"
          >
            Desafios
          </Link>
          <Link
            href="/sobre"
            class="text-sm font-bold text-slate-500 transition-all hover:text-slate-900 hover:scale-105"
          >
            Sobre
          </Link>
          <Link
            :href="loginHref"
            class="text-sm font-bold text-slate-500 transition-all hover:text-slate-900 hover:scale-105"
          >
            Entrar
          </Link>
          <Button
            :as="Link"
            :href="registerHref"
            size="sm"
            class="rounded-xl bg-slate-900 text-white font-black text-xs uppercase tracking-widest px-6 hover:bg-slate-800 hover:scale-105 hover:shadow-xl hover:shadow-slate-900/10 active:scale-95 transition-all"
          >
            Começar Agora
          </Button>
        </div>

        <!-- Mobile toggle -->
        <Button
          variant="ghost"
          size="icon"
          aria-label="Abrir menu"
          class="sm:hidden rounded-xl hover:bg-slate-100 transition-all active:scale-90"
          @click="toggleMenu"
        >
          <Icon
            :icon="isMenuOpen ? 'lucide:x' : 'lucide:menu'"
            class="h-6 w-6 text-slate-700"
            aria-hidden="true"
          />
        </Button>
      </div>

      <!-- Mobile menu -->
      <transition 
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-1 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-1 opacity-0"
      >
        <div v-show="isMenuOpen" class="border-t border-slate-100 pb-6 pt-4 sm:hidden">
          <nav class="flex flex-col gap-2">
            <Link
              href="/challenges"
              class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 active:bg-slate-100"
              @click="toggleMenu"
            >
              <div class="size-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <Icon icon="lucide:target" class="size-4.5 text-blue-600" />
              </div>
              Desafios
            </Link>
            <Link
              href="/sobre"
              class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 active:bg-slate-100"
              @click="toggleMenu"
            >
              <div class="size-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                <Icon icon="lucide:info" class="size-4.5 text-indigo-600" />
              </div>
              Sobre
            </Link>
            <Link
              :href="loginHref"
              class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 active:bg-slate-100"
              @click="toggleMenu"
            >
              <div class="size-8 rounded-lg bg-violet-50 flex items-center justify-center">
                <Icon icon="lucide:log-in" class="size-4.5 text-violet-600" />
              </div>
              Entrar
            </Link>
            <div class="mt-4 px-2">
              <Button
                :as="Link"
                :href="registerHref"
                class="w-full rounded-2xl bg-gradient-to-r from-blue-600 to-violet-600 text-white font-black text-sm uppercase tracking-widest py-6 shadow-lg shadow-blue-500/20 active:scale-95 transition-all"
                @click="toggleMenu"
              >
                Criar seu primeiro desafio
              </Button>
            </div>
          </nav>
        </div>
      </transition>
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
