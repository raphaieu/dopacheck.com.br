<script setup>
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { AlertTriangle, CheckCircle2, Info, XCircle } from 'lucide-vue-next'
import WebLayout from '@/layouts/WebLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import Button from '@/components/ui/button/Button.vue'
import InputError from '@/components/InputError.vue'
import Card from '@/components/ui/card/Card.vue'
import CardHeader from '@/components/ui/card/CardHeader.vue'
import CardTitle from '@/components/ui/card/CardTitle.vue'
import CardDescription from '@/components/ui/card/CardDescription.vue'
import CardContent from '@/components/ui/card/CardContent.vue'
import Alert from '@/components/ui/alert/Alert.vue'
import AlertTitle from '@/components/ui/alert/AlertTitle.vue'
import AlertDescription from '@/components/ui/alert/AlertDescription.vue'

const props = defineProps({
  team: Object,
})

const page = usePage()
const flashSuccess = computed(() => page.props.flash?.success)
const flashError = computed(() => page.props.flash?.error)
const flashWarning = computed(() => page.props.flash?.warning || page.props.flash?.message)
const teamName = computed(() => props.team?.name || 'o time')
const onboardingTitle = computed(() => props.team?.onboarding_title || `🎈 Formulário de Inscrição | ${teamName.value} 🎈`)
const whatsappJoinUrl = computed(() => props.team?.whatsapp_join_url || '')
const onboardingBody = computed(() => props.team?.onboarding_body || '')

const form = useForm({
  name: '',
  birthdate: '',
  email: '',
  whatsapp_number: '',
  city: '',
  neighborhood: '',
  circle_url: '',
})

function onlyDigits(value) {
  return String(value ?? '').replace(/\D+/g, '')
}

function formatBrPhoneFromDigits(digits) {
  const clean = onlyDigits(digits).slice(0, 11)
  if (clean === '') return ''

  const ddd = clean.slice(0, 2)
  const rest = clean.slice(2) // 8 ou 9 dígitos (fixo ou celular)

  let formatted = ''
  if (ddd.length > 0) formatted += `(${ddd}`
  if (ddd.length === 2) formatted += ') '

  const firstBlockLen = rest.length > 8 ? 5 : 4
  if (rest.length > firstBlockLen) formatted += `${rest.slice(0, firstBlockLen)}-${rest.slice(firstBlockLen)}`
  else formatted += rest

  return formatted.trim()
}

const whatsappDigits = computed({
  get: () => onlyDigits(form.whatsapp_number).slice(0, 11),
  set: (value) => {
    form.whatsapp_number = onlyDigits(value).slice(0, 11)
  },
})

const whatsappMasked = computed({
  get: () => formatBrPhoneFromDigits(whatsappDigits.value),
  set: (value) => {
    whatsappDigits.value = value
  },
})

function onWhatsappBeforeInput(event) {
  // Bloqueia letras/símbolos (mantém backspace/delete etc.)
  if (event?.data && /\D/.test(event.data)) event.preventDefault()
}

function onWhatsappPaste(event) {
  event.preventDefault()
  const pasted = event?.clipboardData?.getData('text') ?? ''
  whatsappDigits.value = pasted
}

function submit() {
  form.post(route('teams.join.store', props.team.slug), {
    preserveScroll: true,
  })
}

const showFormErrorAlert = computed(() => form.hasErrors && !flashError.value)
</script>

<template>
  <WebLayout>
    <main
      class="min-h-screen bg-gradient-to-br from-black via-zinc-950 to-red-950"
      style="--primary: hsl(0 84% 45%); --primary-foreground: hsl(0 0% 98%); --ring: hsl(0 84% 45%);"
    >
      <div class="mx-auto w-full max-w-5xl px-4 py-10">
        <div class="mb-8">
          <h1 class="text-3xl font-bold tracking-tight text-white">
            {{ onboardingTitle }}
          </h1>
          <p class="mt-2 text-zinc-300">
            Bem-vindo(a) - que bom ter você por aqui.
          </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
          <!-- Mensagem e passos -->
          <Card class="px-0 border-white/10 bg-white/5 text-white">
            <CardHeader class="px-6">
              <CardTitle class="text-xl">
                {{ teamName }}
              </CardTitle>
              <CardDescription>
                Uma comunidade que busca compartilhar conhecimento, fortalecer conexões e criar experiências que fazem bem à mente, ao corpo e ao coração.
              </CardDescription>
            </CardHeader>

            <CardContent class="px-6">
              <div v-if="onboardingBody" class="prose prose-invert max-w-none" v-html="onboardingBody" />

              <div v-else class="space-y-4 text-sm leading-relaxed text-zinc-200">
                <p>
                  Para fazer parte do <strong>{{ teamName }}</strong> é rapidinho - só seguir dois passos:
                </p>

                <ol class="space-y-3">
                  <li class="flex gap-3">
                    <span class="mt-0.5 inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-red-600 text-xs font-semibold text-white">1</span>
                    <div>
                      <p class="font-medium text-white">Preencha o formulário</p>
                      <p class="text-zinc-300">
                        Assim a gente te conhece melhor e consegue incluir você nas nossas ações e encontros.
                      </p>
                    </div>
                  </li>
                  <li class="flex gap-3">
                    <span class="mt-0.5 inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-red-600 text-xs font-semibold text-white">2</span>
                    <div>
                      <p class="font-medium text-white">Solicite entrada no grupo do WhatsApp</p>
                      <p class="text-zinc-300">
                        Depois de enviar este formulário, solicite sua entrada no grupo do WhatsApp.
                      </p>
                    </div>
                  </li>
                </ol>

                <div v-if="whatsappJoinUrl" class="pt-2">
                  <a
                    :href="whatsappJoinUrl"
                    target="_blank"
                    rel="noreferrer"
                    class="inline-flex w-full items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                  >
                    Solicitar entrada no WhatsApp
                  </a>
                </div>

                <Alert
                  class="mt-4 border-white/20 bg-white/10 text-white [&_[data-slot=alert-title]]:text-white [&_[data-slot=alert-description]]:text-white/90"
                  variant="default"
                >
                  <Info />
                  <AlertTitle>Importante</AlertTitle>
                  <AlertDescription>
                    A entrada no grupo só será liberada após o preenchimento do formulário.
                  </AlertDescription>
                </Alert>

                <p class="pt-2 text-zinc-300">
                  Vem com a gente voar alto de forma leve.
                </p>
              </div>
            </CardContent>
          </Card>

          <!-- Formulário -->
          <Card class="px-0 border-white/10 bg-white text-gray-900">
            <CardHeader class="px-6">
              <CardTitle class="text-xl">
                Seus dados
              </CardTitle>
              <CardDescription>
                Todos os campos são obrigatórios.
              </CardDescription>
            </CardHeader>

            <CardContent class="px-6">
              <Alert v-if="flashError" class="mb-6" variant="destructive">
                <XCircle />
                <AlertTitle>Não foi possível enviar</AlertTitle>
                <AlertDescription>
                  {{ flashError }}
                </AlertDescription>
              </Alert>

              <Alert v-else-if="flashWarning" class="mb-6" variant="warning">
                <AlertTriangle />
                <AlertTitle>Atenção</AlertTitle>
                <AlertDescription>
                  {{ flashWarning }}
                </AlertDescription>
              </Alert>

              <Alert v-else-if="flashSuccess" class="mb-6" variant="success">
                <CheckCircle2 />
                <AlertTitle>Inscrição enviada</AlertTitle>
                <AlertDescription>
                  {{ flashSuccess }}
                </AlertDescription>
              </Alert>

              <Alert v-if="showFormErrorAlert" class="mb-6" variant="destructive">
                <XCircle />
                <AlertTitle>Revise os campos</AlertTitle>
                <AlertDescription>
                  Alguns dados estão inválidos ou já foram usados. Confira os campos destacados abaixo e tente novamente.
                </AlertDescription>
              </Alert>

              <form class="space-y-5" @submit.prevent="submit">
                <div>
                  <Label for="name">Nome completo</Label>
                  <Input id="name" v-model="form.name" class="mt-1 w-full" autocomplete="name" required />
                  <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <div>
                  <Label for="birthdate">Data de nascimento</Label>
                  <Input id="birthdate" v-model="form.birthdate" type="date" class="mt-1 w-full" required />
                  <InputError :message="form.errors.birthdate" class="mt-2" />
                </div>

                <div>
                  <Label for="email">Seu melhor e-mail</Label>
                  <Input id="email" v-model="form.email" type="email" class="mt-1 w-full" autocomplete="email" required />
                  <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <div>
                  <Label for="whatsapp_number">Número de WhatsApp</Label>
                  <Input
                    id="whatsapp_number"
                    v-model="whatsappMasked"
                    class="mt-1 w-full"
                    inputmode="tel"
                    pattern="[(][0-9]{2}[)] ?[0-9]{4,5}-[0-9]{4}"
                    autocomplete="tel"
                    maxlength="15"
                    placeholder="Ex: (11) 98888-7777"
                    @beforeinput="onWhatsappBeforeInput"
                    @paste="onWhatsappPaste"
                    required
                  />
                  <p class="mt-2 text-xs text-gray-500">
                    Dica: informe seu número com DDD (a máscara é automática).
                  </p>
                  <InputError :message="form.errors.whatsapp_number" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <Label for="city">Cidade</Label>
                    <Input id="city" v-model="form.city" class="mt-1 w-full" required />
                    <InputError :message="form.errors.city" class="mt-2" />
                  </div>

                  <div>
                    <Label for="neighborhood">Bairro</Label>
                    <Input id="neighborhood" v-model="form.neighborhood" class="mt-1 w-full" required />
                    <InputError :message="form.errors.neighborhood" class="mt-2" />
                  </div>
                </div>

                <div>
                  <Label for="circle_url">Link do seu perfil na Jugular (Circle) ou do seu Padrinho(a)</Label>
                  <Input
                    id="circle_url"
                    v-model="form.circle_url"
                    class="mt-1 w-full"
                    type="url"
                    placeholder="https://comunidade.reservatoriodedopamina.com.br/u/..."
                    required
                  />
                  <InputError :message="form.errors.circle_url" class="mt-2" />
                </div>

                <div class="pt-2">
                  <Button type="submit" :disabled="form.processing" class="w-full">
                    {{ form.processing ? 'Enviando...' : 'Enviar inscrição' }}
                  </Button>
                  <p class="mt-3 text-xs text-gray-500">
                    Ao enviar, você confirma que os dados serão usados apenas para organização do time e do grupo.
                  </p>
                </div>
              </form>
            </CardContent>
          </Card>
        </div>
      </div>
    </main>
  </WebLayout>
</template>

