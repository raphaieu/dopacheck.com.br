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
import CardContent from '@/components/ui/card/CardContent.vue'
import Alert from '@/components/ui/alert/Alert.vue'
import AlertTitle from '@/components/ui/alert/AlertTitle.vue'
import AlertDescription from '@/components/ui/alert/AlertDescription.vue'

const props = defineProps({
  team: {
    type: Object,
    required: true,
  },
})

const schema = computed(() => Array.isArray(props.team?.form_schema) ? props.team.form_schema : [])

const initialForm = computed(() => {
  const obj = {}
  for (const field of schema.value) {
    const key = field?.key
    if (key) obj[key] = ''
  }
  return obj
})

const isCreateUser = computed(() => props.team?.onboarding_behavior === 'create_user')

const form = useForm({
  ...initialForm.value,
  terms_accepted: false,
})

const page = usePage()
const flashSuccess = computed(() => page.props.flash?.success)
const flashError = computed(() => page.props.flash?.error)
const flashWarning = computed(() => page.props.flash?.warning || page.props.flash?.message)
const teamName = computed(() => props.team?.name || 'Comunidade')
const onboardingTitle = computed(() => props.team?.onboarding_title || `Inscreva-se | ${teamName.value}`)
const whatsappJoinUrl = computed(() => props.team?.whatsapp_join_url || '')
const onboardingBody = computed(() => props.team?.onboarding_body || '')
const theme = computed(() => props.team?.theme || {})

const mainStyle = computed(() => {
  const primary = theme.value?.primary ?? 'hsl(220 70% 50%)'
  const bg = theme.value?.background ?? 'linear-gradient(to bottom right, #0f172a, #1e293b)'
  return {
    '--primary': primary,
    '--primary-foreground': 'hsl(0 0% 98%)',
    '--ring': primary,
    background: bg,
  }
})

function onlyDigits(value) {
  return String(value ?? '').replace(/\D+/g, '')
}

function formatBrPhoneFromDigits(digits) {
  const clean = onlyDigits(digits).slice(0, 11)
  if (clean === '') return ''
  const ddd = clean.slice(0, 2)
  const rest = clean.slice(2)
  let formatted = ''
  if (ddd.length > 0) formatted += `(${ddd}`
  if (ddd.length === 2) formatted += ') '
  const firstBlockLen = rest.length > 8 ? 5 : 4
  if (rest.length > firstBlockLen) formatted += `${rest.slice(0, firstBlockLen)}-${rest.slice(firstBlockLen)}`
  else formatted += rest
  return formatted.trim()
}

const telFields = computed(() => schema.value.filter(f => (f?.type ?? 'text') === 'tel').map(f => f.key))

function getTelMasked(key) {
  const raw = form[key]
  return formatBrPhoneFromDigits(onlyDigits(raw).slice(0, 11))
}

function setTelMasked(key, value) {
  form[key] = onlyDigits(value).slice(0, 11)
}

function onTelBeforeInput(key, event) {
  if (event?.data && /\D/.test(event.data)) event.preventDefault()
}

function onTelPaste(key, event) {
  event.preventDefault()
  const pasted = event?.clipboardData?.getData('text') ?? ''
  setTelMasked(key, pasted)
}

function inputType(field) {
  const t = field?.type ?? 'text'
  if (t === 'tel' || t === 'email' || t === 'url' || t === 'date') return t
  return 'text'
}

function submit() {
  form.post(route('teams.join.store', props.team.slug), { preserveScroll: true })
}

const showFormErrorAlert = computed(() => form.hasErrors && !flashError.value)
</script>

<template>
  <WebLayout>
    <main
      class="min-h-screen"
      :style="mainStyle"
    >
      <div class="mx-auto w-full max-w-5xl px-4 py-10">
        <div class="mb-8">
          <h1 class="text-3xl font-bold tracking-tight text-white">
            {{ onboardingTitle }}
          </h1>
          <p class="mt-2 text-zinc-300">
            Bem-vindo(a) – preencha o formulário para participar.
          </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
          <!-- Conteúdo / onboarding -->
          <Card class="px-0 border-white/10 bg-white/5 text-white">
            <CardHeader class="px-6">
              <CardTitle class="text-xl">
                {{ teamName }}
              </CardTitle>
              <CardDescription>
                Confira as informações abaixo e preencha seus dados ao lado.
              </CardDescription>
            </CardHeader>
            <CardContent class="px-6">
              <div v-if="onboardingBody" class="prose prose-invert max-w-none" v-html="onboardingBody" />
              <p v-else class="text-zinc-300">
                Preencha o formulário ao lado para se inscrever.
              </p>
              <div v-if="whatsappJoinUrl" class="pt-4">
                <a
                  :href="whatsappJoinUrl"
                  target="_blank"
                  rel="noreferrer"
                  class="inline-flex w-full items-center justify-center rounded-md px-4 py-2 text-sm font-semibold text-white [background-color:hsl(var(--primary))] hover:opacity-90"
                >
                  Entrar no grupo do WhatsApp
                </a>
              </div>
            </CardContent>
          </Card>

          <!-- Formulário dinâmico -->
          <Card class="px-0 border-white/10 bg-white text-gray-900">
            <CardHeader class="px-6">
              <CardTitle class="text-xl">
                Seus dados
              </CardTitle>
              <CardDescription>
                Preencha os campos abaixo.
              </CardDescription>
            </CardHeader>
            <CardContent class="px-6">
              <Alert v-if="flashError" class="mb-6" variant="destructive">
                <XCircle />
                <AlertTitle>Não foi possível enviar</AlertTitle>
                <AlertDescription>{{ flashError }}</AlertDescription>
              </Alert>
              <Alert v-else-if="flashWarning" class="mb-6" variant="warning">
                <AlertTriangle />
                <AlertTitle>Atenção</AlertTitle>
                <AlertDescription>{{ flashWarning }}</AlertDescription>
              </Alert>
              <Alert v-else-if="flashSuccess" class="mb-6" variant="success">
                <CheckCircle2 />
                <AlertTitle>Inscrição enviada</AlertTitle>
                <AlertDescription>{{ flashSuccess }}</AlertDescription>
              </Alert>
              <Alert v-if="showFormErrorAlert" class="mb-6" variant="destructive">
                <XCircle />
                <AlertTitle>Revise os campos</AlertTitle>
                <AlertDescription>
                  Alguns dados estão inválidos. Confira os campos destacados e tente novamente.
                </AlertDescription>
              </Alert>

              <form
                v-if="schema.length"
                class="space-y-5"
                @submit.prevent="submit"
              >
                <div
                  v-for="field in schema"
                  :key="field.key"
                >
                  <Label :for="field.key">{{ field.label || field.key }}</Label>
                  <template v-if="field.type === 'tel'">
                    <Input
                      :id="field.key"
                      :model-value="getTelMasked(field.key)"
                      class="mt-1 w-full"
                      inputmode="tel"
                      autocomplete="tel"
                      maxlength="15"
                      placeholder="(11) 98888-7777"
                      :required="!!field.required"
                      @update:model-value="setTelMasked(field.key, $event)"
                      @beforeinput="onTelBeforeInput(field.key, $event)"
                      @paste="onTelPaste(field.key, $event)"
                    />
                  </template>
                  <template v-else>
                    <Input
                      :id="field.key"
                      v-model="form[field.key]"
                      class="mt-1 w-full"
                      :type="inputType(field)"
                      :placeholder="field.placeholder ?? ''"
                      :required="!!field.required"
                      :autocomplete="field.type === 'email' ? 'email' : undefined"
                    />
                  </template>
                  <InputError :message="form.errors[field.key]" class="mt-2" />
                </div>

                <div
                  v-if="isCreateUser"
                  class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50/50 p-4"
                >
                  <input
                    id="terms_accepted"
                    v-model="form.terms_accepted"
                    type="checkbox"
                    class="mt-1 size-4 rounded border-gray-300"
                    required
                  />
                  <Label for="terms_accepted" class="cursor-pointer text-sm font-normal">
                    Aceito os
                    <a
                      href="/termos"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="underline hover:no-underline"
                      @click.stop
                    >termos e condições</a>
                    e o uso dos meus dados para esta comunidade.
                  </Label>
                </div>
                <InputError v-if="isCreateUser" :message="form.errors.terms_accepted" class="mt-2" />

                <div class="pt-2">
                  <Button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full"
                  >
                    {{ form.processing ? 'Enviando...' : 'Enviar inscrição' }}
                  </Button>
                </div>
              </form>
              <p
                v-else
                class="text-sm text-gray-500"
              >
                Formulário não configurado para esta comunidade.
              </p>
            </CardContent>
          </Card>
        </div>
      </div>
    </main>
  </WebLayout>
</template>
