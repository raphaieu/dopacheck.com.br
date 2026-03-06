<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import Accordion from '@/components/ui/accordion/Accordion.vue'
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue'
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue'
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue'
import Button from '@/components/ui/button/Button.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import WebLayout from '@/layouts/WebLayout.vue'

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  stats: {
    type: Object,
    default: () => ({
      completion_rate: 0,
      total_checkins: 0,
      total_users: 0,
      total_challenges: 0,
    }),
  },
  seo: {
    type: Object,
    default: () => null,
  },
})

useSeoMetaTags(props.seo)

const page = usePage()
const isLoggedIn = computed(() => !!page.props.auth?.user)
const registerHref = computed(() => (isLoggedIn.value ? '/dopa' : route('register')))
const proCtaHref = computed(() => (isLoggedIn.value ? route('subscriptions.create') : route('register')))

const avatars = [
  { initials: 'A', color: 'bg-violet-500' },
  { initials: 'C', color: 'bg-emerald-500' },
  { initials: 'M', color: 'bg-amber-500' },
  { initials: 'R', color: 'bg-blue-500' },
  { initials: 'L', color: 'bg-rose-500' },
]

const flowSteps = [
  { icon: 'lucide:target', title: 'Escolha um desafio (ou crie)', description: 'Defina o que conta como “feito” e por quantos dias.' },
  { icon: 'lucide:circle-check-big', title: 'Faça check-in diário (web ou WhatsApp)', description: 'Rápido o suficiente pra você não negociar com você mesmo.' },
  { icon: 'lucide:bar-chart-3', title: 'Veja seu progresso (e compartilhe se quiser)', description: 'Streak, histórico e taxa de conclusão — claros, sem achismo.' },
]

const progressHighlights = [
  { icon: 'lucide:flame', text: 'Streak (dias seguidos sem quebrar o ritmo)' },
  { icon: 'lucide:percent', text: 'Taxa de conclusão (por desafio e no geral)' },
  { icon: 'lucide:history', text: 'Histórico completo (sem “memória seletiva”)' },
  { icon: 'lucide:file-chart-column', text: 'Relatório por desafio (e evolução no tempo)' },
  { icon: 'lucide:globe', text: 'Perfil público opcional (quando fizer sentido)' },
]

const youVsGroup = [
  {
    title: 'Você',
    icon: 'lucide:user',
    items: [
      'Autonomia: você decide o desafio e o ritmo',
      'Rotina: check-in simples, todo dia',
      'Progresso: streak, histórico e conclusão em um lugar',
    ],
  },
  {
    title: 'Grupo',
    icon: 'lucide:users',
    items: [
      'Visibilidade: o progresso aparece (sem exposição obrigatória)',
      'Compromisso: quando o grupo enxerga, você continua',
      'Consistência: o combinado vira padrão — e fica mensurável',
    ],
  },
]

const freeFeatures = [
  '1 desafio ativo',
  'Check-in via web ou WhatsApp',
  'Dashboard básico',
  'Histórico de 90 dias',
]

const proFeatures = [
  'Desafios ilimitados',
  'Check-ins via web ou WhatsApp',
  'Dashboard completo',
  'Histórico completo',
  'Relatórios e exportação',
  'Perfil público completo',
  'Suporte prioritário',
]

const faqItems = [
  {
    value: 'item-1',
    title: 'Como funciona no WhatsApp?',
    content: 'Você faz check-in com foto + #hashtag. Pode ser no grupo (pra dar visibilidade) ou no privado com o bot (pra fazer sozinho). O DOPA Check registra, confirma e atualiza seu progresso.',
  },
  {
    value: 'item-2',
    title: 'Preciso instalar algum aplicativo?',
    content: 'Não. Você usa pela web. Se preferir, pode fazer check-in pelo WhatsApp também — sem app extra.',
  },
  {
    value: 'item-3',
    title: 'Funciona sem grupo?',
    content: 'Sim. Dá pra usar 100% individual: check-in na web (ou no WhatsApp no privado) e o progresso fica no seu dashboard.',
  },
  {
    value: 'item-4',
    title: 'Posso cancelar o plano PRO?',
    content: 'A qualquer momento. Sem fidelidade.',
  },
]

const billingCycle = ref('monthly')
const proMonthlyPrice = 11.9
const proYearlyPrice = 99

const formatBRL = (v) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v)

const proPrice = computed(() => (billingCycle.value === 'monthly' ? proMonthlyPrice : proYearlyPrice))
const proPeriodLabel = computed(() => (billingCycle.value === 'monthly' ? '/mês' : '/ano'))
const yearlySavings = computed(() => (proMonthlyPrice * 12) - proYearlyPrice)
const yearlyEquivalentMonthly = computed(() => proYearlyPrice / 12)

const formatInt = (v) => new Intl.NumberFormat('pt-BR').format(Number(v ?? 0))
</script>

<template>
  <WebLayout :can-login="canLogin" :can-register="canRegister">
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 overflow-x-clip">
      <!-- Decorative Blurs -->
      <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none -z-10">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-400/10 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute top-[20%] -right-[10%] w-[35%] h-[35%] bg-purple-400/10 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s"></div>
        <div class="absolute -bottom-[10%] left-[20%] w-[30%] h-[30%] bg-emerald-400/10 rounded-full blur-[120px] animate-pulse" style="animation-delay: 4s"></div>
      </div>

    <!-- ===== HERO ===== -->
    <section class="relative pt-32 pb-20 sm:pt-40 sm:pb-32">
      <div class="mx-auto max-w-6xl px-6">
        <div class="lg:grid lg:grid-cols-12 lg:items-center lg:gap-16">

          <div class="lg:col-span-6 relative z-10">
            <div
              class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50/50 backdrop-blur-md px-4 py-2 text-[13px] font-black uppercase tracking-widest text-blue-600 shadow-sm mb-8"
            >
              <Icon icon="lucide:sparkles" class="h-3.5 w-3.5" />
              Disciplina Visível & Mensurável
            </div>

            <h1 class="text-5xl font-black leading-[1.1] tracking-tight text-slate-900 md:text-7xl lg:text-8xl">
              Consistência 
              <br>
              é o novo 
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">superpoder</span>.
            </h1>

            <p class="mt-8 max-w-lg text-xl leading-relaxed text-slate-600 font-medium">
              Crie desafios, faça check-in diário e acompanhe sua evolução
              — sozinho ou com seu grupo.
              Sem motivação forçada: só estrutura e <span class="text-slate-900 font-black">progresso real</span>.
            </p>

            <div class="mt-10 flex flex-wrap items-center gap-5">
              <Button
                :as="Link"
                :href="registerHref"
                size="lg"
                class="h-14 px-8 rounded-2xl bg-slate-900 text-white shadow-2xl shadow-slate-900/20 transition-all duration-300 hover:scale-105 hover:bg-slate-800 active:scale-95 text-base font-black uppercase tracking-wider"
              >
                Começar Agora
                <Icon icon="lucide:arrow-right" class="ml-2 h-5 w-5" />
              </Button>

              <Button
                :as="Link"
                href="/challenges"
                variant="outline"
                size="lg"
                class="h-14 px-8 rounded-2xl border-slate-200 bg-white/50 backdrop-blur-md text-slate-700 transition-all duration-300 hover:bg-white hover:border-slate-300 hover:shadow-xl hover:shadow-slate-200/50 active:scale-95 text-base font-black uppercase tracking-wider"
              >
                Ver desafios
              </Button>
            </div>

            <div class="mt-10 flex items-center gap-4">
              <div class="flex -space-x-3">
                <div
                  v-for="(a, idx) in avatars"
                  :key="a.initials"
                  class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white text-xs font-black text-white shadow-lg transition-transform hover:scale-110 hover:z-20 cursor-default"
                  :class="a.color"
                  :style="`z-index: ${20 - idx}`"
                >
                  {{ a.initials }}
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-slate-100 text-[10px] font-black text-slate-600 shadow-lg z-0">
                  +2k
                </div>
              </div>
              <div class="flex flex-col">
                <p class="text-[13px] font-black text-slate-900 uppercase tracking-wider">
                  Comunidade Ativa
                </p>
                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-widest">
                  Beta aberto e gratuito
                </p>
              </div>
            </div>
          </div>

          <!-- WhatsApp Group Mock -->
          <div class="mt-20 lg:col-span-6 lg:mt-0 relative group">
            <!-- Glow Effect -->
            <div class="absolute -inset-10 bg-gradient-to-tr from-blue-500/10 via-purple-500/10 to-emerald-500/10 rounded-[3rem] blur-3xl opacity-50 group-hover:opacity-100 transition-opacity duration-1000"></div>
            
            <div class="relative mx-auto max-w-sm lg:max-w-md">
              <div
                class="relative overflow-hidden rounded-[2.5rem] border border-white/80 bg-white shadow-[0_32px_80px_-20px_rgba(0,0,0,0.12)] backdrop-blur-xl"
              >
                <!-- WhatsApp Header -->
                <div class="flex items-center gap-3 bg-slate-900 px-6 py-5">
                  <Icon icon="lucide:arrow-left" class="h-5 w-5 text-white/40" />
                  <div class="flex -space-x-2">
                    <div
                      v-for="a in avatars.slice(0, 3)"
                      :key="a.initials"
                      class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-slate-900 text-[10px] font-black text-white"
                      :class="a.color"
                    >
                      {{ a.initials }}
                    </div>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-black text-white uppercase tracking-wider">30 dias de Movimento</p>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">12 participantes · online</p>
                  </div>
                  <Icon icon="lucide:more-vertical" class="h-5 w-5 text-white/40" />
                </div>

                <!-- Chat Content -->
                <div class="space-y-4 bg-slate-50 p-6 min-h-[400px]">
                  <div class="flex justify-center mb-6">
                    <span class="px-3 py-1 bg-slate-200/50 text-[10px] font-black text-slate-500 rounded-full uppercase tracking-tighter">Hoje</span>
                  </div>

                  <div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                    <p class="mb-1.5 text-[10px] font-black text-violet-600 uppercase tracking-widest ml-1">Ana</p>
                    <div class="inline-block max-w-[85%] rounded-2xl rounded-tl-sm bg-white p-3 shadow-sm border border-slate-100">
                      <div
                        class="relative aspect-[4/3] w-56 rounded-xl overflow-hidden bg-slate-100 mb-2 group-inner"
                      >
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-purple-600/20 flex items-center justify-center">
                          <Icon icon="lucide:dumbbell" class="h-10 w-10 text-white drop-shadow-lg" />
                        </div>
                      </div>
                      <div class="flex items-center justify-between gap-10 px-1">
                        <span class="text-sm font-black text-slate-900">#gymtime</span>
                        <span class="text-[10px] text-slate-400 font-bold">09:14</span>
                      </div>
                    </div>
                  </div>

                  <div class="animate-in fade-in slide-in-from-bottom-4 duration-700 delay-300 fill-mode-both">
                    <p class="mb-1.5 text-[10px] font-black text-emerald-600 uppercase tracking-widest ml-1">🧠 DOPA Check</p>
                    <div class="inline-block max-w-[90%] rounded-2xl rounded-tl-sm bg-emerald-600 p-4 shadow-xl shadow-emerald-600/20">
                      <div class="flex items-start gap-3">
                        <div class="size-8 rounded-lg bg-white/20 flex items-center justify-center text-white">
                          <Icon icon="lucide:check-circle-2" class="size-5" />
                        </div>
                        <div>
                          <p class="text-[13px] font-bold text-white leading-relaxed">
                            Check-in confirmado para <span class="font-black text-emerald-100 underline decoration-emerald-300">Ana</span>!
                          </p>
                          <div class="mt-2 space-y-1">
                             <p class="text-[11px] text-emerald-100/90 font-medium">🔥 Streak: <span class="font-black text-white">14 dias</span></p>
                             <p class="text-[11px] text-emerald-100/90 font-medium">📊 Progresso: <span class="font-black text-white">67% do desafio</span></p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Footer Stats -->
                <div class="flex items-center justify-between border-t border-slate-200 bg-white px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="flex -space-x-2">
                      <div
                        v-for="a in avatars.slice(0, 4)"
                        :key="a.initials"
                        class="flex h-5 w-5 items-center justify-center rounded-full border-2 border-white text-[8px] font-black text-white shadow-sm"
                        :class="a.color"
                      >
                        {{ a.initials }}
                      </div>
                    </div>
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">5 de 12 check-ins hoje</span>
                  </div>
                  <div class="flex gap-1.5">
                    <div class="size-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                    <div class="size-1.5 rounded-full bg-blue-500/40"></div>
                    <div class="size-1.5 rounded-full bg-blue-500/20"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== COMO FUNCIONA ===== -->
    <section id="como-funciona" class="relative py-24 sm:py-32">
      <div class="mx-auto max-w-6xl px-6">
        <div class="mx-auto max-w-2xl text-center mb-20">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest mb-6 border border-blue-100 shadow-sm">
            Metodologia DOPA
          </div>
          <h2 class="text-4xl font-black tracking-tight text-slate-900 sm:text-5xl lg:text-6xl uppercase">
            Disciplina <br class="hidden sm:block"> <span class="text-blue-600">sem atrito.</span>
          </h2>
          <p class="mt-6 text-xl text-slate-600 font-medium leading-relaxed">
            Esqueça as notificações chatas e a culpa. O DOPA foca na visibilidade do seu progresso, porque o que você enxerga, você melhora.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
           <!-- Connection Lines (Desktop) -->
           <div class="hidden md:block absolute top-1/2 left-0 w-full h-px bg-slate-200 -z-10 -translate-y-12"></div>

          <div 
            v-for="(step, i) in flowSteps" 
            :key="i"
            class="group relative p-8 rounded-[2rem] bg-white/70 backdrop-blur-xl border border-white/80 shadow-xl shadow-slate-200/50 hover:shadow-blue-500/10 hover:-translate-y-2 transition-all duration-500"
          >
            <div class="absolute -top-4 -left-4 w-12 h-12 bg-slate-900 text-white rounded-2xl flex items-center justify-center text-lg font-black shadow-lg group-hover:scale-110 transition-transform duration-500">
              {{ i + 1 }}
            </div>
            
            <div
              class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 mb-8 group-hover:scale-110 transition-transform duration-500"
            >
              <Icon :icon="step.icon" class="h-8 w-8" />
            </div>
            
            <h3 class="text-xl font-black text-slate-900 mb-4 leading-snug uppercase tracking-tight">
              {{ step.title }}
            </h3>
            <p class="text-slate-600 font-medium leading-relaxed">
              {{ step.description }}
            </p>
          </div>
        </div>

        <div class="mt-20 p-8 rounded-[2.5rem] bg-slate-900 text-white overflow-hidden relative group">
          <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
          <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
          
          <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-10">
            <div class="max-w-xl text-center md:text-left">
              <h4 class="text-2xl font-black mb-4 uppercase tracking-tighter italic">Check-in via WhatsApp ou Web</h4>
              <p class="text-slate-400 font-medium text-lg leading-relaxed">
                No WhatsApp, é só enviar <span class="text-white font-black">foto + #hashtag</span>. Rápido e direto ao ponto. 
                Funciona para qualquer rotina: fitness, estudos, meditação ou trabalho.
              </p>
            </div>
            <div class="flex-shrink-0">
               <div class="inline-flex items-center gap-4 bg-white/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/10">
                  <Icon icon="lucide:message-circle" class="size-8 text-emerald-400" />
                  <div class="text-left font-black leading-tight uppercase tracking-widest text-sm">
                    Simple as <br> <span class="text-emerald-400 italic font-black">a message</span>
                  </div>
               </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== VOCÊ VS GRUPO ===== -->
    <section class="py-24 sm:py-32">
      <div class="mx-auto max-w-6xl px-6">
        <div class="mx-auto max-w-3xl text-center mb-16">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-50 text-violet-600 text-[10px] font-black uppercase tracking-widest mb-6 border border-violet-100 shadow-sm">
            Efeito Rede
          </div>
          <h2 class="text-4xl font-black tracking-tight text-slate-900 sm:text-5xl uppercase">
            Sozinho funciona. <br>
            <span class="text-violet-600 italic underline decoration-violet-200">Em grupo funciona melhor.</span>
          </h2>
          <p class="mt-6 text-xl text-slate-600 font-medium">
            Você é o protagonista. O grupo só acelera: dá visibilidade e aumenta o compromisso social que todos nós precisamos.
          </p>
        </div>

        <div class="mx-auto mt-14 grid max-w-5xl gap-8 lg:grid-cols-2">
          <div
            v-for="col in youVsGroup"
            :key="col.title"
            class="group relative rounded-[2.5rem] bg-white/70 backdrop-blur-xl border border-white/80 p-10 shadow-xl shadow-slate-200/50 hover:shadow-violet-500/10 transition-all duration-500"
          >
            <div class="flex items-center gap-5 mb-10">
              <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 group-hover:bg-violet-600 group-hover:text-white transition-colors duration-500">
                <Icon :icon="col.icon" class="h-8 w-8" />
              </div>
              <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic">{{ col.title }}</h3>
            </div>

            <ul class="space-y-6">
              <li v-for="item in col.items" :key="item" class="flex items-start gap-4">
                <div class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-50 text-violet-600">
                   <Icon icon="lucide:check" class="h-3.5 w-3.5" />
                </div>
                <span class="text-lg text-slate-700 font-medium leading-normal">{{ item }}</span>
              </li>
            </ul>

            <!-- Decorative corner -->
            <div class="absolute bottom-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
               <Icon :icon="col.icon" class="size-24" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== PROGRESSO VISÍVEL ===== -->
    <section class="relative py-24 sm:py-32 overflow-hidden">
      <!-- Decorative Background element -->
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-[60%] bg-blue-600/[0.02] -skew-y-3 -z-10"></div>
      
      <div class="mx-auto max-w-6xl px-6">
        <div class="items-center gap-20 lg:grid lg:grid-cols-2">
          <div class="relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest mb-6 border border-blue-100 shadow-sm">
              Progresso Visível
            </div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 sm:text-5xl lg:text-5xl uppercase leading-[1.1]">
              O que é visível, <br> vira
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">consistência.</span>
            </h2>
            <p class="mt-8 max-w-lg text-xl text-slate-600 font-medium leading-relaxed">
              Dashboard e relatórios detalhados para você saber exatamente onde está e o que precisa ajustar — sem achismo, sem desculpas.
            </p>
          </div>

          <div class="mt-16 space-y-8 lg:mt-0">
            <div
              v-for="item in progressHighlights"
              :key="item.text"
              class="group flex items-center gap-6 p-6 rounded-3xl bg-white/40 hover:bg-white/70 border border-white/50 hover:border-white shadow-sm hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300"
            >
              <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white shadow-lg text-blue-600 group-hover:scale-110 transition-transform duration-500"
              >
                <Icon :icon="item.icon" class="h-6 w-6" />
              </div>
              <p class="text-lg text-slate-700 font-black uppercase tracking-tight">{{ item.text }}</p>
            </div>
          </div>
        </div>

        <div class="mt-20 grid gap-6 sm:grid-cols-3">
          <div class="group h-full p-[1px] rounded-[2.5rem] bg-gradient-to-br from-white/80 to-slate-200/50 shadow-xl shadow-slate-200/50">
            <div class="h-full bg-white/70 backdrop-blur-xl rounded-[2.45rem] p-10 flex flex-col justify-between hover:bg-white transition-colors duration-500">
               <div class="size-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-10 group-hover:scale-110 transition-transform duration-500">
                  <Icon icon="lucide:check-circle" class="size-6" />
               </div>
               <div>
                  <div class="text-5xl font-black text-slate-900 tabular-nums mb-2 leading-none whitespace-nowrap">
                    {{ formatInt(stats.total_checkins) }}
                  </div>
                  <div class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Check-ins realizados</div>
               </div>
            </div>
          </div>

          <div class="group h-full p-[1px] rounded-[2.5rem] bg-gradient-to-br from-white/80 to-slate-200/50 shadow-xl shadow-slate-200/50">
            <div class="h-full bg-white/70 backdrop-blur-xl rounded-[2.45rem] p-10 flex flex-col justify-between hover:bg-white transition-colors duration-500">
               <div class="size-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-10 group-hover:scale-110 transition-transform duration-500">
                  <Icon icon="lucide:users" class="size-6" />
               </div>
               <div>
                  <div class="text-5xl font-black text-slate-900 tabular-nums mb-2 leading-none whitespace-nowrap">
                    {{ formatInt(stats.total_users) }}
                  </div>
                  <div class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Membros no Beta</div>
               </div>
            </div>
          </div>

          <div class="group h-full p-[1px] rounded-[2.5rem] bg-slate-900 shadow-2xl shadow-slate-900/40 relative overflow-hidden">
            <!-- Decorative light -->
            <div class="absolute -top-10 -right-10 size-40 bg-blue-500/20 rounded-full blur-3xl"></div>
            
            <div class="h-full p-10 flex flex-col justify-between relative z-10">
               <div class="size-12 rounded-2xl bg-white/10 text-white flex items-center justify-center mb-10 group-hover:scale-110 transition-transform duration-500">
                  <Icon icon="lucide:target" class="size-6" />
               </div>
               <div>
                  <div class="text-5xl font-black text-white tabular-nums mb-2 leading-none whitespace-nowrap">
                    {{ formatInt(stats.total_challenges) }}
                  </div>
                  <div class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Desafios ativos</div>
               </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== PRICING ===== -->
    <section id="pricing" class="relative py-24 sm:py-32 overflow-hidden">
      <!-- Background Decorations -->
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-slate-50/50 -z-10"></div>
      <div class="absolute top-1/4 -left-20 size-96 bg-blue-400/10 rounded-full blur-[100px]"></div>
      <div class="absolute bottom-1/4 -right-20 size-96 bg-purple-400/10 rounded-full blur-[100px]"></div>

      <div class="mx-auto max-w-6xl px-6">
        <div class="mx-auto max-w-2xl text-center mb-16">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-600 text-[10px] font-black uppercase tracking-widest mb-4 border border-violet-200 shadow-sm">
            Planos & Preços
          </div>
          <h2 class="text-4xl font-black tracking-tight text-slate-900 sm:text-5xl uppercase leading-[0.9]">
            Comece de graça. <br> <span class="text-slate-400">Evolua quando fizer sentido.</span>
          </h2>
        </div>

        <div class="flex justify-center mb-12">
          <div class="inline-flex items-center rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-md p-1.5 shadow-xl shadow-slate-200/50">
            <button
              type="button"
              class="rounded-xl px-8 py-3 text-xs font-black uppercase tracking-widest transition-all"
              :class="
                billingCycle === 'monthly'
                  ? 'bg-slate-900 text-white shadow-lg'
                  : 'text-slate-500 hover:text-slate-900'
              "
              @click="billingCycle = 'monthly'"
            >
              Mensal
            </button>
            <button
              type="button"
              class="rounded-xl px-8 py-3 text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2"
              :class="
                billingCycle === 'yearly'
                  ? 'bg-slate-900 text-white shadow-lg'
                  : 'text-slate-500 hover:text-slate-900'
              "
              @click="billingCycle = 'yearly'"
            >
              Anual
              <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[9px] font-black text-emerald-600 border border-emerald-500/20">
                −30%
              </span>
            </button>
          </div>
        </div>

        <div class="mx-auto grid max-w-5xl gap-8 lg:grid-cols-2 items-stretch">
          <!-- Free Plan -->
          <div
            class="group relative rounded-[2.5rem] border border-white bg-white/70 backdrop-blur-xl p-10 shadow-2xl shadow-slate-200/50 transition-all duration-500 hover:scale-[1.02] flex flex-col"
          >
            <div class="mb-8">
               <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-4">Plano Gratuito</h3>
               <div class="flex items-baseline gap-2">
                 <span class="text-5xl font-black tracking-tighter text-slate-900">R$&nbsp;0</span>
                 <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">/ sempre</span>
               </div>
               <p class="mt-4 text-slate-500 font-medium leading-relaxed">Ideal para validar sua rotina com o seu grupo inicial sem custos.</p>
            </div>

            <Button
              :as="Link"
              :href="registerHref"
              variant="outline"
              class="h-14 w-full rounded-2xl border-slate-200 text-slate-900 font-black uppercase tracking-widest text-xs transition-all hover:bg-slate-900 hover:text-white hover:border-slate-900"
            >
              Criar desafio agora
            </Button>

            <ul class="mt-10 space-y-4 flex-1">
              <li
                v-for="f in freeFeatures"
                :key="f"
                class="flex items-center gap-3 text-sm font-bold text-slate-700"
              >
                <div class="size-5 rounded-full bg-slate-100 flex items-center justify-center">
                  <Icon icon="lucide:check" class="h-3 w-3 text-slate-600" />
                </div>
                {{ f }}
              </li>
            </ul>
          </div>

          <!-- PRO Plan -->
          <div
            class="group relative rounded-[2.5rem] bg-slate-900 p-10 shadow-2xl shadow-slate-900/40 transition-all duration-500 hover:scale-[1.02] flex flex-col overflow-hidden"
          >
            <!-- Subtle Background Glow -->
            <div class="absolute -top-24 -right-24 size-64 bg-violet-600/10 rounded-full blur-[80px] pointer-events-none group-hover:bg-violet-600/20 transition-colors"></div>
            
            <div class="relative z-10 mb-8">
               <div class="flex items-center justify-between mb-4">
                  <h3 class="text-xs font-black text-violet-400 uppercase tracking-[0.3em]">Plano PRO</h3>
                  <span class="rounded-full bg-violet-600 px-3 py-1 text-[9px] font-black text-white uppercase tracking-widest shadow-lg shadow-violet-600/30">
                    Popular
                  </span>
               </div>
               <div class="flex items-baseline gap-2">
                 <span class="text-5xl font-black tracking-tighter text-white">
                   {{ formatBRL(proPrice) }}
                 </span>
                 <span class="text-sm font-bold text-slate-500 uppercase tracking-widest">/ {{ billingCycle === 'monthly' ? 'mês' : 'ano' }}</span>
               </div>
               <p
                  v-if="billingCycle === 'yearly'"
                  class="mt-2 text-xs font-black text-emerald-400 uppercase tracking-widest"
                >
                  Economize {{ formatBRL(yearlySavings) }}/ano
                </p>
               <p class="mt-4 text-slate-400 font-medium leading-relaxed">Para comunidades e líderes que levam a consistência a sério e querem dados profundos.</p>
            </div>

            <Button
              :as="Link"
              :href="proCtaHref"
              class="h-14 relative z-10 w-full rounded-2xl bg-violet-600 text-white font-black uppercase tracking-widest text-xs shadow-xl shadow-violet-600/20 transition-all hover:bg-violet-500 hover:scale-[1.02] active:scale-95"
            >
              Assinar PRO
            </Button>

            <p class="mt-4 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest relative z-10">Sem fidelidade. Cancele quando quiser.</p>

            <ul class="mt-8 space-y-4 flex-1 relative z-10">
              <li
                v-for="f in proFeatures"
                :key="f"
                class="flex items-center gap-3 text-sm font-bold text-slate-300"
              >
                <div class="size-5 rounded-full bg-white/10 flex items-center justify-center">
                   <Icon icon="lucide:check" class="h-3 w-3 text-violet-400" />
                </div>
                {{ f }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="py-24 sm:py-32 relative overflow-hidden">
      <div class="mx-auto max-w-6xl px-6 relative z-10">
        <div class="mx-auto max-w-2xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest mb-6 border border-slate-200 shadow-sm">
            Dúvidas Frequentes
          </div>
          <h2 class="text-4xl font-black tracking-tight text-slate-900 sm:text-5xl uppercase mb-12">
            Perguntas <br class="sm:hidden"> <span class="text-slate-400">frequentes.</span>
          </h2>

          <Accordion
            type="single"
            class="w-full space-y-4"
            collapsible
            default-value="item-1"
          >
            <AccordionItem
              v-for="item in faqItems"
              :key="item.value"
              :value="item.value"
              class="border-slate-200 bg-white/50 backdrop-blur-sm rounded-2xl px-6 border hover:border-blue-200 transition-colors duration-300 shadow-sm"
            >
              <AccordionTrigger
                class="text-left text-lg font-black text-slate-900 transition-colors hover:text-blue-600 data-[state=open]:text-blue-600 uppercase tracking-tight py-6"
              >
                {{ item.title }}
              </AccordionTrigger>
              <AccordionContent class="text-lg leading-relaxed text-slate-600 font-medium pb-6 border-t border-slate-50 pt-4">
                {{ item.content }}
              </AccordionContent>
            </AccordionItem>
          </Accordion>
        </div>
      </div>
    </section>

    <!-- ===== CTA FINAL ===== -->
<section class="relative overflow-hidden py-24 sm:py-32 bg-[radial-gradient(circle_at_top,_#101935_15%,_#0f172b_60%,_#050912_100%)]">
  <!-- base de profundidade -->
  <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(255,255,255,0.02),transparent_55%,transparent_90%,rgba(80,120,255,0.04))] -z-20"></div>

  <!-- glow sutil central -->
  <div class="absolute left-1/2 top-[22%] -translate-x-1/2 w-[680px] h-[320px] bg-blue-500/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>
  <div class="absolute left-1/2 top-[28%] -translate-x-1/2 w-[520px] h-[240px] bg-violet-500/8 blur-[120px] rounded-full pointer-events-none -z-10"></div>

  <div class="mx-auto max-w-4xl px-6 text-center relative z-10">
    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 text-white/80 text-[10px] font-black uppercase tracking-[0.28em] mb-6 border border-white/10 backdrop-blur-sm">
      Pronto para começar de verdade?
    </div>

    <h2 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight uppercase leading-[0.95] mb-6">
      <span class="text-white/90">Pare de recomeçar.</span><br />
      <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 via-blue-400 to-violet-400 italic">
        Comece a sustentar.
      </span>
    </h2>

    <p class="text-base sm:text-xl text-slate-200/90 mb-10 max-w-2xl mx-auto leading-relaxed">
      Crie seu desafio, acompanhe seu progresso e mantenha a consistência com muito menos fricção.
      <span class="text-white font-semibold"> Grátis para começar.</span>
    </p>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-10">
      <Button
        :as="Link"
        :href="registerHref"
        size="lg"
        class="h-16 w-full sm:w-auto px-10 rounded-2xl bg-white text-slate-950 shadow-[0_12px_40px_-12px_rgba(255,255,255,0.28)] transition-all duration-300 hover:scale-[1.02] hover:bg-slate-100 active:scale-95 text-base sm:text-lg font-black uppercase tracking-[0.16em] border border-white/20"
      >
        Criar meu desafio
        <Icon icon="lucide:zap" class="ml-2 h-5 w-5 text-blue-600" />
      </Button>

      <Link
        href="/challenges"
        class="group h-16 px-8 flex items-center justify-center gap-3 text-sm sm:text-base font-black text-white/95 transition-all uppercase tracking-[0.16em] border border-white/15 rounded-2xl bg-white/[0.03] hover:bg-white/[0.06] hover:text-white hover:border-white/30 w-full sm:w-auto"
      >
        Explorar desafios
        <Icon icon="lucide:arrow-right" class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
      </Link>
    </div>

    <p class="text-sm text-slate-300/70 max-w-xl mx-auto">
      Sem cartão de crédito. Comece sozinho ou com seu grupo.
    </p>
  </div>
</section>

    <!-- ===== FOOTER ===== -->
    <footer class="bg-slate-900 border-t border-white/5 py-16">
      <div class="mx-auto max-w-6xl px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
           <div class="md:col-span-2">
              <div class="flex items-center gap-3 mb-6">
                 <div class="size-10 rounded-xl bg-gradient-to-br from-blue-600 via-violet-600 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-600/20">
                    <span class="text-white text-xl select-none">🧠</span>
                 </div>
                 <span class="text-xl font-black text-white tracking-tighter uppercase">DOPA Check</span>
              </div>
              <p class="text-slate-500 font-medium leading-relaxed max-w-xs">
                 A metodologia visível para quem leva disciplina e consistência a sério. Sozinho ou em grupo.
              </p>
           </div>
           
           <div>
              <h4 class="text-sm font-black text-white uppercase tracking-widest mb-6">Plataforma</h4>
              <ul class="space-y-4">
                 <li><Link href="/challenges" class="text-slate-500 hover:text-white transition-colors font-medium">Explorar Desafios</Link></li>
                 <li><Link href="/register" class="text-slate-500 hover:text-white transition-colors font-medium">Criar Conta</Link></li>
                 <li><Link href="/login" class="text-slate-500 hover:text-white transition-colors font-medium">Fazer Login</Link></li>
              </ul>
           </div>

           <div>
              <h4 class="text-sm font-black text-white uppercase tracking-widest mb-6">Legal</h4>
              <ul class="space-y-4">
                 <li><Link :href="route('policy.show')" class="text-slate-500 hover:text-white transition-colors font-medium">Privacidade</Link></li>
                 <li><Link :href="route('terms.show')" class="text-slate-500 hover:text-white transition-colors font-medium">Termos de Uso</Link></li>
                 <li><Link :href="route('about')" class="text-slate-500 hover:text-white transition-colors font-medium">Sobre Nós</Link></li>
              </ul>
           </div>
        </div>

        <div class="flex flex-col items-center justify-between gap-6 pt-12 border-t border-white/5 sm:flex-row">
          <p class="text-xs text-slate-600 font-bold uppercase tracking-widest">
            © {{ new Date().getFullYear() }} DOPA Check · Built for consistency.
          </p>
          <div class="flex items-center gap-6">
             <a href="https://instagram.com/dopacheck" target="_blank" class="text-slate-600 hover:text-white transition-colors"><Icon icon="lucide:instagram" class="size-5" /></a>
             <a href="#" class="text-slate-600 hover:text-white transition-colors"><Icon icon="lucide:twitter" class="size-5" /></a>
             <a href="#" class="text-slate-600 hover:text-white transition-colors"><Icon icon="lucide:github" class="size-5" /></a>
          </div>
        </div>
      </div>
    </footer>
    </div>
  </WebLayout>
</template>
