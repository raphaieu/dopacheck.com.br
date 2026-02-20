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
  'Onboarding manual via Google Forms',
  'Aprovação feita no braço',
  'Check-ins perdidos no feed do grupo',
  'Histórico inexistente',
  'Admin virando fiscal',
  'Energia inicial forte — e depois queda',
]

const beliefs = [
  'Ambiente certo',
  'Pressão social saudável',
  'Visibilidade',
  'Sistema',
  'Consistência mensurável',
]

const produces = [
  'Check-in automático',
  'Histórico organizado',
  'Dashboard coletivo',
  'Progresso visível',
]

const audiences = [
  'Comunidades de desenvolvimento pessoal',
  'Grupos de estudo',
  'Times de trabalho',
  'Igrejas',
  'Famílias',
  'Amigos',
  'Desafios fitness',
  'Rotinas acadêmicas',
]

const visionItems = [
  'Relatórios inteligentes',
  'Análises de consistência',
  'Reconhecimento automático',
  'Insights coletivos',
]
</script>

<template>
  <WebLayout :can-login="canLogin" :can-register="canRegister">

    <!-- ===== HERO ===== -->
    <section class="py-24 md:py-32">
      <div class="mx-auto max-w-3xl px-6">
        <p class="text-[13px] font-semibold uppercase tracking-widest text-violet-600">
          Sobre o DOPA Check
        </p>

        <h1 class="mt-6 text-4xl font-bold leading-tight tracking-tight text-slate-900 md:text-5xl">
          Disciplina não nasce sozinha.
          <br>
          Ela nasce no
          <span class="text-violet-600">grupo</span>.
        </h1>

        <p class="mt-8 text-xl leading-relaxed text-slate-700">
          O DOPA Check é uma infraestrutura de disciplina via WhatsApp.
        </p>
        <p class="mt-4 text-lg leading-relaxed text-slate-600">
          Não é um aplicativo de hábito tradicional.
          É uma estrutura para comunidades que já decidiram evoluir.
        </p>
      </div>
    </section>

    <!-- ===== ORIGEM ===== -->
    <section class="border-t border-slate-100 bg-slate-50/60 py-20">
      <div class="mx-auto max-w-3xl px-6">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
          De onde isso surgiu
        </h2>

        <div class="mt-8 space-y-4 text-lg leading-relaxed text-slate-700">
          <p>
            O DOPA Check nasceu dentro de comunidades que já promoviam
            desafios semanais de desenvolvimento pessoal.
          </p>
          <p>
            Jovens querendo ser 1% melhores por dia.
            Grupos ativos. Desafios intensos. Gente comprometida.
          </p>
          <p>
            Mas havia um padrão recorrente:
          </p>
        </div>

        <!-- Problem list -->
        <ul class="mt-8 space-y-3">
          <li
            v-for="problem in problems"
            :key="problem"
            class="flex items-start gap-3 text-slate-600"
          >
            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400" />
            {{ problem }}
          </li>
        </ul>

        <!-- Pivot statement -->
        <div class="mt-12 border-l-2 border-violet-600 pl-6">
          <p class="text-2xl font-bold tracking-tight text-slate-900">
            A disciplina existia.
            <br>
            A estrutura não.
          </p>
        </div>

        <div class="mt-10 space-y-4 text-lg leading-relaxed text-slate-700">
          <p>O DOPA Check nasceu para resolver isso.</p>
          <p class="text-slate-600">
            Sem mudar o comportamento do grupo.
            <br>
            Sem criar mais fricção.
            <br>
            Sem obrigar ninguém a abrir mais um app.
          </p>
        </div>
      </div>
    </section>

    <!-- ===== NO QUE ACREDITAMOS ===== -->
    <section class="py-20">
      <div class="mx-auto max-w-3xl px-6">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
          No que acreditamos
        </h2>

        <p class="mt-6 text-lg leading-relaxed text-slate-700">
          Não acreditamos em motivação eterna.
        </p>

        <p class="mt-4 text-lg text-slate-700">Acreditamos em:</p>

        <ul class="mt-6 space-y-2.5">
          <li
            v-for="belief in beliefs"
            :key="belief"
            class="flex items-center gap-3 text-lg font-medium text-slate-800"
          >
            <span class="h-1 w-4 rounded-full bg-violet-600" />
            {{ belief }}
          </li>
        </ul>

        <div class="mt-12 space-y-3 text-lg leading-relaxed text-slate-700">
          <p>
            Disciplina individual é frágil.
            <br>
            Estrutura coletiva é forte.
          </p>
          <p class="text-slate-600">
            Quando o progresso é visível, ele se sustenta.
            <br>
            Quando é mensurável, ele vira cultura.
          </p>
        </div>
      </div>
    </section>

    <!-- ===== O QUE É ===== -->
    <section class="border-t border-slate-100 bg-slate-50/60 py-20">
      <div class="mx-auto max-w-3xl px-6">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
          O que o DOPA Check realmente é
        </h2>

        <p class="mt-6 text-lg leading-relaxed text-slate-700">
          Não é mais um habit tracker.
        </p>
        <p class="mt-4 text-lg leading-relaxed text-slate-700">
          É uma camada invisível que organiza o que já acontece
          dentro do grupo. A pessoa já tira a foto. Já posta no grupo.
          Já usa hashtag.
        </p>

        <p class="mt-8 text-lg text-slate-700">O DOPA Check transforma isso em:</p>

        <ul class="mt-6 space-y-2.5">
          <li
            v-for="item in produces"
            :key="item"
            class="flex items-center gap-3 text-lg font-medium text-slate-800"
          >
            <Icon icon="lucide:check" class="h-4 w-4 shrink-0 text-emerald-600" />
            {{ item }}
          </li>
        </ul>

        <p class="mt-8 text-lg leading-relaxed text-slate-600">
          Tudo direto no WhatsApp.
          <br>
          Sem abrir app. Sem planilha. Sem fricção.
        </p>
      </div>
    </section>

    <!-- ===== PARA QUEM ===== -->
    <section class="py-20">
      <div class="mx-auto max-w-3xl px-6">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
          Para quem foi feito
        </h2>

        <div class="mt-6 space-y-2 text-lg leading-relaxed text-slate-700">
          <p>Para comunidades que querem consistência.</p>
          <p>Para líderes que querem estrutura.</p>
          <p>Para grupos que querem crescer juntos.</p>
        </div>

        <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-4">
          <div
            v-for="audience in audiences"
            :key="audience"
            class="rounded-xl bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700"
          >
            {{ audience }}
          </div>
        </div>

        <div class="mt-10 border-l-2 border-violet-600 pl-6">
          <p class="text-lg font-medium text-slate-800">
            Se existe grupo e existe disciplina,
            <br>
            o DOPA Check organiza.
          </p>
        </div>
      </div>
    </section>

    <!-- ===== INDEPENDÊNCIA ===== -->
    <section class="border-t border-slate-100 bg-slate-50/60 py-20">
      <div class="mx-auto max-w-3xl px-6">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
          Independência
        </h2>

        <div class="mt-6 space-y-4 text-lg leading-relaxed text-slate-700">
          <p>O DOPA Check nasceu inspirado em desafios reais.</p>
          <p>Mas é um produto independente.</p>
          <p class="text-slate-600">
            Não pertence a nenhuma comunidade específica.
            <br>
            Não depende de nenhuma estrutura externa.
          </p>
          <p class="font-medium text-slate-800">
            É uma infraestrutura plugável para qualquer grupo de WhatsApp.
          </p>
        </div>
      </div>
    </section>

    <!-- ===== CONSTRUÇÃO + VISÃO ===== -->
    <section class="py-20">
      <div class="mx-auto max-w-3xl px-6">
        <div class="grid gap-16 md:grid-cols-2">
          <!-- Como construímos -->
          <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
              Como estamos construindo
            </h2>
            <div class="mt-6 space-y-4 text-lg leading-relaxed text-slate-700">
              <p>Estamos em beta.</p>
              <p>Estamos evoluindo junto com as comunidades.</p>
              <p class="text-slate-600">
                Feedback direto.
                <br>
                Iteração rápida.
                <br>
                Transparência total.
              </p>
              <p class="font-medium text-slate-800">
                Primeiro estrutura. Depois escala.
              </p>
            </div>
          </div>

          <!-- Visão -->
          <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
              Visão
            </h2>
            <p class="mt-6 text-lg leading-relaxed text-slate-700">
              O futuro da disciplina coletiva não está em mais motivação.
              Está em sistemas que sustentam comportamento ao longo do tempo.
            </p>
            <ul class="mt-6 space-y-2.5">
              <li
                v-for="item in visionItems"
                :key="item"
                class="flex items-center gap-3 text-slate-700"
              >
                <span class="h-1 w-4 rounded-full bg-violet-600" />
                {{ item }}
              </li>
            </ul>
            <p class="mt-6 text-lg font-medium text-slate-800">
              Menos fricção. Mais execução.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CLOSING ===== -->
    <section class="border-t border-slate-100 bg-slate-50/60 py-20">
      <div class="mx-auto max-w-3xl px-6 text-center">
        <h2 class="text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-4xl">
          Se o seu grupo já tem disciplina,
          <br>
          talvez só falte
          <span class="text-violet-600">estrutura</span>.
        </h2>

        <Button
          :as="Link"
          :href="registerHref"
          size="lg"
          class="mt-10 rounded-xl bg-violet-600 px-8 text-white shadow-[0_20px_60px_-15px_rgba(124,58,237,0.35)] transition-all duration-200 hover:bg-violet-700 hover:shadow-[0_20px_60px_-15px_rgba(124,58,237,0.5)]"
        >
          Criar desafio para meu grupo
          <Icon icon="lucide:arrow-right" class="ml-1 h-4 w-4" />
        </Button>

        <p class="mt-4 text-sm text-slate-500">
          Beta aberto. Gratuito por enquanto.
        </p>
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
