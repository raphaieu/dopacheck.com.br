<template>
  <header class="bg-white/70 backdrop-blur-xl border-b border-white/80 fixed top-0 inset-x-0 z-50 transition-all duration-300">
    <div :class="['mx-auto px-4 py-3 sm:py-4', maxWidthClass]">
      <div class="flex items-center justify-between relative">
        <!-- Left: Back Button or Empty Space -->
        <div class="flex items-center w-10 sm:w-12">
          <slot name="back">
            <Link v-if="showBackButton" :href="backLink" class="text-slate-500 hover:text-slate-900 transition-all hover:scale-110 active:scale-95 p-2 rounded-xl hover:bg-slate-100">
              <Icon icon="lucide:chevron-left" class="size-6" />
            </Link>
          </slot>
        </div>

        <!-- Center: Logo & Title -->
        <div class="flex-1 flex items-center justify-start sm:justify-center">
          <Link :href="homeLink" class="flex items-center gap-3 group transition-all">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 via-violet-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
              <span class="text-white text-xl">🧠</span>
            </div>
            <div class="text-left hidden xs:block">
              <h1 class="text-lg font-black text-slate-900 tracking-tight leading-none group-hover:text-blue-600 transition-colors">DOPA Check</h1>
              <p v-if="subtitle" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ subtitle }}</p>
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
              class="cursor-pointer relative group focus:outline-none"
              aria-label="Abrir menu do perfil"
            >
              <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity blur-[2px]"></div>
              <img 
                :src="user?.profile_photo_url || '/default-avatar.png'" 
                :alt="user?.name || 'Usuário'"
                class="relative w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover border-2 border-white shadow-sm"
              >
              <!-- PRO Badge inside avatar -->
              <div v-if="user?.is_pro" 
                class="absolute -bottom-1 -right-1 w-5 h-5 bg-gradient-to-tr from-amber-400 to-orange-500 rounded-full border-2 border-white flex items-center justify-center shadow-md animate-pulse"
                title="PRO"
              >
                <Icon icon="lucide:sparkles" class="size-2.5 text-white" />
              </div>
            </button>
            <transition name="menu-pop">
              <div
                v-if="showMenu"
                class="absolute right-0 mt-3 w-56 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl shadow-slate-900/10 border border-slate-100 z-50 overflow-hidden py-1.5"
              >
                <div class="px-4 py-3 border-b border-slate-50 mb-1.5">
                  <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Conectado como</p>
                  <p class="text-sm font-black text-slate-900 truncate mt-0.5">{{ user?.display_name || user?.name }}</p>
                </div>

                <Link 
                  href="/reports" 
                  class="flex items-center px-4 py-2.5 hover:bg-blue-50/50 text-slate-600 hover:text-blue-600 transition-all font-bold text-sm mx-1.5 rounded-xl"
                  @click="showMenu = false"
                >
                  <div class="size-8 rounded-lg bg-blue-50 flex items-center justify-center mr-3">
                    <Icon icon="lucide:bar-chart-3" class="size-4.5" />
                  </div>
                  Relatórios
                </Link>
                <Link 
                  :href="profileHref" 
                  class="flex items-center px-4 py-2.5 hover:bg-violet-50/50 text-slate-600 hover:text-violet-600 transition-all font-bold text-sm mx-1.5 rounded-xl"
                  @click="showMenu = false"
                >
                  <div class="size-8 rounded-lg bg-violet-50 flex items-center justify-center mr-3">
                    <Icon icon="lucide:user" class="size-4.5" />
                  </div>
                  Perfil
                </Link>
                <Link
                  :href="route('teams.my-index')"
                  class="flex items-center px-4 py-2.5 hover:bg-emerald-50/60 text-slate-600 hover:text-emerald-600 transition-all font-bold text-sm mx-1.5 rounded-xl"
                  @click="showMenu = false"
                >
                  <div class="size-8 rounded-lg bg-emerald-50 flex items-center justify-center mr-3">
                    <Icon icon="lucide:users" class="size-4.5" />
                  </div>
                  Meus Times
                </Link>
                <Link 
                  href="/challenges" 
                  class="flex items-center px-4 py-2.5 hover:bg-indigo-50/50 text-slate-600 hover:text-indigo-600 transition-all font-bold text-sm mx-1.5 rounded-xl"
                  @click="showMenu = false"
                >
                  <div class="size-8 rounded-lg bg-indigo-50 flex items-center justify-center mr-3">
                    <Icon icon="lucide:target" class="size-4.5" />
                  </div>
                  Desafios
                </Link>
                <Link 
                  href="/profile/settings" 
                  class="flex items-center px-4 py-2.5 hover:bg-slate-50 text-slate-600 hover:text-slate-900 transition-all font-bold text-sm mx-1.5 rounded-xl"
                  @click="showMenu = false"
                >
                  <div class="size-8 rounded-lg bg-slate-50 flex items-center justify-center mr-3">
                    <Icon icon="lucide:settings" class="size-4.5" />
                  </div>
                  Configurações
                </Link>
                <div class="border-t border-slate-50 my-1.5 mx-4"></div>
                <button
                  @click="handleLogout"
                  class="w-[calc(100%-1.5rem)] flex items-center px-4 py-2.5 hover:bg-rose-50 text-slate-600 hover:text-rose-600 transition-all font-bold text-sm mx-3 rounded-xl text-left"
                >
                  <div class="size-8 rounded-lg bg-rose-50 flex items-center justify-center mr-3">
                    <Icon icon="lucide:log-out" class="size-4.5" />
                  </div>
                  Sair
                </button>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Bottom navigation (Premium Dock) -->
  <div
    v-if="user"
    class="md:hidden fixed left-1/2 -translate-x-1/2 bottom-6 z-50 pointer-events-none"
  >
    <nav class="bg-slate-900/90 backdrop-blur-xl border border-white/10 rounded-[2rem] shadow-2xl shadow-slate-900/40 p-2 flex items-center gap-1 pointer-events-auto whitespace-nowrap">
      <Link
        href="/reports"
        class="flex flex-col items-center justify-center rounded-2xl w-14 h-14 transition-all duration-300"
        :class="isActive('/reports') ? 'bg-white text-slate-900 shadow-lg scale-110' : 'text-slate-400 hover:text-white'"
      >
        <Icon icon="lucide:bar-chart-3" class="size-5" />
        <span class="text-[9px] font-black uppercase tracking-tighter mt-1" v-if="isActive('/reports')">Relatórios</span>
      </Link>

      <Link
        href="/challenges"
        class="flex flex-col items-center justify-center rounded-2xl w-14 h-14 transition-all duration-300"
        :class="isActive('/challenges') ? 'bg-white text-slate-900 shadow-lg scale-110' : 'text-slate-400 hover:text-white'"
      >
        <Icon icon="lucide:target" class="size-5" />
        <span class="text-[9px] font-black uppercase tracking-tighter mt-1" v-if="isActive('/challenges')">Desafios</span>
      </Link>

      <Link
        href="/dopa"
        class="flex flex-col items-center justify-center rounded-2xl w-16 h-16 transition-all duration-300 -mt-8 bg-gradient-to-br from-blue-600 to-violet-600 text-white shadow-xl shadow-blue-500/30 border-4 border-white active:scale-95"
      >
        <Icon icon="lucide:layout-grid" class="size-6" />
      </Link>

      <Link
        :href="profileHref"
        class="flex flex-col items-center justify-center rounded-2xl w-14 h-14 transition-all duration-300"
        :class="isActive('/u/') ? 'bg-white text-slate-900 shadow-lg scale-110' : 'text-slate-400 hover:text-white'"
        @click="showMenu = false"
      >
        <Icon icon="lucide:user" class="size-5" />
        <span class="text-[9px] font-black uppercase tracking-tighter mt-1" v-if="isActive('/u/')">Perfil</span>
      </Link>

      <Link
        href="/profile/settings"
        class="flex flex-col items-center justify-center rounded-2xl w-14 h-14 transition-all duration-300"
        :class="isActive('/profile/settings') ? 'bg-white text-slate-900 shadow-lg scale-110' : 'text-slate-400 hover:text-white'"
      >
        <Icon icon="lucide:settings" class="size-5" />
        <span class="text-[9px] font-black uppercase tracking-tighter mt-1" v-if="isActive('/profile/settings')">Config</span>
      </Link>
    </nav>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

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

const profileHref = computed(() => {
  if (!user.value) return '/profile/settings'
  const key = user.value.username || user.value.id
  return `/u/${key}`
})

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

function isActive(prefix) {
  const url = String(page.url || '')
  if (prefix === '/u/') return url.startsWith('/u/')
  return url === prefix || url.startsWith(`${prefix}/`)
}

// Fechar menu ao clicar fora
function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeMenu()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  if (user.value && typeof document !== 'undefined') {
    document.body?.classList?.add('dopa-bottom-nav-dock')
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (typeof document !== 'undefined') {
    document.body?.classList?.remove('dopa-bottom-nav-dock')
  }
})
</script>

<style scoped>
.menu-pop-enter-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.menu-pop-leave-active {
  transition: all 0.2s ease-in;
}

.menu-pop-enter-from,
.menu-pop-leave-to {
  opacity: 0;
  transform: translateY(10px) scale(0.95);
}

@media (max-width: 639px) {
  .xs\:block {
    display: block;
  }
}
</style>

<style>
/* Global style to add padding when bottom nav is present */
.dopa-bottom-nav-dock {
  padding-bottom: 6rem;
}
</style>

