<script setup>
/**
 * Landing premium – Reset by Michele Gramacho.
 * Visual leve, texto corrido, tom de boas-vindas. Header vem do WebLayout.
 */
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import WebLayout from '@/layouts/WebLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import Button from '@/components/ui/button/Button.vue'
import InputError from '@/components/InputError.vue'
import Alert from '@/components/ui/alert/Alert.vue'
import AlertTitle from '@/components/ui/alert/AlertTitle.vue'
import AlertDescription from '@/components/ui/alert/AlertDescription.vue'
import { CheckCircle2, XCircle } from 'lucide-vue-next'

const PLATFORM_START = '09/03/2026'
const CHALLENGE_DAYS = 30
const CHALLENGE_START_EXTERNAL = '25/02/2026'

const props = defineProps({
  team: { type: Object, required: true },
})

const schema = computed(() => Array.isArray(props.team?.form_schema) ? props.team.form_schema : [])
const initialForm = computed(() => {
  const obj = {}
  for (const field of schema.value) {
    if (field?.key) obj[field.key] = ''
  }
  return obj
})
const isCreateUser = computed(() => props.team?.onboarding_behavior === 'create_user')
const form = useForm({ ...initialForm.value, terms_accepted: false })

function submit() {
  form.post(route('teams.join.store', props.team.slug), { preserveScroll: true })
}

const page = usePage()
const flashError = computed(() => page.props.flash?.error)
const flashSuccess = computed(() => page.props.flash?.success)

const dailyTasks = [
  {
    id: 'treino',
    icon: '💪',
    title: 'Treino do dia',
    rule: 'Mínimo 30 minutos de atividade física. No check-in, envie uma foto do treino — academia, corrida, luta, bike, relógio ou espelho. No whatsapp, poste a foto com a hashtag #gym na legenda.',
  },
  {
    id: 'zeroalcool',
    icon: '🍃',
    title: 'Zero álcool',
    rule: 'Nada de bebida alcoólica durante o desafio. No check-in, foto no rolê com bebida sem álcool: água, suco, refri zero. Use #zeroalcool.',
  },
  {
    id: 'alimentacao',
    icon: '🥗',
    title: 'Alimentação limpa',
    rule: 'Sem açúcar refinado e sem ultraprocessados. No check-in, foto da refeição saudável — café da manhã, almoço, marmita. Use #alimentacao.',
  },
]
</script>

<template>
  <WebLayout>
    <main class="min-h-screen bg-[#faf9f7]">
      <!-- Hero: imagem + headline editorial -->
      <section class="relative overflow-hidden">
        <div class="relative aspect-4/3 min-h-[280px] sm:aspect-21/9 sm:min-h-[320px]">
          <img
            src="/images/landing/michelegramacho/bg.png"
            alt=""
            class="absolute inset-0 h-full w-full object-cover object-top"
          />
          <div class="absolute inset-0 bg-linear-to-t from-stone-900/80 via-stone-900/20 to-transparent" />
          <div class="absolute inset-0 flex flex-col justify-end p-6 pb-10 sm:p-10 sm:pb-14">
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-white/80 sm:text-sm">
              Desafio de {{ CHALLENGE_DAYS }} dias
            </p>
            <h1 class="mt-2 text-3xl font-light leading-tight text-white sm:text-4xl md:text-5xl" style="letter-spacing: -0.02em;">
              Reset {{ CHALLENGE_DAYS }}D
            </h1>
            <p class="mt-2 text-lg text-white/95 sm:text-xl" style="letter-spacing: -0.01em;">
              por Michele Gramacho
            </p>
            <p class="mt-4 max-w-md text-sm leading-relaxed text-white/90 sm:text-base">
              Boas-vindas. Você está a um passo de entrar no desafio: preencha o formulário abaixo e comece sua jornada de disciplina e bem-estar na plataforma.
            </p>
          </div>
        </div>
      </section>

      <div class="mx-auto max-w-2xl px-5 py-12 sm:px-8 sm:py-16">
        <!-- O que é o desafio: texto corrido + pilares suaves -->
        <section class="mb-16 sm:mb-20">
          <h2 class="text-xl font-semibold tracking-tight text-stone-800 sm:text-2xl">
            O que é o desafio
          </h2>
          <p class="mt-4 text-base leading-relaxed text-stone-600 sm:text-lg">
            O <strong class="font-medium text-stone-800">Reset {{ CHALLENGE_DAYS }}D</strong> são {{ CHALLENGE_DAYS }} dias de disciplina física e mental. A ideia é simples: treinar todo dia (pelo menos 30 minutos), cortar álcool e açúcar e priorizar alimentação saudável. Tudo isso com check-ins diários aqui na plataforma, para você acompanhar sua evolução e manter o foco.
          </p>
          <div class="mt-8 grid gap-4 sm:grid-cols-2 sm:gap-5">
            <div class="flex gap-4 rounded-2xl bg-white/70 p-5 shadow-sm ring-1 ring-stone-100/80 backdrop-blur-sm">
              <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-2xl">✅</span>
              <div>
                <p class="font-medium text-stone-800">Treino diário</p>
                <p class="mt-0.5 text-sm text-stone-600">Mínimo 30 min por dia.</p>
              </div>
            </div>
            <div class="flex gap-4 rounded-2xl bg-white/70 p-5 shadow-sm ring-1 ring-stone-100/80 backdrop-blur-sm">
              <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50/80 text-2xl">🚫</span>
              <div>
                <p class="font-medium text-stone-800">Zero álcool</p>
                <p class="mt-0.5 text-sm text-stone-600">Nada de bebida alcoólica.</p>
              </div>
            </div>
            <div class="flex gap-4 rounded-2xl bg-white/70 p-5 shadow-sm ring-1 ring-stone-100/80 backdrop-blur-sm">
              <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50/80 text-2xl">🚫</span>
              <div>
                <p class="font-medium text-stone-800">Zero açúcar</p>
                <p class="mt-0.5 text-sm text-stone-600">Sem açúcar refinado.</p>
              </div>
            </div>
            <div class="flex gap-4 rounded-2xl bg-white/70 p-5 shadow-sm ring-1 ring-stone-100/80 backdrop-blur-sm">
              <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50/80 text-2xl">🥗</span>
              <div>
                <p class="font-medium text-stone-800">Alimentação saudável</p>
                <p class="mt-0.5 text-sm text-stone-600">Foco em comida de verdade.</p>
              </div>
            </div>
          </div>
          <div class="mt-6 flex flex-wrap gap-2">
            <span class="rounded-full bg-sky-100/90 px-3.5 py-1.5 text-xs font-medium text-sky-800">Fitness</span>
            <span class="rounded-full bg-amber-100/90 px-3.5 py-1.5 text-xs font-medium text-amber-800">Intermediário</span>
            <span class="rounded-full bg-stone-100 px-3.5 py-1.5 text-xs font-medium text-stone-700">{{ CHALLENGE_DAYS }} dias</span>
          </div>
          <p class="mt-6 text-sm text-stone-500">
            Início na plataforma: {{ PLATFORM_START }} · O desafio começou em {{ CHALLENGE_START_EXTERNAL }}.
          </p>
        </section>

        <!-- Tasks diárias: bloco editorial -->
        <section class="mb-16 sm:mb-20">
          <h2 class="text-xl font-semibold tracking-tight text-stone-800 sm:text-2xl">
            Como funciona no dia a dia
          </h2>
          <p class="mt-4 text-base leading-relaxed text-stone-600 sm:text-lg">
            <strong>Fez a tarefa?</strong> Poste no grupo uma foto + a hashtag.<br/>
            <strong>O bot registra seu check-in</strong> e o seu progresso sobe na hora.<br/><br/>
            <strong>Você se mantém no trilho</strong> e ainda vira combustível pra quem tá quase desistindo.
          </p>
          <ul class="mt-10 space-y-6">
            <li
              v-for="task in dailyTasks"
              :key="task.id"
              class="group flex gap-5 rounded-2xl bg-white/80 p-6 shadow-sm ring-1 ring-stone-100/80 transition-shadow hover:shadow-md sm:p-7"
            >
              <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-stone-100 text-3xl transition-colors group-hover:bg-stone-200/80 sm:size-16">
                {{ task.icon }}
              </span>
              <div class="min-w-0 flex-1">
                <h3 class="text-lg font-semibold text-stone-800">
                  {{ task.title }}
                </h3>
                <p class="mt-2 leading-relaxed text-stone-600">
                  {{ task.rule }}
                </p>
                <span class="mt-3 inline-block rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700">
                  {{ task.tag }}
                </span>
              </div>
            </li>
          </ul>
        </section>

        <!-- Formulário: área limpa e CTA claro -->
        <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-stone-100/80 sm:p-10">
          <h2 class="text-xl font-semibold tracking-tight text-stone-800 sm:text-2xl">
            Entrar no desafio
          </h2>
          <p class="mt-3 text-base leading-relaxed text-stone-600">
            Preencha seus dados abaixo. Depois de enviar, você acessa o dashboard e já pode começar seus check-ins. É rápido.
          </p>

          <Alert v-if="flashError" class="mt-6" variant="destructive">
            <XCircle />
            <AlertTitle>Erro</AlertTitle>
            <AlertDescription>{{ flashError }}</AlertDescription>
          </Alert>
          <Alert v-else-if="flashSuccess" class="mt-6" variant="success">
            <CheckCircle2 />
            <AlertTitle>Pronto</AlertTitle>
            <AlertDescription>{{ flashSuccess }}</AlertDescription>
          </Alert>

          <form class="mt-8 space-y-5" @submit.prevent="submit">
            <div v-for="field in schema" :key="field.key">
              <Label :for="field.key" class="text-stone-700">
                {{ field.label || field.key }}
              </Label>
              <Input
                :id="field.key"
                v-model="form[field.key]"
                :type="(field.type === 'email' || field.type === 'tel' || field.type === 'url' || field.type === 'date') ? field.type : 'text'"
                class="mt-2 w-full rounded-xl border-stone-200 bg-stone-50/50 focus:bg-white"
                :required="!!field.required"
                :placeholder="field.placeholder"
                :inputmode="field.type === 'tel' ? 'tel' : undefined"
              />
              <InputError :message="form.errors[field.key]" class="mt-2" />
            </div>

            <div v-if="isCreateUser" class="rounded-2xl bg-stone-50/80 p-5">
              <div class="flex items-start gap-4">
                <input
                  id="terms_michele"
                  v-model="form.terms_accepted"
                  type="checkbox"
                  class="mt-1 size-5 shrink-0 rounded border-stone-300 text-violet-600 focus:ring-violet-500"
                  required
                />
                <Label
                  for="terms_michele"
                  class="min-w-0 flex-1 cursor-pointer text-sm leading-relaxed text-stone-700"
                >
                  <span class="inline">
                    Aceito e concordo com os
                    <a
                      href="/terms-of-service"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline font-medium text-violet-700 underline decoration-violet-300/80 underline-offset-2 hover:decoration-violet-500"
                      @click.stop
                    >Termos de Serviço</a>
                    e a
                    <a
                      href="/privacy-policy"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline font-medium text-violet-700 underline decoration-violet-300/80 underline-offset-2 hover:decoration-violet-500"
                      @click.stop
                    >Política de Privacidade</a>
                    e com o uso dos meus dados para este desafio.
                  </span>
                </Label>
              </div>
              <InputError :message="form.errors.terms_accepted" class="mt-2" />
            </div>

            <Button
              type="submit"
              class="w-full rounded-2xl bg-violet-600 py-4 text-base font-semibold text-white shadow-lg shadow-violet-500/25 transition hover:bg-violet-700 hover:shadow-violet-500/30"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Enviando...' : 'Participar do desafio' }}
            </Button>
          </form>
        </section>

        <p class="mt-12 text-center text-sm text-stone-400">
          Dúvidas? Fale com o organizador do desafio.
        </p>
      </div>
    </main>
  </WebLayout>
</template>
