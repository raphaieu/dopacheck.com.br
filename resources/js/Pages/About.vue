<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import Button from '@/components/ui/button/Button.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import WebLayout from '@/layouts/WebLayout.vue'

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  seo: {
    type: Object,
    default: () => null,
  },
})

useSeoMetaTags(props.seo)

const page = usePage()
const isLoggedIn = computed(() => !!page.props.auth?.user)
const registerHref = computed(() => (isLoggedIn.value ? '/dopa' : route('register')))

const problems = [
  'Check-ins perdidos no histórico',
  'Planilhas paralelas',
  'Aprovação manual',
  'Sem visão clara de progresso',
  'Energia forte no início, queda depois',
]

const beliefs = [
  'Visibilidade gera compromisso.',
  'Mensuração gera consistência.',
  'Consistência gera cultura.',
]

const produces = [
  'Check-in simples.',
  'Histórico organizado.',
  'Progresso mensurável.',
]

const audiences = [
  'Para quem decidiu levar consistência a sério.',
  'Para comunidades que querem estrutura.',
  'Para líderes que querem organização.',
  'Para indivíduos que querem progresso real.',
]

const visionItems = [
  'Relatórios inteligentes',
  'Análise de consistência',
  'Reconhecimento automático de padrões',
  'Insights coletivos',
]
</script>

<template>
  <WebLayout :can-login="canLogin" :can-register="canRegister">
    <div class="relative overflow-hidden">
      <!-- Decorative Blurs -->
      <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none"></div>
      <div class="absolute top-[20%] -right-[10%] w-[35%] h-[35%] bg-purple-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 2s"></div>
      <div class="absolute -bottom-[10%] left-[20%] w-[30%] h-[30%] bg-emerald-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 4s"></div>

      <!-- ===== HERO ===== -->
      <section class="relative py-20 sm:py-32">
        <div class="mx-auto max-w-4xl px-6 text-center">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-100 mb-8">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
            </span>
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600">Manifesto DOPA Check</span>
          </div>

          <h1 class="text-4xl font-black leading-tight tracking-tighter text-slate-900 sm:text-6xl lg:text-7xl uppercase">
            Disciplina não <span class="text-slate-400 font-light italic">nasce sozinha.</span>
            <br>
            <span class="bg-gradient-to-r from-blue-600 to-violet-600 bg-clip-text text-transparent">Ela se sustenta.</span>
          </h1>

          <p class="mt-8 text-lg sm:text-xl leading-relaxed text-slate-600 max-w-2xl mx-auto font-medium">
            O DOPA Check transforma compromisso em progresso visível — no individual e no coletivo. Menos fricção, mais execução.
          </p>
        </div>
      </section>

      <!-- ===== ORIGEM ===== -->
      <section class="py-20 bg-slate-50/30">
        <div class="mx-auto max-w-4xl px-6">
          <div class="bg-white/60 backdrop-blur-xl border border-white/80 rounded-[2.5rem] p-8 sm:p-12 shadow-2xl shadow-slate-200/50">
            <h2 class="text-2xl font-black tracking-tighter text-slate-900 sm:text-4xl uppercase mb-8">
              De onde <span class="text-blue-600">surgiu</span>
            </h2>

            <div class="space-y-6 text-lg sm:text-xl leading-relaxed text-slate-700">
              <p>
                O <span class="font-black">DOPA Check</span> nasceu dentro de comunidades reais. Grupos que queriam evoluir juntos através de desafios intensos, energia alta e compromisso público.
              </p>
              <p class="text-slate-500 font-medium italic">
                Mas sempre o mesmo padrão:
              </p>
            </div>

            <!-- Problem list -->
            <div class="mt-10 grid gap-4 sm:grid-cols-2">
              <div
                v-for="problem in problems"
                :key="problem"
                class="flex items-center gap-4 bg-white/50 border border-slate-100 p-5 rounded-2xl transition-all hover:border-blue-200 hover:shadow-lg hover:shadow-blue-500/5 group"
              >
                <div class="size-2 rounded-full bg-slate-300 group-hover:bg-blue-500 transition-colors"></div>
                <span class="text-sm font-black uppercase tracking-widest text-slate-600 group-hover:text-slate-900 transition-colors">{{ problem }}</span>
              </div>
            </div>

            <!-- Pivot statement -->
            <div class="mt-16 relative">
              <div class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b from-blue-600 to-violet-600 rounded-full"></div>
              <div class="pl-10">
                <p class="text-2xl sm:text-4xl font-black tracking-tighter text-slate-900 uppercase leading-none">
                  A disciplina existia.
                  <br>
                  <span class="text-slate-400">Faltava estrutura.</span>
                </p>
              </div>
            </div>

            <div class="mt-12 space-y-6 text-lg font-medium leading-relaxed text-slate-600">
              <p>
                O DOPA Check nasceu para resolver isso — sem mudar o comportamento do grupo, sem criar fricção ou obrigar ninguém a instalar mais um app.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== NO QUE ACREDITAMOS ===== -->
      <section class="py-20">
        <div class="mx-auto max-w-4xl px-6">
          <div class="text-center mb-16">
            <h2 class="text-3xl font-black tracking-tighter text-slate-900 sm:text-5xl uppercase">
              No que <span class="bg-gradient-to-r from-blue-600 to-violet-600 bg-clip-text text-transparent">acreditamos</span>
            </h2>
            <p class="mt-6 text-lg text-slate-500 font-medium uppercase tracking-widest">Estrutura sustenta o que a motivação não consegue.</p>
          </div>

          <div class="grid gap-6 md:grid-cols-3">
            <div
              v-for="(belief, index) in beliefs"
              :key="belief"
              class="relative bg-white/40 backdrop-blur-md border border-white/60 p-8 rounded-3xl shadow-xl shadow-slate-200/40 hover:scale-[1.02] transition-transform"
            >
              <div class="text-4xl font-black text-blue-600/20 mb-4">{{ String(index + 1).padStart(2, '0') }}</div>
              <p class="text-sm font-black uppercase tracking-[0.15em] text-slate-800 leading-relaxed">{{ belief }}</p>
            </div>
          </div>

          <div class="mt-16 text-center">
            <p class="text-lg sm:text-2xl font-black text-slate-900 uppercase tracking-tighter italic">
              "Disciplina individual é frágil.
              <span class="text-blue-600">O coletivo visível é inabalável.</span>"
            </p>
          </div>
        </div>
      </section>

      <!-- ===== O QUE É ===== -->
      <section class="py-20 bg-slate-50/30">
        <div class="mx-auto max-w-4xl px-6">
          <div class="bg-white/60 backdrop-blur-xl border border-white/80 rounded-[2.5rem] p-8 sm:p-12 shadow-2xl shadow-slate-200/50">
            <h2 class="text-2xl font-black tracking-tighter text-slate-900 sm:text-4xl uppercase mb-8">
              O que o DOPA Check <span class="text-blue-600">realmente é</span>
            </h2>

            <p class="text-xl leading-relaxed text-slate-700 font-medium">
              Não é apenas um habit tracker. É uma camada que <span class="text-blue-600">organiza disciplina</span>.
            </p>

            <div class="mt-12 grid gap-8 md:grid-cols-3">
              <div class="space-y-4">
                <div class="size-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm">
                  <Icon icon="lucide:target" class="size-6" />
                </div>
                <p class="text-sm font-black uppercase tracking-widest text-slate-900">Você assume um desafio</p>
              </div>
              <div class="space-y-4">
                <div class="size-12 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600 shadow-sm">
                  <Icon icon="lucide:pen-tool" class="size-6" />
                </div>
                <p class="text-sm font-black uppercase tracking-widest text-slate-900">Você registra o que faz</p>
              </div>
              <div class="space-y-4">
                <div class="size-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm">
                  <Icon icon="lucide:trending-up" class="size-6" />
                </div>
                <p class="text-sm font-black uppercase tracking-widest text-slate-900">Você acompanha sua evolução</p>
              </div>
            </div>

            <div class="mt-12 p-6 rounded-2xl bg-blue-50/50 border border-blue-100 flex flex-col sm:flex-row gap-6 items-center">
              <div class="text-center sm:text-left flex-1">
                <p class="text-lg font-black text-slate-900 uppercase tracking-tight">
                  Se estiver em grupo, a visibilidade acelera.
                  <br>
                  <span class="text-blue-600">Se estiver sozinho, o sistema sustenta.</span>
                </p>
              </div>
            </div>

            <ul class="mt-12 space-y-4">
              <li
                v-for="item in produces"
                :key="item"
                class="flex items-center gap-4 text-slate-800 font-black uppercase tracking-widest text-sm"
              >
                <div class="size-6 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                  <Icon icon="lucide:check" class="size-4" />
                </div>
                {{ item }}
              </li>
            </ul>

            <p class="mt-12 text-lg font-black text-slate-400 uppercase tracking-[0.2em] italic">
              Sem drama. Sem planilha.
            </p>
          </div>
        </div>
      </section>

      <!-- ===== PARA QUEM ===== -->
      <section class="py-20">
        <div class="mx-auto max-w-4xl px-6">
          <h2 class="text-3xl font-black tracking-tighter text-slate-900 sm:text-5xl uppercase text-center mb-16">
            Para <span class="bg-gradient-to-r from-blue-600 to-violet-600 bg-clip-text text-transparent">quem é</span>
          </h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="audience in audiences"
              :key="audience"
              class="group relative bg-white border border-slate-100 p-6 rounded-3xl transition-all hover:border-blue-200 hover:shadow-xl hover:shadow-blue-500/5"
            >
              <div class="flex items-center gap-4">
                <div class="size-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                  <Icon icon="lucide:users" class="size-5" />
                </div>
                <span class="text-xs font-black uppercase tracking-widest text-slate-600 group-hover:text-slate-900 transition-colors">{{ audience }}</span>
              </div>
            </div>
          </div>

          <div class="mt-16 flex justify-center">
            <div class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-slate-900 text-white font-black uppercase tracking-widest text-[10px] shadow-xl">
              <span>Funciona sozinho. Funciona melhor em grupo.</span>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== INDEPENDÊNCIA ===== -->
      <section class="py-20 bg-slate-50/30">
        <div class="mx-auto max-w-4xl px-6">
          <div class="bg-white/60 backdrop-blur-xl border border-white/80 rounded-[2.5rem] p-8 sm:p-12 shadow-2xl shadow-slate-200/50">
            <h2 class="text-2xl font-black tracking-tighter text-slate-900 sm:text-4xl uppercase mb-8">
              Independência
            </h2>

            <div class="space-y-6 text-lg sm:text-xl leading-relaxed text-slate-700">
              <p>O DOPA Check nasceu inspirado em comunidades, mas é um <span class="text-blue-600 font-black">produto independente</span>.</p>
              
              <div class="grid gap-4 mt-8">
                <div class="flex items-center gap-3 text-slate-600">
                  <div class="size-2 rounded-full bg-slate-300"></div>
                  <span class="text-xs font-black uppercase tracking-widest leading-none">Não pertence a nenhuma estrutura específica</span>
                </div>
                <div class="flex items-center gap-3 text-slate-600">
                  <div class="size-2 rounded-full bg-slate-300"></div>
                  <span class="text-xs font-black uppercase tracking-widest leading-none">Não depende de uma comunidade única</span>
                </div>
              </div>

              <div class="mt-10 p-6 bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl text-white">
                <p class="text-lg font-black uppercase tracking-tight">
                  É uma infraestrutura plugável para qualquer grupo — ou para você, individualmente.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== CONSTRUÇÃO + VISÃO ===== -->
      <section class="py-20">
        <div class="mx-auto max-w-4xl px-6">
          <div class="grid gap-12 md:grid-cols-2">
            <!-- Visão -->
            <div class="space-y-8">
              <h2 class="text-2xl font-black tracking-tighter text-slate-900 uppercase">
                Visão
              </h2>
              <div class="space-y-6 text-lg font-medium text-slate-700 leading-relaxed">
                <p>O futuro da disciplina não está em motivação constante. Está em sistemas que sustentam comportamento ao longo do tempo.</p>
                <div class="space-y-3 p-6 bg-blue-50/50 border border-blue-100 rounded-3xl">
                  <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 mb-2 italic">Princípios:</p>
                  <p class="text-sm font-black uppercase tracking-widest text-slate-900">Visibilidade gera compromisso.</p>
                  <p class="text-sm font-black uppercase tracking-widest text-slate-900">Mensuração gera consistência.</p>
                  <p class="text-sm font-black uppercase tracking-widest text-slate-900">Consistência gera cultura.</p>
                </div>
              </div>
            </div>

            <!-- Estamos construindo -->
            <div class="space-y-8">
              <h2 class="text-2xl font-black tracking-tighter text-slate-900 uppercase">
                Construindo
              </h2>
              <div class="grid gap-3">
                <div
                  v-for="item in visionItems"
                  :key="item"
                  class="flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100"
                >
                  <div class="size-2 rounded-full bg-blue-600"></div>
                  <span class="text-[11px] font-black uppercase tracking-widest text-slate-700">{{ item }}</span>
                </div>
              </div>
              <p class="text-lg font-black text-slate-900 uppercase tracking-tighter italic">
                Menos fricção. <span class="text-blue-600">Mais execução.</span>
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== CLOSING ===== -->
      <section class="py-32 relative overflow-hidden group">
        <!-- Dark immersive background -->
        <div class="absolute inset-0 bg-slate-950 -z-10"></div>
        <div class="absolute -top-1/4 -left-1/4 w-full h-full bg-blue-600/10 rounded-full blur-[120px] animate-pulse pointer-events-none"></div>
        <div class="absolute -bottom-1/4 -right-1/4 w-full h-full bg-purple-600/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 2s"></div>

        <div class="mx-auto max-w-4xl px-6 text-center">
          <h2 class="text-3xl font-black leading-tight tracking-tighter text-white sm:text-6xl uppercase mb-12">
            Funciona sozinho.
            <br>
            <span class="text-slate-500">Funciona melhor em</span>
            <span class="bg-gradient-to-r from-blue-400 to-violet-400 bg-clip-text text-transparent"> grupo.</span>
          </h2>

          <Button
            :as="Link"
            :href="registerHref"
            size="lg"
            class="h-16 px-10 rounded-2xl bg-white text-slate-950 hover:bg-slate-100 font-black uppercase tracking-[0.2em] text-sm shadow-2xl shadow-blue-500/20 active:scale-95 transition-all"
          >
            Começar Agora
            <Icon icon="lucide:arrow-right" class="ml-2 h-4 w-4" />
          </Button>

          <p class="mt-8 text-[10px] font-black text-slate-500 uppercase tracking-[0.4em]">
            Menos fricção. Mais execução.
          </p>
        </div>
      </section>

      <!-- ===== FOOTER ===== -->
      <footer class="py-12 border-t border-slate-100 relative">
        <div class="mx-auto max-w-6xl px-6">
          <div class="flex flex-col items-center gap-8">
            <div class="flex items-center gap-3">
              <div class="size-10 rounded-xl bg-gradient-to-br from-blue-600 via-violet-600 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-600/20">
                <span class="text-white text-xl select-none">🧠</span>
              </div>
              <span class="text-lg font-black tracking-tighter text-slate-900 uppercase">DOPA Check</span>
            </div>

            <nav class="flex flex-wrap justify-center gap-x-12 gap-y-4">
              <Link :href="route('home')" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-blue-600 transition-colors">Home</Link>
              <Link :href="route('policy.show')" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-blue-600 transition-colors">Privacidade</Link>
              <Link :href="route('terms.show')" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-blue-600 transition-colors">Termos</Link>
            </nav>

            <div class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 select-none">
              DOPA Check · Built for consistency.
            </div>
          </div>
        </div>
      </footer>
    </div>
  </WebLayout>
</template>
