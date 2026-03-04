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

    <!-- ===== HERO ===== -->
    <section class="relative overflow-hidden py-12">
      <div class="mx-auto max-w-6xl px-6">
        <div class="lg:grid lg:grid-cols-12 lg:items-center lg:gap-16">

          <div class="lg:col-span-6">
            <div
              class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1.5 text-[13px] font-medium text-emerald-700"
            >
              <Icon icon="lucide:message-circle" class="h-3.5 w-3.5" />
              Estrutura + mensuração + visibilidade
            </div>

            <h1 class="mt-8 text-4xl font-bold leading-tight tracking-tight text-slate-900 md:text-6xl">
              Disciplina funciona melhor
              <br>
              quando é
              <span class="text-violet-600">visível</span>.
            </h1>

            <p class="mt-6 max-w-lg text-lg leading-relaxed text-slate-700">
              Crie desafios, faça check-in diário e acompanhe sua evolução
              — sozinho ou com seu grupo.
              Sem motivação forçada: só estrutura e progresso registrado.
            </p>

            <div class="mt-8 flex items-center gap-3">
              <div class="flex -space-x-2">
                <div
                  v-for="a in avatars"
                  :key="a.initials"
                  class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white text-xs font-bold text-white"
                  :class="a.color"
                >
                  {{ a.initials }}
                </div>
              </div>
              <p class="text-sm text-slate-600">
                Comunidades em beta ativo
              </p>
            </div>

            <div class="mt-10 flex flex-wrap items-center gap-4">
              <Button
                :as="Link"
                :href="registerHref"
                size="lg"
                class="rounded-xl bg-violet-600 px-7 text-white shadow-[0_20px_60px_-15px_rgba(124,58,237,0.35)] transition-all duration-200 hover:bg-violet-700 hover:shadow-[0_20px_60px_-15px_rgba(124,58,237,0.5)]"
              >
                Criar desafio
                <Icon icon="lucide:arrow-right" class="ml-1 h-4 w-4" />
              </Button>

              <Button
                :as="Link"
                href="/challenges"
                variant="outline"
                size="lg"
                class="rounded-xl border-slate-200 px-7 text-slate-700 transition-colors hover:border-slate-300 hover:bg-slate-50"
              >
                Ver desafios
              </Button>

              <a
                href="#como-funciona"
                class="text-sm font-medium text-slate-500 transition-colors hover:text-violet-600"
              >
                Entenda o fluxo no WhatsApp
              </a>
            </div>

            <p class="mt-4 text-sm text-slate-500">
              Beta aberto. Gratuito por enquanto.
            </p>
          </div>

          <!-- WhatsApp Group Mock -->
          <div class="mt-16 lg:col-span-6 lg:mt-0">
            <div class="relative mx-auto max-w-sm lg:max-w-none">
              <div class="absolute -inset-6 rounded-4xl bg-emerald-500/6 blur-3xl" />

              <div
                class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-[0_20px_60px_-15px_rgba(0,0,0,0.15)]"
              >
                <div class="flex items-center gap-3 bg-emerald-700 px-4 py-3">
                  <Icon icon="lucide:arrow-left" class="h-5 w-5 text-white/60" />
                  <div class="flex -space-x-1.5">
                    <div
                      v-for="a in avatars.slice(0, 3)"
                      :key="a.initials"
                      class="flex h-6 w-6 items-center justify-center rounded-full border-[1.5px] border-emerald-700 text-[9px] font-bold text-white"
                      :class="a.color"
                    >
                      {{ a.initials }}
                    </div>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white">30 dias de Movimento</p>
                    <p class="text-[11px] text-emerald-200">12 participantes</p>
                  </div>
                </div>

                <div class="space-y-3 bg-[#f0f2f5] p-4">
                  <div>
                    <p class="mb-1 text-[11px] font-semibold text-violet-600">Ana</p>
                    <div class="inline-block max-w-[85%] rounded-lg rounded-tl-sm bg-white p-2.5 shadow-sm">
                      <div
                        class="flex h-28 w-44 items-center justify-center rounded bg-slate-100"
                      >
                        <Icon icon="lucide:image" class="h-8 w-8 text-slate-300" />
                      </div>
                      <div class="mt-1.5 flex items-end justify-between gap-6">
                        <span class="text-sm text-slate-800">#gymtime</span>
                        <span class="text-[10px] text-slate-400">9:14</span>
                      </div>
                    </div>
                  </div>

                  <div>
                    <p class="mb-1 text-[11px] font-semibold text-emerald-700">🧠 DOPA Check</p>
                    <div class="inline-block max-w-[85%] rounded-lg rounded-tl-sm bg-emerald-50 p-2.5 shadow-sm">
                      <p class="text-[13px] leading-relaxed text-slate-800">
                        ✅ Check-in:
                        <span class="font-semibold">Ana</span>
                        <br>
                        🔥 Streak:
                        <span class="font-semibold text-amber-600">14 dias</span>
                        <br>
                        📊 Dia 14 de 21 · 67%
                      </p>
                      <p class="mt-1 text-right text-[10px] text-slate-400">9:14</p>
                    </div>
                  </div>

                  <div>
                    <p class="mb-1 text-[11px] font-semibold text-blue-600">Carlos</p>
                    <div class="inline-block max-w-[85%] rounded-lg rounded-tl-sm bg-white p-2.5 shadow-sm">
                      <div
                        class="flex h-28 w-44 items-center justify-center rounded bg-slate-100"
                      >
                        <Icon icon="lucide:image" class="h-8 w-8 text-slate-300" />
                      </div>
                      <div class="mt-1.5 flex items-end justify-between gap-6">
                        <span class="text-sm text-slate-800">#gymtime</span>
                        <span class="text-[10px] text-slate-400">9:18</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 bg-white px-4 py-2.5">
                  <div class="flex -space-x-1.5">
                    <div
                      v-for="a in avatars.slice(0, 4)"
                      :key="a.initials"
                      class="flex h-5 w-5 items-center justify-center rounded-full border-[1.5px] border-white text-[8px] font-bold text-white"
                      :class="a.color"
                    >
                      {{ a.initials }}
                    </div>
                  </div>
                  <span class="text-[11px] text-slate-500">5 de 12 check-ins hoje</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== COMO FUNCIONA ===== -->
    <section id="como-funciona" class="border-t border-slate-100 bg-slate-50/60 py-20">
      <div class="mx-auto max-w-6xl px-6">
        <div class="mx-auto max-w-2xl text-center">
          <p class="text-[13px] font-semibold uppercase tracking-widest text-emerald-600">
            Como funciona (sem drama)
          </p>
          <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            Três passos. Todo dia.
          </h2>
          <p class="mt-3 text-lg text-slate-600">
            Comece no individual. Se quiser, traga o grupo pra aumentar o compromisso.
          </p>
        </div>

        <div class="mx-auto mt-16 max-w-3xl">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:gap-0">
            <template v-for="(step, i) in flowSteps" :key="i">
              <div
                v-if="i > 0"
                class="flex items-center justify-center py-1 sm:px-3 sm:py-0 sm:pt-5"
              >
                <Icon icon="lucide:chevron-down" class="h-4 w-4 text-slate-300 sm:hidden" />
                <Icon icon="lucide:chevron-right" class="hidden h-4 w-4 text-slate-300 sm:block" />
              </div>

              <div class="flex-1 text-center">
                <div
                  class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                >
                  <Icon :icon="step.icon" class="h-5 w-5" />
                </div>
                <p class="mt-3 text-sm font-semibold text-slate-900">{{ step.title }}</p>
                <p class="mt-1 text-[13px] leading-relaxed text-slate-600">{{ step.description }}</p>
              </div>
            </template>
          </div>
        </div>

        <p class="mx-auto mt-14 max-w-2xl text-center text-sm leading-relaxed text-slate-600">
          Você pode fazer check-in na web ou no WhatsApp.
          No WhatsApp, é foto + #hashtag (no grupo ou no privado com o bot).
          Funciona pra qualquer rotina — fitness, estudo, fé, trabalho, família.
        </p>
      </div>
    </section>

    <!-- ===== VOCÊ VS GRUPO ===== -->
    <section class="py-20">
      <div class="mx-auto max-w-6xl px-6">
        <div class="mx-auto max-w-3xl text-center">
          <p class="text-[13px] font-semibold uppercase tracking-widest text-violet-600">
            Onde entra o grupo
          </p>
          <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            Sozinho funciona.
            <span class="text-violet-600">Em grupo funciona melhor.</span>
          </h2>
          <p class="mt-3 text-lg text-slate-600">
            Você é o protagonista. O grupo só acelera: dá visibilidade e aumenta o compromisso.
          </p>
        </div>

        <div class="mx-auto mt-14 grid max-w-5xl gap-6 lg:grid-cols-2">
          <div
            v-for="col in youVsGroup"
            :key="col.title"
            class="rounded-2xl border border-slate-200 bg-white p-8"
          >
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                <Icon :icon="col.icon" class="h-5 w-5" />
              </div>
              <h3 class="text-lg font-semibold text-slate-900">{{ col.title }}</h3>
            </div>

            <ul class="mt-6 space-y-3 text-sm text-slate-700">
              <li v-for="item in col.items" :key="item" class="flex items-start gap-3">
                <Icon icon="lucide:check" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                <span>{{ item }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== PROGRESSO VISÍVEL ===== -->
    <section class="border-t border-slate-100 bg-slate-50/60 py-20">
      <div class="mx-auto max-w-6xl px-6">
        <div class="items-center gap-16 lg:grid lg:grid-cols-2">
          <div>
            <p class="text-[13px] font-semibold uppercase tracking-widest text-violet-600">
              Progresso visível
            </p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
              O que fica visível vira
              <br>
              <span class="text-violet-600">consistência</span>.
            </h2>
            <p class="mt-4 max-w-lg text-lg text-slate-600">
              Dashboard e relatórios pra você saber onde está — e ajustar o que precisa, sem drama.
            </p>
          </div>

          <div class="mt-10 space-y-6 lg:mt-0">
            <div
              v-for="item in progressHighlights"
              :key="item.text"
              class="flex items-start gap-4"
            >
              <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
              >
                <Icon :icon="item.icon" class="h-5 w-5" />
              </div>
              <p class="pt-2 text-lg text-slate-700">{{ item.text }}</p>
            </div>
          </div>
        </div>

        <div class="mt-14 grid gap-4 sm:grid-cols-3">
          <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <p class="text-xs font-medium text-slate-500">Atividade real</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">
              {{ formatInt(stats.total_checkins) }}
            </p>
            <p class="mt-1 text-sm text-slate-600">check-ins registrados</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <p class="text-xs font-medium text-slate-500">Atividade real</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">
              {{ formatInt(stats.total_users) }}
            </p>
            <p class="mt-1 text-sm text-slate-600">pessoas no beta</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <p class="text-xs font-medium text-slate-500">Atividade real</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">
              {{ formatInt(stats.total_challenges) }}
            </p>
            <p class="mt-1 text-sm text-slate-600">desafios criados</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== PRICING ===== -->
    <section id="pricing" class="hidden border-t border-slate-100 bg-slate-50/60 py-20">
      <div class="mx-auto max-w-6xl px-6">
        <div class="mx-auto max-w-2xl text-center">
          <p class="text-[13px] font-semibold uppercase tracking-widest text-violet-600">
            Planos
          </p>
          <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            Comece de graça. Evolua quando fizer sentido.
          </h2>
        </div>

        <div class="mt-10 flex justify-center">
          <div class="inline-flex items-center rounded-full border border-slate-200 bg-white p-1">
            <button
              type="button"
              class="rounded-full px-5 py-2 text-sm font-medium transition-all"
              :class="
                billingCycle === 'monthly'
                  ? 'bg-slate-900 text-white shadow-sm'
                  : 'text-slate-500 hover:text-slate-700'
              "
              @click="billingCycle = 'monthly'"
            >
              Mensal
            </button>
            <button
              type="button"
              class="rounded-full px-5 py-2 text-sm font-medium transition-all"
              :class="
                billingCycle === 'yearly'
                  ? 'bg-slate-900 text-white shadow-sm'
                  : 'text-slate-500 hover:text-slate-700'
              "
              @click="billingCycle = 'yearly'"
            >
              Anual
              <span class="ml-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                −30%
              </span>
            </button>
          </div>
        </div>

        <div class="mx-auto mt-12 grid max-w-4xl gap-8 sm:grid-cols-2">
          <div
            class="rounded-2xl border border-slate-200 bg-white p-8 transition-shadow hover:shadow-[0_20px_60px_-15px_rgba(0,0,0,0.08)]"
          >
            <h3 class="text-lg font-semibold text-slate-900">Gratuito</h3>
            <p class="mt-1 text-sm text-slate-500">Para validar com seu grupo.</p>

            <div class="mt-6 flex items-baseline gap-1">
              <span class="text-4xl font-bold tracking-tight text-slate-900">R$&nbsp;0</span>
              <span class="text-sm text-slate-500">para sempre</span>
            </div>

            <Button
              :as="Link"
              :href="registerHref"
              variant="outline"
              class="mt-8 w-full rounded-xl border-slate-200 text-slate-700 transition-colors hover:border-slate-300 hover:bg-slate-50"
            >
              Criar desafio
            </Button>

            <ul class="mt-8 space-y-3.5">
              <li
                v-for="f in freeFeatures"
                :key="f"
                class="flex items-center gap-3 text-sm text-slate-700"
              >
                <Icon icon="lucide:check" class="h-4 w-4 shrink-0 text-slate-400" />
                {{ f }}
              </li>
            </ul>
          </div>

          <div
            class="relative rounded-2xl bg-slate-900 p-8 transition-shadow hover:shadow-[0_20px_60px_-15px_rgba(124,58,237,0.2)]"
          >
            <div class="absolute -top-3 left-6">
              <span
                class="rounded-full bg-violet-600 px-3 py-1 text-xs font-semibold text-white shadow-lg shadow-violet-600/30"
              >
                Para líderes de comunidade
              </span>
            </div>

            <h3 class="text-lg font-semibold text-white">PRO</h3>
            <p class="mt-1 text-sm text-slate-400">Para comunidades que levam consistência a sério.</p>

            <div class="mt-6 flex items-baseline gap-1">
              <span class="text-4xl font-bold tracking-tight text-white">
                {{ formatBRL(proPrice) }}
              </span>
              <span class="text-sm text-slate-400">{{ proPeriodLabel }}</span>
            </div>

            <p
              v-if="billingCycle === 'yearly'"
              class="mt-2 text-sm text-emerald-400"
            >
              Economize {{ formatBRL(yearlySavings) }}/ano
              · {{ formatBRL(yearlyEquivalentMonthly) }}/mês
            </p>

            <Button
              :as="Link"
              :href="proCtaHref"
              class="mt-8 w-full rounded-xl bg-violet-600 text-white shadow-lg shadow-violet-600/25 transition-all hover:bg-violet-500 hover:shadow-xl hover:shadow-violet-600/30"
            >
              Assinar PRO
            </Button>

            <p class="mt-3 text-center text-xs text-slate-500">Sem fidelidade. Cancele quando quiser.</p>

            <ul class="mt-6 space-y-3.5">
              <li
                v-for="f in proFeatures"
                :key="f"
                class="flex items-center gap-3 text-sm text-slate-300"
              >
                <Icon icon="lucide:check" class="h-4 w-4 shrink-0 text-violet-400" />
                {{ f }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="py-20">
      <div class="mx-auto max-w-6xl px-6">
        <div class="mx-auto max-w-2xl">
          <p class="text-[13px] font-semibold uppercase tracking-widest text-violet-600">
            FAQ
          </p>
          <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            Perguntas frequentes
          </h2>

          <Accordion
            type="single"
            class="mt-12 w-full"
            collapsible
            default-value="item-1"
          >
            <AccordionItem
              v-for="item in faqItems"
              :key="item.value"
              :value="item.value"
              class="border-slate-200"
            >
              <AccordionTrigger
                class="text-left text-base font-medium text-slate-900 transition-colors hover:text-violet-600 data-[state=open]:text-violet-600"
              >
                {{ item.title }}
              </AccordionTrigger>
              <AccordionContent class="leading-relaxed text-slate-600">
                {{ item.content }}
              </AccordionContent>
            </AccordionItem>
          </Accordion>
        </div>
      </div>
    </section>

    <!-- ===== CTA FINAL ===== -->
    <section class="border-t border-slate-100 bg-slate-50/60 py-20">
      <div class="mx-auto max-w-2xl px-6 text-center">
        <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
          Transforme disciplina em progresso visível.
        </h2>
        <p class="mt-4 text-lg text-slate-700">
          Comece sozinho ou com seu grupo. Gratuito pra começar. Sem cartão.
        </p>
        <Button
          :as="Link"
          :href="registerHref"
          size="lg"
          class="mt-10 rounded-xl bg-violet-600 px-8 text-white shadow-[0_20px_60px_-15px_rgba(124,58,237,0.35)] transition-all duration-200 hover:bg-violet-700 hover:shadow-[0_20px_60px_-15px_rgba(124,58,237,0.5)]"
        >
          Criar desafio
          <Icon icon="lucide:arrow-right" class="ml-1 h-4 w-4" />
        </Button>
        <p class="mt-4 text-sm text-slate-500">
          Beta aberto. Feedback direto com o time.
        </p>
        <Link
          href="/challenges"
          class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-violet-600"
        >
          Ver desafios
          <Icon icon="lucide:arrow-right" class="h-3.5 w-3.5" />
        </Link>
        <a
          href="#como-funciona"
          class="mt-3 block text-xs font-medium text-slate-400 transition-colors hover:text-violet-600"
        >
          Entenda o fluxo no WhatsApp
        </a>
      </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="border-t border-slate-100">
      <div class="mx-auto max-w-6xl px-6 py-8">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
          <p class="text-sm text-slate-500">
            © {{ new Date().getFullYear() }} DOPA Check
          </p>
          <div class="flex items-center gap-6 text-sm">
            <Link
              :href="route('about')"
              class="font-medium text-slate-600 transition-colors hover:text-slate-800"
            >
              Sobre
            </Link>
            <Link
              :href="route('policy.show')"
              class="text-slate-500 transition-colors hover:text-slate-800"
            >
              Privacidade
            </Link>
            <Link
              :href="route('terms.show')"
              class="text-slate-500 transition-colors hover:text-slate-800"
            >
              Termos
            </Link>
          </div>
        </div>
      </div>
    </footer>
  </WebLayout>
</template>
