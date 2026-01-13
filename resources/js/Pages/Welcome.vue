<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import FeaturesCard from '@/components/FeaturesCard.vue'
import Accordion from '@/components/ui/accordion/Accordion.vue'
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue'
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue'
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import Button from '@/components/ui/button/Button.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import WebLayout from '@/layouts/WebLayout.vue'

const props = defineProps({
  canLogin: {
    type: Boolean,
  },
  canRegister: {
    type: Boolean,
  },
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

const features = [
  {
    icon: '🎯',
    title: 'Desafios que viram rotina',
    description: 'Crie seu desafio (ou entre em um) e transforme intenção em hábito com metas simples e claras.',
  },
  {
    icon: '✅',
    title: 'Check-in em segundos',
    description: 'Registre seu progresso diariamente sem fricção - rápido, simples e direto ao ponto.',
  },
  {
    icon: '🔥',
    title: 'Streak que motiva',
    description: 'Acompanhe sua sequência e mantenha consistência. Um dia de cada vez, com foco no longo prazo.',
  },
  {
    icon: '📊',
    title: 'Evolução visível',
    description: 'Dashboard com taxa de conclusão, histórico e progresso do desafio. Saiba exatamente onde você está.',
  },
  {
    icon: '🌎',
    title: 'Perfil público compartilhável',
    description: 'Mostre sua jornada (se quiser). Um perfil público para inspirar e criar compromisso.',
  },
  {
    icon: '🏆',
    title: 'Desafios em comunidade',
    description: 'Entre em desafios, veja participantes e ganhe motivação extra com o efeito “vamos juntos”.',
  },
]

const pricingFeatures = [
  '1 desafio ativo',
  'Check-ins manuais',
  'Histórico de 90 dias',
  'Dashboard básico',
  'Perfil público básico',
  'Suporte por email',
]

const proFeatures = [
  'Desafios ilimitados',
  'Check-ins ilimitados',
  'Histórico completo',
  'Dashboard avançado',
  'Perfil público completo',
  'Relatórios e exportação',
  'Suporte prioritário',
  'Acesso antecipado a novidades',
]

const faqItems = [
  {
    value: 'item-1',
    title: 'O que é um desafio no DOPA Check?',
    content: 'É uma meta com duração e rotina (ex: “21 dias de leitura”). Você faz check-in diariamente e acompanha sua evolução no dashboard.',
  },
  {
    value: 'item-2',
    title: 'Posso cancelar o PRO quando quiser?',
    content: 'Sim. Você pode cancelar a assinatura a qualquer momento e continuar usando o plano gratuito.',
  },
  {
    value: 'item-3',
    title: 'Posso criar meus próprios desafios?',
    content: 'Claro! Você pode criar desafios personalizados ou participar dos templates oficiais. Compartilhe com amigos e construa sua comunidade de hábitos saudáveis.',
  },
  {
    value: 'item-4',
    title: 'O que acontece se eu perder um dia?',
    content: 'Não tem problema! O sistema calcula sua taxa de conclusão e você pode retomar a qualquer momento. O importante é a consistência, não a perfeição.',
  },
]

const billingCycle = ref('monthly') // 'monthly' | 'yearly'
const proMonthlyPrice = 11.9
const proYearlyPrice = 99

const formatBRL = (value) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)

const proPrice = computed(() => (billingCycle.value === 'monthly' ? proMonthlyPrice : proYearlyPrice))
const proPeriodLabel = computed(() => (billingCycle.value === 'monthly' ? 'por mês' : 'por ano'))
const yearlySavings = computed(() => (proMonthlyPrice * 12) - proYearlyPrice)
const yearlyEquivalentMonthly = computed(() => proYearlyPrice / 12)

// Formatar números para exibição
const formatNumber = (num) => {
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'k'
  }
  return num.toString()
}

// Estatísticas calculadas a partir dos dados do backend
const stats = computed(() => [
  { 
    number: `${props.stats.completion_rate}%`, 
    label: 'Taxa de conclusão' 
  },
  { 
    number: formatNumber(props.stats.total_checkins), 
    label: 'Check-ins processados' 
  },
  { 
    number: formatNumber(props.stats.total_users), 
    label: 'Usuários ativos' 
  },
  { 
    number: `${props.stats.total_challenges}+`, 
    label: 'Desafios criados' 
  },
])

const testimonials = [
  {
    name: 'Ana Silva',
    role: 'Desenvolvedora',
    content: 'Finalmente consegui manter consistência na leitura! O WhatsApp é genial, não preciso de mais um app.',
    avatar: '👩‍💻',
  },
  {
    name: 'Carlos Santos',
    role: 'Personal Trainer',
    content: 'Uso para acompanhar meus clientes. A IA analisa as fotos dos treinos automaticamente. Incrível!',
    avatar: '💪',
  },
  {
    name: 'Marina Costa',
    role: 'Estudante',
    content: '21 dias de meditação completados! A comunidade me manteve motivada até o final.',
    avatar: '🧘',
  },
]
</script>

<template>
  <WebLayout :can-login="canLogin" :can-register="canRegister">
    <!-- Hero Section -->
    <section class="relative overflow-hidden border-b bg-gradient-to-br from-blue-50 via-white to-purple-50 py-20 sm:py-32">
      <div class="container mx-auto px-4 text-center">
        <!-- Badge -->
        <div class="mb-8 inline-flex justify-center">
          <Badge variant="outline" class="rounded-full border bg-blue-100 px-4 py-1 text-xs sm:text-sm">
            ✨ Desafios + check-ins + streak = consistência de verdade
          </Badge>
        </div>

        <!-- Main Heading -->
        <div class="mx-auto max-w-4xl">
          <h1
            class="text-4xl font-extrabold tracking-tight sm:text-5xl md:text-6xl lg:text-7xl"
            :style="{ contain: 'layout paint' }"
          >
            <span class="block text-gray-900">Transforme seus</span>
            <span
              class="mt-2 block bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent"
            >
              hábitos em conquistas
            </span>
          </h1>
        </div>

        <!-- Subtitle -->
        <p
          class="mx-auto mt-6 max-w-2xl text-center text-base text-gray-600 sm:text-lg md:text-xl"
          :style="{ contain: 'layout paint' }"
          fetchpriority="high"
        >
          Crie desafios, faça check-in em segundos e acompanhe sua evolução com streak e dashboard.
          Um jeito simples (e gostoso) de manter constância.
        </p>

        <div class="mx-auto mt-8 max-w-2xl">
          <ul class="grid gap-3 text-left text-sm text-gray-700 sm:grid-cols-3">
            <li class="flex items-start gap-2">
              <span class="mt-0.5">✅</span>
              <span>Check-in rápido e sem complicação</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="mt-0.5">🔥</span>
              <span>Streak e motivação todos os dias</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="mt-0.5">🌎</span>
              <span>Perfil público para compartilhar (opcional)</span>
            </li>
          </ul>
        </div>

        <!-- CTA Buttons -->
        <div class="mt-10 flex items-center justify-center gap-4 flex-col sm:flex-row">
          <Button
            :as="Link" :href="registerHref" size="lg"
            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white"
          >
            Começar Gratuitamente
          </Button>
          <Button
            :as="Link" :href="route('challenges.index')" size="lg" variant="outline"
            class="w-full sm:w-auto border-gray-300 text-gray-700 hover:bg-gray-50"
          >
            Ver Desafios
          </Button>
        </div>

        <!-- Stats -->
        <div class="mt-16 grid grid-cols-2 gap-8 sm:grid-cols-4">
          <div v-for="stat in stats" :key="stat.label" class="text-center">
            <div class="text-2xl font-bold text-blue-600 sm:text-3xl">{{ stat.number }}</div>
            <div class="text-sm text-gray-600">{{ stat.label }}</div>
          </div>
        </div>
      </div>

      <!-- Background Effects -->
      <div
        class="absolute inset-0 -z-10 h-full w-full bg-[linear-gradient(to_right,#4f4f4f2e_1px,transparent_1px),linear-gradient(to_bottom,#4f4f4f2e_1px,transparent_1px)] bg-[size:14px_24px]"
      />
      <div
        class="absolute left-0 right-0 top-0 -z-10 m-auto h-[310px] w-[310px] rounded-full bg-blue-500/20 opacity-20 blur-[100px]"
      />
    </section>

    <!-- How It Works Section -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Como funciona em 30 segundos</h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Sem planilha, sem fricção. É só escolher um desafio e fazer check-in todo dia.
          </p>
        </div>

        <div class="grid md:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">1️⃣</span>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">Escolha um desafio</h3>
            <p class="text-gray-600 text-sm">21 dias de leitura, 30 dias de treino ou crie o seu</p>
          </div>
          
          <div class="text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">2️⃣</span>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">Faça o check-in</h3>
            <p class="text-gray-600 text-sm">Registre seu progresso em segundos e siga a rotina</p>
          </div>
          
          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">3️⃣</span>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">Veja sua evolução</h3>
            <p class="text-gray-600 text-sm">Streak, taxa de conclusão e histórico sempre à mão</p>
          </div>
          
          <div class="text-center">
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">4️⃣</span>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">Compartilhe (opcional)</h3>
            <p class="text-gray-600 text-sm">Deixe público para ganhar motivação e compromisso</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Grid -->
    <section id="features" class="container mx-auto px-4 py-16 sm:px-6 lg:px-8 bg-gray-50">
      <h2 class="text-center text-2xl font-bold tracking-tight sm:text-4xl text-gray-900">
        Por que escolher o DOPA Check? ✨
      </h2>
      <p class="mx-auto mt-4 max-w-2xl text-center text-gray-600">
        Combinação única de simplicidade, tecnologia e comunidade para transformar seus hábitos.
      </p>

      <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <FeaturesCard
          v-for="feature in features" :key="feature.title" :icon="feature.icon"
          :title="feature.title" :description="feature.description"
        />
      </div>
    </section>

    <!-- Testimonials -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <h2 class="text-center text-3xl font-bold text-gray-900 mb-12">O que nossos usuários dizem</h2>
        
        <div class="grid md:grid-cols-3 gap-8">
          <div v-for="testimonial in testimonials" :key="testimonial.name" 
               class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                <span class="text-xl">{{ testimonial.avatar }}</span>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">{{ testimonial.name }}</h4>
                <p class="text-sm text-gray-600">{{ testimonial.role }}</p>
              </div>
            </div>
            <p class="text-gray-700">{{ testimonial.content }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="border-t bg-gray-50">
      <div class="container mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mx-auto max-w-3xl text-center">
          <h2 class="text-center text-2xl font-bold tracking-tight sm:text-4xl text-gray-900">
            Escolha seu plano 🚀
          </h2>
          <p class="mx-auto mt-4 max-w-2xl text-center text-gray-600">
            Comece grátis e evolua conforme suas necessidades. Sem surpresas, sem contratos longos.
          </p>
        </div>

        <div class="mt-16 grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
          <!-- Free Plan -->
          <div class="bg-white rounded-2xl p-8 border border-gray-200">
            <div class="text-center mb-8">
              <h3 class="text-2xl font-bold text-gray-900 mb-2">Gratuito</h3>
              <div class="text-4xl font-bold text-gray-900 mb-2">R$ 0</div>
              <p class="text-gray-600">Para sempre</p>
            </div>
            
            <ul class="space-y-4 mb-8">
              <li v-for="feature in pricingFeatures" :key="feature" class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                {{ feature }}
              </li>
            </ul>
            
            <Button :as="Link" :href="registerHref" class="w-full  bg-blue-600 hover:bg-blue-700">
              Começar Grátis
            </Button>
          </div>

          <!-- Pro Plan -->
          <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl p-8 text-white relative">
            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
              <Badge class="bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-sm font-medium">
                Mais Popular
              </Badge>
            </div>
            
            <div class="text-center mb-8">
              <h3 class="text-2xl font-bold mb-2">PRO</h3>
              <div class="mx-auto mt-4 inline-flex rounded-full bg-white/10 p-1 text-sm">
                <button
                  type="button"
                  class="rounded-full px-4 py-2 transition"
                  :class="billingCycle === 'monthly' ? 'bg-white text-blue-700' : 'text-white/90 hover:text-white'"
                  @click="billingCycle = 'monthly'"
                >
                  Mensal
                </button>
                <button
                  type="button"
                  class="rounded-full px-4 py-2 transition"
                  :class="billingCycle === 'yearly' ? 'bg-white text-blue-700' : 'text-white/90 hover:text-white'"
                  @click="billingCycle = 'yearly'"
                >
                  Anual
                  <span class="ml-2 rounded-full bg-yellow-400 px-2 py-0.5 text-xs font-semibold text-yellow-900">
                    economize
                  </span>
                </button>
              </div>

              <div class="mt-6">
                <div class="text-4xl font-bold leading-none">
                  {{ formatBRL(proPrice) }}
                </div>
                <p class="mt-2 text-blue-100">
                  {{ proPeriodLabel }}
                </p>
                <p v-if="billingCycle === 'yearly'" class="mt-3 text-sm text-blue-100">
                  Economize {{ formatBRL(yearlySavings) }} no ano (equivale a {{ formatBRL(yearlyEquivalentMonthly) }}/mês).
                </p>
              </div>
            </div>
            
            <ul class="space-y-4 mb-8">
              <li v-for="feature in proFeatures" :key="feature" class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                {{ feature }}
              </li>
            </ul>
            
            <Button :as="Link" :href="proCtaHref" class="w-full">
              Assinar PRO
            </Button>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="mx-auto max-w-3xl text-center">
          <h2 class="text-2xl font-bold text-gray-900 mb-8">
            Perguntas Frequentes
          </h2>
          <Accordion type="single" class="w-full text-left" collapsible default-value="item-1">
            <AccordionItem v-for="item in faqItems" :key="item.value" :value="item.value">
              <AccordionTrigger class="text-lg">
                {{ item.title }}
              </AccordionTrigger>
              <AccordionContent>
                {{ item.content }}
              </AccordionContent>
            </AccordionItem>
          </Accordion>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="border-t bg-gradient-to-br from-blue-600 to-purple-600 text-white">
      <div class="container mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <div class="rounded-2xl px-6 py-12 sm:p-16">
          <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight sm:text-6xl">
              Pronto para transformar seus hábitos?
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-blue-100">
              Junte-se a milhares de pessoas que já transformaram suas rotinas com o DOPA Check.
            </p>
            <div class="mt-8 flex justify-center gap-4">
              <Button :as="Link" :href="registerHref" size="lg" variant="secondary">
                Começar Agora
              </Button>
              <Button :as="Link" :href="route('challenges.index')" size="lg" variant="outline" class="bg-white/10 text-white border-white hover:bg-white hover:text-blue-600">
                Ver Desafios
              </Button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer (necessário para validação do Google OAuth) -->
    <footer class="border-t bg-white">
      <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
          <div class="text-sm text-gray-600">
            © {{ new Date().getFullYear() }} DOPA Check. Todos os direitos reservados.
          </div>

          <div class="flex items-center gap-6 text-sm">
            <Link
              :href="route('policy.show')"
              class="text-gray-600 hover:text-gray-900 hover:underline underline-offset-4"
            >
              Política de Privacidade
            </Link>
            <Link
              :href="route('terms.show')"
              class="text-gray-600 hover:text-gray-900 hover:underline underline-offset-4"
            >
              Termos de Serviço
            </Link>
          </div>
        </div>
      </div>
    </footer>
  </WebLayout>
</template>
