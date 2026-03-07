<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 pb-20 overflow-x-clip pt-28">
    <!-- Header -->
    <DopaHeaderWrapper subtitle="Perfil Público" max-width="4xl" :show-back-button="true" back-link="/challenges" />

    <main class="max-w-4xl mx-auto px-4 py-8 relative">
      <!-- Decorative Blurs -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl -z-10"></div>
      <div class="absolute top-1/2 -left-24 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl -z-10"></div>

      <!-- Profile Header Card -->
      <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-white/80 p-6 sm:p-8 mb-8 overflow-hidden relative">
        <div class="absolute top-0 right-0 p-8 opacity-5">
            <Icon icon="lucide:user" class="size-48" />
        </div>

        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8 relative z-10">
          <div class="relative shrink-0">
            <div class="absolute -inset-1.5 bg-gradient-to-tr from-blue-500 to-purple-600 rounded-full blur opacity-25"></div>
            <img 
              :src="profileUser.avatar || '/default-avatar.png'" 
              :alt="profileUser.name"
              class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-white shadow-lg"
            />
          </div>

          <div class="flex-1 text-center sm:text-left">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
              <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-1">{{ profileUser.name }}</h2>
                <p class="text-slate-500 font-bold tracking-wide">@{{ profileUser.username || profileUser.id }}</p>
              </div>
              
              <div v-if="isOwnProfile" class="flex flex-wrap gap-3 justify-center">
                <Link
                  href="/subscriptions/create"
                  class="bg-slate-900 text-white px-5 py-2.5 rounded-2xl font-bold hover:bg-slate-800 transition-all active:scale-95 shadow-lg shadow-slate-900/10 flex items-center gap-2 text-sm"
                >
                  <Icon icon="lucide:credit-card" class="size-4" />
                  Assinaturas
                </Link>
                <Link
                  :href="route('teams.my-index')"
                  class="bg-white/80 backdrop-blur-md border border-slate-200 text-slate-700 px-5 py-2.5 rounded-2xl font-bold hover:bg-slate-50 transition-all active:scale-95 flex items-center gap-2 text-sm shadow-sm"
                >
                  <Icon icon="lucide:users" class="size-4" />
                  Meus times
                </Link>
                <Link
                  href="/profile/settings"
                  class="bg-white/80 backdrop-blur-md border border-slate-200 text-slate-700 px-5 py-2.5 rounded-2xl font-bold hover:bg-slate-50 transition-all active:scale-95 flex items-center gap-2 text-sm shadow-sm"
                >
                  <Icon icon="lucide:settings" class="size-4" />
                  Configurar
                </Link>
              </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6 border-t border-slate-100">
              <div class="text-center sm:text-left p-2 rounded-2xl hover:bg-blue-50/50 transition-colors">
                <div class="text-2xl font-black text-blue-600 tabular-nums">{{ stats.total_challenges }}</div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Desafios</div>
              </div>
              <div class="text-center sm:text-left p-2 rounded-2xl hover:bg-emerald-50/50 transition-colors">
                <div class="text-2xl font-black text-emerald-600 tabular-nums">{{ stats.completed_challenges }}</div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Completos</div>
              </div>
              <div class="text-center sm:text-left p-2 rounded-2xl hover:bg-purple-50/50 transition-colors">
                <div class="text-2xl font-black text-purple-600 tabular-nums">{{ stats.total_checkins }}</div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Check-ins</div>
              </div>
              <div class="text-center sm:text-left p-2 rounded-2xl hover:bg-orange-50/50 transition-colors">
                <div class="text-2xl font-black text-orange-600 tabular-nums">{{ stats.best_streak }}</div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Melhor Sequência</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Current Challenge -->
      <div v-if="currentChallenge" class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-white/80 p-6 sm:p-8 mb-8 relative overflow-hidden">
        <div class="absolute -top-12 -left-12 opacity-[0.03] rotate-12">
            <Icon icon="lucide:flame" class="size-48 text-orange-500" />
        </div>

        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
            <Icon icon="lucide:target" class="size-4 text-blue-500" />
            Desafio Atual
        </h3>

        <div class="flex flex-col md:flex-row items-center justify-between gap-8 relative z-10">
          <div class="flex-1 text-center md:text-left">
            <h4 class="text-2xl font-black text-slate-900 mb-2 leading-tight">{{ currentChallenge.challenge.title }}</h4>
            <p class="text-slate-600 mb-6 leading-relaxed">{{ currentChallenge.challenge.description }}</p>
            
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-6 text-sm">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 font-bold group hover:bg-blue-100 transition-colors">
                    <Icon icon="lucide:calendar" class="size-4" />
                    Dia {{ currentChallenge.current_day }} de {{ currentChallenge.challenge.duration_days }}
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 font-bold group hover:bg-emerald-100 transition-colors">
                    <Icon icon="lucide:trending-up" class="size-4" />
                    {{ Math.round(currentChallenge.progress_percentage) }}% concluído
                </div>
            </div>
          </div>

          <div class="shrink-0 relative">
            <div class="absolute inset-0 bg-blue-400/20 rounded-full blur-2xl opacity-50 animate-pulse"></div>
            <ProgressRing 
              :progress="currentChallenge.progress_percentage" 
              :size="100" 
              :stroke-width="10" 
              color="blue"
              class="relative z-10"
            />
          </div>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-8">
        <!-- Completed Challenges -->
        <div v-if="completedChallenges.length > 0">
          <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 px-2 flex items-center gap-2">
            <Icon icon="lucide:award" class="size-4 text-emerald-500" />
            Concluídos
          </h3>
          <div class="grid grid-cols-1 gap-4">
            <div 
              v-for="userChallenge in completedChallenges" 
              :key="userChallenge.id"
              class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-2xl p-5 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1 transition-all duration-300 group"
            >
              <div class="flex items-start justify-between gap-4">
                <div>
                  <h4 class="font-black text-slate-900 mb-1 group-hover:text-blue-600 transition-colors leading-tight">
                    {{ userChallenge.challenge.title }}
                  </h4>
                  <div class="flex items-center gap-2 text-xs text-slate-400 font-bold uppercase tracking-wider">
                    <Icon icon="lucide:check-circle" class="size-3 text-emerald-500" />
                    {{ userChallenge.challenge.duration_days }} dias
                  </div>
                </div>
                <div class="bg-emerald-50 p-2 rounded-xl text-emerald-600 transition-transform group-hover:scale-110">
                    <Icon icon="lucide:medal" class="size-5" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Check-ins Grid -->
        <div v-if="recentCheckins.length > 0">
          <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 px-2 flex items-center gap-2">
            <Icon icon="lucide:image" class="size-4 text-purple-500" />
            Check-ins
          </h3>
          <div class="grid grid-cols-3 gap-3">
            <div 
              v-for="checkin in recentCheckins" 
              :key="checkin.id"
              class="group relative aspect-square rounded-2xl overflow-hidden cursor-pointer shadow-sm hover:shadow-xl transition-all duration-300"
              @click="openImageModal(checkin.image_url)"
            >
              <div class="absolute inset-0 bg-slate-900 opacity-0 group-hover:opacity-40 transition-opacity z-10 flex items-center justify-center">
                <Icon icon="lucide:maximize-2" class="size-6 text-white scale-0 group-hover:scale-100 transition-transform duration-300" />
              </div>
              <img 
                :src="checkin.image_url" 
                :alt="checkin.task.name"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!currentChallenge && completedChallenges.length === 0 && recentCheckins.length === 0" 
        class="bg-white/70 backdrop-blur-xl rounded-3xl border border-white/80 p-16 text-center shadow-xl shadow-slate-200/50">
        <div class="w-24 h-24 bg-slate-50 rounded-3xl flex items-center justify-center text-4xl mx-auto mb-6 text-slate-300">
            <Icon icon="lucide:ghost" class="size-12" />
        </div>
        <h3 class="text-2xl font-black text-slate-900 mb-2">Perfil ainda vazio</h3>
        <p class="text-slate-500 max-w-sm mx-auto leading-relaxed">
            Este aventureiro ainda não registrou desafios ou check-ins públicos. Volte em breve!
        </p>
      </div>
    </main>

    <!-- Image Modal -->
    <Transition name="fade">
        <div v-if="selectedImage" 
            class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[100] flex items-center justify-center p-4"
            @click="selectedImage = null"
        >
            <button class="absolute top-6 right-6 p-2 rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors z-[110]">
                <Icon icon="lucide:x" class="size-6" />
            </button>
            <img 
                :src="selectedImage" 
                alt="Check-in"
                class="max-w-full max-h-full object-contain rounded-3xl shadow-2xl border-2 border-white/10"
                @click.stop
            />
        </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import DopaHeaderWrapper from '@/components/DopaHeaderWrapper.vue'
import ProgressRing from '@/components/ProgressRing.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import { Icon } from '@iconify/vue'

const props = defineProps({
  profileUser: Object,
  completedChallenges: Array,
  currentChallenge: Object,
  recentCheckins: Array,
  stats: Object,
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user || null)
const isOwnProfile = computed(() => {
  return !!authUser.value && String(authUser.value.id) === String(props.profileUser?.id)
})

const selectedImage = ref(null)

const openImageModal = (imageUrl) => {
  selectedImage.value = imageUrl
}
const origin = typeof window !== 'undefined' ? window.location.origin : 'https://dopacheck.com.br'
const ogImageUrl = computed(() => {
  const avatarUrl = props.profileUser?.avatar
  return avatarUrl ? avatarUrl : `${origin}/images/og.png`
})

useSeoMetaTags({
  title: computed(() => props.profileUser?.name ? props.profileUser.name : 'Perfil Público'),
  description: computed(() => {
    if (!props.profileUser?.name) return undefined
    const u = props.profileUser?.username ? `@${props.profileUser.username}` : `#${props.profileUser.id}`
    return `Veja o perfil público de ${props.profileUser.name} (${u}) no DOPA Check e acompanhe desafios e check-ins.`
  }),

  ogTitle: computed(() => props.profileUser?.name ? `${props.profileUser.name} | DOPA Check` : 'DOPA Check'),
  ogDescription: computed(() => {
    if (!props.profileUser?.name) return undefined
    const u = props.profileUser?.username ? `@${props.profileUser.username}` : `#${props.profileUser.id}`
    return `Veja o perfil público de ${props.profileUser.name} (${u}) no DOPA Check e acompanhe desafios e check-ins.`
  }),
  ogUrl: computed(() => typeof window !== 'undefined' ? window.location.href : undefined),
  ogType: 'profile',
  ogImage: ogImageUrl,

  twitterTitle: computed(() => props.profileUser?.name ? `${props.profileUser.name} | DOPA Check` : 'DOPA Check'),
  twitterDescription: computed(() => {
    if (!props.profileUser?.name) return undefined
    const u = props.profileUser?.username ? `@${props.profileUser.username}` : `#${props.profileUser.id}`
    return `Veja o perfil público de ${props.profileUser.name} (${u}) no DOPA Check e acompanhe desafios e check-ins.`
  }),
  twitterCard: 'summary_large_image',
  twitterImage: ogImageUrl,
})
</script>

