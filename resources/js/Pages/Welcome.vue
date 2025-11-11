<script setup>
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import { Link } from '@inertiajs/vue3'
import FeaturesCard from '@/components/FeaturesCard.vue'
import PricingCard from '@/components/PricingCard.vue'
import Terminal from '@/components/Terminal.vue'
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

const features = [
  {
    icon: '📱',
    title: 'WhatsApp Integration',
    description: 'Envie fotos + hashtags direto no WhatsApp. Zero apps extras, zero fricção. Check-in automático em 30 segundos.',
  },
  {
    icon: '🧠',
    title: 'IA Inteligente',
    description: 'Análise automática de fotos com OpenAI Vision. Extrai dados, valida check-ins e personaliza respostas.',
  },
  {
    icon: '📊',
    title: 'Dashboard Visual',
    description: 'Progresso visual com streaks, estatísticas e conquistas. Veja sua evolução em tempo real.',
  },
  {
    icon: '👥',
    title: 'Comunidade Ativa',
    description: 'Participe de desafios com milhares de pessoas. Social proof e motivação coletiva.',
  },
  {
    icon: '🎯',
    title: 'Desafios Personalizados',
    description: 'Crie seus próprios desafios ou participe dos templates oficiais. 21 dias de leitura, 30 dias de treino.',
  },
  {
    icon: '🚀',
    title: 'Compartilhamento Viral',
    description: 'Gere imagens automáticas para stories. Perfil público para mostrar suas conquistas.',
  },
  {
    icon: '⚡',
    title: 'Performance Otimizada',
    description: 'Laravel 11 + Vue 3 + Redis. Arquitetura escalável para milhares de usuários simultâneos.',
  },
  {
    icon: '🔒',
    title: 'Segurança Total',
    description: 'Autenticação social, dados criptografados e backup automático. Sua privacidade é prioridade.',
  },
]

const pricingFeatures = [
  '1 desafio ativo',
  'Check-in manual',
  '90 dias de storage',
  'Dashboard básico',
  'Comunidade limitada',
  'Suporte por email',
]

const proFeatures = [
  'Desafios ilimitados',
  'IA analisa suas fotos',
  'Storage ilimitado',
  'Dashboard avançado',
  'Comunidade completa',
  'Suporte prioritário',
  'Relatórios detalhados',
  'Integração personalizada',
]

const faqItems = [
  {
    value: 'item-1',
    title: 'Como funciona o check-in via WhatsApp?',
    content: 'Simples! Você salva o número do bot no WhatsApp, escolhe um desafio e envia uma foto + hashtag (ex: #leitura). O bot confirma automaticamente e seu dashboard é atualizado em tempo real.',
  },
  {
    value: 'item-2',
    title: 'A IA realmente analisa minhas fotos?',
    content: 'Sim! Com o plano PRO, nossa IA analisa suas fotos para extrair dados (distância, tempo, etc) e validar check-ins automaticamente. Tudo com sua privacidade protegida.',
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
            ✨ WhatsApp + IA + Hábitos = Tracking sem fricção
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
          Envie uma foto + hashtag no WhatsApp e receba confirmação automática. 
          Dashboard visual, IA inteligente e comunidade ativa para manter consistência.
        </p>

        <!-- CTA Buttons -->
        <div class="mt-10 flex items-center justify-center gap-4 flex-col sm:flex-row">
          <Button
            :as="Link" :href="route('register')" size="lg"
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
            Zero apps extras, zero fricção. Use o WhatsApp que você já tem.
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
            <h3 class="font-semibold text-gray-900 mb-2">Salve o bot</h3>
            <p class="text-gray-600 text-sm">Receba o número do WhatsApp e adicione aos contatos</p>
          </div>
          
          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">3️⃣</span>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">Envie foto + hashtag</h3>
            <p class="text-gray-600 text-sm">Ex: foto do livro + #leitura</p>
          </div>
          
          <div class="text-center">
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">4️⃣</span>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">Dashboard atualiza</h3>
            <p class="text-gray-600 text-sm">Progresso visual + streak + confirmação</p>
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
            
            <Button :as="Link" :href="route('register')" class="w-full">
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
              <div class="text-4xl font-bold mb-2">R$ 19</div>
              <p class="text-blue-100">por mês</p>
            </div>
            
            <ul class="space-y-4 mb-8">
              <li v-for="feature in proFeatures" :key="feature" class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                {{ feature }}
              </li>
            </ul>
            
            <Button :as="Link" :href="route('register')" variant="secondary" class="w-full">
              Começar PRO
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
              <Button :as="Link" :href="route('register')" size="lg" variant="secondary">
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
  </WebLayout>
</template>
