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
  'Funciona sozinho.',
  'Funciona melhor em grupo.',
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

    <!-- ===== HERO ===== -->
    <section class="py-24 md:py-32">
      <div class="mx-auto max-w-3xl px-6">
        <p class="text-[13px] font-semibold uppercase tracking-widest text-violet-600">
          Sobre
        </p>

        <h1 class="mt-6 text-4xl font-bold leading-tight tracking-tight text-slate-900 md:text-5xl">
          Disciplina não nasce sozinha.
          <br>
          Ela se sustenta quando vira
          <span class="text-violet-600">sistema</span>.
        </h1>

        <p class="mt-8 text-xl leading-relaxed text-slate-700">
          O DOPA Check transforma compromisso em progresso visível —
        </p>
        <p class="mt-4 text-lg leading-relaxed text-slate-600">
          no individual e no coletivo.
        </p>
      </div>
    </section>

    <!-- ===== ORIGEM ===== -->
    <section class="border-t border-slate-100 bg-slate-50/60 py-20">
      <div class="mx-auto max-w-3xl px-6">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
          De onde surgiu
        </h2>

        <div class="mt-8 space-y-4 text-lg leading-relaxed text-slate-700">
          <p>
            O DOPA Check nasceu dentro de comunidades reais.
          </p>
          <p>
            Grupos que queriam evoluir juntos.
            Desafios intensos. Energia alta. Compromisso público.
          </p>
          <p>
            Mas sempre o mesmo padrão:
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
            Faltava estrutura.
          </p>
        </div>

        <div class="mt-10 space-y-4 text-lg leading-relaxed text-slate-700">
          <p>
            O DOPA Check nasceu para resolver isso —
            sem mudar o comportamento do grupo.
          </p>
          <p class="text-slate-600">
            Sem criar fricção.
            <br>
            Sem obrigar ninguém a instalar mais um app.
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
          Motivação é volátil.
        </p>

        <p class="mt-4 text-lg text-slate-700">Estrutura sustenta.</p>

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
          </p>
          <p class="text-slate-600">
            Quando o progresso fica visível, ele se fortalece.
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
          Não é apenas um habit tracker.
        </p>
        <p class="mt-4 text-lg leading-relaxed text-slate-700">
          É uma camada que organiza disciplina.
          <br>
          <br>
          Você assume um desafio.
          <br>
          Você registra o que faz.
          <br>
          Você acompanha sua evolução.
        </p>

        <p class="mt-8 text-lg text-slate-700">
          Se estiver em grupo, a visibilidade acelera.
          <br>
          Se estiver sozinho, o sistema sustenta.
        </p>

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
          Sem drama. Sem planilha.
        </p>
      </div>
    </section>

    <!-- ===== PARA QUEM ===== -->
    <section class="py-20">
      <div class="mx-auto max-w-3xl px-6">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
          Para quem é
        </h2>

        <div class="mt-6 space-y-2 text-lg leading-relaxed text-slate-700">
          <p>Para quem decidiu levar consistência a sério.</p>
          <p>Para comunidades que querem estrutura. Para líderes que querem organização.</p>
          <p>Para indivíduos que querem progresso real.</p>
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
            Funciona sozinho.
            <br>
            Funciona melhor em grupo.
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
          <p>O DOPA Check nasceu inspirado em comunidades,</p>
          <p>mas é um produto independente.</p>
          <p class="text-slate-600">
            Não pertence a nenhuma estrutura específica.
            <br>
            Não depende de uma comunidade única.
          </p>
          <p class="font-medium text-slate-800">
            É uma infraestrutura plugável para qualquer grupo —
            ou para você, individualmente.
          </p>
        </div>
      </div>
    </section>

    <!-- ===== CONSTRUÇÃO + VISÃO ===== -->
    <section class="py-20">
      <div class="mx-auto max-w-3xl px-6">
        <div class="grid gap-16 md:grid-cols-2">
          <!-- Visão -->
          <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
              Visão
            </h2>
            <div class="mt-6 space-y-4 text-lg leading-relaxed text-slate-700">
              <p>O futuro da disciplina não está em motivação constante.</p>
              <p>Está em sistemas que sustentam comportamento ao longo do tempo.</p>
              <p class="text-slate-600">
                Visibilidade gera compromisso.
                <br>
                Mensuração gera consistência.
                <br>
                Consistência gera cultura.
              </p>
              <p class="font-medium text-slate-800">
                Estrutura sustenta.
              </p>
            </div>
          </div>

          <!-- Estamos construindo -->
          <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
              Estamos construindo
            </h2>
            <p class="mt-6 text-lg leading-relaxed text-slate-700">
              Estamos construindo:
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
          Funciona sozinho.
          <br>
          Funciona melhor em
          <span class="text-violet-600">grupo</span>.
        </h2>

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
          Menos fricção. Mais execução.
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
